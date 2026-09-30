<?php

namespace App\Livewire\Products;

use App\Models\Batch;
use App\Models\BoxSize;
use App\Models\Category;
use App\Models\Generic;
use App\Models\Manufacturer;
use App\Models\MedicineType;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Tax;
use App\Models\Unit;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Products & Catalog Management - PMS')]
class ProductManager extends Component
{
    use WithFileUploads, WithPagination;

    public string $tab = 'all'; // 'all', 'create', 'expired', 'categories', 'units', 'types', 'manufacturers', 'box_sizes', 'bulk'

    // Search & Filter
    public string $search = '';

    public string $filterCategory = '';

    public string $filterManufacturer = '';

    public int $perPage = 15;

    // Create / Edit Product Basic Form State
    public ?int $editingProductId = null;

    public string $name = '';

    public string $strength = '';

    public string $dosageForm = '';

    public ?int $genericId = null;

    public ?int $categoryId = null;

    public ?int $medicineTypeId = null;

    public ?int $manufacturerId = null;

    public ?int $boxSizeId = null;

    public string $sku = '';

    public string $barcode = '';

    public string $rackLocation = '';

    public int $minStockAlert = 20;

    public int $maxStockLevel = 1000;

    public bool $isNarcotic = false;

    public bool $requiresPrescription = false;

    public bool $isColdChain = false;

    // Product Price, Stock Section State (Exact to Reference UI)
    public string $stockType = 'Single'; // 'Single' or 'Batch'

    public string $taxType = 'Exclusive'; // 'Exclusive' or 'Inclusive'

    public ?int $taxId = null;

    public float $taxRate = 0.00;

    public int $quantity = 10;

    public float $costExclTax = 0.00;

    public float $costInclTax = 0.00;

    public float $profitMargin = 15.00; // Profit (%)

    public float $mrpPrice = 0.00; // MRP / Sales Price

    public float $wholesalePrice = 0.00;

    public float $dealerPrice = 0.00;

    public ?string $manufacturingDate = null;

    public ?string $expireDate = null;

    public string $batchNumber = '';

    // Dynamic Multi-Unit System
    public ?int $baseUnitId = null;

    public float $purchasePrice = 0.00; // Base Unit buy price

    // Array of higher level units (e.g. Strip, Box, Carton)
    public array $packagingUnits = [];

    // Image Upload
    public $imageFile;

    public ?string $existingImagePath = null;

    // Masters Quick Create
    public string $newMasterName = '';

    public string $newMasterDescription = '';

    // Bulk Upload
    public $csvFile;

    public function mount()
    {
        $routeName = request()->route()?->getName();
        if ($routeName === 'products.create') {
            $this->tab = 'create';
        } elseif ($routeName === 'products.expired') {
            $this->tab = 'expired';
        } elseif ($routeName === 'products.categories') {
            $this->tab = 'categories';
        } elseif ($routeName === 'products.units') {
            $this->tab = 'units';
        } elseif ($routeName === 'products.medicine-types') {
            $this->tab = 'types';
        } elseif ($routeName === 'products.manufacturers') {
            $this->tab = 'manufacturers';
        } elseif ($routeName === 'products.box-sizes') {
            $this->tab = 'box_sizes';
        } elseif ($routeName === 'products.generics') {
            $this->tab = 'generics';
        } elseif ($routeName === 'products.bulk-upload') {
            $this->tab = 'bulk';
        } else {
            $this->tab = request()->query('tab', 'all');
        }

        $pieceUnit = Unit::where('name', 'like', '%piece%')->orWhere('short_code', 'pcs')->first() ?? Unit::first();
        if ($pieceUnit && ! $this->baseUnitId) {
            $this->baseUnitId = $pieceUnit->id;
        }

        // Initialize default standard packaging (Strip & Box)
        if (empty($this->packagingUnits)) {
            $this->applyPreset('10x10');
        }

        if (empty($this->manufacturingDate)) {
            $this->manufacturingDate = date('Y-m-d');
        }
        if (empty($this->expireDate)) {
            $this->expireDate = date('Y-m-d', strtotime('+2 years'));
        }
    }

    public function generateBarcode()
    {
        $this->barcode = '880'.rand(1000000000, 9999999999);
    }

