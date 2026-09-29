<?php

namespace App\Livewire\Purchases;

use App\Actions\Purchases\ReceiveGoodsNoteAction;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use Exception;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Receive Goods Note (GRN Purchase) - PMS')]
class PurchaseCreateForm extends Component
{
    public ?int $supplier_id = null;

    public string $supplier_invoice_no = '';

    public string $invoice_date = '';

    public string $delivery_date = '';

    public float $discount = 0.00;

    public float $tax_amount = 0.00;

    public float $paid_amount = 0.00;

    public string $notes = '';

    // Quick Supplier Modal
    public bool $showSupplierModal = false;

    public string $newSupplierName = '';

    public string $newSupplierCompany = '';

    public string $newSupplierPhone = '';

    // Purchase Items Array
    // item = [product_id, product_name, unit_id, quantity, bonus_quantity, batch_number, expiry_date, purchase_price_per_unit, mrp_per_unit, total_cost, effective_base_cost]
    public array $items = [];

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    public function mount()
    {
        $this->invoice_date = now()->toDateString();
        $this->delivery_date = now()->toDateString();
        $this->addItemRow();
    }

    public function addItemRow()
    {
        $this->items[] = [
            'product_id' => null,
            'unit_id' => null,
            'quantity' => 1,
            'bonus_quantity' => 0,
            'batch_number' => 'B-'.strtoupper(substr(md5(uniqid()), 0, 5)),
            'expiry_date' => now()->addYears(2)->format('Y-m-d'),
            'purchase_price_per_unit' => 0.00,
            'mrp_per_unit' => 0.00,
            'total_cost' => 0.00,
        ];
    }

    public function removeItemRow(int $index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        if (empty($this->items)) {
            $this->addItemRow();
        }
    }

    public function productSelected(int $index, int $productId)
    {
        $product = Product::with(['productUnits.unit', 'baseUnit'])->find($productId);
        if (! $product) {
            return;
        }

        $this->items[$index]['product_id'] = $product->id;
        $this->items[$index]['unit_id'] = $product->base_unit_id;

        $defaultPurchaseUnit = $product->productUnits->where('is_default_purchase', true)->first()
            ?? $product->productUnits->first();

        if ($defaultPurchaseUnit) {
            $this->items[$index]['unit_id'] = $defaultPurchaseUnit->unit_id;
            $this->items[$index]['purchase_price_per_unit'] = (float) $defaultPurchaseUnit->purchase_price;
            $this->items[$index]['mrp_per_unit'] = (float) $defaultPurchaseUnit->mrp_price;
        }

        $this->recalculateRow($index);
    }

    public function recalculateRow(int $index)
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $qty = (int) ($this->items[$index]['quantity'] ?? 0);
        $price = (float) ($this->items[$index]['purchase_price_per_unit'] ?? 0);
        $this->items[$index]['total_cost'] = round($qty * $price, 2);
    }

    public function getSubTotalProperty(): float
    {
        return round(collect($this->items)->sum('total_cost'), 2);
    }

    public function getGrandTotalProperty(): float
    {
        return round(max(0, $this->subTotal - $this->discount + $this->tax_amount), 2);
    }

    public function getDueAmountProperty(): float
    {
        return round(max(0, $this->grandTotal - $this->paid_amount), 2);
    }

    public function saveSupplier()
    {
        $this->validate([
            'newSupplierName' => 'required|string|min:2',
            'newSupplierPhone' => 'required|string|min:6',
        ]);

        $supplier = Supplier::create([
            'name' => $this->newSupplierName,
            'company_name' => $this->newSupplierCompany,
            'phone' => $this->newSupplierPhone,
        ]);

        $this->supplier_id = $supplier->id;
        $this->showSupplierModal = false;
        $this->newSupplierName = '';
        $this->newSupplierCompany = '';
        $this->newSupplierPhone = '';
    }

    public function savePurchase(ReceiveGoodsNoteAction $grnAction)
    {
        $this->errorMessage = null;

        $this->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.batch_number' => 'required|string',
            'items.*.expiry_date' => 'required|date|after:today',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.purchase_price_per_unit' => 'required|numeric|min:0',
        ]);

        try {
            $purchase = $grnAction->execute([
                'branch_id' => Auth::user()?->branch_id,
                'supplier_id' => $this->supplier_id,
                'supplier_invoice_no' => $this->supplier_invoice_no,
                'invoice_date' => $this->invoice_date,
                'delivery_date' => $this->delivery_date,
                'discount' => $this->discount,
                'tax_amount' => $this->tax_amount,
                'paid_amount' => $this->paid_amount,
                'notes' => $this->notes,
                'items' => $this->items,
            ]);

            $this->successMessage = "GRN Purchase #{$purchase->purchase_no} recorded & batches created!";
            $this->reset(['supplier_id', 'supplier_invoice_no', 'discount', 'tax_amount', 'paid_amount', 'notes']);
            $this->items = [];
            $this->addItemRow();
        } catch (Exception $e) {
            $this->errorMessage = 'GRN failed: '.$e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.purchases.purchase-create-form', [
            'suppliers' => Supplier::orderBy('name')->get(),
            'products' => Product::with('baseUnit')->where('status', 'active')->orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
        ]);
    }
}
