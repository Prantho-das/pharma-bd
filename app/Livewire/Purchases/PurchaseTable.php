<?php

namespace App\Livewire\Purchases;

use App\Models\Batch;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchasePayment;
use App\Models\Supplier;
use App\Services\LedgerAccountingService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Procurement & Purchase Invoices - PMS')]
class PurchaseTable extends Component
{
    use WithPagination;

    // Filters & Search
    public string $search = '';
    public string $selectedSupplier = '';
    public string $paymentStatus = 'all';
    public string $agingFilter = 'all'; // 'all', '0-30', '31-60', '61-90', '90+'
    public int $perPage = 15;

    // Flash / Feedback Messages
    public ?string $feedbackMessage = null;
    public ?string $errorMessage = null;

    // 1. Invoice / View GRN Modal State
    public bool $showInvoiceModal = false;
    public ?Purchase $viewingPurchase = null;

    // 2. Split / Multiple Installment Payment Modal State
    public bool $showPaymentModal = false;
    public ?Purchase $paymentPurchase = null;
    public float $newPaymentAmount = 0.00;
    public string $newPaymentMethod = 'CASH'; // CASH, BANK_TRANSFER, CHEQUE, BKASH, NAGAD, ROCKET
    public string $newPaymentDate = '';
    public string $newPaymentRef = '';
    public string $newPaymentNotes = '';

    // 3. Edit Purchase Modal State
    public bool $showEditModal = false;
    public ?int $editPurchaseId = null;
    public string $editSupplierInvoiceNo = '';
    public string $editInvoiceDate = '';
    public string $editDeliveryDate = '';
    public string $editNotes = '';

    // 4. Delete Purchase Modal State
    public bool $showDeleteModal = false;
    public ?int $deletePurchaseId = null;
    public ?string $deletePurchaseNo = null;