    public function addPackagingUnit()
    {
        $usedUnitIds = array_column($this->packagingUnits, 'unit_id');
        $usedUnitIds[] = $this->baseUnitId;

        $nextUnit = Unit::whereNotIn('id', $usedUnitIds)->first() ?? Unit::where('id', '!=', $this->baseUnitId)->first() ?? Unit::first();

        $lastMult = count($this->packagingUnits) > 0 ? (int) ($this->packagingUnits[count($this->packagingUnits) - 1]['multiplier'] ?? 1) : 1;
        $newMult = $lastMult >= 10 ? $lastMult * 10 : 10;

        $this->packagingUnits[] = [
            'unit_id' => $nextUnit?->id ?? $this->baseUnitId,
            'multiplier' => $newMult,
            'purchase_price' => round($this->purchasePrice * $newMult, 2),
            'mrp_price' => round($this->mrpPrice * $newMult, 2),
        ];
    }

    public function removePackagingUnit(int $index)
    {
        unset($this->packagingUnits[$index]);
        $this->packagingUnits = array_values($this->packagingUnits);
    }

    public function setBaseUnit(int $unitId)
    {
        $this->baseUnitId = $unitId;
    }

    public function updatedBaseUnitId($value)
    {
        $this->baseUnitId = (int) $value;
    }

    // Exact Pricing Calculations
    public function recalculatePrices(string $trigger = 'cost_excl')
    {
        if ($trigger === 'cost_excl') {
            if ($this->taxType === 'Exclusive' && $this->taxRate > 0) {
                $this->costInclTax = round($this->costExclTax * (1 + ($this->taxRate / 100)), 2);
            } else {
                $this->costInclTax = $this->costExclTax;
            }
            if ($this->profitMargin > 0) {
                $this->mrpPrice = round($this->costInclTax * (1 + ($this->profitMargin / 100)), 2);
            }
        } elseif ($trigger === 'cost_incl') {
            if ($this->taxType === 'Exclusive' && $this->taxRate > 0) {
                $this->costExclTax = round($this->costInclTax / (1 + ($this->taxRate / 100)), 2);
            } else {
                $this->costExclTax = $this->costInclTax;
            }
            if ($this->profitMargin > 0) {
                $this->mrpPrice = round($this->costInclTax * (1 + ($this->profitMargin / 100)), 2);
            }
        } elseif ($trigger === 'profit') {
            $baseCost = $this->costInclTax > 0 ? $this->costInclTax : $this->costExclTax;
            if ($baseCost > 0) {
                $this->mrpPrice = round($baseCost * (1 + ($this->profitMargin / 100)), 2);
            }
        } elseif ($trigger === 'mrp') {
            $baseCost = $this->costInclTax > 0 ? $this->costInclTax : $this->costExclTax;
            if ($baseCost > 0 && $this->mrpPrice > 0) {
                $this->profitMargin = round((($this->mrpPrice - $baseCost) / $baseCost) * 100, 2);
            }
        }

        $this->purchasePrice = $this->costExclTax > 0 ? $this->costExclTax : $this->costInclTax;
        $this->recalculateAllPackagingPrices();
    }

    public function updatedCostExclTax($value)
    {
        $this->costExclTax = (float) $value;
        $this->recalculatePrices('cost_excl');
    }

    public function updatedCostInclTax($value)
    {
        $this->costInclTax = (float) $value;
        $this->recalculatePrices('cost_incl');
    }

    public function updatedProfitMargin($value)
    {
        $this->profitMargin = (float) $value;
        $this->recalculatePrices('profit');
    }

    public function updatedTaxRate($value)
    {
        $this->taxRate = (float) $value;
        $this->recalculatePrices('cost_excl');
    }

    public function updatedTaxType($value)
    {
        $this->taxType = $value;
        $this->recalculatePrices('cost_excl');
    }

    public function updatedTaxId($value)
    {
        $this->taxId = $value ? (int) $value : null;
        if ($this->taxId) {
            $tax = Tax::find($this->taxId);
            if ($tax) {
                $this->taxRate = (float) $tax->rate;
                $this->recalculatePrices('cost_excl');
            }
        } else {
            $this->taxRate = 0.00;
            $this->recalculatePrices('cost_excl');
        }
    }

    public function updatedMrpPrice($value)
    {
        $this->mrpPrice = (float) $value;
        $this->recalculatePrices('mrp');
    }

    public function updatedPurchasePrice($value)
    {
        $this->costExclTax = (float) $value;
        $this->recalculatePrices('cost_excl');
    }

