<?php

namespace App\Livewire\Purchases;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create Purchase Order (PO) - PMS')]
class PurchaseOrderCreateForm extends Component
{
    public ?int $supplier_id = null;
    public string $order_date = '';
    public ?string $expected_delivery_date = null;
    public string $status = 'ORDERED';
    public ?string $notes = null;

    public array $items = [];

    public float $discount = 0.0;
    public float $tax_amount = 0.0;

    public ?string $successMessage = null;
    public ?string $errorMessage = null;

    public function mount()
    {
        $this->order_date = Carbon::now()->toDateString();
        $this->expected_delivery_date = Carbon::now()->addDays(3)->toDateString();
        $this->addItemRow();
    }

    public function addItemRow()
    {
        $this->items[] = [
            'product_id' => null,
            'unit_id' => null,
            'quantity_ordered' => 1,
            'unit_price' => 0.00,
            'mrp_per_unit' => 0.00,
            'total_cost' => 0.00,
        ];
    }

    public function removeItemRow(int $index)
    {
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
        }
    }

    public function productSelected(int $index, $productId)
    {
        if (!$productId) return;

        $product = Product::find($productId);
        if ($product) {
            $this->items[$index]['product_id'] = $product->id;
            $this->items[$index]['unit_id'] = $product->unit_id;
            $this->items[$index]['unit_price'] = (float) $product->purchase_price;
            $this->items[$index]['mrp_per_unit'] = (float) $product->selling_price;
            $this->recalculateRow($index);
        }
    }

    public function recalculateRow(int $index)
    {
        $qty = (float) ($this->items[$index]['quantity_ordered'] ?? 0);
        $rate = (float) ($this->items[$index]['unit_price'] ?? 0);
        $this->items[$index]['total_cost'] = round($qty * $rate, 2);
    }

    public function getSubTotalProperty(): float
    {
        return (float) collect($this->items)->sum('total_cost');
    }

    public function getGrandTotalProperty(): float
    {
        return max(0, $this->subTotal - (float)$this->discount + (float)$this->tax_amount);
    }

    public function savePurchaseOrder()
    {
        $this->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_ordered' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ], [
            'supplier_id.required' => 'অনুগ্রহ করে সাপ্লায়ার নির্বাচন করুন।',
            'items.*.product_id.required' => 'ওষুধ নির্বাচন করুন।',
            'items.*.quantity_ordered.required' => 'পরিমাণ আবশ্যক।',
        ]);

        try {
            DB::beginTransaction();

            $poNumber = 'PO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $totalQty = collect($this->items)->sum('quantity_ordered');

            $po = PurchaseOrder::create([
                'branch_id' => auth()->user()?->branch_id,
                'po_number' => $poNumber,
                'supplier_id' => $this->supplier_id,
                'order_date' => $this->order_date,
                'expected_delivery_date' => $this->expected_delivery_date,
                'status' => $this->status,
                'total_items' => count($this->items),
                'total_ordered_qty' => $totalQty,
                'total_received_qty' => 0,
                'sub_total' => $this->subTotal,
                'discount' => $this->discount,
                'tax_amount' => $this->tax_amount,
                'grand_total' => $this->grandTotal,
                'notes' => $this->notes,
                'created_by' => auth()->id(),
            ]);

            foreach ($this->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'] ?? null,
                    'quantity_ordered' => $item['quantity_ordered'],
                    'quantity_received' => 0,
                    'unit_price' => $item['unit_price'],
                    'mrp_per_unit' => $item['mrp_per_unit'] ?? 0,
                    'total_cost' => $item['total_cost'],
                ]);
            }

            DB::commit();

            session()->flash('success', "পারচেজ অর্ডার {$poNumber} সফলভাবে তৈরি হয়েছে!");
            return redirect()->route('purchase-orders.index');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->errorMessage = 'PO তৈরিতে সমস্যা: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.purchases.purchase-order-create-form', [
            'suppliers' => Supplier::orderBy('name')->get(),
            'products' => Product::where('status', 'active')->orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
        ]);
    }
}
