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
            <svg class="w-4 h-4 flex-shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
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
                                <span class="w-7 h-7 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center font-bold text-xs">১</span>
                                <div>
                                    <h3 class="font-bold text-sm text-slate-900">ওষুধের প্রাথমিক তথ্য (Basic Medicine Info)</h3>
                                    <p class="text-[11px] text-slate-500">ব্র্যান্ডের নাম, জেনেরিক, পাওয়ার এবং ম্যানুফ্যাকচারার</p>
                                </div>
                            </div>
                            <span class="text-[11px] text-slate-400 font-medium"><span class="text-rose-500">*</span> চিহ্নিত ফিল্ডগুলো আবশ্যক</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Brand Name (Full width, prominent) -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-800 mb-1">
                                    ওষুধের ব্র্যান্ডের নাম (Brand Name) <span class="text-rose-500">*</span>
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
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">জেনেরিক / মূল উপাদান (Generic)</label>
                                <select wire:model.live="genericId" class="w-full text-xs rounded-lg border border-slate-300 py-2 px-3 text-slate-800 focus:border-sky-600 focus:ring-sky-500/15 bg-white">
                                    <option value="">-- সিলেক্ট করুন (বা খালি রাখুন) --</option>
                                    @foreach ($generics as $gen) 
                                        <option value="{{ $gen->id }}">{{ $gen->name }}</option> 
                                    @endforeach
                                </select>
                            </div>

                            <!-- Strength / Power -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">পাওয়ার / মাত্রা (Strength)</label>
                                <input 
                                    type="text" 
                                    wire:model.live.debounce.300ms="strength" 
                                    placeholder="e.g. 500mg, 20mg, 100ml, 5mg/5ml" 
                                    class="w-full text-xs rounded-lg border border-slate-300 py-2 px-3 text-slate-800 focus:border-sky-600 focus:ring-sky-500/15" 
                                />
                            </div>

                            <!-- Dosage Form / Medicine Type -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">ওষুধের ধরন (Dosage Form)</label>
                                <select wire:model.live="medicineTypeId" class="w-full text-xs rounded-lg border border-slate-300 py-2 px-3 text-slate-800 focus:border-sky-600 focus:ring-sky-500/15 bg-white">
                                    <option value="">-- ধরন সিলেক্ট করুন --</option>
                                    @foreach ($medicineTypes as $type) 
                                        <option value="{{ $type->id }}">{{ $type->name }}</option> 
                                    @endforeach
                                </select>
                            </div>

                            <!-- Category -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">ক্যাটাগরি (Category)</label>
                                <select wire:model.live="categoryId" class="w-full text-xs rounded-lg border border-slate-300 py-2 px-3 text-slate-800 focus:border-sky-600 focus:ring-sky-500/15 bg-white">
                                    <option value="">-- ক্যাটাগরি সিলেক্ট করুন --</option>
                                    @foreach ($categories as $cat) 
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option> 
                                    @endforeach
                                </select>
                            </div>

                            <!-- Manufacturer -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 mb-1">কোম্পানি / প্রস্তুতকারক (Manufacturer)</label>
                                <select wire:model.live="manufacturerId" class="w-full text-xs rounded-lg border border-slate-300 py-2 px-3 text-slate-800 focus:border-sky-600 focus:ring-sky-500/15 bg-white">
                                    <option value="">-- কোম্পানি সিলেক্ট করুন --</option>
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
                                <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">২</span>
                                <div>
                                    <h3 class="font-bold text-sm text-slate-900">দাম, লাভ ও প্যাকেজিং (Pricing & Packaging)</h3>
                                    <p class="text-[11px] text-slate-500">প্রতি পিসের ক্রয় ও বিক্রয় মূল্য দিলে পাতা ও বক্সের দাম নিজে নিজে হিসাব হবে</p>
                                </div>
                            </div>
                            <span class="text-[10px] bg-emerald-50 text-emerald-800 font-bold px-2.5 py-1 rounded-full border border-emerald-200 self-start sm:self-auto">
                                ⚡ অটো প্রফিট ক্যালকুলেটর
                            </span>
                        </div>

                        <!-- Main 1-Piece Pricing Row -->
                        <div class="bg-slate-50 p-4 rounded-lg border border-slate-200/80 space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <!-- Base Unit Selector -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-800 mb-1">মৌলিক একক (Base Unit) <span class="text-rose-500">*</span></label>
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
                                    <label class="block text-xs font-bold text-slate-800 mb-1">১ পিসের ক্রয়মূল্য (Buy ৳)</label>
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
                                    <label class="block text-xs font-bold text-slate-800 mb-1">১ পিসের বিক্রয়মূল্য (MRP ৳) <span class="text-rose-500">*</span></label>
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
                                    <span>প্রতি পিসে লাভ (Profit Margin):</span>
                                </div>
                                <div class="font-mono font-bold text-xs flex items-center gap-2">
                                    @if ($baseSell > 0)
                                        <span class="{{ $profitPerPiece >= 0 ? 'text-emerald-800' : 'text-rose-600' }}">
                                            ৳{{ number_format($profitPerPiece, 2) }} / পিস
                                        </span>
                                        <span class="px-2 py-0.5 rounded text-[11px] {{ $profitPerPiece >= 0 ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                            {{ $marginPct }}% লাভ
                                        </span>
                                    @else
                                        <span class="text-slate-400 font-normal text-[11px]">ক্রয় ও বিক্রয় মূল্য লিখুন</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- 1-Click Packaging Presets & Add Unit Button -->
                        <div class="space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <label class="block text-xs font-bold text-slate-700">⚡ দ্রুত প্যাকেজিং সাইজ সিলেক্ট করুন (Quick Presets):</label>
                                <button type="button" wire:click="addPackagingUnit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-xs transition cursor-pointer self-start sm:self-auto">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                    <span>+ নতুন প্যাকেজিং যোগ করুন (Add Unit)</span>
                                </button>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" wire:click="applyPreset('10x10')" class="px-3 py-1.5 text-xs rounded-lg border font-bold transition cursor-pointer bg-white text-slate-700 border-slate-300 hover:bg-sky-50 hover:border-sky-300 hover:text-sky-800 shadow-2xs">
                                    💊 ১০ × ১০ পাতা (১০০ পিস বক্স)
                                </button>
                                <button type="button" wire:click="applyPreset('5x10')" class="px-3 py-1.5 text-xs rounded-lg border font-bold transition cursor-pointer bg-white text-slate-700 border-slate-300 hover:bg-sky-50 hover:border-sky-300 hover:text-sky-800 shadow-2xs">
                                    💊 ৫ × ১০ পাতা (৫০ পিস বক্স)
                                </button>
                                <button type="button" wire:click="applyPreset('3x10')" class="px-3 py-1.5 text-xs rounded-lg border font-bold transition cursor-pointer bg-white text-slate-700 border-slate-300 hover:bg-sky-50 hover:border-sky-300 hover:text-sky-800 shadow-2xs">
                                    💊 ৩ × ১০ পাতা (৩০ পিস বক্স)
                                </button>
                                <button type="button" wire:click="applyPreset('2x14')" class="px-3 py-1.5 text-xs rounded-lg border font-bold transition cursor-pointer bg-white text-slate-700 border-slate-300 hover:bg-sky-50 hover:border-sky-300 hover:text-sky-800 shadow-2xs">
                                    💊 ২ × ১৪ পাতা (২৮ পিস বক্স)
                                </button>
                                <button type="button" wire:click="applyPreset('single')" class="px-3 py-1.5 text-xs rounded-lg border font-bold transition cursor-pointer bg-white text-slate-700 border-slate-300 hover:bg-sky-50 hover:border-sky-300 hover:text-sky-800 shadow-2xs">
                                    🧴 ১ পিস / বোতল (সিঙ্গেল প্যাক)
                                </button>
                            </div>
                        </div>

                        <!-- Packaging Breakdown Table (Always visible or has clear Add action) -->
                        <div class="border border-slate-200 rounded-lg overflow-hidden shadow-2xs">
                            <div class="bg-slate-50 px-3.5 py-2.5 border-b border-slate-200 text-xs font-bold text-slate-800 flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-sky-600"></span>
                                    পাতা ও বক্সের হিসাব তালিকা (Packaging Breakdown Table):
                                </span>
                                <span class="text-[11px] font-normal text-slate-500">মোট স্তর: {{ count($packagingUnits) + 1 }}টি</span>
                            </div>

                            @if (count($packagingUnits) > 0)
                                <table class="w-full text-left text-xs border-collapse">
                                    <thead>
                                        <tr class="bg-white text-[11px] font-bold uppercase text-slate-600 border-b border-slate-200">
                                            <th class="p-2.5">প্যাকেজিং ইউনিট (Unit)</th>
                                            <th class="p-2.5 w-28">সাইজ (পিস)</th>
                                            <th class="p-2.5">ক্রয়মূল্য (Buy ৳)</th>
                                            <th class="p-2.5">বিক্রয়মূল্য (MRP ৳)</th>
                                            <th class="p-2.5 text-right">লাভ (৳)</th>
                                            <th class="p-2.5 text-center w-12">মুছুন</th>
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
                                                        <span class="text-[11px] text-slate-500">পিস</span>
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
                                                    <button type="button" wire:click="removePackagingUnit({{ $index }})" class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded transition cursor-pointer font-bold" title="বাদ দিন">
                                                        ✕
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="p-6 text-center bg-white space-y-2">
                                    <p class="text-xs text-slate-500">বর্তমানে শুধুমাত্র ১টি বেস ইউনিট রয়েছে। পাতা বা বক্সের হিসাব রাখতে নতুন প্যাকেজিং যোগ করুন।</p>
                                    <button type="button" wire:click="addPackagingUnit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-sky-50 text-sky-800 border border-sky-200 text-xs font-bold hover:bg-sky-100 transition cursor-pointer">
                                        <svg class="w-4 h-4 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                        <span>+ নতুন প্যাকেজিং স্তর যোগ করুন (Add Strip / Box)</span>
                                    </button>
                                </div>
                            @endif

                            <div class="p-2.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                                <button type="button" wire:click="addPackagingUnit" class="text-xs font-bold text-sky-700 hover:text-sky-900 inline-flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                    <span>+ আরও প্যাকেজিং স্তর যোগ করুন</span>
                                </button>
                                <span class="text-[11px] text-slate-400">পাতা ও বক্সের দাম অটো আপডেট হবে</span>
                            </div>
                        </div>

                        <!-- Collapsible Advanced Pricing (Tax, Wholesale, Dealer) -->
                        <div class="border-t border-slate-100 pt-3">
                            <button 
                                type="button" 
                                @click="showAdvancedPricing = !showAdvancedPricing" 
                                class="text-xs font-bold text-slate-600 hover:text-sky-700 flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-4 h-4 transition-transform duration-200" :class="showAdvancedPricing ? 'rotate-90 text-sky-600' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                                <span>অতিরিক্ত মূল্য সেটিংস (ট্যাক্স, পাইকারি দর, ডিলার দর)</span>
                                <span class="text-[10px] text-slate-400 font-normal">(প্রয়োজন হলে ক্লিক করুন)</span>
                            </button>

                            <div x-show="showAdvancedPricing" x-collapse class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3 pt-3 border-t border-slate-100 bg-slate-50/70 p-3.5 rounded-lg">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">পাইকারি দর (Wholesale Price ৳)</label>
                                    <input type="number" step="0.01" wire:model="wholesalePrice" placeholder="0.00" class="w-full text-xs font-mono font-bold rounded-lg border border-slate-300 py-1.5 px-2 bg-white" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">ডিলার দর (Dealer Price ৳)</label>
                                    <input type="number" step="0.01" wire:model="dealerPrice" placeholder="0.00" class="w-full text-xs font-mono font-bold rounded-lg border border-slate-300 py-1.5 px-2 bg-white" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">ভ্যাট / ট্যাক্স (Tax / VAT)</label>
                                    <select wire:model.live="taxId" class="w-full text-xs rounded-lg border border-slate-300 py-1.5 px-2 bg-white">
                                        <option value="">ট্যাক্স নেই (0%)</option>
                                        @foreach ($taxes as $t)
                                            <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->rate }}%)</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Initial Stock, Batch & Storage -->
                    <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-xs space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs">৩</span>
                                <div>
                                    <h3 class="font-bold text-sm text-slate-900">প্রারম্ভিক স্টক ও মেয়াদ (Initial Stock & Expiry)</h3>
                                    <p class="text-[11px] text-slate-500">দোকানের বর্তমান মজুদ, মেয়াদোত্তীর্ণ তারিখ ও র‍্যাক লোকেশন</p>
                                </div>
                            </div>
                            
                            <!-- Stock Type Selector Pill -->
                            <div class="inline-flex rounded-lg border border-slate-200 p-0.5 bg-slate-100 text-xs">
                                <button 
                                    type="button" 
                                    wire:click="$set('stockType', 'Single')" 
                                    class="px-3 py-1 rounded-md font-bold text-xs transition cursor-pointer {{ $stockType === 'Single' ? 'bg-white text-sky-800 shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                                    সিঙ্গেল স্টক
                                </button>
                                <button 
                                    type="button" 
                                    wire:click="$set('stockType', 'Batch')" 
                                    class="px-3 py-1 rounded-md font-bold text-xs transition cursor-pointer {{ $stockType === 'Batch' ? 'bg-white text-sky-800 shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                                    ব্যাচ ভিত্তিক
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Quantity -->
                            <div>
                                <label class="block text-xs font-bold text-slate-800 mb-1">প্রারম্ভিক মজুদ (Stock Qty)</label>
                                <div class="flex items-center">
                                    <input 
                                        type="number" 
                                        min="0" 
                                        wire:model="quantity" 
                                        placeholder="EX: 100" 
                                        class="w-full text-xs font-mono font-bold text-slate-900 rounded-lg border border-slate-300 py-2 px-3 focus:border-sky-600" 
                                    />
                                </div>
                            </div>

                            <!-- Expiry Date -->
                            <div>
                                <label class="block text-xs font-bold text-slate-800 mb-1">মেয়াদোত্তীর্ণ তারিখ (Expiry Date)</label>
                                <input 
                                    type="date" 
                                    wire:model="expireDate" 
                                    class="w-full text-xs font-bold text-rose-600 rounded-lg border border-slate-300 py-2 px-2.5 focus:border-sky-600 bg-white" 
                                />
                            </div>

                            <!-- Batch Number (if Batch or optional) -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">ব্যাচ নম্বর (Batch No)</label>
                                <input 
                                    type="text" 
                                    wire:model="batchNumber" 
                                    placeholder="e.g. B-2026-09" 
                                    class="w-full text-xs font-mono font-bold uppercase rounded-lg border border-slate-300 py-2 px-3 focus:border-sky-600" 
                                />
                            </div>

                            <!-- Shelf / Rack Location -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">র‍্যাক / ড্রয়ার (Shelf/Rack)</label>
                                <input 
                                    type="text" 
                                    wire:model="rackLocation" 
                                    placeholder="e.g. Rack A-1, Drawer 3" 
                                    class="w-full text-xs font-bold text-slate-800 rounded-lg border border-slate-300 py-2 px-3 focus:border-sky-600" 
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">কম স্টক সতর্কতা (Min Stock Alert)</label>
                                <input 
                                    type="number" 
                                    wire:model="minStockAlert" 
                                    placeholder="20" 
                                    class="w-full text-xs font-bold text-rose-600 rounded-lg border border-slate-300 py-1.5 px-3" 
                                />
                                <span class="text-[10px] text-slate-400">স্টক এই পরিমাণের নিচে নামলে সতর্কতা দেখাবে</span>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">ম্যানুফ্যাকচারিং তারিখ (Mfg Date)</label>
                                <input 
                                    type="date" 
                                    wire:model="manufacturingDate" 
                                    class="w-full text-xs rounded-lg border border-slate-300 py-1.5 px-2.5 bg-white" 
                                />
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
                                লাইভ প্রিভিউ (Live Preview)
                            </span>
                            <span class="text-xs font-mono font-bold text-sky-100">{{ $stockType }} Stock</span>
                        </div>

                        <div>
                            <h4 class="text-lg font-black tracking-tight leading-tight text-white">
                                {{ $name ? $name : 'Medicine Brand Name' }}
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
                                <span class="text-[10px] text-sky-100 block">১ পিসের বিক্রয়মূল্য</span>
                                <span class="text-sm font-black font-mono text-white">৳{{ number_format((float)$mrpPrice, 2) }}</span>
                            </div>
                            <div class="rounded p-2" style="background-color: rgba(255,255,255,0.15) !important;">
                                <span class="text-[10px] text-sky-100 block">১ পিসের ক্রয়মূল্য</span>
                                <span class="text-sm font-bold font-mono text-white">৳{{ number_format((float)$purchasePrice, 2) }}</span>
                            </div>
                        </div>

                        @if ($manufacturers->firstWhere('id', $manufacturerId))
                            <div class="text-[11px] text-sky-100 flex items-center justify-between pt-1">
                                <span>কোম্পানি:</span>
                                <span class="font-bold text-white">{{ $manufacturers->firstWhere('id', $manufacturerId)->name }}</span>
                            </div>
                        @endif

                        @if ($rackLocation)
                            <div class="text-[11px] text-sky-100 flex items-center justify-between">
                                <span>র‍্যাক লোকেশন:</span>
                                <span class="font-bold text-amber-200">{{ $rackLocation }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Barcode Generation -->
                    <div class="bg-white rounded-lg p-4 border border-slate-200 shadow-xs space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-800">বারকোড (Barcode / EAN-13)</label>
                            <button type="button" wire:click="generateBarcode" class="text-[11px] text-sky-700 hover:text-sky-900 font-bold flex items-center gap-1 cursor-pointer">
                                ⚡ অটো জেনারেট
                            </button>
                        </div>
                        <input 
                            type="text" 
                            wire:model="barcode" 
                            placeholder="বারকোড স্ক্যান করুন বা লিখুন..." 
                            class="w-full font-mono text-xs font-bold rounded-lg border border-slate-300 py-2 px-3" 
                        />
                    </div>

                    <!-- Medicine Photo Upload (Compact) -->
                    <div class="bg-white rounded-lg p-4 border border-slate-200 shadow-xs space-y-2.5">
                        <label class="block text-xs font-bold text-slate-800">ওষুধের ছবি (Pack Photo)</label>
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
                                    <span>ছবি বাছাই করুন</span>
                                    <input type="file" wire:model="imageFile" accept="image/*" class="hidden" />
                                </label>
                                <span class="text-[10px] text-slate-400 block mt-1">PNG, JPG (Max 2MB)</span>
                            </div>
                        </div>
                        @error('imageFile') <span class="text-[10px] text-rose-500 font-semibold block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Special Regulatory Badges -->
                    <div class="bg-white rounded-lg p-4 border border-slate-200 shadow-xs space-y-2.5">
                        <label class="block text-xs font-bold text-slate-800">বিশেষ সতর্কতা ট্যাগ</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-700 font-medium">
                                <input type="checkbox" wire:model="isColdChain" class="rounded text-sky-600 focus:ring-sky-500" />
                                <span>❄️ কোল্ড চেইন (ফ্রিজে সংরক্ষণযোগ্য)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-700 font-medium">
                                <input type="checkbox" wire:model="isNarcotic" class="rounded text-sky-600 focus:ring-sky-500" />
                                <span>💊 নারকোটিক / সিডিউল-এইচ</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-700 font-medium">
                                <input type="checkbox" wire:model="requiresPrescription" class="rounded text-sky-600 focus:ring-sky-500" />
                                <span>📋 প্রেসক্রিপশন আবশ্যক (Rx Only)</span>
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
                            <span>{{ $editingProductId ? 'ওষুধ আপডেট করুন (Update)' : '💾 ওষুধ সংরক্ষণ করুন (Save Medicine)' }}</span>
                        </button>

                        <a 
                            href="{{ route('products.index') }}" 
                            wire:navigate 
                            class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs transition border border-slate-200 text-center block">
                            বাতিল করুন ও তালিকায় ফিরুন (Cancel)
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
        <!-- Bulk Upload Form -->
        <div class="bg-white rounded-lg p-6 border border-slate-200 shadow-xs max-w-xl space-y-4">
            <h3 class="font-bold text-base text-slate-900">Bulk Upload Medicines (CSV)</h3>
            <p class="text-xs text-slate-500">Upload CSV file with columns: [Medicine Name, MRP Price, Purchase Rate]</p>

            <form wire:submit="processBulkUpload" class="space-y-4">
                <input type="file" wire:model="csvFile" class="block w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 border border-slate-200 rounded-lg p-1.5 bg-slate-50" />
                @error('csvFile') <span class="text-[11px] text-rose-500 block">{{ $message }}</span> @enderror

                <button type="submit" class="w-full py-2 bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-lg text-xs transition shadow-xs cursor-pointer">
                    Upload & Import Medicines
                </button>
            </form>
        </div>
    @endif

    @if (in_array($tab, ['categories', 'units', 'types', 'manufacturers', 'box_sizes']))
        <!-- Master Tables -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-xs space-y-3.5">
                <h3 class="font-bold text-sm text-slate-900 border-b border-slate-100 pb-2">Add New {{ ucfirst(rtrim($tab, 's')) }}</h3>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Name</label>
                    <input type="text" wire:model="newMasterName" placeholder="Enter name..." class="w-full" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Description / Value</label>
                    <input type="text" wire:model="newMasterDescription" placeholder="Description or code..." class="w-full" />
                </div>
                <button type="button" wire:click="addMaster('{{ $tab === 'categories' ? 'category' : ($tab === 'units' ? 'unit' : ($tab === 'types' ? 'type' : ($tab === 'manufacturers' ? 'manufacturer' : 'box_size'))) }}')" class="w-full py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg text-xs transition shadow-xs cursor-pointer">
                    Add Record
                </button>
            </div>

            <div class="md:col-span-2 bg-white rounded-lg border border-slate-200 overflow-hidden shadow-xs">
                <div class="p-3.5 border-b border-slate-200 bg-slate-50 font-bold text-slate-800 text-xs">
                    Existing {{ ucfirst($tab) }}
                </div>
                <div class="p-3 divide-y divide-slate-100 max-h-96 overflow-y-auto">
                    @php
                        $list = $tab === 'categories' ? $categories : ($tab === 'units' ? $units : ($tab === 'types' ? $medicineTypes : ($tab === 'manufacturers' ? $manufacturers : $boxSizes)));
                    @endphp
                    @foreach ($list as $item)
                        <div class="py-2.5 px-2 flex justify-between items-center text-xs hover:bg-slate-50/80 rounded transition">
                            <span class="font-semibold text-slate-800">{{ $item->name }}</span>
                            <span class="text-[11px] text-slate-500 font-mono">{{ $item->short_code ?? $item->total_quantity ?? '' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
