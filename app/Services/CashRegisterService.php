<?php

namespace App\Services;

use App\Models\CashRegister;
use App\Models\Expense;
use App\Models\SalePayment;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class CashRegisterService
{
    /**
     * Get the active open cash register shift for a user.
     */
    public function getActiveShift(int $userId, ?int $branchId = null): ?CashRegister
    {
        return CashRegister::where('user_id', $userId)
            ->where('status', 'OPEN')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->latest('opened_at')
            ->first();
    }

    /**
     * Open a new shift with starting cash float.
     *
     * @throws Exception
     */
    public function openShift(int $userId, float $openingCash, ?int $branchId = null, ?string $note = null): CashRegister
    {
        $existing = $this->getActiveShift($userId, $branchId);
        if ($existing) {
            throw new Exception('An open cash register shift already exists for this counter.');
        }

        return CashRegister::create([
            'branch_id' => $branchId,
            'user_id' => $userId,
            'opened_at' => now(),
            'opening_cash' => $openingCash,
            'status' => 'OPEN',
            'note' => $note,
        ]);
    }

    /**
     * Calculate current shift reconciliation figures.
     */
    public function calculateShiftTotals(CashRegister $shift): array
    {
        $start = $shift->opened_at;
        $end = $shift->closed_at ?? now();

        $cashSales = SalePayment::where('received_by', $shift->user_id)
            ->where('payment_method', 'CASH')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('amount');

        $cardSales = SalePayment::where('received_by', $shift->user_id)
            ->where('payment_method', 'CARD')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('amount');

        $mfsSales = SalePayment::where('received_by', $shift->user_id)
            ->whereIn('payment_method', ['BKASH', 'NAGAD', 'ROCKET'])
            ->whereBetween('payment_date', [$start, $end])
            ->sum('amount');

        $cashExpenses = Expense::where('user_id', $shift->user_id)
            ->where('payment_method', 'CASH')
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        $expectedCash = (float) $shift->opening_cash + (float) $cashSales - (float) $cashExpenses;

        return [
            'opening_cash' => (float) $shift->opening_cash,
            'cash_sales' => (float) $cashSales,
            'card_sales' => (float) $cardSales,
            'mfs_sales' => (float) $mfsSales,
            'cash_expenses' => (float) $cashExpenses,
            'expected_closing_cash' => round($expectedCash, 2),
        ];
    }

    /**
     * Close a shift, reconcile physical cash, and lock the register.
     */
    public function closeShift(CashRegister $shift, float $physicalClosingCash, ?string $note = null): CashRegister
    {
        return DB::transaction(function () use ($shift, $physicalClosingCash, $note) {
            $shift = CashRegister::lockForUpdate()->findOrFail($shift->id);

            $totals = $this->calculateShiftTotals($shift);
            $difference = round($physicalClosingCash - $totals['expected_closing_cash'], 2);

            $shift->update([
                'closed_at' => now(),
                'closing_cash' => $physicalClosingCash,
                'total_cash_sales' => $totals['cash_sales'],
                'total_card_sales' => $totals['card_sales'],
                'total_mfs_sales' => $totals['mfs_sales'],
                'total_cash_expenses' => $totals['cash_expenses'],
                'expected_closing_cash' => $totals['expected_closing_cash'],
                'difference' => $difference,
                'status' => 'CLOSED',
                'note' => $note ? ($shift->note ? $shift->note.' | '.$note : $note) : $shift->note,
            ]);

            return $shift;
        });
    }
}
