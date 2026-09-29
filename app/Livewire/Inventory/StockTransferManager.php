<?php

namespace App\Livewire\Inventory;

use App\Actions\Inventory\CreateStockTransferAction;
use App\Actions\Inventory\ReceiveStockTransferAction;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\StockTransfer;
use Exception;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Inter-Branch Stock Transfers - PMS')]
class StockTransferManager extends Component
{
    use WithPagination;

    public string $activeTab = 'dispatch'; // 'dispatch', 'incoming', 'history'

    // Dispatch Form State
    public ?int $fromBranchId = null;

    public ?int $toBranchId = null;

    public string $notes = '';

    public array $items = [];

    public string $productSearch = '';

    public array $searchBatches = [];

    // Filter & Search
    public string $historySearch = '';

    public int $perPage = 15;

    public function mount()
    {
        $this->fromBranchId = auth()->user()?->branch_id ?? Branch::first()?->id;
    }

    public function updatedProductSearch()
    {
        $term = trim($this->productSearch);
        if (strlen($term) < 2) {
            $this->searchBatches = [];

            return;
        }

        $this->searchBatches = Batch::with(['product.baseUnit'])
            ->where('branch_id', $this->fromBranchId)
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

    public function addBatch(int $batchId)
    {
        $batch = Batch::with(['product.baseUnit'])->findOrFail($batchId);

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
        ];

        $this->productSearch = '';
        $this->searchBatches = [];
    }

    public function removeItem(int $index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function dispatchTransfer(CreateStockTransferAction $action)
    {
        $this->resetErrorBag();

        if (! $this->fromBranchId || ! $this->toBranchId) {
            $this->addError('toBranchId', 'Please select both source and destination branches.');

            return;
        }

        if ($this->fromBranchId === $this->toBranchId) {
            $this->addError('toBranchId', 'Destination branch cannot be the same as source branch.');

            return;
        }

        if (empty($this->items)) {
            $this->addError('general', 'Please add at least one medicine batch to transfer.');

            return;
        }

        foreach ($this->items as $index => $item) {
            $qty = (int) ($item['quantity'] ?? 0);
            if ($qty <= 0) {
                $this->addError("items.{$index}.quantity", 'Quantity must be greater than 0.');

                return;
            }
            if ($qty > $item['available_stock']) {
                $this->addError("items.{$index}.quantity", "Quantity exceeds available stock ({$item['available_stock']}).");

                return;
            }
        }

        try {
            $action->execute([
                'from_branch_id' => $this->fromBranchId,
                'to_branch_id' => $this->toBranchId,
                'sent_by' => auth()->id(),
                'notes' => $this->notes,
                'items' => $this->items,
            ]);

            session()->flash('success', 'Stock transfer dispatched successfully and marked IN_TRANSIT!');
            $this->items = [];
            $this->notes = '';
            $this->activeTab = 'history';
        } catch (Exception $e) {
            $this->addError('general', $e->getMessage());
        }
    }

    public function handleTransfer(int $transferId, string $status, ReceiveStockTransferAction $action)
    {
        try {
            $transfer = StockTransfer::findOrFail($transferId);
            $action->execute($transfer, $status, auth()->id());
            session()->flash('success', "Stock transfer {$transfer->transfer_no} marked as {$status}.");
        } catch (Exception $e) {
            $this->addError('general', $e->getMessage());
        }
    }

    public function render()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        $incomingTransfers = StockTransfer::with(['fromBranch', 'sentBy', 'items.product', 'items.batch'])
            ->where('to_branch_id', $this->fromBranchId)
            ->whereIn('status', ['PENDING', 'IN_TRANSIT'])
            ->latest('id')
            ->get();

        $historyTransfers = StockTransfer::with(['fromBranch', 'toBranch', 'sentBy', 'receivedBy', 'items.product'])
            ->when($this->historySearch, function ($q) {
                $term = '%'.trim($this->historySearch).'%';
                $q->where('transfer_no', 'like', $term)
                    ->orWhereHas('fromBranch', fn ($b) => $b->where('name', 'like', $term))
                    ->orWhereHas('toBranch', fn ($b) => $b->where('name', 'like', $term));
            })
            ->latest('id')
            ->paginate($this->perPage);

        return view('livewire.inventory.stock-transfer-manager', [
            'branches' => $branches,
            'incomingTransfers' => $incomingTransfers,
            'historyTransfers' => $historyTransfers,
        ]);
    }
}
