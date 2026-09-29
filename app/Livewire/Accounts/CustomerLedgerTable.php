<?php

namespace App\Livewire\Accounts;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Services\LedgerAccountingService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Customer Accounts & Due Ledgers - PMS')]
class CustomerLedgerTable extends Component
{
    use WithPagination;

    public ?int $selectedCustomerId = null;

    public string $search = '';

    public int $perPage = 15;

    // Due Collection Modal
    public bool $showPaymentModal = false;

    public float $collectAmount = 0.00;

    public string $paymentMethod = 'CASH';

    public string $paymentNote = '';

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    public function openCollectDueModal(int $customerId)
    {
        $this->selectedCustomerId = $customerId;
        $customer = Customer::findOrFail($customerId);
        $this->collectAmount = (float) $customer->current_due;
        $this->paymentMethod = 'CASH';
        $this->paymentNote = 'Counter Due Collection';
        $this->showPaymentModal = true;
    }

    public function saveDueCollection(LedgerAccountingService $ledgerService)
    {
        $this->validate([
            'collectAmount' => 'required|numeric|min:1',
            'selectedCustomerId' => 'required|exists:customers,id',
        ]);

        try {
            $ledgerService->recordCustomerTransaction(
                customerId: $this->selectedCustomerId,
                transactionType: 'DUE_COLLECTION',
                debit: 0.00,
                credit: $this->collectAmount, // Credit reduces customer due
                referenceId: null,
                note: $this->paymentNote." via {$this->paymentMethod}",
                branchId: Auth::user()?->branch_id
            );

            $this->successMessage = 'Collected ৳'.number_format($this->collectAmount, 2).' successfully!';
            $this->showPaymentModal = false;
        } catch (Exception $e) {
            $this->errorMessage = 'Error collecting due: '.$e->getMessage();
        }
    }

    public function render()
    {
        $customers = Customer::when($this->search, function ($q) {
            $term = '%'.trim($this->search).'%';
            $q->where('name', 'like', $term)->orWhere('phone', 'like', $term);
        })
            ->orderBy('name')
            ->paginate($this->perPage);

        $selectedCustomer = $this->selectedCustomerId ? Customer::with('ledgers')->find($this->selectedCustomerId) : null;
        $ledgerEntries = $this->selectedCustomerId ? CustomerLedger::where('customer_id', $this->selectedCustomerId)->latest('transaction_date')->paginate(10) : null;

        return view('livewire.accounts.customer-ledger-table', [
            'customers' => $customers,
            'selectedCustomer' => $selectedCustomer,
            'ledgerEntries' => $ledgerEntries,
            'totalDueReceivable' => Customer::sum('current_due'),
        ]);
    }
}
