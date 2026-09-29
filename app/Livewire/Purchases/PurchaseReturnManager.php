<?php

namespace App\Livewire\Purchases;

use App\Actions\Purchases\CreatePurchaseReturnAction;
use App\Models\Batch;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use Exception;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Purchase Returns & Debit Notes - PMS')]
class PurchaseReturnManager extends Component
{
    use WithPagination;

    public string $activeTab = 'create'; // 'create' or 'history'

    // Create Form State
    public ?int $selectedSupplierId = null;

    public ?int $selectedPurchaseId = null;

    public string $reason = '';

    public string $refundStatus = 'ADJUSTED'; // 'PENDING', 'ADJUSTED', 'REFUNDED'

    public array $items = []; // list of items to return

    public string $productSearch = '';

    public array $searchBatches = [];

    // History Tab State
    public string $historySearch = '';

    public int $perPage = 15;

    public function updatedProductSearch()
    {
        $term = trim($this->productSearch);
        if (strlen($term) < 2) {
            $this->searchBatches = [];

            return;
        }

        $this->searchBatches = Batch::with(['product.baseUnit'])
            ->where('current_stock', '>', 0)
            ->whereHas('product', function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhere('barcode', 'like', "%{$term}%");
            })
            ->latest('id')
            ->take(8)
            ->get()
            ->toArray();
    }

    public function addBatchToReturn(int $batchId)
    {
        $batch = Batch::with(['product.baseUnit'])->findOrFail($batchId);

        // Check if already in items
        foreach ($this->items as $item) {
            if ($item['batch_id'] === $batch->id) {
                return;
            }
        }

        $this->items[] = [
            'batch_id' => $batch->id,
            'product_id' => $batch->product_id,
            'product_name' => $batch->product->name,
            'batch_number' => $batch->batch_number,
            'expiry_date' => $batch->expiry_date?->format('d/m/Y') ?? 'N/A',
            'available_stock' => $batch->current_stock,
            'unit_id' => $batch->product->base_unit_id,
            'unit_name' => $batch->product->baseUnit?->name ?? 'Pcs',
            'quantity' => 1,
            'return_price_per_unit' => (float) $batch->purchase_rate,
        ];

        $this->productSearch = '';
        $this->searchBatches = [];
    }

    public function removeItem(int $index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function calculateTotalRefund(): float
    {
        $total = 0.00;
        foreach ($this->items as $item) {
            $qty = (int) ($item['quantity'] ?? 0);
            $price = (float) ($item['return_price_per_unit'] ?? 0);
            if ($qty > 0) {
                $total += ($qty * $price);
            }
        }

        return round($total, 2);
    }

    public function submitReturn(CreatePurchaseReturnAction $action)
    {
        $this->resetErrorBag();

        if (! $this->selectedSupplierId) {
            $this->addError('selectedSupplierId', 'Please select a supplier.');

            return;
        }

        if (empty($this->items)) {
            $this->addError('general', 'Please add at least one medicine batch to return.');

            return;
        }

        foreach ($this->items as $index => $item) {
            $qty = (int) ($item['quantity'] ?? 0);
            if ($qty <= 0) {
                $this->addError("items.{$index}.quantity", 'Quantity must be greater than 0.');

                return;
            }
            if ($qty > $item['available_stock']) {
                $this->addError("items.{$index}.quantity", "Return quantity cannot exceed available batch stock ({$item['available_stock']}).");

                return;
            }
        }

        try {
            $action->execute([
                'purchase_id' => $this->selectedPurchaseId,
                'supplier_id' => $this->selectedSupplierId,
                'branch_id' => auth()->user()?->branch_id,
                'user_id' => auth()->id(),
                'refund_status' => $this->refundStatus,
                'reason' => $this->reason,
                'items' => $this->items,
            ]);

            session()->flash('success', 'Purchase return created successfully and posted to supplier ledger!');
            $this->items = [];
            $this->reason = '';
            $this->activeTab = 'history';
        } catch (Exception $e) {
            $this->addError('general', $e->getMessage());
        }
    }

    public function render()
    {
        $historyQuery = PurchaseReturn::with(['supplier', 'user', 'items.product', 'items.batch'])
            ->when($this->historySearch, function ($q) {
                $term = '%'.trim($this->historySearch).'%';
                $q->where('return_no', 'like', $term)
                    ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', $term)->orWhere('company_name', 'like', $term));
            })
            ->latest('id');

        $returns = $historyQuery->paginate($this->perPage);

        return view('livewire.purchases.purchase-return-manager', [
            'returns' => $returns,
            'suppliers' => Supplier::orderBy('name')->get(),
            'totalRefundCalculated' => $this->calculateTotalRefund(),
        ]);
    }
}
