<div class="p-6 space-y-6 max-w-7xl mx-auto">
    {{-- Header & Add Action --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Medicine & Product Catalog</h1>
            <p class="text-xs text-slate-500 mt-1">Manage pharmaceutical products, multi-unit conversions, barcode mappings, and inventory alert levels.</p>
        </div>
        <button 
            wire:click="openCreateModal" 
            type="button" 
            class="px-4 py-2.5 bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-600 hover:to-blue-700 text-white rounded-lg text-xs font-bold shadow-md hover:shadow-lg transition flex items-center gap-1.5 self-start sm:self-auto cursor-pointer"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Add New Medicine</span>
        </button>
    </div>

    {{-- Alert Messages --}}
    @if($successMessage)
        <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded-lg flex items-center justify-between">
            <span>{{ $successMessage }}</span>
            <button wire:click="$set('successMessage', null)" class="text-emerald-500 hover:text-emerald-800">&times;</button>
        </div>
    @endif
    @if($errorMessage)
        <div class="p-3 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold rounded-lg flex items-center justify-between">
            <span>{{ $errorMessage }}</span>
            <button wire:click="$set('errorMessage', null)" class="text-rose-500 hover:text-rose-800">&times;</button>
        </div>
    @endif

    {{-- Filter & Search Toolbar --}}
    <div class="bg-white p-4 rounded-lg border border-slate-200/80 shadow-xs space-y-3">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            {{-- Universal Search --}}
            <div class="md:col-span-2 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input 
                    wire:model.live.debounce.300ms="search" 
                    type="text" 
                    placeholder="Search medicine brand, generic molecule, barcode, SKU..." 
                    class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-sky-500 font-medium placeholder:text-slate-400"
                >
            </div>

            {{-- Category Filter --}}
            <div>
                <select wire:model.live="selectedCategory" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg font-medium text-slate-700">
                    <option value="">All Categories</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Manufacturer Filter --}}
            <div>
                <select wire:model.live="selectedManufacturer" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg font-medium text-slate-700">
                    <option value="">All Manufacturers</option>
                    @foreach($manufacturers as $m)
                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Products Data Table --}}
    <div class="bg-white rounded-lg border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                    <tr>
                        <th class="py-3 px-4">Medicine Name</th>
                        <th class="py-3 px-3">Generic & Category</th>
                        <th class="py-3 px-3">Company</th>
                        <th class="py-3 px-3">Rack & SKU</th>
                        <th class="py-3 px-3 text-center">Stock Level</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($products as $prod)
                        @php
                            $stock = $prod->totalStockPieces();
                            $isLow = $stock <= $prod->min_stock_alert && $stock > 0;
                            $isOut = $stock <= 0;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                    <span>{{ $prod->name }}</span>
                                    @if($prod->is_narcotic)
                                        <span class="bg-rose-100 text-rose-700 text-[9px] font-extrabold px-1.5 py-0.5 rounded border border-rose-200">NARCOTIC</span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5">Base Unit: <span class="font-medium text-slate-600">{{ $prod->baseUnit?->name ?? 'Piece' }}</span></div>
                            </td>
                            <td class="py-3 px-3">
                                <div class="font-semibold text-sky-800">{{ $prod->generic?->name ?? 'N/A' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $prod->category?->name ?? 'General' }}</div>
                            </td>
                            <td class="py-3 px-3 font-medium text-slate-700">
                                {{ $prod->manufacturer?->name ?? 'N/A' }}
                            </td>
                            <td class="py-3 px-3">
                                <div class="font-mono text-slate-700 text-[11px]">{{ $prod->sku }}</div>
                                <div class="text-[11px] text-slate-400">Rack: <span class="font-semibold text-slate-600">{{ $prod->rack_location ?: 'N/A' }}</span></div>
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if($isOut)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                        0 {{ $prod->baseUnit?->name ?? 'pcs' }} (Out)
                                    </span>
                                @elseif($isLow)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        {{ $stock }} {{ $prod->baseUnit?->name ?? 'pcs' }} (Low)
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        {{ $stock }} {{ $prod->baseUnit?->name ?? 'pcs' }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $prod->status === 'active' ? 'bg-sky-50 text-sky-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ ucfirst($prod->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <button 
                                    wire:click="editProduct({{ $prod->id }})" 
                                    class="px-2.5 py-1 text-sky-700 hover:bg-sky-50 rounded-lg font-bold text-xs transition inline-flex items-center gap-1"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    <span>Edit</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 text-xs">No medicines match your search criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- Pagination --}}
        <div class="p-4 border-t border-slate-100">
            {{ $products->links() }}
        </div>
    </div>

    {{-- Product Create / Edit Modal --}}
    @if($showProductModal)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-3xl rounded-lg shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
                <div class="bg-gradient-to-r from-sky-600 to-blue-700 px-6 py-4 text-white flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold">{{ $editingProductId ? 'Edit Medicine' : 'Add New Medicine' }}</h2>
                        <p class="text-xs text-sky-100">Configure brand formulation, base atomic unit, and packaging multiplier hierarchy.</p>
                    </div>
                    <button wire:click="$set('showProductModal', false)" class="text-white hover:text-sky-200 text-xl font-bold">&times;</button>
                </div>

                <div class="p-6 space-y-4 overflow-y-auto flex-1 text-xs">
                    {{-- Row 1: Name, Generic, Category, Manufacturer --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="font-bold text-slate-700">Brand Name *</label>
                            <input wire:model="name" type="text" placeholder="e.g. Napa Extra 500mg" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500 font-medium">
                            @error('name') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="font-bold text-slate-700">Generic Molecule</label>
                            <select wire:model="generic_id" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500 font-medium">
                                <option value="">-- Select Generic --</option>
                                @foreach($generics as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="font-bold text-slate-700">Category</label>
                            <select wire:model="category_id" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500 font-medium">
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="font-bold text-slate-700">Manufacturer / Company</label>
                            <select wire:model="manufacturer_id" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500 font-medium">
                                <option value="">-- Select Company --</option>
                                @foreach($manufacturers as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Row 2: SKU, Barcode, Rack, Base Unit --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="font-bold text-slate-700">SKU Code *</label>
                            <input wire:model="sku" type="text" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg font-mono focus:ring-2 focus:ring-sky-500">
                            @error('sku') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="font-bold text-slate-700">Barcode</label>
                            <input wire:model="barcode" type="text" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg font-mono focus:ring-2 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="font-bold text-slate-700">Rack Location</label>
                            <input wire:model="rack_location" type="text" placeholder="Rack A-04" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="font-bold text-slate-700">Base Unit (Atomic) *</label>
                            <select wire:model="base_unit_id" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500 font-bold">
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->short_code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Row 3: Flags (Narcotic, Prescription, Min/Max stock, Tax) --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 bg-slate-50 p-3 rounded-lg border border-slate-200">
                        <div>
                            <label class="font-bold text-slate-700">Min Stock Alert</label>
                            <input wire:model="min_stock_alert" type="number" min="0" class="w-full py-1.5 px-2 mt-1 border border-slate-300 rounded-lg">
                        </div>
                        <div>
                            <label class="font-bold text-slate-700">Tax Rate (%)</label>
                            <input wire:model="tax_rate" type="number" min="0" class="w-full py-1.5 px-2 mt-1 border border-slate-300 rounded-lg">
                        </div>
                        <div class="flex items-center space-x-2 pt-4">
                            <input wire:model="is_narcotic" type="checkbox" id="narcotic_check" class="w-4 h-4 text-rose-600 rounded">
                            <label for="narcotic_check" class="font-bold text-rose-700">Narcotic Drug</label>
                        </div>
                        <div class="flex items-center space-x-2 pt-4">
                            <input wire:model="requires_prescription" type="checkbox" id="rx_check" class="w-4 h-4 text-sky-600 rounded">
                            <label for="rx_check" class="font-bold text-sky-800">Requires Rx</label>
                        </div>
                    </div>

                    {{-- Packaging Hierarchy & Multi-Unit Pricing Model --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <h4 class="font-bold text-slate-800 text-xs">Packaging Units & Pricing Hierarchy</h4>
                            <button wire:click="addPackagingUnitRow" type="button" class="text-xs text-sky-700 hover:text-sky-900 font-bold">+ Add Unit Tier</button>
                        </div>
                        <div class="space-y-2">
                            @foreach($packagingUnits as $pIdx => $pUnit)
                                <div class="grid grid-cols-5 gap-2 p-2 bg-slate-50 border border-slate-200 rounded-lg items-center">
                                    <div>
                                        <label class="text-[10px] text-slate-500 font-bold">Unit</label>
                                        <select wire:model="packagingUnits.{{ $pIdx }}.unit_id" class="w-full py-1 px-1.5 border border-slate-300 rounded font-medium">
                                            <option value="">Select</option>
                                            @foreach($units as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-[10px] text-slate-500 font-bold">Pieces / Unit</label>
                                        <input wire:model="packagingUnits.{{ $pIdx }}.multiplier" type="number" min="1" class="w-full py-1 px-1.5 border border-slate-300 rounded font-bold font-mono">
                                    </div>
                                    <div>
                                        <label class="text-[10px] text-slate-500 font-bold">Purchase (৳)</label>
                                        <input wire:model="packagingUnits.{{ $pIdx }}.purchase_price" type="number" min="0" step="0.01" class="w-full py-1 px-1.5 border border-slate-300 rounded font-mono">
                                    </div>
                                    <div>
                                        <label class="text-[10px] text-slate-500 font-bold">MRP (৳)</label>
                                        <input wire:model="packagingUnits.{{ $pIdx }}.mrp_price" type="number" min="0" step="0.01" class="w-full py-1 px-1.5 border border-slate-300 rounded font-bold font-mono">
                                    </div>
                                    <div class="text-center pt-3">
                                        @if($pIdx > 0)
                                            <button wire:click="removePackagingUnitRow({{ $pIdx }})" type="button" class="text-rose-500 hover:text-rose-700 font-bold">Remove</button>
                                        @else
                                            <span class="text-[10px] text-sky-700 font-bold">Base Unit</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end space-x-3">
                    <button wire:click="$set('showProductModal', false)" type="button" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-lg transition">
                        Cancel
                    </button>
                    <button wire:click="saveProduct" type="button" class="px-6 py-2 bg-sky-700 hover:bg-sky-800 text-white font-bold rounded-lg shadow-md transition">
                        Save Medicine
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
