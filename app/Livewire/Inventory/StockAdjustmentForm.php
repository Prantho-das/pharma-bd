<?php

namespace App\Livewire\Inventory;

use App\Actions\Inventory\AdjustBatchStockAction;
use App\Models\Batch;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\Unit;
use Exception;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Stock Adjustment & Damage Log - PMS')]
class StockAdjustmentForm extends Component
{
    public string $reason = 'DAMAGE'; // DAMAGE, EXPIRED, AUDIT_DISCREPANCY, THEFT, CORRECTION

    public string $notes = '';

    // items array: [['product_id' => int, 'batch_id' => int, 'unit_id' => int, 'quantity' => int, 'adjustment_type' => 'DEDUCTION'|'ADDITION']]
    public array $items = [];

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    public function mount()
    {
        $this->addRow();
    }

    public function addRow()
    {
        $this->items[] = [
            'product_id' => null,
            'batch_id' => null,
            'unit_id' => null,
            'quantity' => 1,
            'adjustment_type' => 'DEDUCTION',
        ];
    }

    public function removeRow(int $index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        if (empty($this->items)) {
            $this->addRow();
        }
    }

    public function productSelected(int $index, int $productId)
    {
        $product = Product::with('baseUnit')->find($productId);
        if (! $product) {
            return;
        }

        $this->items[$index]['product_id'] = $product->id;
        $this->items[$index]['unit_id'] = $product->base_unit_id;

        $firstBatch = Batch::where('product_id', $product->id)->where('current_stock', '>', 0)->first();
        $this->items[$index]['batch_id'] = $firstBatch?->id;
    }

    public function saveAdjustment(AdjustBatchStockAction $action)
    {
        $this->validate([
            'reason' => 'required|in:DAMAGE,EXPIRED,AUDIT_DISCREPANCY,THEFT,CORRECTION',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.batch_id' => 'required|exists:batches,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            $adj = $action->execute([
                'branch_id' => Auth::user()?->branch_id,
                'user_id' => Auth::id() ?? 1,
                'reason' => $this->reason,
                'notes' => $this->notes,
                'items' => $this->items,
            ]);

            $this->successMessage = "Stock adjustment #{$adj->adjustment_no} processed successfully! Loss value: ৳".number_format($adj->total_loss_value, 2);
            $this->items = [];
            $this->addRow();
        } catch (Exception $e) {
            $this->errorMessage = 'Adjustment failed: '.$e->getMessage();
        }
    }

    public function render()
    {
        $products = Product::with(['baseUnit', 'batches'])->where('status', 'active')->orderBy('name')->get();
        $recentAdjustments = StockAdjustment::with(['user', 'items.product', 'items.batch'])->latest('id')->paginate(10);

        return view('livewire.inventory.stock-adjustment-form', [
            'products' => $products,
            'recentAdjustments' => $recentAdjustments,
            'units' => Unit::orderBy('name')->get(),
        ]);
    }
}
