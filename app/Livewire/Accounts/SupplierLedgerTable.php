<?php

namespace App\Livewire\Accounts;

use App\Models\Supplier;
use App\Models\SupplierLedger;
use App\Services\LedgerAccountingService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Supplier Accounts & Payables Ledger - PMS')]
class SupplierLedgerTable extends Component
{
    use WithPagination;

    public ?int $selectedSupplierId = null;

    public string $search = '';

    public int $perPage = 15;

    // Supplier Payment Modal
    public bool $showPaymentModal = false;

    public float $payAmount = 0.00;

    public string $paymentMethod = 'BANK_TRANSFER';

    public string $paymentNote = '';

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    public function openPayModal(int $supplierId)
    {
        $this->selectedSupplierId = $supplierId;
        $supplier = Supplier::findOrFail($supplierId);
        $this->payAmount = (float) $supplier->current_due;
        $this->paymentMethod = 'BANK_TRANSFER';
        $this->paymentNote = 'Supplier Bill Settlement';
        $this->showPaymentModal = true;
    }

    public function saveSupplierPayment(LedgerAccountingService $ledgerService)
    {
        $this->validate([
            'payAmount' => 'required|numeric|min:1',
            'selectedSupplierId' => 'required|exists:suppliers,id',
        ]);

        try {
            $ledgerService->recordSupplierTransaction(
                supplierId: $this->selectedSupplierId,
                transactionType: 'PAYMENT',
                debit: $this->payAmount, // Debit reduces supplier payable
                credit: 0.00,
                referenceId: null,
                note: $this->paymentNote." via {$this->paymentMethod}",
                branchId: Auth::user()?->branch_id
            );

            $this->successMessage = 'Paid ৳'.number_format($this->payAmount, 2).' to supplier successfully!';
            $this->showPaymentModal = false;
        } catch (Exception $e) {
            $this->errorMessage = 'Payment failed: '.$e->getMessage();
        }
    }

    public function render()
    {
        $suppliers = Supplier::when($this->search, function ($q) {
            $term = '%'.trim($this->search).'%';
            $q->where('name', 'like', $term)->orWhere('company_name', 'like', $term);
        })
            ->orderBy('name')
            ->paginate($this->perPage);

        $selectedSupplier = $this->selectedSupplierId ? Supplier::find($this->selectedSupplierId) : null;
        $ledgerEntries = $this->selectedSupplierId ? SupplierLedger::where('supplier_id', $this->selectedSupplierId)->latest('transaction_date')->paginate(10) : null;

        return view('livewire.accounts.supplier-ledger-table', [
            'suppliers' => $suppliers,
            'selectedSupplier' => $selectedSupplier,
            'ledgerEntries' => $ledgerEntries,
            'totalPayable' => Supplier::sum('current_due'),
        ]);
    }
}
