<?php

namespace App\Livewire\Purchases;

use App\Models\Purchase;
use App\Models\Supplier;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Procurement & Purchase Invoices - PMS')]
class PurchaseTable extends Component
{
    use WithPagination;

    public string $search = '';

    public string $selectedSupplier = '';

    public string $paymentStatus = 'all';

    public int $perPage = 15;

    public function render()
    {
        $query = Purchase::with(['supplier', 'items.product', 'branch'])
            ->when($this->search, function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where('purchase_no', 'like', $term)
                    ->orWhere('supplier_invoice_no', 'like', $term)
                    ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', $term)->orWhere('company_name', 'like', $term));
            })
            ->when($this->selectedSupplier, fn ($q) => $q->where('supplier_id', $this->selectedSupplier))
            ->when($this->paymentStatus !== 'all', fn ($q) => $q->where('payment_status', $this->paymentStatus))
            ->latest('id');

        $purchases = $query->paginate($this->perPage);

        return view('livewire.purchases.purchase-table', [
            'purchases' => $purchases,
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }
}
