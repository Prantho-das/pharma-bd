<?php

namespace App\Livewire\Inventory;

use App\Models\Batch;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Batch Inventory & FEFO Expiry Tracking - PMS')]
class BatchTable extends Component
{
    use WithPagination;

    public string $search = '';

    public string $expiryFilter = 'all'; // all, near_expiry, expired, active

    public int $perPage = 15;

    public function render()
    {
        $query = Batch::with(['product.baseUnit', 'product.generic', 'branch'])
            ->when($this->search, function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where('batch_number', 'like', $term)
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', $term)->orWhere('sku', 'like', $term));
            })
            ->when($this->expiryFilter === 'near_expiry', fn ($q) => $q->nearExpiry(90))
            ->when($this->expiryFilter === 'expired', fn ($q) => $q->expired())
            ->when($this->expiryFilter === 'active', fn ($q) => $q->available())
            ->orderBy('expiry_date', 'asc');

        $batches = $query->paginate($this->perPage);

        return view('livewire.inventory.batch-table', [
            'batches' => $batches,
            'totalActiveBatches' => Batch::available()->count(),
            'totalNearExpiry' => Batch::nearExpiry(90)->count(),
            'totalExpired' => Batch::expired()->count(),
        ]);
    }
}
