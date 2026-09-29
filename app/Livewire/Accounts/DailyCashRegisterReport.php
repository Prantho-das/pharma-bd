<?php

namespace App\Livewire\Accounts;

use App\Models\CashRegister;
use App\Services\CashRegisterService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Counter Cash Register Shifts & Z-Report - PMS')]
class DailyCashRegisterReport extends Component
{
    use WithPagination;

    // Shift Opening Modal
    public bool $showOpenShiftModal = false;

    public float $openingCash = 0.00;

    public string $openNote = '';

    // Shift Closing Modal
    public bool $showCloseShiftModal = false;

    public float $physicalClosingCash = 0.00;

    public string $closeNote = '';

    // Active shift stats
    public ?CashRegister $activeShift = null;

    public array $activeShiftTotals = [];

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    public function mount(CashRegisterService $registerService)
    {
        $this->refreshActiveShift($registerService);
    }

    public function refreshActiveShift(CashRegisterService $registerService)
    {
        $this->activeShift = $registerService->getActiveShift(Auth::id() ?? 1, Auth::user()?->branch_id);
        if ($this->activeShift) {
            $this->activeShiftTotals = $registerService->calculateShiftTotals($this->activeShift);
            $this->physicalClosingCash = $this->activeShiftTotals['expected_closing_cash'];
        }
    }

    public function openShift(CashRegisterService $registerService)
    {
        $this->errorMessage = null;
        try {
            $registerService->openShift(
                userId: Auth::id() ?? 1,
                openingCash: $this->openingCash,
                branchId: Auth::user()?->branch_id,
                note: $this->openNote
            );

            $this->showOpenShiftModal = false;
            $this->successMessage = 'Cash register shift opened successfully with ৳'.number_format($this->openingCash, 2).' float!';
            $this->refreshActiveShift($registerService);
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function closeShift(CashRegisterService $registerService)
    {
        $this->errorMessage = null;
        if (! $this->activeShift) {
            return;
        }

        try {
            $closed = $registerService->closeShift(
                shift: $this->activeShift,
                physicalClosingCash: $this->physicalClosingCash,
                note: $this->closeNote
            );

            $this->showCloseShiftModal = false;
            $this->successMessage = 'Register locked & closed. Discrepancy: ৳'.number_format($closed->difference, 2);
            $this->refreshActiveShift($registerService);
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function render(CashRegisterService $registerService)
    {
        $this->refreshActiveShift($registerService);

        $pastShifts = CashRegister::with(['user', 'branch'])
            ->latest('opened_at')
            ->paginate(10);

        return view('livewire.accounts.daily-cash-register-report', [
            'pastShifts' => $pastShifts,
        ]);
    }
}
