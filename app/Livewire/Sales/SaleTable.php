<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Sale List - PMS')]
class SaleTable extends Component
{
    use WithPagination;

    public string $search = '';

    public string $paymentStatus = 'all';

    public int $perPage = 15;

    public function render()
    {
        $sales = Sale::with(['customer', 'branch', 'doctor', 'user', 'items.product'])
            ->when($this->search, function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where('invoice_no', 'like', $term)
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $term)->orWhere('phone', 'like', $term));
            })
            ->when($this->paymentStatus !== 'all', fn ($q) => $q->where('payment_status', $this->paymentStatus))
            ->latest('id')
            ->paginate($this->perPage);

        return view('livewire.sales.sale-table', [
            'sales' => $sales,
        ]);
    }
}
