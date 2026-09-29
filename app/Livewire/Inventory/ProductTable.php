<?php

namespace App\Livewire\Inventory;

use App\Models\Category;
use App\Models\Generic;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Unit;
use Exception;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Product Catalog & Inventory - PMS')]
class ProductTable extends Component
{
    use WithPagination;

    // Search & Filters
    public string $search = '';

    public string $selectedCategory = '';

    public string $selectedManufacturer = '';

    public string $selectedGeneric = '';

    public string $stockStatus = 'all'; // all, in_stock, low_stock, out_of_stock

    public string $narcoticFilter = 'all';

    public int $perPage = 15;

    public string $sortField = 'name';

    public string $sortDirection = 'asc';

    // Modal State: Create / Edit Product
    public bool $showProductModal = false;

    public ?int $editingProductId = null;

    public string $name = '';

    public ?int $generic_id = null;

    public ?int $category_id = null;

    public ?int $manufacturer_id = null;

    public ?int $base_unit_id = null;

    public string $sku = '';

    public string $barcode = '';

    public string $rack_location = '';

    public int $min_stock_alert = 20;

    public int $max_stock_level = 1000;

    public bool $is_narcotic = false;

    public bool $requires_prescription = false;

    public float $tax_rate = 0.00;

    public string $status = 'active';

    // Multi-unit packaging array: [['unit_id' => int, 'multiplier' => int, 'purchase_price' => float, 'mrp_price' => float, 'is_default_sale' => bool]]
    public array $packagingUnits = [];

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'generic_id' => 'nullable|exists:generics,id',
            'category_id' => 'nullable|exists:categories,id',
            'manufacturer_id' => 'nullable|exists:manufacturers,id',
            'base_unit_id' => 'required|exists:units,id',
            'sku' => 'required|string|max:50|unique:products,sku,'.$this->editingProductId,
            'barcode' => 'nullable|string|max:50',
            'rack_location' => 'nullable|string|max:50',
            'min_stock_alert' => 'required|integer|min:0',
            'max_stock_level' => 'required|integer|min:1',
            'is_narcotic' => 'boolean',
            'requires_prescription' => 'boolean',
            'tax_rate' => 'numeric|min:0',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy(string $field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openCreateModal()
    {
        $this->reset(['editingProductId', 'name', 'generic_id', 'category_id', 'manufacturer_id', 'rack_location', 'tax_rate']);
        $this->sku = 'MED-'.strtoupper(Str::random(6));
        $this->barcode = rand(100000000000, 999999999999);
        $this->min_stock_alert = 20;
        $this->max_stock_level = 1000;
        $this->is_narcotic = false;
        $this->requires_prescription = false;
        $this->status = 'active';

        $baseUnit = Unit::where('is_base_unit', true)->first() ?? Unit::first();
        $this->base_unit_id = $baseUnit?->id;

        $this->packagingUnits = [
            [
                'unit_id' => $baseUnit?->id,
                'multiplier' => 1,
                'purchase_price' => 0.00,
                'mrp_price' => 0.00,
                'is_default_sale' => true,
            ],
        ];

        $this->showProductModal = true;
    }

    public function addPackagingUnitRow()
    {
        $this->packagingUnits[] = [
            'unit_id' => null,
            'multiplier' => 10,
            'purchase_price' => 0.00,
            'mrp_price' => 0.00,
            'is_default_sale' => false,
        ];
    }

    public function removePackagingUnitRow(int $index)
    {
        unset($this->packagingUnits[$index]);
        $this->packagingUnits = array_values($this->packagingUnits);
    }

    public function editProduct(int $id)
    {
        $product = Product::with('productUnits')->findOrFail($id);
        $this->editingProductId = $product->id;
        $this->name = $product->name;
        $this->generic_id = $product->generic_id;
        $this->category_id = $product->category_id;
        $this->manufacturer_id = $product->manufacturer_id;
        $this->base_unit_id = $product->base_unit_id;
        $this->sku = $product->sku;
        $this->barcode = $product->barcode ?? '';
        $this->rack_location = $product->rack_location ?? '';
        $this->min_stock_alert = $product->min_stock_alert;
        $this->max_stock_level = $product->max_stock_level;
        $this->is_narcotic = $product->is_narcotic;
        $this->requires_prescription = $product->requires_prescription;
        $this->tax_rate = (float) $product->tax_rate;
        $this->status = $product->status;

        $this->packagingUnits = $product->productUnits->map(function ($pu) {
            return [
                'unit_id' => $pu->unit_id,
                'multiplier' => $pu->multiplier,
                'purchase_price' => (float) $pu->purchase_price,
                'mrp_price' => (float) $pu->mrp_price,
                'is_default_sale' => $pu->is_default_sale,
            ];
        })->toArray();

        $this->showProductModal = true;
    }

    public function saveProduct()
    {
        $this->validate();

        try {
            $product = Product::updateOrCreate(
                ['id' => $this->editingProductId],
                [
                    'name' => $this->name,
                    'generic_id' => $this->generic_id,
                    'category_id' => $this->category_id,
                    'manufacturer_id' => $this->manufacturer_id,
                    'base_unit_id' => $this->base_unit_id,
                    'sku' => $this->sku,
                    'barcode' => $this->barcode,
                    'rack_location' => $this->rack_location,
                    'min_stock_alert' => $this->min_stock_alert,
                    'max_stock_level' => $this->max_stock_level,
                    'is_narcotic' => $this->is_narcotic,
                    'requires_prescription' => $this->requires_prescription,
                    'tax_rate' => $this->tax_rate,
                    'status' => $this->status,
                ]
            );

            // Sync Packaging units
            ProductUnit::where('product_id', $product->id)->delete();
            foreach ($this->packagingUnits as $pu) {
                if (! empty($pu['unit_id'])) {
                    ProductUnit::create([
                        'product_id' => $product->id,
                        'unit_id' => $pu['unit_id'],
                        'multiplier' => max(1, (int) $pu['multiplier']),
                        'purchase_price' => (float) ($pu['purchase_price'] ?? 0),
                        'mrp_price' => (float) ($pu['mrp_price'] ?? 0),
                        'is_default_sale' => ! empty($pu['is_default_sale']),
                    ]);
                }
            }

            $this->showProductModal = false;
            $this->successMessage = "Medicine [{$product->name}] saved successfully!";
        } catch (Exception $e) {
            $this->errorMessage = 'Error saving product: '.$e->getMessage();
        }
    }

    public function render()
    {
        $query = Product::with(['category', 'generic', 'manufacturer', 'baseUnit', 'batches'])
            ->when($this->search, function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('sku', 'like', $term)
                        ->orWhere('barcode', 'like', $term)
                        ->orWhereHas('generic', fn ($g) => $g->where('name', 'like', $term));
                });
            })
            ->when($this->selectedCategory, fn ($q) => $q->where('category_id', $this->selectedCategory))
            ->when($this->selectedManufacturer, fn ($q) => $q->where('manufacturer_id', $this->selectedManufacturer))
            ->when($this->selectedGeneric, fn ($q) => $q->where('generic_id', $this->selectedGeneric))
            ->when($this->narcoticFilter !== 'all', fn ($q) => $q->where('is_narcotic', $this->narcoticFilter === 'yes'))
            ->orderBy($this->sortField, $this->sortDirection);

        $products = $query->paginate($this->perPage);

        return view('livewire.inventory.product-table', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'manufacturers' => Manufacturer::orderBy('name')->get(),
            'generics' => Generic::orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
        ]);
    }
}
