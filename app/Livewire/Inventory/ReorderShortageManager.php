<?php

namespace App\Livewire\Inventory;

use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Product;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Auto Reorder & Medicine Shortage List - PMS')]
class ReorderShortageManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $selectedManufacturer = '';

    public string $stockFilter = 'all'; // all, zero_stock, low_stock

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    // Custom reorder quantities state
    public array $reorderQuantities = [];

    public array $selectedProductIds = [];

    public bool $selectAll = false;

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedProductIds = $this->getLowStockQuery()->pluck('id')->map(fn ($id) => (int) $id)->toArray();
        } else {
            $this->selectedProductIds = [];
        }
    }

    private function getLowStockQuery()
    {
        return Product::with(['category', 'manufacturer', 'baseUnit', 'productUnits'])
            ->where('status', 'active')
            ->where(function ($q) {
                if ($this->search) {
                    $term = '%'.trim($this->search).'%';
                    $q->where('name', 'like', $term)
                        ->orWhere('sku', 'like', $term)
                        ->orWhere('barcode', 'like', $term)
                        ->orWhereHas('generic', fn ($g) => $g->where('name', 'like', $term));
                }
            })
            ->when($this->selectedManufacturer, fn ($q) => $q->where('manufacturer_id', $this->selectedManufacturer))
            ->get()
            ->filter(function ($prod) {
                $stock = $prod->totalStockPieces();
                $minAlert = $prod->min_stock_alert ?? 10;
                if ($this->stockFilter === 'zero_stock') {
                    return $stock <= 0;
                }
                if ($this->stockFilter === 'low_stock') {
                    return $stock > 0 && $stock <= $minAlert;
                }

                return $stock <= $minAlert;
            });
    }

    public function createPurchaseOrder()
    {
        if (empty($this->selectedProductIds)) {
            $this->errorMessage = 'Please select at least one medicine to generate a purchase requisition.';

            return;
        }

        $items = [];
        foreach ($this->selectedProductIds as $pId) {
            $product = Product::with(['baseUnit', 'productUnits'])->find($pId);
            if ($product) {
                $qty = $this->reorderQuantities[$pId] ?? max(10, ($product->min_stock_alert ?? 10) * 2);
                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $qty,
                ];
            }
        }

        session()->put('reorder_requisition_items', $items);
        $this->successMessage = 'Requisition created with '.count($items).' item(s). Redirecting to Purchase Entry...';

        return redirect()->route('purchases.create');
    }

    public function render()
    {
        $lowStockItems = $this->getLowStockQuery();
        $manufacturers = Manufacturer::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        // Initialize default reorder quantities
        foreach ($lowStockItems as $item) {
            if (! isset($this->reorderQuantities[$item->id])) {
                $min = $item->min_stock_alert ?? 10;
                $this->reorderQuantities[$item->id] = max(10, $min * 2);
            }
        }

        $totalEstimatedCost = $lowStockItems->sum(function ($item) {
            $qty = $this->reorderQuantities[$item->id] ?? 10;
            $unitBuyPrice = (float) ($item->productUnits->firstWhere('unit_id', $item->base_unit_id)?->purchase_price ?? 0);

            return $qty * $unitBuyPrice;
        });

        return view('livewire.inventory.reorder-shortage-manager', [
            'shortages' => $lowStockItems,
            'manufacturers' => $manufacturers,
            'categories' => $categories,
            'totalEstimatedCost' => $totalEstimatedCost,
            'totalShortagesCount' => $lowStockItems->count(),
            'zeroStockCount' => $lowStockItems->filter(fn ($p) => $p->totalStockPieces() <= 0)->count(),
        ]);
    }
}
