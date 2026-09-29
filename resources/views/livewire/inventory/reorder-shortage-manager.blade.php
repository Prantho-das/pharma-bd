<div class="p-6 space-y-6 max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <span>স্বয়ংক্রিয় রি-অর্ডার ও শর্টেজ তালিকা (Reorder & Shortage Hub)</span>
                <span class="bg-rose-100 text-rose-800 text-xs px-2.5 py-0.5 rounded-full font-bold">
                    {{ $totalShortagesCount }} Medicines
                </span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">মিনিমাম অ্যালার্টের নিচে নামা ওষুধগুলোর তালিকা ও এক ক্লিকে সাপ্লায়ারের জন্য পারচেজ অর্ডার (PO) তৈরি করুন।</p>
        </div>

        <div class="flex items-center gap-2">
            <button 
                wire:click="createPurchaseOrder" 
                type="button" 
                class="px-4 py-2.5 bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-700 hover:to-blue-800 text-white rounded-lg text-xs font-bold shadow-md transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
                {{ empty($selectedProductIds) ? 'disabled' : '' }}
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Generate PO Requisition ({{ count($selectedProductIds) }})</span>
            </button>
            <button 
                onclick="window.print()" 
                type="button" 
                class="px-3.5 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-xs"
            >
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print List</span>
            </button>
        </div>
    </div>

    {{-- Alerts --}}
    @if($successMessage)
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-lg flex justify-between items-center shadow-xs">
            <span>{{ $successMessage }}</span>
            <button wire:click="$set('successMessage', null)" class="text-emerald-500 font-bold">&times;</button>
        </div>
    @endif
    @if($errorMessage)
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold rounded-lg flex justify-between items-center shadow-xs">
            <span>{{ $errorMessage }}</span>
            <button wire:click="$set('errorMessage', null)" class="text-rose-500 font-bold">&times;</button>
        </div>
    @endif

    {{-- Summary KPIs --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">মোট শর্টেজ আইটেম</div>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $totalShortagesCount }}</div>
                <div class="text-[10px] text-slate-400 mt-0.5">মিনিমাম অ্যালার্টের নিচে</div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg">⚠️</div>
        </div>

        <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">সম্পূর্ণ স্টকআউট (Zero Stock)</div>
                <div class="text-2xl font-black text-rose-600 mt-1">{{ $zeroStockCount }}</div>
                <div class="text-[10px] text-rose-500 font-semibold mt-0.5">তাৎক্ষণিক অর্ডার প্রয়োজন</div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-lg">🚨</div>
        </div>

        <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">আনুমানিক রি-অর্ডার বাজেট</div>
                <div class="text-2xl font-black text-sky-700 font-mono mt-1">৳{{ number_format($totalEstimatedCost, 2) }}</div>
                <div class="text-[10px] text-slate-400 mt-0.5">প্রস্তাবিত রি-অর্ডার মূল্যের হিসাব</div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center font-bold text-lg">💰</div>
        </div>
    </div>

    {{-- Filter & Search Toolbar --}}
    <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="flex flex-1 items-center gap-2 w-full sm:w-auto">
            <div class="relative flex-1">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search brand, generic molecule or SKU..." 
                    class="w-full text-xs rounded-lg border border-slate-300 py-2 px-3 bg-white" 
                />
            </div>
            <select wire:model.live="selectedManufacturer" class="text-xs rounded-lg border border-slate-300 py-2 px-3 bg-white">
                <option value="">-- All Manufacturers --</option>
                @foreach($manufacturers as $mfg)
                    <option value="{{ $mfg->id }}">{{ $mfg->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="inline-flex rounded-lg border border-slate-200 p-0.5 bg-slate-50 text-xs">
            <button 
                type="button" 
                wire:click="$set('stockFilter', 'all')" 
                class="px-3 py-1 rounded-md font-bold text-xs transition cursor-pointer {{ $stockFilter === 'all' ? 'bg-white text-sky-800 shadow-2xs' : 'text-slate-600' }}">
                All Shortages ({{ $totalShortagesCount }})
            </button>
            <button 
                type="button" 
                wire:click="$set('stockFilter', 'zero_stock')" 
                class="px-3 py-1 rounded-md font-bold text-xs transition cursor-pointer {{ $stockFilter === 'zero_stock' ? 'bg-white text-rose-700 shadow-2xs' : 'text-slate-600' }}">
                Zero Stock ({{ $zeroStockCount }})
            </button>
            <button 
                type="button" 
                wire:click="$set('stockFilter', 'low_stock')" 
                class="px-3 py-1 rounded-md font-bold text-xs transition cursor-pointer {{ $stockFilter === 'low_stock' ? 'bg-white text-amber-700 shadow-2xs' : 'text-slate-600' }}">
                Low Stock
            </button>
        </div>
    </div>

    {{-- Shortage Table --}}
    <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-xs">
        <table class="w-full text-left text-xs border-collapse">
            <thead class="bg-slate-50 text-slate-700 border-b border-slate-200 font-bold text-[11px] uppercase tracking-wider">
                <tr>
                    <th class="p-3 w-10 text-center">
                        <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300" />
                    </th>
                    <th class="p-3">ওষুধের নাম ও কোম্পানি (Medicine)</th>
                    <th class="p-3">জেনেরিক (Generic)</th>
                    <th class="p-3 text-center">বর্তমান স্টক (Current)</th>
                    <th class="p-3 text-center">মিনিমাম অ্যালার্ট (Min)</th>
                    <th class="p-3 w-36 text-center">প্রস্তাবিত অর্ডার (Reorder Qty)</th>
                    <th class="p-3 text-right">ক্রয়মূল্য (Buy ৳)</th>
                    <th class="p-3 text-right">আনুমানিক মোট (Total ৳)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($shortages as $prod)
                    @php
                        $stock = $prod->totalStockPieces();
                        $minAlert = $prod->min_stock_alert ?? 10;
                        $buyPrice = (float)($prod->productUnits->firstWhere('unit_id', $prod->base_unit_id)?->purchase_price ?? 0);
                        $reorderQty = (int)($reorderQuantities[$prod->id] ?? ($minAlert * 2));
                        $rowTotal = $reorderQty * $buyPrice;
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition {{ in_array($prod->id, $selectedProductIds) ? 'bg-sky-50/40' : '' }}">
                        <td class="p-3 text-center">
                            <input 
                                type="checkbox" 
                                value="{{ $prod->id }}" 
                                wire:model.live="selectedProductIds" 
                                class="rounded border-slate-300" 
                            />
                        </td>
                        <td class="p-3">
                            <div class="font-bold text-slate-900 text-sm">{{ $prod->name }}</div>
                            <div class="text-[11px] text-slate-500">{{ $prod->manufacturer?->name ?? 'N/A' }}</div>
                        </td>
                        <td class="p-3">
                            <div class="text-sky-800 font-medium">{{ $prod->generic?->name ?? 'N/A' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $prod->category?->name ?? 'General' }}</div>
                        </td>
                        <td class="p-3 text-center">
                            @if($stock <= 0)
                                <span class="px-2 py-0.5 bg-rose-100 text-rose-800 rounded-full font-bold text-[11px]">
                                    0 {{ $prod->baseUnit?->name ?? 'pcs' }} (Stockout)
                                </span>
                            @else
                                <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full font-bold text-[11px]">
                                    {{ $stock }} {{ $prod->baseUnit?->name ?? 'pcs' }}
                                </span>
                            @endif
                        </td>
                        <td class="p-3 text-center font-bold text-slate-600">
                            {{ $minAlert }} {{ $prod->baseUnit?->name ?? 'pcs' }}
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <input 
                                    type="number" 
                                    min="1" 
                                    wire:model.live.debounce.300ms="reorderQuantities.{{ $prod->id }}" 
                                    class="w-20 text-center font-mono font-bold text-xs py-1 px-1 rounded border border-slate-300" 
                                />
                                <span class="text-[11px] text-slate-500">{{ $prod->baseUnit?->name ?? 'pcs' }}</span>
                            </div>
                        </td>
                        <td class="p-3 text-right font-mono font-bold text-slate-700">
                            ৳{{ number_format($buyPrice, 2) }}
                        </td>
                        <td class="p-3 text-right font-mono font-black text-sky-800">
                            ৳{{ number_format($rowTotal, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-12 text-center text-slate-400">
                            <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-2 text-xl">✓</div>
                            <div class="font-bold text-slate-700">আলহামদুলিল্লাহ! কোনো ওষুধের স্টক সংকট নেই।</div>
                            <div class="text-xs text-slate-400 mt-1">সব ওষুধের মজুদ পর্যাপ্ত পরিমাণে রয়েছে।</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