    public function applyPreset(string $preset)
    {
        $stripUnit = Unit::where('name', 'like', '%strip%')->orWhere('short_code', 'str')->first() ?? Unit::skip(1)->first();
        $boxUnit = Unit::where('name', 'like', '%box%')->orWhere('short_code', 'box')->first() ?? Unit::skip(2)->first();
        $pieceUnit = Unit::where('name', 'like', '%piece%')->orWhere('short_code', 'pcs')->first() ?? Unit::first();
        $bottleUnit = Unit::where('name', 'like', '%bottle%')->orWhere('short_code', 'btl')->first() ?? Unit::first();

        if ($preset === 'single') {
            $this->packagingUnits = [];
            if ($bottleUnit && (! $this->baseUnitId || $this->baseUnitId === $stripUnit?->id || $this->baseUnitId === $boxUnit?->id)) {
                $this->baseUnitId = $bottleUnit->id;
            }

            return;
        }

        if ($pieceUnit && ($this->baseUnitId === $stripUnit?->id || $this->baseUnitId === $boxUnit?->id)) {
            $this->baseUnitId = $pieceUnit->id;
        }

        $this->packagingUnits = [];
        if ($preset === '10x10') {
            if ($stripUnit) {
                $this->packagingUnits[] = ['unit_id' => $stripUnit->id, 'multiplier' => 10, 'purchase_price' => round($this->purchasePrice * 10, 2), 'mrp_price' => round($this->mrpPrice * 10, 2)];
            }
            if ($boxUnit) {
                $this->packagingUnits[] = ['unit_id' => $boxUnit->id, 'multiplier' => 100, 'purchase_price' => round($this->purchasePrice * 100, 2), 'mrp_price' => round($this->mrpPrice * 100, 2)];
            }
        } elseif ($preset === '5x10') {
            if ($stripUnit) {
                $this->packagingUnits[] = ['unit_id' => $stripUnit->id, 'multiplier' => 10, 'purchase_price' => round($this->purchasePrice * 10, 2), 'mrp_price' => round($this->mrpPrice * 10, 2)];
            }
            if ($boxUnit) {
                $this->packagingUnits[] = ['unit_id' => $boxUnit->id, 'multiplier' => 50, 'purchase_price' => round($this->purchasePrice * 50, 2), 'mrp_price' => round($this->mrpPrice * 50, 2)];
            }
        } elseif ($preset === '3x10') {
            if ($stripUnit) {
                $this->packagingUnits[] = ['unit_id' => $stripUnit->id, 'multiplier' => 10, 'purchase_price' => round($this->purchasePrice * 10, 2), 'mrp_price' => round($this->mrpPrice * 10, 2)];
            }
            if ($boxUnit) {
                $this->packagingUnits[] = ['unit_id' => $boxUnit->id, 'multiplier' => 30, 'purchase_price' => round($this->purchasePrice * 30, 2), 'mrp_price' => round($this->mrpPrice * 30, 2)];
            }
        } elseif ($preset === '2x14') {
            if ($stripUnit) {
                $this->packagingUnits[] = ['unit_id' => $stripUnit->id, 'multiplier' => 14, 'purchase_price' => round($this->purchasePrice * 14, 2), 'mrp_price' => round($this->mrpPrice * 14, 2)];
            }
            if ($boxUnit) {
                $this->packagingUnits[] = ['unit_id' => $boxUnit->id, 'multiplier' => 28, 'purchase_price' => round($this->purchasePrice * 28, 2), 'mrp_price' => round($this->mrpPrice * 28, 2)];
            }
        }
    }

    public function updatedPackagingUnits($value, $key)
    {
        // If multiplier changed, recalculate prices
        if (str_ends_with($key, '.multiplier')) {
            $index = explode('.', $key)[0];
            $mult = (int) $value ?: 1;
            $this->packagingUnits[$index]['purchase_price'] = round($this->purchasePrice * $mult, 2);
            $this->packagingUnits[$index]['mrp_price'] = round($this->mrpPrice * $mult, 2);
        }
        // If pack buy price changed, recalculate base piece buy price
        elseif (str_ends_with($key, '.purchase_price')) {
            $index = explode('.', $key)[0];
            $mult = (int) ($this->packagingUnits[$index]['multiplier'] ?? 1);
            if ($mult > 0 && (float) $value > 0) {
                $this->costExclTax = round(((float) $value) / $mult, 4);
                $this->recalculatePrices('cost_excl');
                $this->recalculateAllPackagingPricesExcept($index);
            }
        }
        // If pack MRP changed, recalculate base piece MRP
        elseif (str_ends_with($key, '.mrp_price')) {
            $index = explode('.', $key)[0];
            $mult = (int) ($this->packagingUnits[$index]['multiplier'] ?? 1);
            if ($mult > 0 && (float) $value > 0) {
                $this->mrpPrice = round(((float) $value) / $mult, 2);
                $this->recalculatePrices('mrp');
                $this->recalculateAllPackagingPricesExcept($index);
            }
        }
    }

