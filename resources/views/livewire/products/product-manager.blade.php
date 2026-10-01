<div class="py-6 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                <svg class="w-7 h-7 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                </svg>
                Medicine Master & Product Catalog
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Multi-unit packaging conversion (Piece/Strip/Box), generic molecules, cold chain, dosage forms, and catalog management.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a 
                href="{{ route('products.create') }}"
                wire:navigate
                class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg text-xs transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Register Medicine
            </a>
            <a 
                href="{{ route('products.bulk-upload') }}"
                wire:navigate
                class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded-lg text-xs transition border border-slate-300 shadow-xs flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                Bulk Import
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="p-3.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2 shadow-xs">
            <svg class="w-4 h-4 flex-shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4.5 12.75 6 6 9-13.5" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($tab === 'all')
        <!-- All Products Table -->
        <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-xs space-y-3">
            <div class="p-4 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-3 bg-slate-50/50">
                <div class="relative flex-1 max-w-md">
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search medicines by brand, generic, strength or SKU..."
                        class="w-full pl-9 pr-3.5 py-2 text-xs rounded-lg border border-slate-300 bg-white text-slate-800 placeholder-slate-400 focus:border-sky-600 focus:ring-2 focus:ring-sky-500/15"
                    />
                    <svg class="w-4 h-4 absolute left-3 top-2.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                </div>

                <div class="flex items-center gap-3">
                    <select wire:model.live="filterCategory" class="text-xs py-2 px-3 border border-slate-300 rounded-lg bg-white text-slate-700">
                        <option value="">All Categories</option>
                        @foreach ($categories as $c) <option value="{{ $c->id }}">{{ $c->name }}</option> @endforeach
                    </select>

                    <select wire:model.live="filterManufacturer" class="text-xs py-2 px-3 border border-slate-300 rounded-lg bg-white text-slate-700">
                        <option value="">All Manufacturers</option>
                        @foreach ($manufacturers as $m) <option value="{{ $m->id }}">{{ $m->name }}</option> @endforeach
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500 bg-slate-50">
                            <th class="px-4 py-3">Medicine Info</th>
                            <th class="px-4 py-3">Generic & Type</th>
                            <th class="px-4 py-3">Category / Mfg</th>
                            <th class="px-4 py-3">Packaging & Pricing</th>
                            <th class="px-4 py-3 text-center">Rack / Location</th>
                            <th class="px-4 py-3 text-center">Flags</th>
                            <th class="px-4 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        @forelse ($products as $prod)
                            @php
                                $base = $prod->productUnits->firstWhere('unit_id', $prod->base_unit_id);
                                $secondary = $prod->productUnits->where('unit_id', '!=', $prod->base_unit_id)->first();
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        @if ($prod->image_path)
                                            <img src="{{ asset('storage/' . $prod->image_path) }}" alt="{{ $prod->name }}" class="w-10 h-10 object-cover rounded-lg border border-slate-200 shadow-xs" />
                                        @else
                                            <div class="w-10 h-10 rounded-lg bg-sky-50 border border-sky-100 flex items-center justify-center text-sky-700 font-bold text-xs">
                                                {{ substr($prod->name, 0, 2) }}
                                            </div>
                                        @endif
                                        <div>
                                            <span class="font-bold text-slate-900 block text-xs">{{ $prod->name }}</span>
                                            @if ($prod->strength)
                                                <span class="text-[11px] text-sky-700 font-semibold">{{ $prod->strength }}</span>
                                            @endif
                                            <span class="text-[10px] text-slate-400 font-mono block">SKU: {{ $prod->sku }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-slate-800 font-medium block">{{ $prod->generic?->name ?? 'None' }}</span>
                                    <span class="text-[10px] text-slate-500">{{ $prod->dosage_form ?? $prod->medicineType?->name ?? 'General' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 inline-block mb-1">{{ $prod->category?->name ?? 'General' }}</span>
                                    <span class="text-[11px] text-slate-500 block">{{ $prod->manufacturer?->name ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="space-y-0.5 font-mono text-[11px]">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-slate-500 text-[10px]">1 {{ $prod->baseUnit?->name ?? 'Pc' }}:</span>
                                            <span class="font-bold text-sky-700">৳{{ number_format($base?->mrp_price ?? 0, 2) }}</span>
                                            <span class="text-slate-400 text-[10px]">(Buy: ৳{{ number_format($base?->purchase_price ?? 0, 2) }})</span>
                                        </div>
                                        @if ($secondary)
                                            <div class="flex items-center gap-1.5 text-[10px] text-slate-600">
                                                <span>1 {{ $secondary->unit?->name ?? 'Strip' }} ({{ $secondary->multiplier }} Pcs):</span>
                                                <span class="font-bold text-slate-800">৳{{ number_format($secondary->mrp_price, 2) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($prod->rack_location)
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-50 border border-amber-200 text-amber-800">{{ $prod->rack_location }}</span>
                                    @else
                                        <span class="text-slate-400 text-[11px]">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        @if ($prod->is_cold_chain)
                                            <span title="Cold Chain (Refrigerator)" class="px-1.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded text-[10px] font-bold">❄️ Cold</span>
                                        @endif
                                        @if ($prod->is_narcotic)
                                            <span title="Narcotic / Schedule-H" class="px-1.5 py-0.5 bg-rose-50 text-rose-700 border border-rose-200 rounded text-[10px] font-bold">💊 S-H</span>
                                        @endif
                                        @if ($prod->requires_prescription)
                                            <span title="Prescription Required" class="px-1.5 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded text-[10px] font-bold">📋 Rx</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button wire:click="editProduct({{ $prod->id }})" class="px-2.5 py-1 text-[11px] bg-sky-50 hover:bg-sky-100 text-sky-800 font-bold rounded-md transition cursor-pointer">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                                    No medicines found in catalog. Click "Register Medicine" to add.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3.5 border-t border-slate-200">
                {{ $products->links() }}
            </div>
        </div>
    @endif

    @if ($tab === 'create')
        <!-- User-Friendly, Modern Pharmacy Product Registration Form -->
        <form wire:submit="saveProduct" class="space-y-6" x-data="{ showAdvancedPricing: false }">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left 2 Columns: Main Form Workflow -->
                <div class="lg:col-span-2 space-y-5">
                    
                    <!-- 1. Basic Medicine Info -->
                    <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-xs space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center font-bold text-xs">1</span>
                                <div>
                                    <h3 class="font-bold text-sm text-slate-900">{{ __('Basic Medicine Information') }}</h3>
                                    <p class="text-[11px] text-slate-500">{{ __('Brand name, generic molecule, strength power and manufacturer company') }}</p>
                                </div>
                            </div>
                            <span class="text-[11px] text-slate-400 font-medium"><span class="text-rose-500">*</span> {{ __('* fields are required') }}</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Brand Name (Full width, prominent) -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-800 mb-1">
                                    {{ __('Medicine Brand Name') }} <span class="text-rose-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    wire:model.live.debounce.300ms="name" 
                                    placeholder="e.g. Napa Extra, Seclo 20, Maxpro, Sergel, Ceevit" 
                                    class="w-full text-sm font-bold text-slate-900 rounded-lg border border-slate-300 px-3.5 py-2.5 focus:border-sky-600 focus:ring-2 focus:ring-sky-500/15" 
                                    autofocus
                                />
                                @error('name') <span class="text-[11px] text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Generic Molecule -->
                            <div x-data="{ openQuickGeneric: false }">
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-xs font-semibold text-slate-700">{{ __('Generic Molecule / Active Ingredient') }}</label>
                                    <button 
                                        type="button" 
                                        @click="openQuickGeneric = true" 
                                        class="text-[11px] font-bold text-sky-600 hover:text-sky-700 hover:underline flex items-center gap-0.5 cursor-pointer">
                                        {{ __('+ Quick Generic') }}
                                    </button>
                                </div>
                                <select wire:model.live="genericId" class="w-full text-xs rounded-lg border border-slate-300 py-2 px-3 text-slate-800 focus:border-sky-600 focus:ring-sky-500/15 bg-white">
                                    <option value="">{{ __('-- Select Generic (or leave empty) --') }}</option>
                                    @foreach ($generics as $gen) 
                                        <option value="{{ $gen->id }}">{{ $gen->name }}</option> 
                                    @endforeach
                                </select>

                                <!-- Quick Generic Creation Modal -->
                                <div x-show="openQuickGeneric" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-xs p-4">
                                    <div @click.away="openQuickGeneric = false" class="bg-white rounded-xl shadow-xl border border-slate-200 w-full max-w-md p-5 space-y-4">
                                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                            <h4 class="font-bold text-sm text-slate-900">{{ __('Add Quick Generic') }}</h4>
                                            <button type="button" @click="openQuickGeneric = false" class="text-slate-400 hover:text-slate-600 text-lg leading-none cursor-pointer">&times;</button>
                                        </div>
                                        <div class="space-y-3 text-left">
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Generic Molecule Name') }} <span class="text-rose-500">*</span></label>
                                                <input type="text" wire:model="newMasterName" placeholder="e.g. Paracetamol, Omeprazole, Montelukast" class="w-full text-xs rounded-lg border border-slate-300 py-2 px-3 focus:border-sky-600" />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Therapeutic Use / Indication (Optional)') }}</label>
                                                <input type="text" wire:model="newMasterDescription" placeholder="e.g. Fever, Pain relief, Gastric acidity" class="w-full text-xs rounded-lg border border-slate-300 py-2 px-3 focus:border-sky-600" />
                                            </div>
                                        </div>
                                        <div class="flex justify-end gap-2 pt-2">
                                            <button type="button" @click="openQuickGeneric = false" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer">{{ __('Cancel') }}</button>
                                            <button type="button" wire:click="addMaster('generic')" @click="openQuickGeneric = false" class="px-4 py-1.5 rounded-lg text-xs font-bold bg-sky-600 hover:bg-sky-700 text-white shadow-xs cursor-pointer">
                                                {{ __('Save & Select') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Strength / Power -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Strength / Power') }}</label>
                                <input 
                                    type="text" 
                                    wire:model.live.debounce.300ms="strength" 
                                    placeholder="e.g. 500mg, 20mg, 100ml, 5mg/5ml" 
                                    class="w-full text-xs rounded-lg border border-slate-300 py-2 px-3 text-slate-800 focus:border-sky-600 focus:ring-sky-500/15" 
                                />
                            </div>

                            <!-- Dosage Form / Medicine Type -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Dosage Form / Medicine Type') }}</label>
                                <select wire:model.live="medicineTypeId" class="w-full text-xs rounded-lg border border-slate-300 py-2 px-3 text-slate-800 focus:border-sky-600 focus:ring-sky-500/15 bg-white">
                                    <option value="">{{ __('-- Select Dosage Form --') }}</option>
                                    @foreach ($medicineTypes as $type) 
                                        <option value="{{ $type->id }}">{{ $type->name }}</option> 
                                    @endforeach
                                </select>
                            </div>

                            <!-- Category -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Category') }}</label>
                                <select wire:model.live="categoryId" class="w-full text-xs rounded-lg border border-slate-300 py-2 px-3 text-slate-800 focus:border-sky-600 focus:ring-sky-500/15 bg-white">
                                    <option value="">{{ __('-- Select Category --') }}</option>
                                    @foreach ($categories as $cat) 
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option> 
                                    @endforeach
                                </select>
                            </div>

                            <!-- Manufacturer -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Manufacturer / Company') }}</label>
                                <select wire:model.live="manufacturerId" class="w-full text-xs rounded-lg border border-slate-300 py-2 px-3 text-slate-800 focus:border-sky-600 focus:ring-sky-500/15 bg-white">
                                    <option value="">{{ __('-- Select Manufacturer --') }}</option>
                                    @foreach ($manufacturers as $mfg) 
                                        <option value="{{ $mfg->id }}">{{ $mfg->name }}</option> 
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Pricing, Margin & Smart Packaging (Intuitive & Clean) -->
                    <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-xs space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">2</span>
                                <div>
                                    <h3 class="font-bold text-sm text-slate-900">{{ __('Pricing, Margin & Smart Packaging') }}</h3>
                                    <p class="text-[11px] text-slate-500">{{ __('Enter single piece buy & sell rate, box and strip prices calculate automatically') }}</p>
                                </div>
                            </div>
                            <span class="text-[10px] bg-emerald-50 text-emerald-800 font-bold px-2.5 py-1 rounded-full border border-emerald-200 self-start sm:self-auto">
                                {{ __('⚡ Auto Profit Calculator') }}
                            </span>
                        </div>

                        <!-- Main 1-Piece Pricing Row -->
                        <div class="bg-slate-50 p-4 rounded-lg border border-slate-200/80 space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <!-- Base Unit Selector -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-800 mb-1">{{ __('Base Unit') }} <span class="text-rose-500">*</span></label>
                                    <select wire:model.live="baseUnitId" class="w-full text-xs font-bold text-slate-900 rounded-lg border border-slate-300 py-2 px-2.5 bg-white focus:border-sky-600 focus:ring-sky-500/15">
                                        @foreach ($units as $u)
                                            <option value="{{ $u->id }}" @selected((int)$baseUnitId === (int)$u->id)>
                                                {{ $u->name }} ({{ $u->short_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- 1-Pcs Buy Price -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-800 mb-1">{{ __('1-Piece Buy Price (৳)') }}</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-xs font-bold text-slate-400 font-mono">৳</span>
                                        <input 
                                            type="number" 
                                            step="0.0001" 
                                            wire:model.live.debounce.300ms="purchasePrice" 
                                            placeholder="0.00" 
                                            class="w-full pl-7 pr-3 py-2 text-xs font-mono font-bold text-slate-900 rounded-lg border border-slate-300 focus:border-sky-600 focus:ring-sky-500/15 bg-white" 
                                        />
                                    </div>
                                </div>

                                <!-- 1-Pcs MRP / Sell Price -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-800 mb-1">{{ __('1-Piece Sales Price (MRP ৳)') }} <span class="text-rose-500">*</span></label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-xs font-bold text-sky-700 font-mono">৳</span>
                                        <input 
                                            type="number" 
                                            step="0.01" 
                                            wire:model.live.debounce.300ms="mrpPrice" 
                                            placeholder="0.00" 
                                            class="w-full pl-7 pr-3 py-2 text-xs font-mono font-bold text-sky-800 rounded-lg border border-sky-300 focus:border-sky-600 focus:ring-sky-500/15 bg-white" 
                                        />
                                    </div>
                                    @error('mrpPrice') <span class="text-[10px] text-rose-500 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Live Profit Calculation Display -->
                            @php
                                $baseBuy = (float)$purchasePrice;
                                $baseSell = (float)$mrpPrice;
                                $profitPerPiece = $baseSell - $baseBuy;
                                $marginPct = $baseSell > 0 ? round(($profitPerPiece / $baseSell) * 100, 1) : 0;
                            @endphp
                            <div class="flex items-center justify-between px-3.5 py-2 rounded-lg {{ $baseSell > 0 && $profitPerPiece >= 0 ? 'bg-emerald-100/70 border border-emerald-200 text-emerald-900' : 'bg-slate-100 border border-slate-200 text-slate-600' }}">
                                <div class="flex items-center gap-2 text-xs font-bold">
                                    <svg class="w-4 h-4 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" /></svg>
                                    <span>{{ __('Profit per piece:') }}</span>
                                </div>
                                <div class="font-mono font-bold text-xs flex items-center gap-2">
                                    @if ($baseSell > 0)
                                        <span class="{{ $profitPerPiece >= 0 ? 'text-emerald-800' : 'text-rose-600' }}">
                                            ৳{{ number_format($profitPerPiece, 2) }} {{ __('/ Piece') }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded text-[11px] {{ $profitPerPiece >= 0 ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                            {{ $marginPct }}% {{ __('profit') }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 font-normal text-[11px]">{{ __('Enter buy and sell price') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- 1-Click Packaging Presets & Add Unit Button -->
                        <div class="space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <label class="block text-xs font-bold text-slate-700">{{ __('⚡ Select quick packaging preset:') }}</label>
                                <button type="button" wire:click="addPackagingUnit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-xs transition cursor-pointer self-start sm:self-auto">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                    <span>{{ __('+ Add Packaging Unit') }}</span>
                                </button>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" wire:click="applyPreset('10x10')" class="px-3 py-1.5 text-xs rounded-lg border font-bold transition cursor-pointer bg-white text-slate-700 border-slate-300 hover:bg-sky-50 hover:border-sky-300 hover:text-sky-800 shadow-2xs">
                                    {{ __('💊 10 × 10 Strip (100 Pcs Box)') }}
                                </button>
                                <button type="button" wire:click="applyPreset('5x10')" class="px-3 py-1.5 text-xs rounded-lg border font-bold transition cursor-pointer bg-white text-slate-700 border-slate-300 hover:bg-sky-50 hover:border-sky-300 hover:text-sky-800 shadow-2xs">
                                    {{ __('💊 5 × 10 Strip (50 Pcs Box)') }}
                                </button>
                                <button type="button" wire:click="applyPreset('3x10')" class="px-3 py-1.5 text-xs rounded-lg border font-bold transition cursor-pointer bg-white text-slate-700 border-slate-300 hover:bg-sky-50 hover:border-sky-300 hover:text-sky-800 shadow-2xs">
                                    {{ __('💊 3 × 10 Strip (30 Pcs Box)') }}
                                </button>
                                <button type="button" wire:click="applyPreset('2x14')" class="px-3 py-1.5 text-xs rounded-lg border font-bold transition cursor-pointer bg-white text-slate-700 border-slate-300 hover:bg-sky-50 hover:border-sky-300 hover:text-sky-800 shadow-2xs">
                                    {{ __('💊 2 × 14 Strip (28 Pcs Box)') }}
                                </button>
                                <button type="button" wire:click="applyPreset('single')" class="px-3 py-1.5 text-xs rounded-lg border font-bold transition cursor-pointer bg-white text-slate-700 border-slate-300 hover:bg-sky-50 hover:border-sky-300 hover:text-sky-800 shadow-2xs">
                                    {{ __('🧴 1 Piece / Bottle (Single Pack)') }}
                                </button>
                            </div>
                        </div>

                        <!-- Packaging Breakdown Table (Always visible or has clear Add action) -->
                        <div class="border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                            <div class="bg-slate-50 px-3.5 py-2.5 border-b border-slate-200 text-xs font-bold text-slate-800 flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-sky-600"></span>
                                    {{ __('Packaging Breakdown Table:') }}
                                </span>
                                <span class="text-[11px] font-normal text-slate-500">{{ __('Total tiers:') }} {{ count($packagingUnits) + 1 }}</span>
                            </div>

                            @if (count($packagingUnits) > 0)
                                <table class="w-full text-left text-xs border-collapse">
                                    <thead>
                                        <tr class="bg-white text-[11px] font-bold uppercase text-slate-600 border-b border-slate-200">
                                            <th class="p-2.5">{{ __('Packaging Unit') }}</th>
                                            <th class="p-2.5 w-28">{{ __('Size (Pieces)') }}</th>
                                            <th class="p-2.5">{{ __('Buy Price (৳)') }}</th>
                                            <th class="p-2.5">{{ __('MRP Price (৳)') }}</th>
                                            <th class="p-2.5 text-right">{{ __('Profit (৳)') }}</th>
                                            <th class="p-2.5 text-center w-12">{{ __('Delete') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        @foreach ($packagingUnits as $index => $pack)
                                            @php
                                                $pBuy = (float) ($pack['purchase_price'] ?? 0);
                                                $pMrp = (float) ($pack['mrp_price'] ?? 0);
                                                $pProfit = $pMrp - $pBuy;
                                            @endphp
                                            <tr wire:key="pack-row-{{ $index }}" class="hover:bg-slate-50/70 transition">
                                                <td class="p-2.5">
                                                    <select wire:model.live="packagingUnits.{{ $index }}.unit_id" class="w-full text-xs font-medium py-1 px-2 bg-white border border-slate-300 rounded-md">
                                                        @foreach ($units as $u)
                                                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->short_code }})</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="p-2.5">
                                                    <div class="flex items-center gap-1">
                                                        <input type="number" min="1" wire:model.live.debounce.300ms="packagingUnits.{{ $index }}.multiplier" class="w-14 text-xs font-bold font-mono text-center py-1 px-1 rounded border border-slate-300" />
                                                        <span class="text-[11px] text-slate-500">{{ __('Pcs') }}</span>
                                                    </div>
                                                </td>
                                                <td class="p-2.5">
                                                    <input type="number" step="0.01" wire:model.live.debounce.300ms="packagingUnits.{{ $index }}.purchase_price" placeholder="0.00" class="w-full text-xs font-mono font-bold py-1 px-2 rounded border border-slate-300" />
                                                </td>
                                                <td class="p-2.5">
                                                    <input type="number" step="0.01" wire:model.live.debounce.300ms="packagingUnits.{{ $index }}.mrp_price" placeholder="0.00" class="w-full text-xs font-mono font-bold text-sky-800 py-1 px-2 rounded border border-slate-300" />
                                                </td>
                                                <td class="p-2.5 text-right font-mono font-bold text-xs">
                                                    <span class="{{ $pProfit >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                                                        ৳{{ number_format($pProfit, 2) }}
                                                    </span>
                                                </td>
                                                <td class="p-2.5 text-center">
                                                    <button type="button" wire:click="removePackagingUnit({{ $index }})" class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded transition cursor-pointer font-bold" title="{{ __('Delete') }}">
                                                        ✕
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="p-6 text-center bg-white space-y-2">
                                    <p class="text-xs text-slate-500">{{ __('Currently only 1 base unit is configured. Add packaging to manage strips or boxes.') }}</p>
                                    <button type="button" wire:click="addPackagingUnit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-sky-50 text-sky-800 border border-sky-200 text-xs font-bold hover:bg-sky-100 transition cursor-pointer">
                                        <svg class="w-4 h-4 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                        <span>{{ __('+ Add Strip / Box Packaging Tier') }}</span>
                                    </button>
                                </div>
                            @endif

                            <div class="p-2.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                                <button type="button" wire:click="addPackagingUnit" class="text-xs font-bold text-sky-700 hover:text-sky-900 inline-flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                    <span>{{ __('+ Add another packaging tier') }}</span>
                                </button>
                                <span class="text-[11px] text-slate-400">{{ __('Strip and box prices update automatically') }}</span>
                            </div>
                        </div>

                        <!-- Collapsible Advanced Pricing (Tax, Wholesale, Dealer) -->
                        <div class="border-t border-slate-100 pt-3">
                            <button 
                                type="button" 
                                @click="showAdvancedPricing = !showAdvancedPricing" 
                                class="text-xs font-bold text-slate-600 hover:text-sky-700 flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-4 h-4 transition-transform duration-200" :class="showAdvancedPricing ? 'rotate-90 text-sky-600' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                                <span>{{ __('Advanced Pricing Settings (Tax, Wholesale, Dealer)') }}</span>
                                <span class="text-[10px] text-slate-400 font-normal">{{ __('(Click if needed)') }}</span>
                            </button>

                            <div x-show="showAdvancedPricing" x-collapse class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3 pt-3 border-t border-slate-100 bg-slate-50/70 p-3.5 rounded-lg">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Wholesale Price (৳)') }}</label>
                                    <input type="number" step="0.01" wire:model="wholesalePrice" placeholder="0.00" class="w-full text-xs font-mono font-bold rounded-lg border border-slate-300 py-1.5 px-2 bg-white" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Dealer Price (৳)') }}</label>
                                    <input type="number" step="0.01" wire:model="dealerPrice" placeholder="0.00" class="w-full text-xs font-mono font-bold rounded-lg border border-slate-300 py-1.5 px-2 bg-white" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Tax / VAT') }}</label>
                                    <select wire:model.live="taxId" class="w-full text-xs rounded-lg border border-slate-300 py-1.5 px-2 bg-white">
                                        <option value="">{{ __('No Tax (0%)') }}</option>
                                        @foreach ($taxes as $t)
                                            <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->rate }}%)</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Initial Stock, Batch & Expiry Management -->
                    <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-xs space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs">3</span>
                                <div>
                                    <h3 class="font-bold text-sm text-slate-900">{{ __('Initial Stock, Batches & Expiry') }}</h3>
                                    <p class="text-[11px] text-slate-500">{{ __('Log initial stock on hand, batch number and expiry date when registering medicine') }}</p>
                                </div>
                            </div>
                            
                            <!-- Opening Stock Toggle Switch -->
                            <label class="inline-flex items-center gap-2 cursor-pointer select-none bg-slate-100 hover:bg-slate-200/80 px-3 py-1.5 rounded-lg border border-slate-200 transition">
                                <input type="checkbox" wire:model.live="hasOpeningStock" class="w-4 h-4 text-sky-600 rounded border-slate-300 focus:ring-sky-500 cursor-pointer" />
                                <span class="text-xs font-bold text-slate-800">{{ __('Add Opening Stock') }}</span>
                            </label>
                        </div>

                        @if ($hasOpeningStock)
                            <div class="space-y-3">
                                @php
                                    $totalOpeningPieces = 0;
                                    $totalOpeningCost = 0;
                                    $totalOpeningSales = 0;
                                @endphp

                                @foreach ($initialBatches as $bIndex => $bRow)
                                    @php
                                        $selectedUnitId = (int) ($bRow['unit_id'] ?? $baseUnitId);
                                        $uMultiplier = 1;
                                        if ($selectedUnitId !== (int) $baseUnitId) {
                                            $pack = collect($packagingUnits)->firstWhere('unit_id', $selectedUnitId);
                                            if ($pack) {
                                                $uMultiplier = (int) ($pack['multiplier'] ?? 1);
                                            }
                                        }
                                        $rowQty = (int) ($bRow['quantity'] ?? 0);
                                        $rowBasePieces = $rowQty * $uMultiplier;
                                        $rowBuyRate = (float) (!empty($bRow['purchase_price']) && $bRow['purchase_price'] > 0 ? $bRow['purchase_price'] : ($purchasePrice * $uMultiplier));
                                        $rowMrpRate = (float) (!empty($bRow['mrp_price']) && $bRow['mrp_price'] > 0 ? $bRow['mrp_price'] : ($mrpPrice * $uMultiplier));
                                        
                                        $rowTotalBuy = $rowQty * $rowBuyRate;
                                        $rowTotalMrp = $rowQty * $rowMrpRate;

                                        $totalOpeningPieces += $rowBasePieces;
                                        $totalOpeningCost += $rowTotalBuy;
                                        $totalOpeningSales += $rowTotalMrp;
                                    @endphp

                                    <div wire:key="init-batch-row-{{ $bIndex }}" class="p-4 rounded-xl border border-indigo-100 bg-gradient-to-r from-slate-50 to-indigo-50/20 shadow-2xs space-y-3">
                                        <div class="flex items-center justify-between border-b border-slate-200/70 pb-2">
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-0.5 rounded bg-indigo-600 text-white font-bold text-[10px] uppercase tracking-wider">
                                                    {{ __('Batch #') }}{{ $bIndex + 1 }}
                                                </span>
                                                <span class="text-xs font-bold text-slate-800">
                                                    {{ !empty($bRow['batch_number']) ? $bRow['batch_number'] : __('New Batch') }}
                                                </span>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                @if ($rowBasePieces > 0)
                                                    <span class="text-[11px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full border border-emerald-200">
                                                        {{ __('Total =') }} {{ $rowBasePieces }} {{ __('Pcs') }}
                                                    </span>
                                                @endif
                                                @if (count($initialBatches) > 1)
                                                    <button 
                                                        type="button" 
                                                        wire:click="removeBatchRow({{ $bIndex }})" 
                                                        class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 p-1 rounded transition text-xs font-bold cursor-pointer"
                                                        title="{{ __('Delete Batch') }}"
                                                    >
                                                        ✕ {{ __('Delete') }}
                                                    </button>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                            <!-- 1. Batch Number with Auto-gen -->
                                            <div>
                                                <div class="flex items-center justify-between mb-1">
                                                    <label class="block text-xs font-bold text-slate-800">{{ __('Batch Number (Batch No)') }} <span class="text-rose-500">*</span></label>
                                                    <button type="button" wire:click="generateBatchNumber({{ $bIndex }})" class="text-[10px] font-bold text-sky-600 hover:text-sky-800 cursor-pointer">
                                                        {{ __('⚡ Auto') }}
                                                    </button>
                                                </div>
                                                <input 
                                                    type="text" 
                                                    wire:model="initialBatches.{{ $bIndex }}.batch_number" 
                                                    placeholder="e.g. B-2026-10" 
                                                    class="w-full text-xs font-mono font-bold uppercase rounded-lg border border-slate-300 py-2 px-2.5 focus:border-sky-600 bg-white" 
                                                />
                                            </div>

                                            <!-- 2. Unit & Quantity -->
                                            <div>
                                                <label class="block text-xs font-bold text-slate-800 mb-1">{{ __('Quantity & Unit') }} <span class="text-rose-500">*</span></label>
                                                <div class="flex items-center gap-1.5">
                                                    <input 
                                                        type="number" 
                                                        min="1" 
                                                        wire:model.live.debounce.300ms="initialBatches.{{ $bIndex }}.quantity" 
                                                        placeholder="Qty" 
                                                        class="w-20 text-xs font-mono font-bold text-slate-900 rounded-lg border border-slate-300 py-2 px-2 text-center bg-white focus:border-sky-600" 
                                                    />
                                                    <select wire:model.live="initialBatches.{{ $bIndex }}.unit_id" class="flex-1 text-xs font-semibold rounded-lg border border-slate-300 py-2 px-2 bg-white focus:border-sky-600">
                                                        @foreach ($units as $u)
                                                            <option value="{{ $u->id }}">
                                                                {{ $u->name }} ({{ $u->short_code }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- 3. Expiry Date with 1-Click Chips -->
                                            <div class="sm:col-span-2 lg:col-span-2">
                                                <div class="flex items-center justify-between mb-1">
                                                    <label class="block text-xs font-bold text-rose-700">{{ __('Expiry Date') }} <span class="text-rose-500">*</span></label>
                                                    <div class="flex items-center gap-1">
                                                        <button type="button" wire:click="setBatchExpiryPreset({{ $bIndex }}, '1 year')" class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-200 text-slate-700 hover:bg-sky-100 hover:text-sky-800 cursor-pointer">{{ __('+1 Year') }}</button>
                                                        <button type="button" wire:click="setBatchExpiryPreset({{ $bIndex }}, '2 years')" class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-200 text-slate-700 hover:bg-sky-100 hover:text-sky-800 cursor-pointer">{{ __('+2 Years') }}</button>
                                                        <button type="button" wire:click="setBatchExpiryPreset({{ $bIndex }}, '3 years')" class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-200 text-slate-700 hover:bg-sky-100 hover:text-sky-800 cursor-pointer">{{ __('+3 Years') }}</button>
                                                    </div>
                                                </div>
                                                <input 
                                                    type="date" 
                                                    wire:model="initialBatches.{{ $bIndex }}.expiry_date" 
                                                    class="w-full text-xs font-bold text-rose-600 rounded-lg border border-rose-300 focus:border-rose-500 focus:ring-rose-500/20 py-2 px-2.5 bg-white" 
                                                />
                                            </div>
                                        </div>

                                        <!-- Extra Batch Details (Mfg Date & Rack) -->
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-slate-200/50">
                                            <div class="flex items-center gap-2">
                                                <label class="text-[11px] font-semibold text-slate-600 whitespace-nowrap">{{ __('Manufacturing Date (Mfg):') }}</label>
                                                <input 
                                                    type="date" 
                                                    wire:model="initialBatches.{{ $bIndex }}.manufacturing_date" 
                                                    class="flex-1 text-xs rounded-lg border border-slate-300 py-1.5 px-2 bg-white" 
                                                />
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <label class="text-[11px] font-semibold text-slate-600 whitespace-nowrap">{{ __('Rack Location:') }}</label>
                                                <input 
                                                    type="text" 
                                                    wire:model="initialBatches.{{ $bIndex }}.rack_location" 
                                                    placeholder="e.g. Rack A-1, Drawer 3" 
                                                    class="flex-1 text-xs font-bold text-slate-800 rounded-lg border border-slate-300 py-1.5 px-2 bg-white" 
                                                />
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                <!-- Add Another Batch Button & Total Summary Card -->
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
                                    <button 
                                        type="button" 
                                        wire:click="addBatchRow" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-xs transition cursor-pointer self-start sm:self-auto"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                        <span>{{ __('+ Add Another Batch') }}</span>
                                    </button>

                                    <div class="flex items-center gap-3 text-xs">
                                        <div class="text-right">
                                            <span class="text-slate-500 text-[11px] block">{{ __('Total Opening Stock:') }}</span>
                                            <span class="font-mono font-black text-sky-800 text-sm">{{ $totalOpeningPieces }} {{ __('Pcs') }}</span>
                                        </div>
                                        <div class="text-right border-l border-slate-200 pl-3">
                                            <span class="text-slate-500 text-[11px] block">{{ __('Total Opening Cost Valuation:') }}</span>
                                            <span class="font-mono font-bold text-slate-800">৳{{ number_format($totalOpeningCost, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="p-6 text-center bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                                <div class="text-2xl">📦</div>
                                <div class="font-bold text-xs text-slate-700">{{ __('No initial stock currently logged') }}</div>
                                <p class="text-[11px] text-slate-500 max-w-md mx-auto">{{ __('You can receive stock later via Purchase Inward (GRN) or add initial inventory batches right now.') }}</p>
                                <button 
                                    type="button" 
                                    wire:click="$set('hasOpeningStock', true)" 
                                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg text-xs transition cursor-pointer shadow-xs"
                                >
                                    {{ __('+ Add Initial Stock & Batches') }}
                                </button>
                            </div>
                        @endif

                        <!-- Min Stock Alert Setting -->
                        <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-800">{{ __('Low Stock Reorder Alert') }}</label>
                                <span class="text-[11px] text-slate-400">{{ __('System will generate requisition alerts when physical stock drops below this threshold') }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <input 
                                    type="number" 
                                    min="0" 
                                    wire:model="minStockAlert" 
                                    class="w-24 text-xs font-bold text-rose-600 rounded-lg border border-slate-300 py-1.5 px-3 text-center bg-white" 
                                />
                                <span class="text-xs font-bold text-slate-600">{{ __('Pcs') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right 1 Column: Live Preview & Quick Actions -->
                <div class="space-y-5">
                    
                    <!-- Live Medicine Summary Card Preview -->
                    <div class="rounded-lg p-5 shadow-sm space-y-3.5 border border-sky-700" style="background-color: #0284c7 !important; color: #ffffff !important;">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded text-white" style="background-color: rgba(255,255,255,0.2) !important;">
                                {{ __('Live Preview') }}
                            </span>
                            <span class="text-xs font-mono font-bold text-sky-100">{{ $stockType }} Stock</span>
                        </div>

                        <div>
                            <h4 class="text-lg font-black tracking-tight leading-tight text-white">
                                {{ $name ? $name : __('Medicine Brand Name') }}
                            </h4>
                            <div class="flex items-center gap-2 mt-1 text-xs text-sky-100 font-semibold">
                                @if ($strength) 
                                    <span class="px-1.5 py-0.5 rounded text-[11px] font-bold text-white" style="background-color: rgba(255,255,255,0.2) !important;">{{ $strength }}</span> 
                                @endif
                                <span class="text-white">{{ $medicineTypes->firstWhere('id', $medicineTypeId)?->name ?? 'Tablet / Syrup' }}</span>
                            </div>
                            <p class="text-[11px] text-sky-100 mt-1 font-medium italic">
                                {{ $generics->firstWhere('id', $genericId)?->name ?? 'Generic Molecule' }}
                            </p>
                        </div>

                        <div class="pt-3 border-t border-sky-500/50 grid grid-cols-2 gap-2 text-xs">
                            <div class="rounded p-2" style="background-color: rgba(255,255,255,0.15) !important;">
                                <span class="text-[10px] text-sky-100 block">{{ __('1-Piece Sales Price') }}</span>
                                <span class="text-sm font-black font-mono text-white">৳{{ number_format((float)$mrpPrice, 2) }}</span>
                            </div>
                            <div class="rounded p-2" style="background-color: rgba(255,255,255,0.15) !important;">
                                <span class="text-[10px] text-sky-100 block">{{ __('1-Piece Buy Price') }}</span>
                                <span class="text-sm font-bold font-mono text-white">৳{{ number_format((float)$purchasePrice, 2) }}</span>
                            </div>
                        </div>

                        @if ($manufacturers->firstWhere('id', $manufacturerId))
                            <div class="text-[11px] text-sky-100 flex items-center justify-between pt-1">
                                <span>{{ __('Company:') }}</span>
                                <span class="font-bold text-white">{{ $manufacturers->firstWhere('id', $manufacturerId)->name }}</span>
                            </div>
                        @endif

                        @if ($hasOpeningStock && !empty($initialBatches))
                            @php
                                $prevPcs = 0;
                                foreach ($initialBatches as $ib) {
                                    $uId = (int) ($ib['unit_id'] ?? $baseUnitId);
                                    $m = 1;
                                    if ($uId !== (int) $baseUnitId) {
                                        $pUnit = collect($packagingUnits)->firstWhere('unit_id', $uId);
                                        if ($pUnit) $m = (int) ($pUnit['multiplier'] ?? 1);
                                    }
                                    $prevPcs += ((int) ($ib['quantity'] ?? 0)) * $m;
                                }
                                $firstExp = !empty($initialBatches[0]['expiry_date']) ? $initialBatches[0]['expiry_date'] : null;
                            @endphp
                            <div class="pt-2 border-t border-sky-500/40 text-[11px] space-y-1">
                                <div class="flex items-center justify-between text-sky-100">
                                    <span>{{ __('Initial Total Stock:') }}</span>
                                    <span class="font-bold text-white font-mono bg-white/20 px-2 py-0.5 rounded">{{ $prevPcs }} {{ __('Pcs') }}</span>
                                </div>
                                @if ($firstExp)
                                    <div class="flex items-center justify-between text-sky-100">
                                        <span>{{ __('Expiry Date:') }}</span>
                                        <span class="font-bold text-amber-200 font-mono">{{ date('d M, Y', strtotime($firstExp)) }}</span>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if ($rackLocation)
                            <div class="text-[11px] text-sky-100 flex items-center justify-between">
                                <span>{{ __('Rack Location:') }}</span>
                                <span class="font-bold text-amber-200">{{ $rackLocation }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Barcode Generation -->
                    <div class="bg-white rounded-lg p-4 border border-slate-200 shadow-xs space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-800">{{ __('Barcode (Barcode / EAN-13)') }}</label>
                            <button type="button" wire:click="generateBarcode" class="text-[11px] text-sky-700 hover:text-sky-900 font-bold flex items-center gap-1 cursor-pointer">
                                {{ __('⚡ Auto Generate') }}
                            </button>
                        </div>
                        <input 
                            type="text" 
                            wire:model="barcode" 
                            placeholder="{{ __('Scan or type barcode...') }}" 
                            class="w-full font-mono text-xs font-bold rounded-lg border border-slate-300 py-2 px-3" 
                        />
                    </div>

                    <!-- Medicine Photo Upload (Compact) -->
                    <div class="bg-white rounded-lg p-4 border border-slate-200 shadow-xs space-y-2.5">
                        <label class="block text-xs font-bold text-slate-800">{{ __('Medicine Photo (Pack Photo)') }}</label>
                        <div class="flex items-center gap-3">
                            <div class="w-16 h-16 rounded-lg border-2 border-dashed border-slate-200 bg-slate-50 flex items-center justify-center shrink-0 overflow-hidden">
                                @if ($imageFile)
                                    <img src="{{ $imageFile->temporaryUrl() }}" class="w-full h-full object-cover" />
                                @elseif ($existingImagePath)
                                    <img src="{{ asset('storage/' . $existingImagePath) }}" class="w-full h-full object-cover" />
                                @else
                                    <svg class="w-6 h-6 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" /></svg>
                                @endif
                            </div>
                            <div class="flex-1">
                                <label class="px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold rounded-lg text-xs cursor-pointer shadow-2xs inline-block">
                                    <span>{{ __('Choose Photo') }}</span>
                                    <input type="file" wire:model="imageFile" accept="image/*" class="hidden" />
                                </label>
                                <span class="text-[10px] text-slate-400 block mt-1">PNG, JPG (Max 2MB)</span>
                            </div>
                        </div>
                        @error('imageFile') <span class="text-[10px] text-rose-500 font-semibold block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Special Regulatory Badges -->
                    <div class="bg-white rounded-lg p-4 border border-slate-200 shadow-xs space-y-2.5">
                        <label class="block text-xs font-bold text-slate-800">{{ __('Inventory & Special Settings') }}</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer text-xs text-sky-900 font-bold bg-sky-50/80 p-2 rounded-lg border border-sky-200">
                                <input type="checkbox" wire:model.live="hasBatchTracking" class="rounded text-sky-600 focus:ring-sky-500 w-4 h-4" />
                                <span>{{ __('🏷️ Track Batches & Expiry') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-700 font-medium">
                                <input type="checkbox" wire:model="isColdChain" class="rounded text-sky-600 focus:ring-sky-500" />
                                <span>{{ __('❄️ Cold Chain (Refrigerator Required)') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-700 font-medium">
                                <input type="checkbox" wire:model="isNarcotic" class="rounded text-sky-600 focus:ring-sky-500" />
                                <span>{{ __('💊 Narcotic / Schedule-H') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-700 font-medium">
                                <input type="checkbox" wire:model="requiresPrescription" class="rounded text-sky-600 focus:ring-sky-500" />
                                <span>{{ __('📋 Prescription Required (Rx Only)') }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="bg-white rounded-lg p-4 border border-slate-200 shadow-xs space-y-2.5">
                        <button 
                            type="submit" 
                            wire:loading.attr="disabled"
                            class="w-full py-3 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg text-sm transition shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                            <svg wire:loading.remove class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <svg wire:loading class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>{{ $editingProductId ? __('Update Medicine') : __('💾 Save Medicine') }}</span>
                        </button>

                        <a 
                            href="{{ route('products.index') }}" 
                            wire:navigate 
                            class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs transition border border-slate-200 text-center block">
                            {{ __('Cancel & Return to List') }}
                        </a>
                    </div>
                </div>
            </div>
        </form>
    @endif

    @if ($tab === 'expired')
        <!-- Expired Batches Table -->
        <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-xs">
            <div class="p-3.5 border-b border-rose-200 bg-rose-50/60 font-bold text-rose-900 text-xs flex items-center justify-between">
                <span>Expired Medicines Archive</span>
                <span class="text-[11px] text-rose-700 font-semibold">Immediate quarantine recommended</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500 bg-slate-50">
                            <th class="px-4 py-3">Medicine</th>
                            <th class="px-4 py-3">Batch Number</th>
                            <th class="px-4 py-3">Expiry Date</th>
                            <th class="px-4 py-3">Remaining Stock</th>
                            <th class="px-4 py-3 text-right">Financial Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        @forelse ($expiredBatches as $b)
                            <tr class="hover:bg-rose-50/30 transition">
                                <td class="px-4 py-3 font-bold text-slate-900">{{ $b->product->name }}</td>
                                <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-700">{{ $b->batch_number }}</td>
                                <td class="px-4 py-3 text-xs font-bold text-rose-600">{{ \Carbon\Carbon::parse($b->expiry_date)->format('d M Y') }}</td>
                                <td class="px-4 py-3 font-mono font-bold">{{ $b->current_stock }} Pcs</td>
                                <td class="px-4 py-3 text-right font-mono font-bold text-rose-600">৳{{ number_format($b->current_stock * $b->purchase_rate, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">No expired batches currently logged.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($tab === 'bulk')
        <!-- TRUE EXCEL SPREADSHEET STUDIO -->
        <div class="space-y-4" x-data="{
            activeCell: 'A1',
            activeVal: '',
            pasteModal: false,
            pastedText: '',
            updateActive(cell, val) {
                this.activeCell = cell;
                this.activeVal = val || '';
            },
            handleGridPaste(e) {
                let text = (e.clipboardData || window.clipboardData).getData('text');
                if (text && text.includes('\t')) {
                    e.preventDefault();
                    $wire.importPastedTsv(text);
                }
            }
        }">
            
            <!-- Excel Window Container -->
            <div class="bg-white rounded-xl border border-slate-300 shadow-md overflow-hidden font-sans">
                
                <!-- 1. Excel Top Green Title Bar -->
                <div class="bg-[#107c41] text-white px-4 py-2 flex items-center justify-between select-none">
                    <div class="flex items-center space-x-3">
                        <div class="w-6 h-6 rounded bg-white text-[#107c41] flex items-center justify-center font-black text-xs shadow-xs">
                            X
                        </div>
                        <div class="flex items-center space-x-2 text-xs">
                            <span class="font-bold">Pharmacy_Products_Catalog.xlsx</span>
                            <span class="text-emerald-200">- Excel Studio</span>
                            <span class="bg-emerald-800 text-[10px] font-bold px-1.5 py-0.5 rounded text-emerald-200">AutoSave ON</span>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button 
                            type="button" 
                            wire:click="$set('bulkMode', 'grid')" 
                            class="px-2.5 py-1 text-xs rounded transition {{ $bulkMode === 'grid' ? 'bg-white text-[#107c41] font-bold shadow-xs' : 'text-white hover:bg-emerald-700' }}"
                        >
                            📊 Excel Spreadsheet
                        </button>
                        <button 
                            type="button" 
                            wire:click="$set('bulkMode', 'file')" 
                            class="px-2.5 py-1 text-xs rounded transition {{ $bulkMode === 'file' ? 'bg-white text-[#107c41] font-bold shadow-xs' : 'text-white hover:bg-emerald-700' }}"
                        >
                            📁 CSV File Import
                        </button>
                    </div>
                </div>

                @if ($bulkMode === 'grid')
                    <!-- 2. Excel Ribbon Toolbar -->
                    <div class="bg-[#f3f4f6] border-b border-slate-300 p-2 flex flex-wrap items-center justify-between gap-2 text-xs select-none">
                        
                        <!-- Ribbon Action Groups -->
                        <div class="flex flex-wrap items-center gap-1.5">
                            
                            <!-- Paste Button -->
                            <button 
                                type="button" 
                                @click="pasteModal = true"
                                class="px-2.5 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 hover:border-emerald-600 rounded flex items-center gap-1.5 font-semibold text-slate-700 shadow-2xs cursor-pointer"
                                title="Paste from Excel or Google Sheets (Ctrl+V)"
                            >
                                <svg class="w-4 h-4 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                <span>Paste from Excel</span>
                            </button>

                            <div class="h-5 w-px bg-slate-300 mx-0.5"></div>

                            <!-- Row Controls -->
                            <button 
                                type="button" 
                                wire:click="addBulkRow" 
                                class="px-2.5 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 hover:border-emerald-600 rounded flex items-center gap-1 text-slate-700 font-semibold shadow-2xs cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                                +1 Row
                            </button>

                            <button 
                                type="button" 
                                wire:click="addMultipleBulkRows(10)" 
                                class="px-2.5 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 hover:border-emerald-600 rounded flex items-center gap-1 text-slate-700 font-semibold shadow-2xs cursor-pointer"
                            >
                                +10 Rows
                            </button>

                            <div class="h-5 w-px bg-slate-300 mx-0.5"></div>

                            <!-- Tools -->
                            <button 
                                type="button" 
                                wire:click="loadSampleBulkData" 
                                class="px-2.5 py-1.5 bg-emerald-50 border border-emerald-300 text-[#107c41] hover:bg-emerald-100 rounded font-semibold flex items-center gap-1 cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                Fill Demo Medicines
                            </button>

                            <button 
                                type="button" 
                                wire:click="clearBulkRows" 
                                class="px-2 py-1.5 text-rose-600 hover:bg-rose-50 rounded transition cursor-pointer"
                            >
                                Clear Sheet
                            </button>
                        </div>

                        <!-- Main Import Button -->
                        <div class="flex items-center gap-2">
                            <button 
                                type="button" 
                                wire:click="downloadSampleCsv"
                                class="px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded font-medium flex items-center gap-1 text-xs cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                Template (.CSV)
                            </button>

                            <button 
                                type="button" 
                                wire:click="saveBulkGrid" 
                                wire:loading.attr="disabled"
                                class="px-5 py-1.5 bg-[#107c41] hover:bg-[#0b5c30] text-white font-bold rounded shadow-sm transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
                            >
                                <svg wire:loading.remove class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                <span wire:loading.remove>Save Sheet to Database</span>
                                <span wire:loading>Saving Medicines...</span>
                            </button>
                        </div>

                    </div>

                    <!-- 3. Excel Formula Bar (fx) -->
                    <div class="bg-white border-b border-slate-300 px-3 py-1.5 flex items-center gap-2 text-xs">
                        <div class="w-16 px-2 py-1 bg-slate-100 border border-slate-300 rounded text-center font-mono font-bold text-slate-700 select-none shadow-inner" x-text="activeCell">
                            A1
                        </div>
                        <div class="text-slate-400 font-serif italic text-sm font-bold select-none px-1">
                            fx
                        </div>
                        <div class="flex-1 bg-white border border-slate-200 rounded px-2.5 py-1 font-mono text-xs text-slate-800" x-text="activeVal || 'Ready for data entry...'">
                        </div>
                    </div>

                    <!-- 4. Excel Spreadsheet Grid with Authentic Grid Headers & Cells -->
                    <div class="overflow-x-auto max-h-[580px] bg-slate-200" @paste="handleGridPaste($event)">
                        <table class="w-full border-collapse text-xs bg-white">
                            <!-- Excel Column Headers (A, B, C, D...) -->
                            <thead class="sticky top-0 z-20 select-none">
                                <tr class="bg-[#f3f4f6] text-slate-700 text-center font-semibold text-[11px] border-b border-slate-300">
                                    <th class="w-12 p-1.5 bg-[#e5e7eb] border-r border-slate-300 text-slate-500 font-mono text-[10px]">#</th>
                                    <th class="p-1.5 min-w-[230px] border-r border-slate-300 bg-[#f3f4f6]">
                                        <div class="text-[10px] text-slate-400 font-mono font-normal">A</div>
                                        <div class="font-bold text-slate-800">Medicine Name <span class="text-rose-500">*</span></div>
                                    </th>
                                    <th class="p-1.5 min-w-[160px] border-r border-slate-300 bg-[#f3f4f6]">
                                        <div class="text-[10px] text-slate-400 font-mono font-normal">B</div>
                                        <div class="font-bold text-slate-800">Generic Molecule</div>
                                    </th>
                                    <th class="p-1.5 min-w-[130px] border-r border-slate-300 bg-[#f3f4f6]">
                                        <div class="text-[10px] text-slate-400 font-mono font-normal">C</div>
                                        <div class="font-bold text-slate-800">Category</div>
                                    </th>
                                    <th class="p-1.5 min-w-[160px] border-r border-slate-300 bg-[#f3f4f6]">
                                        <div class="text-[10px] text-slate-400 font-mono font-normal">D</div>
                                        <div class="font-bold text-slate-800">Company / Mfg</div>
                                    </th>
                                    <th class="p-1.5 min-w-[90px] border-r border-slate-300 bg-[#f3f4f6]">
                                        <div class="text-[10px] text-slate-400 font-mono font-normal">E</div>
                                        <div class="font-bold text-slate-800">Base Unit</div>
                                    </th>
                                    <th class="p-1.5 min-w-[110px] border-r border-slate-300 bg-[#f3f4f6]">
                                        <div class="text-[10px] text-slate-400 font-mono font-normal">F</div>
                                        <div class="font-bold text-slate-800">Purchase (৳)</div>
                                    </th>
                                    <th class="p-1.5 min-w-[110px] border-r border-slate-300 bg-[#f3f4f6]">
                                        <div class="text-[10px] text-slate-400 font-mono font-normal">G</div>
                                        <div class="font-bold text-slate-800">MRP / Sale (৳) <span class="text-rose-500">*</span></div>
                                    </th>
                                    <th class="p-1.5 min-w-[90px] border-r border-slate-300 bg-[#f3f4f6]">
                                        <div class="text-[10px] text-slate-400 font-mono font-normal">H</div>
                                        <div class="font-bold text-slate-800">Stock Qty</div>
                                    </th>
                                    <th class="p-1.5 min-w-[120px] border-r border-slate-300 bg-[#f3f4f6]">
                                        <div class="text-[10px] text-slate-400 font-mono font-normal">I</div>
                                        <div class="font-bold text-slate-800">Batch No</div>
                                    </th>
                                    <th class="p-1.5 min-w-[130px] border-r border-slate-300 bg-[#f3f4f6]">
                                        <div class="text-[10px] text-slate-400 font-mono font-normal">J</div>
                                        <div class="font-bold text-slate-800">Expiry Date</div>
                                    </th>
                                    <th class="w-10 p-1.5 bg-[#e5e7eb] text-center font-bold text-slate-500">Del</th>
                                </tr>
                            </thead>
                            
                            <!-- Excel Rows & Cells -->
                            <tbody class="divide-y divide-slate-300 bg-white font-sans">
                                @foreach ($bulkRows as $index => $row)
                                    @php $rowNum = $index + 1; @endphp
                                    <tr class="hover:bg-emerald-50/30 transition group">
                                        
                                        <!-- Row Number Cell -->
                                        <td class="p-1 text-center bg-[#f3f4f6] text-slate-500 font-mono text-[11px] border-r border-slate-300 select-none group-hover:bg-[#e5e7eb]">
                                            {{ $rowNum }}
                                        </td>

                                        <!-- Cell A: Medicine Name -->
                                        <td class="p-0 border-r border-slate-300 relative focus-within:ring-2 focus-within:ring-[#107c41] focus-within:z-10">
                                            <input 
                                                type="text" 
                                                wire:model.lazy="bulkRows.{{ $index }}.name" 
                                                @focus="updateActive('A{{ $rowNum }}', $el.value)"
                                                @input="updateActive('A{{ $rowNum }}', $el.value)"
                                                placeholder="Medicine Name..." 
                                                class="w-full h-8 px-2.5 bg-transparent border-0 text-xs text-slate-900 font-medium placeholder-slate-300 focus:outline-none focus:bg-white"
                                                style="border-radius: 0 !important; border: none !important; box-shadow: none !important;"
                                            />
                                        </td>

                                        <!-- Cell B: Generic Molecule -->
                                        <td class="p-0 border-r border-slate-300 relative focus-within:ring-2 focus-within:ring-[#107c41] focus-within:z-10">
                                            <select 
                                                wire:model.lazy="bulkRows.{{ $index }}.generic_id"
                                                @focus="updateActive('B{{ $rowNum }}', $el.options[$el.selectedIndex]?.text)"
                                                class="w-full h-8 px-2 bg-transparent border-0 text-xs text-slate-700 focus:outline-none focus:bg-white cursor-pointer"
                                                style="border-radius: 0 !important; border: none !important; box-shadow: none !important;"
                                            >
                                                <option value="">(Select Generic)</option>
                                                @foreach ($generics as $gen)
                                                    <option value="{{ $gen->id }}">{{ $gen->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <!-- Cell C: Category -->
                                        <td class="p-0 border-r border-slate-300 relative focus-within:ring-2 focus-within:ring-[#107c41] focus-within:z-10">
                                            <select 
                                                wire:model.lazy="bulkRows.{{ $index }}.category_id"
                                                @focus="updateActive('C{{ $rowNum }}', $el.options[$el.selectedIndex]?.text)"
                                                class="w-full h-8 px-2 bg-transparent border-0 text-xs text-slate-700 focus:outline-none focus:bg-white cursor-pointer"
                                                style="border-radius: 0 !important; border: none !important; box-shadow: none !important;"
                                            >
                                                <option value="">(Category)</option>
                                                @foreach ($categories as $cat)
                                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <!-- Cell D: Manufacturer -->
                                        <td class="p-0 border-r border-slate-300 relative focus-within:ring-2 focus-within:ring-[#107c41] focus-within:z-10">
                                            <select 
                                                wire:model.lazy="bulkRows.{{ $index }}.manufacturer_id"
                                                @focus="updateActive('D{{ $rowNum }}', $el.options[$el.selectedIndex]?.text)"
                                                class="w-full h-8 px-2 bg-transparent border-0 text-xs text-slate-700 focus:outline-none focus:bg-white cursor-pointer"
                                                style="border-radius: 0 !important; border: none !important; box-shadow: none !important;"
                                            >
                                                <option value="">(Company)</option>
                                                @foreach ($manufacturers as $mfg)
                                                    <option value="{{ $mfg->id }}">{{ $mfg->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <!-- Cell E: Base Unit -->
                                        <td class="p-0 border-r border-slate-300 relative focus-within:ring-2 focus-within:ring-[#107c41] focus-within:z-10">
                                            <select 
                                                wire:model.lazy="bulkRows.{{ $index }}.unit_id"
                                                @focus="updateActive('E{{ $rowNum }}', $el.options[$el.selectedIndex]?.text)"
                                                class="w-full h-8 px-2 bg-transparent border-0 text-xs text-slate-700 font-medium focus:outline-none focus:bg-white cursor-pointer"
                                                style="border-radius: 0 !important; border: none !important; box-shadow: none !important;"
                                            >
                                                @foreach ($units as $u)
                                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <!-- Cell F: Purchase Price -->
                                        <td class="p-0 border-r border-slate-300 relative focus-within:ring-2 focus-within:ring-[#107c41] focus-within:z-10">
                                            <input 
                                                type="number" 
                                                step="0.01" 
                                                wire:model.lazy="bulkRows.{{ $index }}.purchase_price" 
                                                @focus="updateActive('F{{ $rowNum }}', $el.value)"
                                                @input="updateActive('F{{ $rowNum }}', $el.value)"
                                                placeholder="0.00" 
                                                class="w-full h-8 px-2.5 bg-transparent border-0 text-xs text-slate-800 text-right font-mono focus:outline-none focus:bg-white"
                                                style="border-radius: 0 !important; border: none !important; box-shadow: none !important;"
                                            />
                                        </td>

                                        <!-- Cell G: MRP Price -->
                                        <td class="p-0 border-r border-slate-300 relative focus-within:ring-2 focus-within:ring-[#107c41] focus-within:z-10 bg-emerald-50/20">
                                            <input 
                                                type="number" 
                                                step="0.01" 
                                                wire:model.lazy="bulkRows.{{ $index }}.mrp_price" 
                                                @focus="updateActive('G{{ $rowNum }}', $el.value)"
                                                @input="updateActive('G{{ $rowNum }}', $el.value)"
                                                placeholder="0.00" 
                                                class="w-full h-8 px-2.5 bg-transparent border-0 text-xs text-emerald-800 font-bold text-right font-mono focus:outline-none focus:bg-white"
                                                style="border-radius: 0 !important; border: none !important; box-shadow: none !important;"
                                            />
                                        </td>

                                        <!-- Cell H: Stock Qty -->
                                        <td class="p-0 border-r border-slate-300 relative focus-within:ring-2 focus-within:ring-[#107c41] focus-within:z-10">
                                            <input 
                                                type="number" 
                                                wire:model.lazy="bulkRows.{{ $index }}.quantity" 
                                                @focus="updateActive('H{{ $rowNum }}', $el.value)"
                                                @input="updateActive('H{{ $rowNum }}', $el.value)"
                                                placeholder="0" 
                                                class="w-full h-8 px-2 bg-transparent border-0 text-xs text-slate-800 text-right font-mono focus:outline-none focus:bg-white"
                                                style="border-radius: 0 !important; border: none !important; box-shadow: none !important;"
                                            />
                                        </td>

                                        <!-- Cell I: Batch No -->
                                        <td class="p-0 border-r border-slate-300 relative focus-within:ring-2 focus-within:ring-[#107c41] focus-within:z-10">
                                            <input 
                                                type="text" 
                                                wire:model.lazy="bulkRows.{{ $index }}.batch_number" 
                                                @focus="updateActive('I{{ $rowNum }}', $el.value)"
                                                @input="updateActive('I{{ $rowNum }}', $el.value)"
                                                placeholder="Auto batch" 
                                                class="w-full h-8 px-2 bg-transparent border-0 text-[11px] font-mono text-slate-700 focus:outline-none focus:bg-white"
                                                style="border-radius: 0 !important; border: none !important; box-shadow: none !important;"
                                            />
                                        </td>

                                        <!-- Cell J: Expiry Date -->
                                        <td class="p-0 border-r border-slate-300 relative focus-within:ring-2 focus-within:ring-[#107c41] focus-within:z-10">
                                            <input 
                                                type="date" 
                                                wire:model.lazy="bulkRows.{{ $index }}.expiry_date" 
                                                @focus="updateActive('J{{ $rowNum }}', $el.value)"
                                                class="w-full h-8 px-2 bg-transparent border-0 text-[11px] text-slate-700 focus:outline-none focus:bg-white"
                                                style="border-radius: 0 !important; border: none !important; box-shadow: none !important;"
                                            />
                                        </td>

                                        <!-- Delete Cell -->
                                        <td class="p-0 text-center bg-slate-50">
                                            <button 
                                                type="button" 
                                                wire:click="removeBulkRow({{ $index }})" 
                                                class="h-8 w-full text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition cursor-pointer font-bold"
                                                title="Delete Row {{ $rowNum }}"
                                            >
                                                ✕
                                            </button>
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- 5. Excel Bottom Status Bar -->
                    <div class="bg-[#f3f4f6] border-t border-slate-300 px-3 py-1.5 flex flex-wrap items-center justify-between text-[11px] text-slate-600 select-none">
                        <!-- Sheet Tabs -->
                        <div class="flex items-center space-x-1">
                            <span class="px-3 py-1 bg-white border-t-2 border-[#107c41] font-bold text-[#107c41] shadow-2xs rounded-t">
                                Sheet1 (Products Catalog)
                            </span>
                            <span class="px-2 py-1 text-slate-400 font-bold hover:text-slate-600 cursor-pointer">+</span>
                        </div>

                        <!-- Real-time Status Metrics -->
                        <div class="flex items-center space-x-4 font-mono">
                            <span class="flex items-center gap-1.5 font-sans font-semibold text-emerald-700">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                READY
                            </span>
                            <span>COUNT: <strong class="text-slate-900 font-bold">{{ count($bulkRows) }}</strong> rows</span>
                            <span>FILLED: <strong class="text-[#107c41] font-bold">{{ count(array_filter($bulkRows, fn($r) => !empty(trim($r['name'] ?? '')))) }}</strong> items</span>
                            <span>ZOOM: 100%</span>
                        </div>
                    </div>

                    <!-- PASTE FROM EXCEL MODAL DIALOG -->
                    <div x-show="pasteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
                        <div @click.away="pasteModal = false" class="bg-white rounded-2xl shadow-2xl border border-slate-300 w-full max-w-xl p-6 space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded bg-emerald-100 text-[#107c41] flex items-center justify-center font-bold">
                                        📋
                                    </div>
                                    <h3 class="text-base font-bold text-slate-900">Paste Table Data from Microsoft Excel</h3>
                                </div>
                                <button type="button" @click="pasteModal = false" class="text-slate-400 hover:text-slate-600 text-lg leading-none cursor-pointer">&times;</button>
                            </div>

                            <p class="text-xs text-slate-500">
                                Select cells in your real Excel/Google Sheets, press <strong>Ctrl+C</strong> to copy, and paste (<strong>Ctrl+V</strong>) below:
                            </p>

                            <textarea 
                                x-model="pastedText"
                                rows="7" 
                                placeholder="Paste Excel cells here (Medicine Name [Tab] MRP [Tab] Purchase [Tab] Qty...)"
                                class="w-full text-xs font-mono p-3 border border-slate-300 rounded-xl focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/20 bg-slate-50 placeholder-slate-400"
                            ></textarea>

                            <div class="flex items-center justify-end gap-2 pt-2">
                                <button type="button" @click="pasteModal = false" class="px-4 py-2 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer">
                                    Cancel
                                </button>
                                <button 
                                    type="button" 
                                    @click="$wire.importPastedTsv(pastedText); pasteModal = false; pastedText = '';" 
                                    class="px-5 py-2 rounded-lg text-xs font-bold bg-[#107c41] hover:bg-[#0b5c30] text-white shadow-sm cursor-pointer"
                                >
                                    Import Pasted Rows
                                </button>
                            </div>
                        </div>
                    </div>

                @endif

                <!-- MODE 2: FILE UPLOAD -->
                @if ($bulkMode === 'file')
                    <div class="p-6 bg-slate-50 space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Upload Dropzone -->
                            <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-6 shadow-xs space-y-5">
                                <div>
                                    <h3 class="font-bold text-sm text-slate-900">Upload CSV Spreadsheet File</h3>
                                    <p class="text-xs text-slate-500">Upload your prepared medicine spreadsheet in .csv or .txt format</p>
                                </div>

                                <form wire:submit="processBulkUpload" class="space-y-4">
                                    <div class="border-2 border-dashed border-slate-200 hover:border-emerald-500 rounded-2xl p-8 text-center bg-slate-50/60 transition space-y-3">
                                        <div class="mx-auto h-12 w-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                        </div>
                                        <div>
                                            <label class="cursor-pointer">
                                                <span class="text-sm font-bold text-emerald-700 hover:text-emerald-800 underline">Click to choose file</span>
                                                <span class="text-xs text-slate-500"> or drag and drop</span>
                                                <input type="file" wire:model="csvFile" class="hidden" accept=".csv,.txt" />
                                            </label>
                                            <p class="text-[11px] text-slate-400 mt-1">Supported formats: CSV, TXT (Max size: 5MB)</p>
                                        </div>
                                    </div>

                                    @if ($csvFile)
                                        <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                <span class="font-semibold">{{ $csvFile->getClientOriginalName() }}</span>
                                            </div>
                                            <span class="text-[11px] text-slate-500">{{ round($csvFile->getSize() / 1024, 1) }} KB</span>
                                        </div>
                                    @endif

                                    @error('csvFile') 
                                        <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                                            {{ $message }}
                                        </div> 
                                    @enderror

                                    <div class="flex items-center justify-end gap-3 pt-2">
                                        <button 
                                            type="submit" 
                                            wire:loading.attr="disabled"
                                            class="w-full sm:w-auto px-6 py-2.5 bg-[#107c41] hover:bg-[#0b5c30] text-white font-bold rounded-lg text-xs shadow-sm transition cursor-pointer disabled:opacity-50"
                                        >
                                            <span wire:loading.remove>Upload & Process File</span>
                                            <span wire:loading>Importing Records...</span>
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- CSV Column Guide Card -->
                            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs space-y-4">
                                <h4 class="font-bold text-xs uppercase tracking-wider text-slate-700">Column Format Guide</h4>
                                <div class="space-y-2 text-xs">
                                    <div class="p-2 rounded-lg bg-slate-50 border border-slate-200 font-mono text-[11px]">
                                        <div class="font-bold text-slate-800">Columns:</div>
                                        <div class="text-slate-600 mt-1">1. Medicine Name <span class="text-rose-500 font-bold">*</span></div>
                                        <div class="text-slate-600">2. MRP Price <span class="text-rose-500 font-bold">*</span></div>
                                        <div class="text-slate-600">3. Purchase Price</div>
                                        <div class="text-slate-600">4. Opening Qty</div>
                                        <div class="text-slate-600">5. Batch Number</div>
                                        <div class="text-slate-600">6. Expiry Date (YYYY-MM-DD)</div>
                                    </div>

                                    <button 
                                        type="button" 
                                        wire:click="downloadSampleCsv" 
                                        class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition cursor-pointer flex items-center justify-center gap-1.5"
                                    >
                                        <svg class="w-3.5 h-3.5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                        Download Sample .CSV
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    @endif

    @if (in_array($tab, ['generics', 'categories', 'units', 'types', 'manufacturers', 'box_sizes']))
        <!-- Master Tables -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-xs space-y-3.5">
                <h3 class="font-bold text-sm text-slate-900 border-b border-slate-100 pb-2">Add New {{ ucfirst(rtrim($tab, 's')) }}</h3>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Name <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="newMasterName" placeholder="Enter {{ rtrim($tab, 's') }} name..." class="w-full text-xs rounded-lg border border-slate-300 py-2 px-3" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ $tab === 'generics' ? 'Indication / Usage' : 'Description / Code' }}</label>
                    <input type="text" wire:model="newMasterDescription" placeholder="Enter details..." class="w-full text-xs rounded-lg border border-slate-300 py-2 px-3" />
                </div>
                <button type="button" wire:click="addMaster('{{ $tab === 'generics' ? 'generic' : ($tab === 'categories' ? 'category' : ($tab === 'units' ? 'unit' : ($tab === 'types' ? 'type' : ($tab === 'manufacturers' ? 'manufacturer' : 'box_size')))) }}')" class="w-full py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg text-xs transition shadow-xs cursor-pointer">
                    Add {{ ucfirst(rtrim($tab, 's')) }}
                </button>
            </div>

            <div class="md:col-span-2 bg-white rounded-lg border border-slate-200 overflow-hidden shadow-xs">
                <div class="p-3.5 border-b border-slate-200 bg-slate-50 font-bold text-slate-800 text-xs flex items-center justify-between">
                    <span>Existing {{ ucfirst($tab) }} ({{ count($tab === 'generics' ? $generics : ($tab === 'categories' ? $categories : ($tab === 'units' ? $units : ($tab === 'types' ? $medicineTypes : ($tab === 'manufacturers' ? $manufacturers : $boxSizes))))) }})</span>
                </div>
                <div class="p-3 divide-y divide-slate-100 max-h-96 overflow-y-auto">
                    @php
                        $list = $tab === 'generics' ? $generics : ($tab === 'categories' ? $categories : ($tab === 'units' ? $units : ($tab === 'types' ? $medicineTypes : ($tab === 'manufacturers' ? $manufacturers : $boxSizes))));
                    @endphp
                    @foreach ($list as $item)
                        <div class="py-2.5 px-2 flex justify-between items-center text-xs hover:bg-slate-50/80 rounded transition">
                            <span class="font-semibold text-slate-800">{{ $item->name }}</span>
                            <span class="text-[11px] text-slate-500 font-mono">{{ $item->indication ?? $item->short_code ?? $item->total_quantity ?? $item->description ?? '' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
