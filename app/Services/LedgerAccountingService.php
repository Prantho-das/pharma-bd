<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Supplier;
use App\Models\SupplierLedger;
use Illuminate\Support\Facades\DB;

class LedgerAccountingService
{
    /**
     * Record a transaction in the Customer Ledger and update current due.
     */
    public function recordCustomerTransaction(
        int $customerId,
        string $transactionType, // SALE, RETURN, DUE_COLLECTION, OPENING_BALANCE, ADJUSTMENT
        float $debit, // Increases customer debt (e.g. Sale)
        float $credit, // Decreases customer debt (e.g. Payment / Return)
        ?int $referenceId = null,
        ?string $note = null,
        ?int $branchId = null
    ): CustomerLedger {
        return DB::transaction(function () use ($customerId, $transactionType, $debit, $credit, $referenceId, $note, $branchId) {
            $customer = Customer::lockForUpdate()->findOrFail($customerId);

            // Customer Due = Opening Due + (Debits - Credits)
            $newDue = (float) $customer->current_due + ($debit - $credit);
            $customer->current_due = max(0, $newDue);
            $customer->save();

            return CustomerLedger::create([
                'branch_id' => $branchId ?? $customer->branch_id,
                'customer_id' => $customer->id,
                'transaction_type' => $transactionType,
                'reference_id' => $referenceId,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $customer->current_due,
                'note' => $note,
                'transaction_date' => now(),
            ]);
        });
    }

    /**
     * Record a transaction in the Supplier Ledger and update supplier due.
     */
    public function recordSupplierTransaction(
        int $supplierId,
        string $transactionType, // PURCHASE, RETURN, PAYMENT, OPENING_BALANCE, ADJUSTMENT
        float $debit, // Decreases our payable (e.g. Payment to supplier / Return)
        float $credit, // Increases our payable (e.g. Purchase invoice)
        ?int $referenceId = null,
        ?string $note = null,
        ?int $branchId = null
    ): SupplierLedger {
        return DB::transaction(function () use ($supplierId, $transactionType, $debit, $credit, $referenceId, $note, $branchId) {
            $supplier = Supplier::lockForUpdate()->findOrFail($supplierId);

            // Payable = Opening Due + (Credits - Debits)
            $newPayable = (float) $supplier->current_due + ($credit - $debit);
            $supplier->current_due = max(0, $newPayable);
            $supplier->save();

            return SupplierLedger::create([
                'branch_id' => $branchId,
                'supplier_id' => $supplier->id,
                'transaction_type' => $transactionType,
                'reference_id' => $referenceId,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $supplier->current_due,
                'note' => $note,
                'transaction_date' => now(),
            ]);
        });
    }
}