    public function mount()
    {
        $this->newPaymentDate = now()->toDateString();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedSupplier()
    {
        $this->resetPage();
    }

    public function updatingPaymentStatus()
    {
        $this->resetPage();
    }

    public function updatingAgingFilter()
    {
        $this->resetPage();
    }

    /**
     * View full purchase invoice details
     */
    public function viewInvoice(int $id)
    {
        $this->viewingPurchase = Purchase::with(['supplier', 'items.product', 'items.unit', 'payments.creator', 'branch'])->findOrFail($id);
        $this->showInvoiceModal = true;
    }

    /**
     * Open Split Payment Modal
     */
    public function openPaymentModal(int $id)
    {
        $this->paymentPurchase = Purchase::with(['supplier', 'payments.creator'])->findOrFail($id);
        $this->newPaymentAmount = (float) $this->paymentPurchase->due_amount;
        $this->newPaymentMethod = 'CASH';
        $this->newPaymentDate = now()->toDateString();
        $this->newPaymentRef = '';
        $this->newPaymentNotes = '';
        $this->showPaymentModal = true;
    }

    /**
     * Record a new installment / split payment for purchase
     */
    public function recordSplitPayment(LedgerAccountingService $ledgerService)
    {
        if (! Auth::user()->can('manage_purchase_payments') && ! Auth::user()->can('edit_purchases')) {
            $this->errorMessage = 'আপনার পারচেজ পেমেন্ট যুক্ত করার অনুমতি নেই।';
            return;
        }

        $this->validate([
            'newPaymentAmount' => 'required|numeric|min:0.01',
            'newPaymentMethod' => 'required|string',
            'newPaymentDate' => 'required|date',
        ]);

        if (! $this->paymentPurchase) {
            return;
        }

        $purchase = Purchase::findOrFail($this->paymentPurchase->id);
        $amount = round((float) $this->newPaymentAmount, 2);

        if ($amount > (float) $purchase->due_amount) {
            $this->errorMessage = "পেমেন্টের পরিমাণ বকেয়া (৳" . number_format($purchase->due_amount, 2) . ") থেকে বেশি হতে পারবে না।";
            return;
        }

        DB::transaction(function () use ($purchase, $amount, $ledgerService) {
            // 1. Create Purchase Payment Record
            PurchasePayment::create([
                'purchase_id' => $purchase->id,
                'branch_id' => $purchase->branch_id,
                'supplier_id' => $purchase->supplier_id,
                'payment_method' => $this->newPaymentMethod,
                'amount' => $amount,
                'payment_date' => $this->newPaymentDate,
                'transaction_reference' => $this->newPaymentRef,
                'notes' => $this->newPaymentNotes ?: 'Split installment payment',
                'created_by' => Auth::id(),
            ]);

            // 2. Update Purchase Totals
            $newPaid = round((float) $purchase->paid_amount + $amount, 2);
            $newDue = round(max(0, (float) $purchase->grand_total - $newPaid), 2);
            $paymentStatus = $newDue <= 0 ? 'PAID' : ($newPaid > 0 ? 'PARTIAL' : 'DUE');

            $purchase->update([
                'paid_amount' => $newPaid,
                'due_amount' => $newDue,
                'payment_status' => $paymentStatus,
            ]);

            // 3. Record in Supplier Ledger (Debit reduces payable)
            $ledgerService->recordSupplierTransaction(
                supplierId: $purchase->supplier_id,
                transactionType: 'PAYMENT',
                debit: $amount,
                credit: 0.00,
                referenceId: $purchase->id,
                note: "Installment Payment for Purchase #{$purchase->purchase_no} via {$this->newPaymentMethod}" . ($this->newPaymentRef ? " (Ref: {$this->newPaymentRef})" : ''),
                branchId: $purchase->branch_id
            );
        });

        $this->showPaymentModal = false;
        $this->feedbackMessage = "৳" . number_format($amount, 2) . " কিস্তি পেমেন্ট সফলভাবে এন্ট্রি হয়েছে!";
        $this->paymentPurchase = null;
    }

    /**
     * Open Edit Purchase Modal
     */
    public function openEditModal(int $id)
    {
        $p = Purchase::findOrFail($id);
        $this->editPurchaseId = $p->id;
        $this->editSupplierInvoiceNo = $p->supplier_invoice_no ?? '';
        $this->editInvoiceDate = $p->invoice_date ? $p->invoice_date->format('Y-m-d') : '';
        $this->editDeliveryDate = $p->delivery_date ? $p->delivery_date->format('Y-m-d') : '';
        $this->editNotes = $p->notes ?? '';
        $this->showEditModal = true;
    }

    /**
     * Save edited purchase header details
     */
    public function updatePurchase()
    {
        if (! Auth::user()->can('edit_purchases')) {
            $this->errorMessage = 'আপনার পারচেজ এডিট করার অনুমতি নেই।';
            return;
        }

        $this->validate([
            'editInvoiceDate' => 'required|date',
        ]);

        $purchase = Purchase::findOrFail($this->editPurchaseId);
        $purchase->update([
            'supplier_invoice_no' => $this->editSupplierInvoiceNo,
            'invoice_date' => $this->editInvoiceDate,
            'delivery_date' => $this->editDeliveryDate ?: null,
            'notes' => $this->editNotes,
        ]);

        $this->showEditModal = false;
        $this->feedbackMessage = "পারচেজ #{$purchase->purchase_no} এর তথ্য সফলভাবে আপডেট হয়েছে!";
    }

    /**
     * Open Delete Confirmation Modal
     */
    public function confirmDelete(int $id)
    {
        $p = Purchase::findOrFail($id);
        $this->deletePurchaseId = $p->id;
        $this->deletePurchaseNo = $p->purchase_no;
        $this->showDeleteModal = true;
    }

    /**
     * Delete purchase, reverse batches, and reverse ledger
     */
    public function deletePurchase(LedgerAccountingService $ledgerService)
    {
        if (! Auth::user()->can('delete_purchases')) {
            $this->errorMessage = 'আপনার পারচেজ ডিলিট করার অনুমতি নেই।';
            return;
        }

        if (! $this->deletePurchaseId) {
            return;
        }

        $purchase = Purchase::with(['items', 'payments'])->findOrFail($this->deletePurchaseId);

        DB::transaction(function () use ($purchase, $ledgerService) {
            // 1. Revert Inventory Batches created by this purchase
            foreach ($purchase->items as $item) {
                Batch::where('purchase_item_id', $item->id)->delete();
            }

            // 2. Reverse Supplier Ledger Transactions
            if ((float) $purchase->grand_total > 0) {
                $ledgerService->recordSupplierTransaction(
                    supplierId: $purchase->supplier_id,
                    transactionType: 'ADJUSTMENT',
                    debit: (float) $purchase->grand_total, // Reverse bill
                    credit: (float) $purchase->paid_amount, // Reverse paid
                    referenceId: $purchase->id,
                    note: "Reversal due to Deletion of Purchase #{$purchase->purchase_no}",
                    branchId: $purchase->branch_id
                );
            }

            // 3. Delete Purchase Record (cascade handles items and payments)
            $purchase->delete();
        });

        $this->showDeleteModal = false;
        $this->feedbackMessage = "পারচেজ #{$this->deletePurchaseNo} সফলভাবে বাতিল ও মুছে ফেলা হয়েছে!";
        $this->deletePurchaseId = null;
    }

    public function render()
    {
        $query = Purchase::with(['supplier', 'items.product', 'branch', 'payments'])
            ->when($this->search, function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('purchase_no', 'like', $term)
                        ->orWhere('supplier_invoice_no', 'like', $term)
                        ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', $term)->orWhere('company_name', 'like', $term));
                });
            })
            ->when($this->selectedSupplier, fn ($q) => $q->where('supplier_id', $this->selectedSupplier))
            ->when($this->paymentStatus !== 'all', fn ($q) => $q->where('payment_status', $this->paymentStatus))
            ->latest('id');

        // Apply Aging Filter
        if ($this->agingFilter !== 'all') {
            $now = Carbon::now();
            if ($this->agingFilter === '0-30') {
                $query->whereDate('invoice_date', '>=', $now->copy()->subDays(30)->toDateString());
            } elseif ($this->agingFilter === '31-60') {
                $query->whereDate('invoice_date', '<', $now->copy()->subDays(30)->toDateString())
                      ->whereDate('invoice_date', '>=', $now->copy()->subDays(60)->toDateString());
            } elseif ($this->agingFilter === '61-90') {
                $query->whereDate('invoice_date', '<', $now->copy()->subDays(60)->toDateString())
                      ->whereDate('invoice_date', '>=', $now->copy()->subDays(90)->toDateString());
            } elseif ($this->agingFilter === '90+') {
                $query->whereDate('invoice_date', '<', $now->copy()->subDays(90)->toDateString());
            }
        }

        $purchases = $query->paginate($this->perPage);

        return view('livewire.purchases.purchase-table', [
            'purchases' => $purchases,
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }
}
