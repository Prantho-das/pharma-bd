<?php

namespace App\Livewire\Pos;

use App\Actions\Pos\ProcessPosCheckoutAction;
use App\Models\Customer;
use App\Models\Doctor;
use App\Models\Generic;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Tax;
use Exception;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.pos')]
#[Title('POS Terminal - Pharmacy Enterprise')]
class Terminal extends Component
{
    use WithFileUploads;

    // Search & Barcode
    public string $search = '';

    public string $barcodeInput = '';

    public array $searchResults = [];

    // Cart Items: array of items
    // item = [id, product_id, name, sku, barcode, is_narcotic, requires_prescription, is_cold_chain, selected_unit_id, available_units, quantity, unit_price, mrp_price, discount, total, max_pieces]
    public array $cart = [];

    // Prescription Attachment
    public $prescriptionFile = null;

    // Cart Financials
    public float $subTotal = 0.00;

    public string $discountType = 'fixed'; // fixed or percentage

    public float $discountValue = 0.00;

    public float $discountAmount = 0.00;

    public float $taxRate = 0.00;

    public float $taxAmount = 0.00;

    public float $roundOff = 0.00;

    public float $grandTotal = 0.00;

    // Customer & Doctor
    public ?int $selectedCustomerId = null;

    public ?int $selectedDoctorId = null;

    public string $customerSearch = '';

    public string $doctorSearch = '';

    public string $notes = '';

    // Quick Customer Modal
    public bool $showCustomerModal = false;

    public string $newCustomerName = '';

    public string $newCustomerPhone = '';

    public string $newCustomerAddress = '';

    // Generic Substitute Modal
    public bool $showSubstituteModal = false;

    public ?int $selectedGenericId = null;

    public string $selectedGenericName = '';

    public array $substituteProducts = [];

    // Hold / Suspended Carts
    public array $heldCarts = [];

    public bool $showHeldCartsDrawer = false;

    // Dynamic Split Payment Rows
    public bool $showPaymentModal = false;

    public array $paymentRows = [
        ['method' => 'CASH', 'amount' => 0.00, 'reference' => ''],
    ];

    public float $paidTotal = 0.00;

    public float $changeAmount = 0.00;

    public float $remainingDue = 0.00;

    // Last completed sale for receipt
    public ?Sale $lastSale = null;

    public bool $showReceiptModal = false;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public function mount()
    {
        $this->loadHeldCartsFromSession();
    }

