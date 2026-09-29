<?php

namespace App\Livewire\Inventory;

use App\Models\Batch;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Barcode & Shelf Label Generator - PMS')]
class BarcodeGenerator extends Component
{
    public string $search = '';

    public array $searchResults = [];

    public ?int $selectedBatchId = null;

    public ?Batch $selectedBatch = null;

    // Label Settings
    public string $pharmacyName = 'Pharmacy PMS';

    public string $labelSize = '50x25'; // '50x25', '38x25', 'a4-24', 'a4-30'

    public int $labelCount = 10;

    public bool $showPharmacyName = true;

    public bool $showGeneric = true;

    public bool $showBatchNo = true;

    public bool $showExpiry = true;

    public bool $showPrice = true;

    public bool $showBarcodeText = true;

    public function updatedSearch()
    {
        $term = trim($this->search);
        if (strlen($term) < 2) {
            $this->searchResults = [];

            return;
        }

        $this->searchResults = Batch::with(['product.generic'])
            ->where('current_stock', '>', 0)
            ->whereHas('product', function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhere('barcode', 'like', "%{$term}%");
            })
            ->take(8)
            ->get()
            ->toArray();
    }

    public function selectBatch(int $batchId)
    {
        $this->selectedBatch = Batch::with(['product.generic', 'product.manufacturer'])->findOrFail($batchId);
        $this->selectedBatchId = $this->selectedBatch->id;
        $this->searchResults = [];
        $this->search = $this->selectedBatch->product->name.' ('.$this->selectedBatch->batch_number.')';
    }

    public function render()
    {
        return view('livewire.inventory.barcode-generator', [
            'batch' => $this->selectedBatch,
        ]);
    }
}
