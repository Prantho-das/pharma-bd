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
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
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

    public bool $hasBatchTracking = true;

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

    // Initial Stock & Batches System
    public bool $hasOpeningStock = true;

    public array $initialBatches = [];

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

    // Bulk Studio State
    public string $bulkMode = 'grid'; // 'grid' (Direct Excel Table) or 'file' (CSV Upload)

    public $csvFile;

    public array $bulkRows = [];

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

        if (empty($this->bulkRows)) {
            $this->addMultipleBulkRows(6);
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

        if (empty($this->initialBatches)) {
            $this->initialBatches = [
                [
                    'batch_number' => 'B-'.date('ym').'-'.rand(100, 999),
                    'unit_id' => $this->baseUnitId,
                    'quantity' => 10,
                    'purchase_price' => $this->purchasePrice > 0 ? $this->purchasePrice : 0,
                    'mrp_price' => $this->mrpPrice > 0 ? $this->mrpPrice : 0,
                    'manufacturing_date' => date('Y-m-d'),
                    'expiry_date' => date('Y-m-d', strtotime('+2 years')),
                    'rack_location' => '',
                ],
            ];
        }
    }

    public function addBatchRow()
    {
        $this->initialBatches[] = [
            'batch_number' => 'B-'.date('ym').'-'.rand(100, 999),
            'unit_id' => $this->baseUnitId,
            'quantity' => 10,
            'purchase_price' => $this->purchasePrice > 0 ? $this->purchasePrice : 0,
            'mrp_price' => $this->mrpPrice > 0 ? $this->mrpPrice : 0,
            'manufacturing_date' => date('Y-m-d'),
            'expiry_date' => date('Y-m-d', strtotime('+2 years')),
            'rack_location' => $this->rackLocation ?? '',
        ];
    }

    public function removeBatchRow(int $index)
    {
        unset($this->initialBatches[$index]);
        $this->initialBatches = array_values($this->initialBatches);
        if (empty($this->initialBatches)) {
            $this->hasOpeningStock = false;
        }
    }

    public function setBatchExpiryPreset(int $index, string $duration)
    {
        if (isset($this->initialBatches[$index])) {
            $this->initialBatches[$index]['expiry_date'] = date('Y-m-d', strtotime("+{$duration}"));
        }
    }

    public function generateBatchNumber(int $index)
    {
        if (isset($this->initialBatches[$index])) {
            $this->initialBatches[$index]['batch_number'] = 'B-'.date('ym').'-'.rand(100, 999);
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
                'has_batch_tracking' => $this->hasBatchTracking,
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

        // 3. Save Opening Stock Batches
        if ($this->hasOpeningStock && ! empty($this->initialBatches)) {
            foreach ($this->initialBatches as $b) {
                $qty = (int) ($b['quantity'] ?? 0);
                if ($qty > 0) {
                    $unitId = ! empty($b['unit_id']) ? (int) $b['unit_id'] : $this->baseUnitId;
                    $multiplier = 1;
                    if ($unitId !== $this->baseUnitId) {
                        $pack = collect($this->packagingUnits)->firstWhere('unit_id', $unitId);
                        if ($pack) {
                            $multiplier = (int) ($pack['multiplier'] ?? 1);
                        }
                    }
                    $baseQty = $qty * $multiplier;
                    $batchNo = ! empty($b['batch_number']) ? trim($b['batch_number']) : ('B-'.date('ym').'-'.rand(100, 999));
                    $expiry = ! empty($b['expiry_date']) ? $b['expiry_date'] : date('Y-m-d', strtotime('+2 years'));
                    $mfg = ! empty($b['manufacturing_date']) ? $b['manufacturing_date'] : date('Y-m-d');
                    $buyRate = (float) ($b['purchase_price'] ?? ($this->costExclTax > 0 ? $this->costExclTax : $this->purchasePrice));
                    $mrpRate = (float) ($b['mrp_price'] ?? $this->mrpPrice);

                    if ($multiplier > 1 && $buyRate > 0) {
                        $buyRate = round($buyRate / $multiplier, 4);
                    }
                    if ($multiplier > 1 && $mrpRate > 0) {
                        $mrpRate = round($mrpRate / $multiplier, 2);
                    }

                    if (! empty($b['id'])) {
                        Batch::where('id', $b['id'])->update([
                            'branch_id' => $product->branch_id,
                            'batch_number' => $batchNo,
                            'base_unit_id' => $this->baseUnitId,
                            'current_stock' => $baseQty,
                            'purchase_rate' => $buyRate,
                            'mrp_rate' => $mrpRate,
                            'manufacture_date' => $mfg,
                            'expiry_date' => $expiry,
                        ]);
                    } else {
                        Batch::updateOrCreate(
                            ['product_id' => $product->id, 'batch_number' => $batchNo],
                            [
                                'branch_id' => $product->branch_id,
                                'base_unit_id' => $this->baseUnitId,
                                'initial_stock' => $baseQty,
                                'current_stock' => $baseQty,
                                'purchase_rate' => $buyRate,
                                'mrp_rate' => $mrpRate,
                                'manufacture_date' => $mfg,
                                'expiry_date' => $expiry,
                            ]
                        );
                    }
                }
            }
        }

        session()->flash('success', 'Medicine registered successfully with pricing, stock & packaging units!');
        $this->reset([
            'name', 'strength', 'dosageForm', 'genericId', 'categoryId', 'medicineTypeId',
            'manufacturerId', 'baseUnitId', 'boxSizeId', 'sku', 'barcode', 'rackLocation',
            'purchasePrice', 'mrpPrice', 'costExclTax', 'costInclTax', 'wholesalePrice', 'dealerPrice',
            'packagingUnits', 'imageFile', 'existingImagePath', 'editingProductId',
            'isNarcotic', 'requiresPrescription', 'isColdChain', 'taxRate', 'quantity', 'batchNumber',
        ]);
        $this->initialBatches = [
            [
                'batch_number' => 'B-'.date('ym').'-'.rand(100, 999),
                'unit_id' => $this->baseUnitId,
                'quantity' => 10,
                'purchase_price' => 0.00,
                'mrp_price' => 0.00,
                'manufacturing_date' => date('Y-m-d'),
                'expiry_date' => date('Y-m-d', strtotime('+2 years')),
                'rack_location' => '',
            ],
        ];
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
        $this->hasBatchTracking = (bool) ($p->has_batch_tracking ?? true);
        $this->existingImagePath = $p->image_path;

        $baseUnit = $p->productUnits->firstWhere('unit_id', $p->base_unit_id);
        $this->costExclTax = $baseUnit ? (float) $baseUnit->purchase_price : 0.00;
        $this->purchasePrice = $this->costExclTax;
        $this->mrpPrice = $baseUnit ? (float) $baseUnit->mrp_price : 0.00;

        $this->recalculatePrices('cost_excl');

        // Load batches
        $this->initialBatches = [];
        $batches = $p->batches()->orderBy('expiry_date', 'asc')->get();
        if ($batches->isNotEmpty()) {
            $this->hasOpeningStock = true;
            foreach ($batches as $b) {
                $this->initialBatches[] = [
                    'id' => $b->id,
                    'batch_number' => $b->batch_number,
                    'unit_id' => $b->base_unit_id ?? $p->base_unit_id,
                    'quantity' => (int) $b->current_stock,
                    'purchase_price' => (float) $b->purchase_rate,
                    'mrp_price' => (float) $b->mrp_rate,
                    'manufacturing_date' => $b->manufacture_date ? date('Y-m-d', strtotime($b->manufacture_date)) : date('Y-m-d'),
                    'expiry_date' => $b->expiry_date ? date('Y-m-d', strtotime($b->expiry_date)) : date('Y-m-d', strtotime('+2 years')),
                    'rack_location' => $p->rack_location ?? '',
                ];
            }
        } else {
            $this->hasOpeningStock = false;
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

        Cache::forget('masters_'.$type);
        Cache::forget('masters_categories');
        Cache::forget('masters_manufacturers');
        Cache::forget('masters_generics');
        Cache::forget('masters_units');
        Cache::forget('masters_medicine_types');
        Cache::forget('masters_box_sizes');

        $this->reset(['newMasterName', 'newMasterDescription']);
        session()->flash('success', 'Master entry added successfully!');
    }

    // Bulk Excel Grid Operations
    public function addBulkRow(): void
    {
        $defaultUnit = Unit::first();
        $this->bulkRows[] = [
            'name' => '',
            'generic_id' => '',
            'category_id' => '',
            'manufacturer_id' => '',
            'unit_id' => $defaultUnit?->id ?? '',
            'purchase_price' => '',
            'mrp_price' => '',
            'quantity' => '',
            'batch_number' => '',
            'expiry_date' => date('Y-m-d', strtotime('+2 years')),
        ];
    }

    public function addMultipleBulkRows(int $count = 5): void
    {
        for ($i = 0; $i < $count; $i++) {
            $this->addBulkRow();
        }
    }

    public function removeBulkRow(int $index): void
    {
        unset($this->bulkRows[$index]);
        $this->bulkRows = array_values($this->bulkRows);
        if (empty($this->bulkRows)) {
            $this->addBulkRow();
        }
    }

    public function clearBulkRows(): void
    {
        $this->bulkRows = [];
        $this->addMultipleBulkRows(10);
    }

    public function importPastedTsv(string $rawTsv): void
    {
        $lines = explode("\n", trim($rawTsv));
        if (empty($lines)) {
            return;
        }

        $defaultUnit = Unit::first();
        $newRows = [];

        foreach ($lines as $line) {
            $line = trim($line, "\r");
            if (empty($line)) continue;
            
            // Tab or comma separated
            $cols = strpos($line, "\t") !== false ? explode("\t", $line) : str_getcsv($line);
            
            $name = trim($cols[0] ?? '');
            if (empty($name) || strtolower($name) === 'medicine name' || strtolower($name) === 'name') {
                continue; // Skip header row
            }

            $mrp = trim($cols[1] ?? '');
            $purchase = trim($cols[2] ?? '');
            $qty = trim($cols[3] ?? '');
            $batch = trim($cols[4] ?? '');
            $expiry = trim($cols[5] ?? date('Y-m-d', strtotime('+2 years')));

            $newRows[] = [
                'name' => $name,
                'generic_id' => '',
                'category_id' => '',
                'manufacturer_id' => '',
                'unit_id' => $defaultUnit?->id ?? '',
                'purchase_price' => is_numeric($purchase) ? $purchase : '',
                'mrp_price' => is_numeric($mrp) ? $mrp : '',
                'quantity' => is_numeric($qty) ? $qty : '',
                'batch_number' => $batch,
                'expiry_date' => !empty($expiry) ? $expiry : date('Y-m-d', strtotime('+2 years')),
            ];
        }

        if (!empty($newRows)) {
            $this->bulkRows = $newRows;
            session()->flash('success', count($newRows) . " rows pasted from Excel successfully!");
        }
    }

    public function loadSampleBulkData(): void
    {
        $defaultUnit = Unit::first();
        $cat = Category::first();
        $mfg = Manufacturer::first();
        $gen = Generic::first();

        $this->bulkRows = [
            [
                'name' => 'Napa Extra 500mg+65mg Tablet',
                'generic_id' => $gen?->id ?? '',
                'category_id' => $cat?->id ?? '',
                'manufacturer_id' => $mfg?->id ?? '',
                'unit_id' => $defaultUnit?->id ?? '',
                'purchase_price' => '2.50',
                'mrp_price' => '3.00',
                'quantity' => '100',
                'batch_number' => 'B-NE-'.rand(100, 999),
                'expiry_date' => date('Y-m-d', strtotime('+2 years')),
            ],
            [
                'name' => 'Seclo 20mg Capsule',
                'generic_id' => $gen?->id ?? '',
                'category_id' => $cat?->id ?? '',
                'manufacturer_id' => $mfg?->id ?? '',
                'unit_id' => $defaultUnit?->id ?? '',
                'purchase_price' => '5.00',
                'mrp_price' => '6.00',
                'quantity' => '50',
                'batch_number' => 'B-SC-'.rand(100, 999),
                'expiry_date' => date('Y-m-d', strtotime('+18 months')),
            ],
            [
                'name' => 'Sergel 20mg Capsule',
                'generic_id' => $gen?->id ?? '',
                'category_id' => $cat?->id ?? '',
                'manufacturer_id' => $mfg?->id ?? '',
                'unit_id' => $defaultUnit?->id ?? '',
                'purchase_price' => '6.50',
                'mrp_price' => '7.00',
                'quantity' => '60',
                'batch_number' => 'B-SG-'.rand(100, 999),
                'expiry_date' => date('Y-m-d', strtotime('+2 years')),
            ],
            [
                'name' => 'Maxpro 20mg Tablet',
                'generic_id' => $gen?->id ?? '',
                'category_id' => $cat?->id ?? '',
                'manufacturer_id' => $mfg?->id ?? '',
                'unit_id' => $defaultUnit?->id ?? '',
                'purchase_price' => '7.00',
                'mrp_price' => '8.00',
                'quantity' => '40',
                'batch_number' => 'B-MP-'.rand(100, 999),
                'expiry_date' => date('Y-m-d', strtotime('+2 years')),
            ],
            [
                'name' => 'Ace Plus 500mg+65mg Tablet',
                'generic_id' => $gen?->id ?? '',
                'category_id' => $cat?->id ?? '',
                'manufacturer_id' => $mfg?->id ?? '',
                'unit_id' => $defaultUnit?->id ?? '',
                'purchase_price' => '2.50',
                'mrp_price' => '3.00',
                'quantity' => '80',
                'batch_number' => 'B-AP-'.rand(100, 999),
                'expiry_date' => date('Y-m-d', strtotime('+20 months')),
            ],
        ];

        session()->flash('success', 'Sample medicine data loaded into Excel grid!');
    }

    public function saveBulkGrid(): void
    {
        $validRows = array_filter($this->bulkRows, fn ($r) => ! empty(trim($r['name'] ?? '')));

        if (empty($validRows)) {
            session()->flash('error', 'Please enter at least one medicine name in the table.');

            return;
        }

        $defaultUnit = Unit::firstOrCreate(['name' => 'Piece'], ['short_code' => 'pcs', 'conversion_factor' => 1, 'is_base_unit' => true]);
        $importedCount = 0;

        \Illuminate\Support\Facades\DB::transaction(function () use ($validRows, $defaultUnit, &$importedCount) {
            foreach ($validRows as $row) {
                $name = trim($row['name']);
                $unitId = ! empty($row['unit_id']) ? (int) $row['unit_id'] : $defaultUnit->id;
                $mrp = (float) ($row['mrp_price'] ?? 0);
                $purchase = (float) (! empty($row['purchase_price']) ? $row['purchase_price'] : ($mrp > 0 ? $mrp * 0.8 : 0));
                $qty = (int) ($row['quantity'] ?? 0);
                $batchNo = ! empty($row['batch_number']) ? trim($row['batch_number']) : 'OP-'.date('Ymd').'-'.rand(100, 999);
                $expiry = ! empty($row['expiry_date']) ? $row['expiry_date'] : date('Y-m-d', strtotime('+2 years'));

                $prod = Product::create([
                    'name' => $name,
                    'generic_id' => ! empty($row['generic_id']) ? (int) $row['generic_id'] : null,
                    'category_id' => ! empty($row['category_id']) ? (int) $row['category_id'] : null,
                    'manufacturer_id' => ! empty($row['manufacturer_id']) ? (int) $row['manufacturer_id'] : null,
                    'base_unit_id' => $unitId,
                    'sku' => 'SKU-'.strtoupper(substr(uniqid(), -6)),
                    'barcode' => 'BC'.date('ymd').rand(1000, 9999),
                    'status' => 'active',
                ]);

                ProductUnit::create([
                    'product_id' => $prod->id,
                    'unit_id' => $unitId,
                    'multiplier' => 1,
                    'purchase_price' => $purchase,
                    'mrp_price' => $mrp,
                    'is_default_sale' => true,
                    'is_default_purchase' => true,
                ]);

                if ($qty > 0) {
                    Batch::create([
                        'product_id' => $prod->id,
                        'batch_number' => $batchNo,
                        'base_unit_id' => $unitId,
                        'initial_stock' => $qty,
                        'current_stock' => $qty,
                        'purchase_rate' => $purchase,
                        'mrp_rate' => $mrp,
                        'manufacture_date' => date('Y-m-d'),
                        'expiry_date' => $expiry,
                    ]);
                }

                $importedCount++;
            }
        });

        session()->flash('success', "{$importedCount} medicines imported directly into catalog & inventory!");
        $this->clearBulkRows();
        $this->tab = 'all';
    }

    public function downloadSampleCsv()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="pharmacy_bulk_medicines_sample.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Medicine Name', 'MRP Price', 'Purchase Price', 'Opening Qty', 'Batch No', 'Expiry Date (YYYY-MM-DD)']);
            fputcsv($file, ['Napa Extra 500mg+65mg Tablet', '3.00', '2.50', '100', 'B-101', '2028-12-31']);
            fputcsv($file, ['Seclo 20mg Capsule', '6.00', '5.00', '50', 'B-102', '2028-06-30']);
            fputcsv($file, ['Sergel 20mg Capsule', '7.00', '6.50', '60', 'B-103', '2028-10-15']);
            fputcsv($file, ['Maxpro 20mg Tablet', '8.00', '7.00', '40', 'B-104', '2028-12-31']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function processBulkUpload()
    {
        $this->validate(['csvFile' => 'required|mimes:csv,txt|max:5120']);

        $path = $this->csvFile->getRealPath();
        $file = fopen($path, 'r');
        $header = fgetcsv($file);

        $count = 0;
        $defaultUnit = Unit::firstOrCreate(['name' => 'Piece'], ['short_code' => 'pcs', 'conversion_factor' => 1, 'is_base_unit' => true]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($file, $defaultUnit, &$count) {
            while (($row = fgetcsv($file)) !== false) {
                if (count($row) >= 2) {
                    $name = trim($row[0]);
                    $mrp = (float) ($row[1] ?? 0);
                    $purchase = (float) ($row[2] ?? ($mrp > 0 ? $mrp * 0.8 : 0));
                    $qty = (int) ($row[3] ?? 0);
                    $batchNo = ! empty($row[4]) ? trim($row[4]) : 'OP-'.date('Ymd').'-'.rand(100, 999);
                    $expiry = ! empty($row[5]) ? trim($row[5]) : date('Y-m-d', strtotime('+2 years'));

                    if (! empty($name)) {
                        $prod = Product::create([
                            'name' => $name,
                            'base_unit_id' => $defaultUnit->id,
                            'sku' => 'SKU-'.strtoupper(substr(uniqid(), -6)),
                            'barcode' => 'BC'.date('ymd').rand(1000, 9999),
                            'status' => 'active',
                        ]);

                        ProductUnit::create([
                            'product_id' => $prod->id,
                            'unit_id' => $defaultUnit->id,
                            'multiplier' => 1,
                            'purchase_price' => $purchase,
                            'mrp_price' => $mrp,
                            'is_default_sale' => true,
                            'is_default_purchase' => true,
                        ]);

                        if ($qty > 0) {
                            Batch::create([
                                'product_id' => $prod->id,
                                'batch_number' => $batchNo,
                                'base_unit_id' => $defaultUnit->id,
                                'initial_stock' => $qty,
                                'current_stock' => $qty,
                                'purchase_rate' => $purchase,
                                'mrp_rate' => $mrp,
                                'manufacture_date' => date('Y-m-d'),
                                'expiry_date' => $expiry,
                            ]);
                        }

                        $count++;
                    }
                }
            }
        });

        fclose($file);

        session()->flash('success', "Bulk uploaded and created {$count} medicines with inventory batches!");
        $this->tab = 'all';
    }

    public function render()
    {
        $products = $this->tab === 'all'
            ? Product::with(['generic', 'category', 'manufacturer', 'baseUnit', 'batches', 'productUnits.unit'])
                ->when($this->search, function ($q) {
                    $term = '%'.trim($this->search).'%';
                    $q->where('name', 'like', $term)
                        ->orWhere('sku', 'like', $term)
                        ->orWhere('barcode', 'like', $term);
                })
                ->when($this->filterCategory, fn ($q) => $q->where('category_id', $this->filterCategory))
                ->when($this->filterManufacturer, fn ($q) => $q->where('manufacturer_id', $this->filterManufacturer))
                ->latest('id')
                ->paginate($this->perPage)
            : new LengthAwarePaginator([], 0, $this->perPage);

        $expiredBatches = $this->tab === 'expired'
            ? Batch::with(['product'])
                ->where('current_stock', '>', 0)
                ->where('expiry_date', '<', now()->toDateString())
                ->latest('expiry_date')
                ->paginate($this->perPage)
            : new LengthAwarePaginator([], 0, $this->perPage);

        return view('livewire.products.product-manager', [
            'products' => $products,
            'expiredBatches' => $expiredBatches,
            'categories' => Cache::remember('masters_categories', 120, fn () => Category::orderBy('name')->get()),
            'manufacturers' => Cache::remember('masters_manufacturers', 120, fn () => Manufacturer::orderBy('name')->get()),
            'generics' => Cache::remember('masters_generics', 120, fn () => Generic::orderBy('name')->get()),
            'units' => Cache::remember('masters_units', 120, fn () => Unit::orderBy('name')->get()),
            'medicineTypes' => Cache::remember('masters_medicine_types', 120, fn () => MedicineType::orderBy('name')->get()),
            'boxSizes' => Cache::remember('masters_box_sizes', 120, fn () => BoxSize::orderBy('name')->get()),
            'taxes' => Cache::remember('masters_taxes', 120, fn () => Tax::orderBy('name')->get()),
        ]);
    }
}