    public function updatedSearch()
    {
        $this->errorMessage = null;
        if (strlen(trim($this->search)) < 2) {
            $this->searchResults = [];

            return;
        }

        $term = '%'.trim($this->search).'%';
        $products = Product::with(['category', 'generic', 'manufacturer', 'productUnits.unit', 'baseUnit'])
            ->where('status', 'active')
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('barcode', 'like', $term)
                    ->orWhere('sku', 'like', $term)
                    ->orWhereHas('generic', fn ($g) => $g->where('name', 'like', $term));
            })
            ->limit(10)
            ->get();

        $this->searchResults = $products->map(function ($p) {
            $stock = $p->totalStockPieces();

            return [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'barcode' => $p->barcode,
                'generic_name' => $p->generic?->name ?? 'N/A',
                'generic_id' => $p->generic_id,
                'category_name' => $p->category?->name ?? 'General',
                'manufacturer_name' => $p->manufacturer?->name ?? 'N/A',
                'stock_pieces' => $stock,
                'is_narcotic' => (bool) $p->is_narcotic,
                'requires_prescription' => (bool) $p->requires_prescription,
                'is_cold_chain' => (bool) $p->is_cold_chain,
            ];
        })->toArray();
    }

    public function handleBarcodeScan()
    {
        $barcode = trim($this->barcodeInput);
        $this->barcodeInput = '';
        if (empty($barcode)) {
            return;
        }

        $product = Product::where('barcode', $barcode)
            ->orWhere('sku', $barcode)
            ->first();

        if ($product) {
            $this->addToCart($product->id);
        } else {
            $this->errorMessage = "No medicine found for barcode [{$barcode}].";
        }
    }

    public function addToCart(int $productId)
    {
        $this->errorMessage = null;
        $product = Product::with(['productUnits.unit', 'baseUnit'])->findOrFail($productId);
        $stockPieces = $product->totalStockPieces();

        if ($stockPieces <= 0) {
            $this->errorMessage = "Medicine [{$product->name}] is currently OUT OF STOCK.";
            if ($product->generic_id) {
                $this->openSubstituteFinder($product->generic_id, $product->generic?->name ?? 'Generic');
            }

            return;
        }

        // Available units
        $units = [];
        if ($product->baseUnit) {
            $units[] = [
                'unit_id' => $product->baseUnit->id,
                'unit_name' => $product->baseUnit->name,
                'multiplier' => 1,
                'price' => (float) ($product->productUnits->where('unit_id', $product->base_unit_id)->first()?->mrp_price ?? 0),
            ];
        }

        foreach ($product->productUnits as $pu) {
            if ($pu->unit_id !== $product->base_unit_id && $pu->unit) {
                $units[] = [
                    'unit_id' => $pu->unit_id,
                    'unit_name' => $pu->unit->name,
                    'multiplier' => $pu->multiplier,
                    'price' => (float) $pu->mrp_price,
                ];
            }
        }

        $defaultUnit = $product->productUnits->where('is_default_sale', true)->first();
        $selectedUnitId = $defaultUnit ? $defaultUnit->unit_id : ($units[0]['unit_id'] ?? null);
        $selectedPrice = $defaultUnit ? (float) $defaultUnit->mrp_price : ($units[0]['price'] ?? 0.0);

        // Check if item already exists in cart
        $cartKey = null;
        foreach ($this->cart as $key => $item) {
            if ($item['product_id'] === $productId && $item['selected_unit_id'] === $selectedUnitId) {
                $cartKey = $key;
                break;
            }
        }

        if ($cartKey !== null) {
            $this->cart[$cartKey]['quantity'] += 1;
        } else {
            $this->cart[] = [
                'id' => uniqid(),
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'is_narcotic' => (bool) $product->is_narcotic,
                'requires_prescription' => (bool) $product->requires_prescription,
                'is_cold_chain' => (bool) $product->is_cold_chain,
                'tax_rate' => (float) ($product->tax_rate ?? 0.00),
                'tax_type' => $product->tax_type ?? 'Exclusive',
                'selected_unit_id' => $selectedUnitId,
                'available_units' => $units,
                'quantity' => 1,
                'unit_price' => $selectedPrice,
                'discount' => 0.00,
                'total' => $selectedPrice,
                'stock_pieces' => $stockPieces,
            ];
        }

        $this->search = '';
        $this->searchResults = [];
        $this->recalculateTotals();
    }

    public function updateUnit(int $index, int $unitId)
    {
        if (! isset($this->cart[$index])) {
            return;
        }

        $item = &$this->cart[$index];
        $item['selected_unit_id'] = $unitId;

        foreach ($item['available_units'] as $u) {
            if ($u['unit_id'] === $unitId) {
                $item['unit_price'] = (float) $u['price'];
                break;
            }
        }

        $this->recalculateTotals();
    }

    public function updateQuantity(int $index, int $qty)
    {
        if (! isset($this->cart[$index])) {
            return;
        }

        if ($qty <= 0) {
            $this->removeFromCart($index);

            return;
        }

        $this->cart[$index]['quantity'] = $qty;
        $this->recalculateTotals();
    }

    public function updateDiscount(int $index, float $disc)
    {
        if (! isset($this->cart[$index])) {
            return;
        }

        $this->cart[$index]['discount'] = max(0, $disc);
        $this->recalculateTotals();
    }

    public function removeFromCart(int $index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        $this->recalculateTotals();
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->subTotal = 0.00;
        $this->discountValue = 0.00;
        $this->discountAmount = 0.00;
        $this->taxRate = 0.00;
        $this->taxAmount = 0.00;
        $this->roundOff = 0.00;
        $this->grandTotal = 0.00;
        $this->selectedCustomerId = null;
        $this->selectedDoctorId = null;
        $this->errorMessage = null;
    }

    public function recalculateTotals()
    {
        $sub = 0.00;
        $calculatedItemTaxes = 0.00;

        foreach ($this->cart as $key => $item) {
            $lineSub = (float) $item['unit_price'] * (int) $item['quantity'];
            $lineDiscount = min($lineSub, (float) ($item['discount'] ?? 0));
            $lineNet = max(0, $lineSub - $lineDiscount);

            $itemTaxRate = (float) ($item['tax_rate'] ?? 0);
            $itemTaxType = $item['tax_type'] ?? 'Exclusive';
            $itemTax = 0.00;

            if ($itemTaxRate > 0) {
                if ($itemTaxType === 'Exclusive') {
                    $itemTax = round(($lineNet * $itemTaxRate) / 100, 2);
                    $lineTotal = $lineNet + $itemTax;
                } else {
                    $itemTax = round($lineNet - ($lineNet / (1 + ($itemTaxRate / 100))), 2);
                    $lineTotal = $lineNet;
                }
            } else {
                $lineTotal = $lineNet;
            }

            $this->cart[$key]['total'] = $lineTotal;
            $this->cart[$key]['tax_amount'] = $itemTax;
            $sub += $lineNet;
            $calculatedItemTaxes += $itemTax;
        }

        $this->subTotal = round($sub, 2);

        if ($this->discountType === 'percentage') {
            $this->discountAmount = round(($this->subTotal * min(100, max(0, $this->discountValue))) / 100, 2);
        } else {
            $this->discountAmount = min($this->subTotal, max(0, $this->discountValue));
        }

        // Apply global Tax if explicitly set on POS panel, else use item-level calculated tax
        if ($this->taxRate > 0) {
            $taxable = max(0, $this->subTotal - $this->discountAmount);
            $this->taxAmount = round(($taxable * $this->taxRate) / 100, 2);
        } else {
            $this->taxAmount = round($calculatedItemTaxes, 2);
        }

        $rawGrand = max(0, $this->subTotal - $this->discountAmount) + $this->taxAmount;
        $rounded = round($rawGrand);
        $this->roundOff = round($rounded - $rawGrand, 2);
        $this->grandTotal = $rounded;

        $this->recalculatePaymentBreakdown();
    }

    public function openPaymentModal()
    {
        $this->errorMessage = null;

        if (empty($this->cart)) {
            $this->errorMessage = 'Cart is empty. Please add medicines before checkout.';

            return;
        }

        // Check narcotic prescription requirement
        $hasNarcotic = collect($this->cart)->contains('is_narcotic', true);
        if ($hasNarcotic && ! $this->selectedDoctorId) {
            $this->errorMessage = 'Narcotic / Controlled substance detected! You MUST select a prescribing Doctor.';

            return;
        }

        $this->paymentRows = [
            ['method' => 'CASH', 'amount' => $this->grandTotal, 'reference' => ''],
        ];
        $this->recalculatePaymentBreakdown();
        $this->showPaymentModal = true;
    }

    public function addPaymentRow()
    {
        $remaining = max(0, $this->grandTotal - $this->paidTotal);
        $this->paymentRows[] = [
            'method' => 'BKASH',
            'amount' => $remaining > 0 ? round($remaining, 2) : 0.00,
            'reference' => '',
        ];
        $this->recalculatePaymentBreakdown();
    }

    public function removePaymentRow(int $index)
    {
        if (count($this->paymentRows) > 1) {
            unset($this->paymentRows[$index]);
            $this->paymentRows = array_values($this->paymentRows);
            $this->recalculatePaymentBreakdown();
        }
    }

    public function setExactCash()
    {
        $this->paymentRows = [
            ['method' => 'CASH', 'amount' => $this->grandTotal, 'reference' => ''],
        ];
        $this->recalculatePaymentBreakdown();
    }

    public function addQuickCash(float $amount)
    {
        if (empty($this->paymentRows)) {
            $this->paymentRows[] = ['method' => 'CASH', 'amount' => $amount, 'reference' => ''];
        } else {
            $current = (float) ($this->paymentRows[0]['amount'] ?? 0);
            $this->paymentRows[0]['amount'] = round($current + $amount, 2);
        }
        $this->recalculatePaymentBreakdown();
    }

    public function recalculatePaymentBreakdown()
    {
        $paid = 0.0;
        foreach ($this->paymentRows as $row) {
            $paid += (float) ($row['amount'] ?? 0);
        }
        $this->paidTotal = round($paid, 2);

        $difference = $this->grandTotal - $this->paidTotal;
        if ($difference > 0) {
            $this->remainingDue = round($difference, 2);
            $this->changeAmount = 0.00;
        } else {
            $this->remainingDue = 0.00;
            $this->changeAmount = round(abs($difference), 2);
        }
    }

    public function processCheckout(ProcessPosCheckoutAction $checkoutAction)
    {
        $this->errorMessage = null;

        if (empty($this->cart)) {
            $this->errorMessage = 'Cart is empty.';

            return;
        }

        $this->recalculatePaymentBreakdown();

        $payments = [];
        $totalPaidCollected = 0.0;

        foreach ($this->paymentRows as $row) {
            $amt = (float) ($row['amount'] ?? 0);
            $method = strtoupper($row['method'] ?? 'CASH');
            $ref = ! empty($row['reference']) ? trim($row['reference']) : null;

            if ($amt > 0) {
                $payments[] = [
                    'method' => $method,
                    'amount' => $amt,
                    'ref' => $ref,
                    'card_last_four' => ($method === 'CARD') ? $ref : null,
                ];
                $totalPaidCollected += $amt;
            }
        }

        if ($this->remainingDue > 0) {
            if (! $this->selectedCustomerId) {
                $this->errorMessage = 'Customer must be selected for DUE / Credit sales (বকেয়া বিক্রয়ের জন্য গ্রাহক নির্বাচন বাধ্যতামূলক)!';

                return;
            }
            $payments[] = [
                'method' => 'DUE',
                'amount' => $this->remainingDue,
                'ref' => 'Balance Due',
                'card_last_four' => null,
            ];
        }

        $prescriptionPath = null;
        if ($this->prescriptionFile) {
            $prescriptionPath = $this->prescriptionFile->store('prescriptions', 'public');
        }

        try {
            $sale = $checkoutAction->execute([
                'branch_id' => Auth::user()?->branch_id,
                'user_id' => Auth::id() ?? 1,
                'customer_id' => $this->selectedCustomerId,
                'doctor_id' => $this->selectedDoctorId,
                'prescription_image_path' => $prescriptionPath,
                'items' => array_map(function ($item) {
                    return [
                        'product_id' => $item['product_id'],
                        'unit_id' => $item['selected_unit_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount' => $item['discount'],
                    ];
                }, $this->cart),
                'sub_total' => $this->subTotal,
                'discount_type' => $this->discountType,
                'discount_amount' => $this->discountAmount,
                'tax_amount' => $this->taxAmount,
                'round_off' => $this->roundOff,
                'grand_total' => $this->grandTotal,
                'payments' => $payments,
                'notes' => $this->notes,
            ]);

            $this->lastSale = Sale::with(['items.product.baseUnit', 'items.unit', 'customer', 'doctor', 'user', 'payments', 'branch'])->find($sale->id);
            $this->showPaymentModal = false;
            $this->showReceiptModal = true;
            $this->successMessage = "Invoice #{$sale->invoice_no} completed successfully!";
            $this->prescriptionFile = null;
            $this->clearCart();
        } catch (Exception $e) {
            $this->errorMessage = 'Checkout failed: '.$e->getMessage();
        }
    }

    // Quick Add Customer
    public function saveNewCustomer()
    {
        $this->validate([
            'newCustomerName' => 'required|string|min:2',
            'newCustomerPhone' => 'required|string|min:6',
        ]);

        $customer = Customer::create([
            'branch_id' => Auth::user()?->branch_id,
            'name' => $this->newCustomerName,
            'phone' => $this->newCustomerPhone,
            'address' => $this->newCustomerAddress,
        ]);

        $this->selectedCustomerId = $customer->id;
        $this->showCustomerModal = false;
        $this->newCustomerName = '';
        $this->newCustomerPhone = '';
        $this->newCustomerAddress = '';
    }

    // Generic Substitute Finder
    public function openSubstituteFinder(int $genericId, string $genericName)
    {
        $this->selectedGenericId = $genericId;
        $this->selectedGenericName = $genericName;

        $alternatives = Product::with(['manufacturer', 'category', 'productUnits.unit', 'baseUnit'])
            ->where('generic_id', $genericId)
            ->where('status', 'active')
            ->get();

        $this->substituteProducts = $alternatives->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'manufacturer' => $p->manufacturer?->name ?? 'N/A',
                'stock_pieces' => $p->totalStockPieces(),
                'base_unit' => $p->baseUnit?->name ?? 'Piece',
            ];
        })->toArray();

        $this->showSubstituteModal = true;
    }

    public function selectSubstitute(int $productId)
    {
        $this->showSubstituteModal = false;
        $this->addToCart($productId);
    }

    // Hold & Recall Cart Engine
    public function holdCurrentCart()
    {
        if (empty($this->cart)) {
            $this->errorMessage = 'Cannot hold an empty cart.';

            return;
        }

        $token = 'HOLD-'.strtoupper(substr(md5(uniqid()), 0, 6));
        $this->heldCarts[] = [
            'token' => $token,
            'time' => now()->format('h:i A'),
            'item_count' => count($this->cart),
            'grand_total' => $this->grandTotal,
            'customer_id' => $this->selectedCustomerId,
            'doctor_id' => $this->selectedDoctorId,
            'cart' => $this->cart,
            'discount_type' => $this->discountType,
            'discount_value' => $this->discountValue,
        ];

        session()->put('pos_held_carts', $this->heldCarts);
        $this->clearCart();
        $this->successMessage = "Cart saved on Hold [{$token}]. Ready for next customer!";
    }

    public function recallHeldCart(int $index)
    {
        if (! isset($this->heldCarts[$index])) {
            return;
        }

        $held = $this->heldCarts[$index];
        $this->cart = $held['cart'];
        $this->selectedCustomerId = $held['customer_id'];
        $this->selectedDoctorId = $held['doctor_id'];
        $this->discountType = $held['discount_type'];
        $this->discountValue = $held['discount_value'];

        unset($this->heldCarts[$index]);
        $this->heldCarts = array_values($this->heldCarts);
        session()->put('pos_held_carts', $this->heldCarts);

        $this->showHeldCartsDrawer = false;
        $this->recalculateTotals();
    }

    private function loadHeldCartsFromSession()
    {
        $this->heldCarts = session()->get('pos_held_carts', []);
    }

    public function render()
    {
        $customers = Customer::orderBy('name')->get();
        $doctors = Doctor::orderBy('name')->get();
        $taxes = Tax::where('is_active', true)->orderBy('name')->get();

        return view('livewire.pos.terminal', [
            'customers' => $customers,
            'doctors' => $doctors,
            'taxes' => $taxes,
        ]);
    }
}