    public function recalculateAllPackagingPrices()
    {
        foreach ($this->packagingUnits as $i => $u) {
            $mult = (int) ($u['multiplier'] ?? 1);
            $this->packagingUnits[$i]['purchase_price'] = round($this->purchasePrice * $mult, 2);
            $this->packagingUnits[$i]['mrp_price'] = round($this->mrpPrice * $mult, 2);
        }
    }

    public function recalculateAllPackagingPricesExcept($exceptIndex)
    {
        foreach ($this->packagingUnits as $i => $u) {
            if ($i != $exceptIndex) {
                $mult = (int) ($u['multiplier'] ?? 1);
                $this->packagingUnits[$i]['purchase_price'] = round($this->purchasePrice * $mult, 2);
                $this->packagingUnits[$i]['mrp_price'] = round($this->mrpPrice * $mult, 2);
            }
        }
    }

    public function saveProduct()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'baseUnitId' => 'required|exists:units,id',
            'mrpPrice' => 'required|numeric|min:0',
            'imageFile' => 'nullable|image|max:2048',
        ]);

        $imagePath = $this->existingImagePath;
        if ($this->imageFile) {
            $imagePath = $this->imageFile->store('products', 'public');
        }

        $product = Product::updateOrCreate(
            ['id' => $this->editingProductId],
            [
                'name' => $this->name,
                'strength' => $this->strength,
                'dosage_form' => $this->dosageForm,
                'generic_id' => $this->genericId,
                'category_id' => $this->categoryId,
                'medicine_type_id' => $this->medicineTypeId,
                'manufacturer_id' => $this->manufacturerId,
                'base_unit_id' => $this->baseUnitId,
                'box_size_id' => $this->boxSizeId,
                'sku' => $this->sku ?: ('SKU-'.strtoupper(substr(uniqid(), -6))),
                'barcode' => $this->barcode ?: ('BC'.date('ymdHis')),
                'rack_location' => $this->rackLocation,
                'min_stock_alert' => $this->minStockAlert,
                'max_stock_level' => $this->maxStockLevel,
                'tax_rate' => $this->taxRate,
                'tax_type' => $this->taxType,
                'stock_type' => $this->stockType,
                'wholesale_price' => $this->wholesalePrice,
                'dealer_price' => $this->dealerPrice,
                'is_narcotic' => $this->isNarcotic,
                'requires_prescription' => $this->requiresPrescription,
                'is_cold_chain' => $this->isColdChain,
                'image_path' => $imagePath,
                'status' => 'active',
            ]
        );

        // Delete old non-base product units
        ProductUnit::where('product_id', $product->id)->delete();

        // 1. Save Base Unit
        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $this->baseUnitId,
            'multiplier' => 1,
            'purchase_price' => $this->costExclTax > 0 ? $this->costExclTax : $this->purchasePrice,
            'mrp_price' => $this->mrpPrice,
            'is_default_purchase' => empty($this->packagingUnits),
            'is_default_sale' => true,
        ]);

        // 2. Save Dynamic Packaging Units
        foreach ($this->packagingUnits as $pack) {
            if (! empty($pack['unit_id']) && (int) ($pack['multiplier'] ?? 0) > 1) {
                ProductUnit::updateOrCreate(
                    ['product_id' => $product->id, 'unit_id' => $pack['unit_id']],
                    [
                        'multiplier' => (int) $pack['multiplier'],
                        'purchase_price' => (float) ($pack['purchase_price'] ?? ($this->purchasePrice * $pack['multiplier'])),
                        'mrp_price' => (float) ($pack['mrp_price'] ?? ($this->mrpPrice * $pack['multiplier'])),
                        'is_default_purchase' => true,
                        'is_default_sale' => false,
                    ]
                );
            }
        }

        // 3. Save Opening Stock Batch
        if ($this->quantity > 0) {
            $batchNo = ! empty($this->batchNumber) ? $this->batchNumber : ($this->stockType === 'Batch' ? 'B-'.date('Ym').'-'.rand(100, 999) : 'OP-'.date('Ymd'));
            $expiry = ! empty($this->expireDate) ? $this->expireDate : date('Y-m-d', strtotime('+2 years'));

            Batch::updateOrCreate(
                ['product_id' => $product->id, 'batch_number' => $batchNo],
                [
                    'branch_id' => $product->branch_id,
                    'base_unit_id' => $this->baseUnitId,
                    'initial_stock' => $this->quantity,
                    'current_stock' => $this->quantity,
                    'purchase_rate' => $this->costExclTax > 0 ? $this->costExclTax : $this->costInclTax,
                    'mrp_rate' => $this->mrpPrice,
                    'manufacture_date' => $this->manufacturingDate,
                    'expiry_date' => $expiry,
                ]
            );
        }

        session()->flash('success', 'Medicine registered successfully with pricing, stock & packaging units!');
        $this->reset([
            'name', 'strength', 'dosageForm', 'genericId', 'categoryId', 'medicineTypeId',
            'manufacturerId', 'baseUnitId', 'boxSizeId', 'sku', 'barcode', 'rackLocation',
            'purchasePrice', 'mrpPrice', 'costExclTax', 'costInclTax', 'wholesalePrice', 'dealerPrice',
            'packagingUnits', 'imageFile', 'existingImagePath', 'editingProductId',
            'isNarcotic', 'requiresPrescription', 'isColdChain', 'taxRate', 'quantity', 'batchNumber',
        ]);
        $this->tab = 'all';
    }

    public function editProduct(int $id)
    {
        $p = Product::with(['productUnits', 'batches'])->findOrFail($id);
        $this->editingProductId = $p->id;
        $this->name = $p->name;
        $this->strength = $p->strength ?? '';
        $this->dosageForm = $p->dosage_form ?? '';
        $this->genericId = $p->generic_id;
        $this->categoryId = $p->category_id;
        $this->medicineTypeId = $p->medicine_type_id;
        $this->manufacturerId = $p->manufacturer_id;
        $this->baseUnitId = $p->base_unit_id;
        $this->boxSizeId = $p->box_size_id;
        $this->sku = $p->sku ?? '';
        $this->barcode = $p->barcode ?? '';
        $this->rackLocation = $p->rack_location ?? '';
        $this->minStockAlert = $p->min_stock_alert;
        $this->maxStockLevel = $p->max_stock_level ?? 1000;
        $this->taxRate = (float) $p->tax_rate;
        $this->taxType = $p->tax_type ?? 'Exclusive';
        $this->stockType = $p->stock_type ?? 'Single';
        $this->wholesalePrice = (float) ($p->wholesale_price ?? 0);
        $this->dealerPrice = (float) ($p->dealer_price ?? 0);
        $this->isNarcotic = (bool) $p->is_narcotic;
        $this->requiresPrescription = (bool) $p->requires_prescription;
        $this->isColdChain = (bool) $p->is_cold_chain;
        $this->existingImagePath = $p->image_path;

        $baseUnit = $p->productUnits->firstWhere('unit_id', $p->base_unit_id);
        $this->costExclTax = $baseUnit ? (float) $baseUnit->purchase_price : 0.00;
        $this->purchasePrice = $this->costExclTax;
        $this->mrpPrice = $baseUnit ? (float) $baseUnit->mrp_price : 0.00;

        $this->recalculatePrices('cost_excl');

        // Check latest batch
        $latestBatch = $p->batches()->latest()->first();
        if ($latestBatch) {
            $this->quantity = (int) $latestBatch->current_stock;
            $this->batchNumber = $latestBatch->batch_number;
            $this->manufacturingDate = $latestBatch->manufacture_date;
            $this->expireDate = $latestBatch->expiry_date;
        }

        $this->packagingUnits = [];
        $otherUnits = $p->productUnits->where('unit_id', '!=', $p->base_unit_id)->sortBy('multiplier')->values();
        foreach ($otherUnits as $ou) {
            $this->packagingUnits[] = [
                'unit_id' => $ou->unit_id,
                'multiplier' => $ou->multiplier,
                'purchase_price' => (float) $ou->purchase_price,
                'mrp_price' => (float) $ou->mrp_price,
            ];
        }

        $this->tab = 'create';
    }

    public function addMaster(string $type)
    {
        $this->validate(['newMasterName' => 'required|string|max:255']);

        if ($type === 'generic') {
            $gen = Generic::create(['name' => $this->newMasterName, 'indication' => $this->newMasterDescription]);
            $this->genericId = $gen->id;
        } elseif ($type === 'category') {
            $cat = Category::create(['name' => $this->newMasterName, 'description' => $this->newMasterDescription]);
            $this->categoryId = $cat->id;
        } elseif ($type === 'unit') {
            $u = Unit::create(['name' => $this->newMasterName, 'short_code' => $this->newMasterDescription ?: strtolower(substr($this->newMasterName, 0, 3)), 'conversion_factor' => 1]);
            $this->baseUnitId = $u->id;
        } elseif ($type === 'type') {
            $t = MedicineType::create(['name' => $this->newMasterName, 'description' => $this->newMasterDescription]);
            $this->medicineTypeId = $t->id;
        } elseif ($type === 'manufacturer') {
            $m = Manufacturer::create(['name' => $this->newMasterName, 'short_code' => $this->newMasterDescription]);
            $this->manufacturerId = $m->id;
        } elseif ($type === 'box_size') {
            $b = BoxSize::create(['name' => $this->newMasterName, 'total_quantity' => (int) $this->newMasterDescription ?: 100]);
            $this->boxSizeId = $b->id;
        }

        $this->reset(['newMasterName', 'newMasterDescription']);
        session()->flash('success', 'Master entry added successfully!');
    }

    public function processBulkUpload()
    {
        $this->validate(['csvFile' => 'required|mimes:csv,txt|max:5120']);

        $path = $this->csvFile->getRealPath();
        $file = fopen($path, 'r');
        $header = fgetcsv($file);

        $count = 0;
        $defaultUnit = Unit::firstOrCreate(['name' => 'Piece'], ['short_code' => 'pcs', 'conversion_factor' => 1, 'is_base_unit' => true]);

        while (($row = fgetcsv($file)) !== false) {
            if (count($row) >= 2) {
                $name = trim($row[0]);
                $mrp = (float) ($row[1] ?? 0);
                $purchase = (float) ($row[2] ?? ($mrp * 0.8));

                if (! empty($name)) {
                    $prod = Product::create([
                        'name' => $name,
                        'base_unit_id' => $defaultUnit->id,
                        'sku' => 'SKU-'.strtoupper(substr(uniqid(), -6)),
                        'barcode' => 'BC'.date('ymdHis').rand(10, 99),
                        'status' => 'active',
                    ]);

                    ProductUnit::create([
                        'product_id' => $prod->id,
                        'unit_id' => $defaultUnit->id,
                        'multiplier' => 1,
                        'purchase_price' => $purchase,
                        'mrp_price' => $mrp,
                        'is_default_sale' => true,
                    ]);

                    $count++;
                }
            }
        }
        fclose($file);

        session()->flash('success', "Bulk uploaded {$count} medicines successfully!");
        $this->tab = 'all';
    }

    public function render()
    {
        $productsQuery = Product::with(['generic', 'category', 'manufacturer', 'baseUnit', 'batches'])
            ->when($this->search, function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where('name', 'like', $term)
                    ->orWhere('sku', 'like', $term)
                    ->orWhere('barcode', 'like', $term);
            })
            ->when($this->filterCategory, fn ($q) => $q->where('category_id', $this->filterCategory))
            ->when($this->filterManufacturer, fn ($q) => $q->where('manufacturer_id', $this->filterManufacturer))
            ->latest('id');

        $expiredBatches = Batch::with(['product'])
            ->where('current_stock', '>', 0)
            ->where('expiry_date', '<', now()->toDateString())
            ->latest('expiry_date')
            ->paginate($this->perPage);

        return view('livewire.products.product-manager', [
            'products' => $productsQuery->paginate($this->perPage),
            'expiredBatches' => $expiredBatches,
            'categories' => Category::orderBy('name')->get(),
            'manufacturers' => Manufacturer::orderBy('name')->get(),
            'generics' => Generic::orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
            'medicineTypes' => MedicineType::orderBy('name')->get(),
            'boxSizes' => BoxSize::orderBy('name')->get(),
            'taxes' => Tax::orderBy('name')->get(),
        ]);
    }
}
