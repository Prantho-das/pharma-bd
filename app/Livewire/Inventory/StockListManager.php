<?php

namespace App\Livewire\Inventory;

use App\Models\Product;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Stock List - PMS')]
class StockListManager extends Component
{
    use WithPagination;

    public string $view = 'all'; // 'all' or 'low'

    public string $search = '';

    public int $perPage = 15;

    public function mount()
    {
        if (request()->query('alert_qty')) {
            $this->view = 'low';
        }
    }

    public function render()
    {
        $query = Product::with(['baseUnit', 'category', 'manufacturer', 'batches'])
            ->withSum('batches as total_stock', 'current_stock')
            ->when($this->search, function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where('name', 'like', $term)
                    ->orWhere('sku', 'like', $term)
                    ->orWhere('barcode', 'like', $term);
            })
            ->when($this->view === 'low', function ($q) {
                $q->whereRaw('(SELECT COALESCE(SUM(current_stock), 0) FROM batches WHERE batches.product_id = products.id) <= products.min_stock_alert');
            })
            ->orderBy('name');

        $stocks = $query->paginate($this->perPage);

        return view('livewire.inventory.stock-list-manager', [
            'stocks' => $stocks,
        ]);
    }
}
