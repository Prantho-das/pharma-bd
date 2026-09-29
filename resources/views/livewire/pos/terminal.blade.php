<div 
    x-data="{
        handleShortcuts(e) {
            // Alt + S or Ctrl + K: Focus Search
            if ((e.altKey && (e.key === 's' || e.key === 'S')) || ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K'))) {
                e.preventDefault();
                $refs.searchInput?.focus();
            }
            // Alt + B: Focus Barcode input
            else if (e.altKey && (e.key === 'b' || e.key === 'B')) {
                e.preventDefault();
                $refs.barcodeInput?.focus();
            }
            // Alt + H: Hold Cart
            else if (e.altKey && (e.key === 'h' || e.key === 'H')) {
                e.preventDefault();
                $wire.holdCurrentCart();
            }
            // Alt + R: Recall Held Carts
            else if (e.altKey && (e.key === 'r' || e.key === 'R')) {
                e.preventDefault();
                $wire.set('showHeldCartsDrawer', true);
            }
            // Alt + C: Focus Customer Select
            else if (e.altKey && (e.key === 'c' || e.key === 'C')) {
                e.preventDefault();
                $refs.customerSelect?.focus();
            }
            // Alt + P or Ctrl + Enter: Pay / Settle Modal
            else if ((e.altKey && (e.key === 'p' || e.key === 'P')) || ((e.ctrlKey || e.metaKey) && e.key === 'Enter')) {
                e.preventDefault();
                $wire.openPaymentModal();
            }
        }
    }"
    @keydown.window="handleShortcuts($event)"
    class="h-screen flex flex-col bg-slate-100 text-slate-800 antialiased font-sans select-none overflow-hidden"
>
    {{-- Top POS Navigation Bar --}}
    <header class="px-5 py-2.5 flex items-center justify-between shadow-md z-30 shrink-0 text-white" style="background: linear-gradient(135deg, #0369a1 0%, #0284c7 50%, #0369a1 100%) !important; color: #ffffff !important;">
        <div class="flex items-center space-x-4">
            <div class="flex items-center space-x-2.5">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center border border-white/30 shadow-inner" style="background-color: rgba(255, 255, 255, 0.15);">
                    <svg class="w-5 h-5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                </div>
                <div>
                    <h1 class="text-base font-bold tracking-tight text-white flex items-center gap-2">
                        Pharmacy POS Terminal <span class="text-[11px] px-2.5 py-0.5 rounded-full border border-sky-300/40 font-semibold" style="background-color: rgba(14, 165, 233, 0.35); color: #e0f2fe;">FEFO Engine</span>
                    </h1>
                    <p class="text-[11px] text-sky-100">{{ auth()->user()->branch?->name ?? 'Main Branch' }} &bull; Cashier: <span class="font-bold text-white">{{ auth()->user()->name ?? 'Counter 01' }}</span></p>
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            {{-- Quick Shortcut Badges (Web Friendly) --}}
            <div class="hidden lg:flex items-center space-x-2 text-[11px] text-white mr-2">
                <span class="px-2 py-0.5 rounded border border-white/20" style="background-color: rgba(0, 0, 0, 0.2);"><kbd class="font-bold text-amber-300">Alt+S</kbd> Search</span>
                <span class="px-2 py-0.5 rounded border border-white/20" style="background-color: rgba(0, 0, 0, 0.2);"><kbd class="font-bold text-amber-300">Alt+B</kbd> Barcode</span>
                <span class="px-2 py-0.5 rounded border border-white/20" style="background-color: rgba(0, 0, 0, 0.2);"><kbd class="font-bold text-amber-300">Alt+H</kbd> Hold</span>
                <span class="px-2 py-0.5 rounded border border-white/20" style="background-color: rgba(0, 0, 0, 0.2);"><kbd class="font-bold text-amber-300">Alt+R</kbd> Recall</span>
                <span class="px-2 py-0.5 rounded border border-white/20" style="background-color: rgba(0, 0, 0, 0.2);"><kbd class="font-bold text-amber-300">Alt+P</kbd> Pay</span>
            </div>

            {{-- Suspended / Held Carts Trigger --}}
            <button wire:click="$toggle('showHeldCartsDrawer')" type="button" class="relative text-white text-xs font-semibold px-3.5 py-1.5 rounded-lg border border-sky-300/30 transition flex items-center gap-1.5 shadow-sm cursor-pointer" style="background-color: #075985;">
                <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Held Carts</span>
                @if(count($heldCarts) > 0)
                    <span class="absolute -top-1.5 -right-1.5 bg-amber-400 text-slate-900 text-[10px] font-extrabold w-5 h-5 rounded-full flex items-center justify-center shadow-md animate-pulse">
                        {{ count($heldCarts) }}
                    </span>
                @endif
            </button>

            {{-- Back to Dashboard --}}
            <a href="{{ route('dashboard') }}" class="text-white text-xs font-semibold px-3 py-1.5 rounded-lg border border-white/30 transition flex items-center gap-1 cursor-pointer" style="background-color: rgba(255, 255, 255, 0.18);" wire:navigate>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span>Dashboard</span>
            </a>
        </div>
    </header>

    {{-- System Alerts --}}
    @if($errorMessage)
        <div class="bg-rose-600 text-white px-5 py-2 text-xs font-medium flex items-center justify-between shadow-sm animate-shake">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-white shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                <span>{{ $errorMessage }}</span>
            </div>
            <button wire:click="$set('errorMessage', null)" class="text-rose-200 hover:text-white cursor-pointer">&times;</button>
        </div>
    @endif

    @if($successMessage)
        <div class="bg-emerald-600 text-white px-5 py-2 text-xs font-medium flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-white shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                <span>{{ $successMessage }}</span>
            </div>
            <button wire:click="$set('successMessage', null)" class="text-emerald-200 hover:text-white cursor-pointer">&times;</button>
        </div>
    @endif

    {{-- Main POS Body: Split 2 Columns --}}
    <div class="flex-1 flex overflow-hidden">
        {{-- LEFT COLUMN: Product Lookup & Cart Items Grid (70%) --}}
        <div class="w-8/12 flex flex-col bg-white border-r border-slate-200">
            {{-- Search & Barcode Header --}}
            <div class="p-3 bg-slate-50 border-b border-slate-200 flex items-center space-x-3">
                {{-- Barcode Scanner Input --}}
                <div class="relative w-5/12">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-sky-600">
                        <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    </div>
                    <input 
                        x-ref="barcodeInput"
                        wire:model="barcodeInput" 
                        wire:keydown.enter="handleBarcodeScan" 
                        type="text" 
                        placeholder="Scan Barcode (Alt+B) + Enter..." 
                        class="w-full pr-3 py-2 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 font-mono shadow-xs placeholder:text-slate-400"
                        style="padding-left: 2.5rem !important;"
                        autofocus
                    >
                </div>

                {{-- Universal Name/Generic Live Search --}}
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-sky-600">
                        <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input 
                        x-ref="searchInput"
                        wire:model.live.debounce.250ms="search" 
                        type="text" 
                        placeholder="Search brand name, generic molecule or company... (Alt+S or Ctrl+K)" 
                        class="w-full pr-8 py-2 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 shadow-xs placeholder:text-slate-400"
                        style="padding-left: 2.5rem !important;"
                    >
                    @if($search)
                        <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 text-xs cursor-pointer">&times;</button>
                    @endif

                    {{-- Search Auto-complete Dropdown --}}
                    @if(count($searchResults) > 0)
                        <div class="absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-lg shadow-2xl z-50 max-h-80 overflow-y-auto divide-y divide-slate-100">
                            @foreach($searchResults as $res)
                                <div 
                                    wire:click="addToCart({{ $res['id'] }})"
                                    class="p-2.5 hover:bg-sky-50/80 cursor-pointer flex items-center justify-between transition group"
                                >
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <span class="font-bold text-xs text-slate-800 group-hover:text-sky-700">{{ $res['name'] }}</span>
                                            @if($res['is_narcotic'])
                                                <span class="bg-rose-100 text-rose-700 text-[10px] font-bold px-1.5 py-0.2 rounded border border-rose-200">Narcotic</span>
                                            @endif
                                            @if($res['requires_prescription'])
                                                <span class="bg-amber-100 text-amber-700 text-[10px] font-medium px-1.5 py-0.2 rounded border border-amber-200">Rx Req</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                            <span class="text-sky-700 font-medium">{{ $res['generic_name'] }}</span> &bull; 
                                            <span>{{ $res['manufacturer_name'] }}</span> &bull; 
                                            <span class="font-mono text-slate-400">SKU: {{ $res['sku'] }}</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        @if($res['stock_pieces'] > 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                                {{ $res['stock_pieces'] }} pcs in stock
                                            </span>
                                        @else
                                            <div class="flex flex-col items-end">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-800">
                                                    Out of Stock
                                                </span>
                                                <button 
                                                    wire:click.stop="openSubstituteFinder({{ $res['generic_id'] }}, '{{ addslashes($res['generic_name']) }}')"
                                                    class="text-[10px] text-sky-600 hover:text-sky-800 underline mt-0.5 font-medium"
                                                >
                                                    Find Alternative &rarr;
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Cart Items Table --}}
            <div class="flex-1 overflow-y-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-100 text-slate-600 text-[11px] uppercase tracking-wider font-semibold sticky top-0 z-10 border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3 w-8">#</th>
                            <th class="py-2.5 px-3">Medicine Item</th>
                            <th class="py-2.5 px-2 w-32">Packaging Unit</th>
                            <th class="py-2.5 px-2 w-24 text-center">Qty</th>
                            <th class="py-2.5 px-2 w-24 text-right">Unit Price</th>
                            <th class="py-2.5 px-2 w-20 text-right">Disc</th>
                            <th class="py-2.5 px-3 w-28 text-right">Total (৳)</th>
                            <th class="py-2.5 px-2 w-10 text-center"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($cart as $index => $item)
                            <tr class="hover:bg-slate-50/70 transition {{ !empty($item['is_narcotic']) ? 'bg-rose-50/30' : '' }}">
                                <td class="py-2 px-3 text-slate-400 font-mono text-[11px]">{{ $loop->iteration }}</td>
                                <td class="py-2 px-3">
                                    <div class="font-bold text-slate-800 flex items-center gap-1.5 flex-wrap">
                                        <span>{{ $item['name'] ?? '' }}</span>
                                        @if(!empty($item['is_cold_chain']))
                                            <span class="bg-blue-100 text-blue-800 text-[9px] font-bold px-1.5 py-0.5 rounded border border-blue-200" title="Keep Refrigerated (2°C - 8°C)">❄️ Cold</span>
                                        @endif
                                        @if(!empty($item['is_narcotic']))
                                            <span class="bg-rose-500 text-white text-[9px] font-bold px-1 rounded">Rx-N</span>
                                        @endif
                                        @if(!empty($item['requires_prescription']))
                                            <span class="bg-amber-100 text-amber-800 text-[9px] font-bold px-1 rounded border border-amber-200">Rx</span>
                                        @endif
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono flex items-center gap-2 mt-0.5">
                                        <span>Stock: {{ $item['stock_pieces'] ?? 0 }} pcs</span>
                                    </div>
                                </td>
                                <td class="py-2 px-2">
                                    <select 
                                        wire:change="updateUnit({{ $index }}, $event.target.value)"
                                        class="w-full text-xs py-1 px-1.5 bg-white border border-slate-300 rounded font-medium focus:ring-1 focus:ring-sky-500"
                                    >
                                        @foreach(($item['available_units'] ?? []) as $u)
                                            <option value="{{ $u['unit_id'] }}" {{ ($item['selected_unit_id'] ?? null) == $u['unit_id'] ? 'selected' : '' }}>
                                                {{ $u['unit_name'] }} (৳{{ number_format($u['price'] ?? 0, 2) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="py-2 px-2 text-center">
                                    <div class="flex items-center justify-center space-x-1">
                                        <button 
                                            wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] - 1 }})" 
                                            type="button" 
                                            class="w-6 h-6 rounded bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold flex items-center justify-center text-xs transition cursor-pointer shrink-0"
                                        >-</button>
                                        <input 
                                            wire:change="updateQuantity({{ $index }}, $event.target.value)" 
                                            type="number" 
                                            value="{{ $item['quantity'] }}" 
                                            min="1" 
                                            class="w-12 text-center text-xs py-1 px-1 border border-slate-300 rounded font-bold focus:ring-1 focus:ring-sky-500 font-mono"
                                            style="width: 3.25rem !important; display: inline-block !important;"
                                        >
                                        <button 
                                            wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})" 
                                            type="button" 
                                            class="w-6 h-6 rounded bg-sky-100 hover:bg-sky-200 text-sky-800 font-bold flex items-center justify-center text-xs transition cursor-pointer shrink-0"
                                        >+</button>
                                    </div>
                                </td>
                                <td class="py-2 px-2 text-right font-mono font-medium text-slate-700">
                                    ৳{{ number_format($item['unit_price'] ?? 0, 2) }}
                                </td>
                                <td class="py-2 px-2 text-right">
                                    <input 
                                        wire:change="updateDiscount({{ $index }}, $event.target.value)" 
                                        type="number" 
                                        value="{{ $item['discount'] ?? 0 }}" 
                                        min="0" 
                                        class="w-16 text-right text-xs py-1 px-1 border border-slate-300 rounded font-mono focus:ring-1 focus:ring-sky-500"
                                        style="width: 4rem !important; display: inline-block !important;"
                                    >
                                </td>
                                <td class="py-2 px-3 text-right font-mono font-bold text-sky-800 text-sm">
                                    ৳{{ number_format($item['total'] ?? (($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0) - ($item['discount'] ?? 0)), 2) }}
                                </td>
                                <td class="py-2 px-2 text-center">
                                    <button 
                                        wire:click="removeFromCart({{ $index }})" 
                                        type="button" 
                                        class="text-rose-400 hover:text-rose-600 transition cursor-pointer" 
                                        title="Remove item"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-20 text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center text-slate-300 mb-3">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        </div>
                                        <p class="font-medium text-sm text-slate-500">Cart is empty</p>
                                        <p class="text-xs text-slate-400 mt-1">Scan barcode or type medicine name to start sale</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Cart Action Bottom Bar --}}
            <div class="p-2.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between text-xs">
                <div class="text-slate-500 font-medium">
                    <span>Total Items: <strong class="text-slate-800">{{ count($cart) }}</strong></span> &bull;
                    <span>Total Qty: <strong class="text-slate-800">{{ collect($cart)->sum('quantity') }}</strong></span>
                </div>
                <div class="flex items-center space-x-2">
                    <button 
                        wire:click="holdCurrentCart" 
                        type="button" 
                        class="px-3 py-1.5 rounded-lg bg-amber-100 hover:bg-amber-200 text-amber-900 font-semibold transition flex items-center gap-1 cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Hold Cart (Alt+H)</span>
                    </button>
                    <button 
                        wire:click="clearCart" 
                        type="button" 
                        class="px-3 py-1.5 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold transition cursor-pointer"
                    >
                        Clear (Esc)
                    </button>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: Customer, Doctor & Checkout Settlement (30%) --}}
        <div class="w-4/12 flex flex-col bg-slate-50 p-4 justify-between overflow-y-auto">
            <div class="space-y-3.5">
                {{-- Customer Selection & Quick Add --}}
                <div class="bg-white p-3 rounded-lg border border-slate-200 shadow-sm space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <span>Customer (Alt+C)</span>
                        </label>
                        <button 
                            wire:click="$set('showCustomerModal', true)" 
                            type="button" 
                            class="text-[11px] text-sky-700 hover:text-sky-900 font-bold flex items-center gap-0.5 cursor-pointer"
                        >
                            + Quick Add
                        </button>
                    </div>
                    <select 
                        x-ref="customerSelect"
                        wire:model="selectedCustomerId" 
                        class="w-full text-xs py-1.5 px-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500 font-medium"
                    >
                        <option value="">-- Walk-in Regular Customer --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">
                                {{ $c->name }} ({{ $c->phone }}) - Due: ৳{{ number_format($c->current_due, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Doctor Directory Selection --}}
                <div class="bg-white p-3 rounded-lg border border-slate-200 shadow-sm space-y-2">
                    <label class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>Prescribing Doctor</span>
                    </label>
                    <select 
                        wire:model="selectedDoctorId" 
                        class="w-full text-xs py-1.5 px-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500 font-medium"
                    >
                        <option value="">-- No Doctor / OTC Sale --</option>
                        @foreach($doctors as $d)
                            <option value="{{ $d->id }}">
                                {{ $d->name }} ({{ $d->degrees ?? 'MBBS' }}) - {{ $d->hospital_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Prescription Attachment & Safety Warning --}}
                <div class="bg-white p-3 rounded-lg border border-slate-200 shadow-sm space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            <span>Prescription Upload (Rx)</span>
                        </label>
                        @if($prescriptionFile)
                            <span class="text-[10px] text-emerald-600 font-bold">✓ Attached</span>
                        @endif
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <input 
                            type="file" 
                            wire:model="prescriptionFile" 
                            accept="image/*,.pdf" 
                            class="text-[11px] text-slate-600 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-[11px] file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 cursor-pointer"
                        />
                    </div>

                    @php
                        $hasNarcotic = collect($cart)->contains(fn($i) => !empty($i['is_narcotic']));
                        $hasColdChain = collect($cart)->contains(fn($i) => !empty($i['is_cold_chain']));
                    @endphp

                    @if($hasNarcotic)
                        <div class="p-2 bg-rose-50 border border-rose-200 rounded text-[11px] text-rose-800 font-bold flex items-center gap-1">
                            <span>💊 DGDA Alert: Contains Schedule-H narcotic items. Doctor prescription mandatory!</span>
                        </div>
                    @endif

                    @if($hasColdChain)
                        <div class="p-2 bg-blue-50 border border-blue-200 rounded text-[11px] text-blue-800 font-bold flex items-center gap-1">
                            <span>❄️ Cold Chain Alert: Contains refrigerated items (2°C - 8°C). Pack in ice bag!</span>
                        </div>
                    @endif
                </div>

                {{-- Financial Summary Breakdown --}}
                <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm space-y-2.5">
                    <div class="flex justify-between items-center text-xs text-slate-600">
                        <span>Sub Total</span>
                        <span class="font-mono font-bold text-slate-800 text-sm">৳{{ number_format($subTotal, 2) }}</span>
                    </div>

                    {{-- Overall Discount Control --}}
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                        <div class="flex items-center space-x-1">
                            <span class="text-slate-600">Discount</span>
                            <button 
                                wire:click="$set('discountType', '{{ $discountType === 'fixed' ? 'percentage' : 'fixed' }}')" 
                                wire:click="recalculateTotals"
                                class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-200 text-slate-700 cursor-pointer"
                            >
                                {{ $discountType === 'fixed' ? '৳ Fixed' : '% Pct' }}
                            </button>
                        </div>
                        <div class="flex items-center space-x-2">
                            <input 
                                wire:model.live.debounce.300ms="discountValue" 
                                wire:change="recalculateTotals" 
                                type="number" 
                                min="0" 
                                class="w-20 text-right text-xs py-1 px-1.5 border border-slate-300 rounded font-mono font-bold focus:ring-1 focus:ring-sky-500"
                                style="width: 5rem !important; display: inline-block !important;"
                                placeholder="0.00"
                            >
                            <span class="font-mono text-xs text-rose-600 font-semibold w-16 text-right">
                                -৳{{ number_format($discountAmount, 2) }}
                            </span>
                        </div>
                    </div>

                    {{-- Tax / VAT Rate --}}
                    <div class="flex items-center justify-between text-xs text-slate-600">
                        <div class="flex items-center space-x-1">
                            <span>Tax / VAT (%)</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <input 
                                wire:model.live.debounce.300ms="taxRate" 
                                wire:change="recalculateTotals" 
                                type="number" 
                                min="0" 
                                class="w-16 text-right text-xs py-1 px-1.5 border border-slate-300 rounded font-mono focus:ring-1 focus:ring-sky-500"
                                style="width: 4.5rem !important; display: inline-block !important;"
                                placeholder="0%"
                            >
                            <span class="font-mono text-xs text-slate-700 font-semibold w-16 text-right">
                                +৳{{ number_format($taxAmount, 2) }}
                            </span>
                        </div>
                    </div>

                    {{-- Round Off --}}
                    @if($roundOff != 0)
                        <div class="flex justify-between items-center text-xs text-slate-500">
                            <span>Round Off</span>
                            <span class="font-mono">{{ $roundOff > 0 ? '+' : '' }}৳{{ number_format($roundOff, 2) }}</span>
                        </div>
                    @endif

                    {{-- Grand Total Display Card --}}
                    <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-between">
                        <span class="text-sm font-extrabold text-slate-800">Grand Total</span>
                        <span class="text-2xl font-black text-sky-700 font-mono">
                            ৳{{ number_format($grandTotal, 2) }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Checkout Button Trigger (Alt+P / Ctrl+Enter) --}}
            <div class="mt-4">
                <button 
                    wire:click="openPaymentModal" 
                    type="button" 
                    class="w-full py-3.5 px-4 text-white rounded-lg shadow-lg hover:shadow-xl font-bold text-base flex items-center justify-center gap-2 transition transform active:scale-98 cursor-pointer disabled:opacity-50"
                    style="background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%) !important; color: #ffffff !important;"
                    {{ empty($cart) ? 'disabled' : '' }}
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span>SETTLE & PAY (Alt+P / Ctrl+Enter)</span>
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL 1: Dynamic Split Payment Settlement Drawer --}}
    @if($showPaymentModal)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-2xl rounded-xl shadow-2xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                {{-- Modal Header --}}
                <div class="px-6 py-4 text-white flex items-center justify-between" style="background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%) !important; color: #ffffff !important;">
                    <div>
                        <h2 class="text-lg font-bold flex items-center gap-2">
                            <span>💳 Payment Settlement & Invoice Checkout</span>
                        </h2>
                        <p class="text-xs text-sky-100">
                            মোট প্রদেয় বিল: <span class="font-mono font-black text-white text-base">৳{{ number_format($grandTotal, 2) }}</span>
                        </p>
                    </div>
                    <button wire:click="$set('showPaymentModal', false)" class="text-white/80 hover:text-white text-2xl font-bold cursor-pointer transition">&times;</button>
                </div>

                <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                    {{-- Quick Cash Fast-Action Bar --}}
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                            ⚡ দ্রুত ক্যাশ এন্ট্রি (Quick Cash Actions)
                        </label>
                        <div class="flex flex-wrap items-center gap-2">
                            <button 
                                wire:click="setExactCash" 
                                type="button" 
                                class="px-3 py-1.5 rounded-lg bg-sky-50 text-sky-800 border border-sky-200 hover:bg-sky-100 text-xs font-bold transition cursor-pointer"
                            >
                                Exact (৳{{ number_format($grandTotal, 2) }})
                            </button>
                            <button 
                                wire:click="addQuickCash(50)" 
                                type="button" 
                                class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition cursor-pointer"
                            >
                                +৳50
                            </button>
                            <button 
                                wire:click="addQuickCash(100)" 
                                type="button" 
                                class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition cursor-pointer"
                            >
                                +৳100
                            </button>
                            <button 
                                wire:click="addQuickCash(500)" 
                                type="button" 
                                class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition cursor-pointer"
                            >
                                +৳500
                            </button>
                            <button 
                                wire:click="addQuickCash(1000)" 
                                type="button" 
                                class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition cursor-pointer"
                            >
                                +৳1000
                            </button>
                            <button 
                                wire:click="addQuickCash(2000)" 
                                type="button" 
                                class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition cursor-pointer"
                            >
                                +৳2000
                            </button>
                        </div>
                    </div>

                    {{-- Dynamic Payment Method Rows --}}
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                                পেমেন্ট মেথড ও পরিমাণ (Payment Breakdown)
                            </label>
                            <button 
                                wire:click="addPaymentRow" 
                                type="button" 
                                class="px-3 py-1 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold transition flex items-center gap-1 shadow-xs cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                <span>+ Add New Payment</span>
                            </button>
                        </div>

                        <div class="space-y-2.5">
                            @foreach($paymentRows as $index => $row)
                                <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 flex items-center gap-2.5 transition hover:border-slate-300">
                                    {{-- Method Select --}}
                                    <div class="w-4/12">
                                        <select 
                                            wire:model="paymentRows.{{ $index }}.method" 
                                            class="w-full text-xs font-bold py-2 px-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500"
                                        >
                                            <option value="CASH">💵 Cash (ক্যাশ)</option>
                                            <option value="BKASH">📱 bKash (বিকাশ)</option>
                                            <option value="NAGAD">📱 Nagad (নগদ)</option>
                                            <option value="ROCKET">📱 Rocket (রকেট)</option>
                                            <option value="CARD">💳 POS Card (কার্ড)</option>
                                            <option value="BANK">🏦 Bank Transfer (ব্যাংক)</option>
                                        </select>
                                    </div>

                                    {{-- Amount Input --}}
                                    <div class="w-4/12 relative">
                                        <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400 font-bold text-xs">
                                            ৳
                                        </div>
                                        <input 
                                            type="number" 
                                            step="0.01" 
                                            min="0" 
                                            wire:model.live.debounce.250ms="paymentRows.{{ $index }}.amount" 
                                            wire:keyup="recalculatePaymentBreakdown" 
                                            placeholder="0.00" 
                                            class="w-full pl-6 pr-2 py-2 text-xs font-mono font-bold bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500 text-slate-900"
                                        />
                                    </div>

                                    {{-- Trx ID / Reference --}}
                                    <div class="flex-1">
                                        <input 
                                            type="text" 
                                            wire:model="paymentRows.{{ $index }}.reference" 
                                            placeholder="Trx ID / Card / Note..." 
                                            class="w-full py-2 px-2.5 text-xs bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500 text-slate-700"
                                        />
                                    </div>

                                    {{-- Remove Button --}}
                                    @if(count($paymentRows) > 1)
                                        <button 
                                            wire:click="removePaymentRow({{ $index }})" 
                                            type="button" 
                                            class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition cursor-pointer"
                                            title="Delete Payment Method"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Financial Summary Grid --}}
                    <div class="p-3.5 bg-slate-100 rounded-lg border border-slate-200 grid grid-cols-3 gap-3 text-center">
                        <div class="bg-white p-2.5 rounded border border-slate-200">
                            <span class="block text-[11px] font-medium text-slate-500">মোট পরিশোধিত (Total Paid)</span>
                            <span class="font-mono font-bold text-sm text-slate-800">৳{{ number_format($paidTotal, 2) }}</span>
                        </div>
                        <div class="bg-white p-2.5 rounded border border-slate-200">
                            <span class="block text-[11px] font-medium text-emerald-600">ফেরত দিতে হবে (Change Return)</span>
                            <span class="font-mono font-black text-sm text-emerald-700">৳{{ number_format($changeAmount, 2) }}</span>
                        </div>
                        <div class="bg-white p-2.5 rounded border border-slate-200">
                            <span class="block text-[11px] font-medium text-amber-600">অবশিষ্ট বকেয়া (Due Balance)</span>
                            <span class="font-mono font-black text-sm {{ $remainingDue > 0 ? 'text-amber-700' : 'text-slate-400' }}">৳{{ number_format($remainingDue, 2) }}</span>
                        </div>
                    </div>

                    {{-- Due Warning --}}
                    @if($remainingDue > 0)
                        <div class="bg-amber-50 border border-amber-300 p-3 rounded-lg text-xs text-amber-900 flex items-center justify-between">
                            <div>
                                <span class="font-bold">⚠️ বকেয়া বিক্রয় (Credit Sale):</span> 
                                <span class="font-mono font-bold text-amber-800">৳{{ number_format($remainingDue, 2) }}</span>
                                <p class="text-[11px] text-amber-700 mt-0.5">গ্রাহকের লেজার অ্যাকাউন্টে এই বকেয়া যোগ হবে। (গ্রাহক সিলেক্ট থাকা আবশ্যক)</p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Modal Footer Actions --}}
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end space-x-3">
                    <button wire:click="$set('showPaymentModal', false)" type="button" class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition cursor-pointer">
                        Cancel
                    </button>
                    <button 
                        wire:click="processCheckout" 
                        type="button" 
                        class="px-6 py-2.5 text-white rounded-lg text-xs font-bold shadow-md transition flex items-center gap-1.5 cursor-pointer"
                        style="background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%) !important; color: #ffffff !important;"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>CONFIRM & PRINT INVOICE</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL 2: Generic Molecule Alternative Finder --}}
    @if($showSubstituteModal)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-2xl rounded-lg shadow-2xl border border-slate-200 overflow-hidden">
                <div class="bg-sky-800 px-6 py-3.5 text-white flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                            Alternative Generic Substitutes
                        </h2>
                        <p class="text-xs text-sky-200">Generic Molecule: <span class="font-bold text-white">{{ $selectedGenericName }}</span></p>
                    </div>
                    <button wire:click="$set('showSubstituteModal', false)" class="text-white hover:text-sky-200 text-xl font-bold">&times;</button>
                </div>

                <div class="p-5 max-h-96 overflow-y-auto divide-y divide-slate-100">
                    @forelse($substituteProducts as $sub)
                        <div class="py-3 flex items-center justify-between hover:bg-slate-50 px-2 rounded-lg transition">
                            <div>
                                <h4 class="font-bold text-slate-800 text-xs">{{ $sub['name'] }}</h4>
                                <p class="text-[11px] text-slate-500">{{ $sub['manufacturer'] }}</p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="font-semibold text-xs {{ $sub['stock_pieces'] > 0 ? 'text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded' : 'text-rose-600' }}">
                                    {{ $sub['stock_pieces'] }} {{ $sub['base_unit'] }}(s) available
                                </span>
                                @if($sub['stock_pieces'] > 0)
                                    <button 
                                        wire:click="selectSubstitute({{ $sub['id'] }})" 
                                        class="px-3 py-1 bg-sky-600 hover:bg-sky-700 text-white rounded text-xs font-bold transition"
                                    >
                                        + Add to Cart
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-slate-400 text-xs">No in-stock alternatives found for this molecule.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL 3: Quick Add Customer --}}
    @if($showCustomerModal)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-md rounded-lg shadow-2xl border border-slate-200 overflow-hidden">
                <div class="bg-sky-800 px-5 py-3.5 text-white flex items-center justify-between">
                    <h3 class="text-sm font-bold">Quick Add Customer</h3>
                    <button wire:click="$set('showCustomerModal', false)" class="text-white font-bold">&times;</button>
                </div>
                <div class="p-5 space-y-3 text-xs">
                    <div>
                        <label class="font-bold text-slate-700">Full Name *</label>
                        <input wire:model="newCustomerName" type="text" class="w-full py-1.5 px-2 mt-1 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500 font-medium">
                        @error('newCustomerName') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="font-bold text-slate-700">Mobile Phone *</label>
                        <input wire:model="newCustomerPhone" type="text" placeholder="017xxxxxxxx" class="w-full py-1.5 px-2 mt-1 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500 font-mono">
                        @error('newCustomerPhone') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="font-bold text-slate-700">Address / City</label>
                        <input wire:model="newCustomerAddress" type="text" class="w-full py-1.5 px-2 mt-1 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500">
                    </div>
                </div>
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end space-x-2">
                    <button wire:click="$set('showCustomerModal', false)" class="px-3 py-1.5 bg-slate-200 text-slate-700 rounded-lg text-xs font-bold">Cancel</button>
                    <button wire:click="saveNewCustomer" class="px-4 py-1.5 bg-sky-700 hover:bg-sky-800 text-white rounded-lg text-xs font-bold">Save & Select</button>
                </div>
            </div>
        </div>
    @endif

    {{-- DRAWER: Held / Suspended Carts --}}
    @if($showHeldCartsDrawer)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex justify-end">
            <div class="bg-white w-full max-w-sm h-full shadow-2xl p-4 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                        <h3 class="font-bold text-slate-800 text-sm">Suspended / Held Orders</h3>
                        <button wire:click="$set('showHeldCartsDrawer', false)" class="text-slate-500 hover:text-slate-800 text-xl font-bold">&times;</button>
                    </div>
                    <div class="mt-4 space-y-2.5 overflow-y-auto max-h-[80vh]">
                        @forelse($heldCarts as $idx => $hc)
                            <div class="p-3 bg-slate-50 hover:bg-sky-50 border border-slate-200 rounded-lg transition flex items-center justify-between">
                                <div>
                                    <div class="font-mono font-bold text-xs text-sky-800">{{ $hc['token'] }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $hc['time'] }} &bull; {{ $hc['item_count'] }} items</div>
                                    <div class="font-mono font-bold text-slate-800 text-xs mt-1">৳{{ number_format($hc['grand_total'], 2) }}</div>
                                </div>
                                <button 
                                    wire:click="recallHeldCart({{ $idx }})" 
                                    class="px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-lg transition shadow-sm"
                                >
                                    Recall
                                </button>
                            </div>
                        @empty
                            <div class="text-center py-12 text-slate-400 text-xs">No orders currently on hold.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL 4: Thermal Receipt Viewer (58mm/80mm ESC/POS) --}}
    @if($showReceiptModal && $lastSale)
        <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-2xl border border-slate-200 max-w-sm w-full overflow-hidden">
                <div class="p-3 bg-slate-100 border-b border-slate-200 flex items-center justify-between">
                    <span class="font-bold text-xs text-slate-700">Receipt Ready</span>
                    <button wire:click="$set('showReceiptModal', false)" class="text-slate-500 hover:text-slate-800 text-lg font-bold">&times;</button>
                </div>

                {{-- Printable Receipt Content --}}
                <div id="thermal-receipt" class="p-4 text-slate-900 font-mono text-[11px] leading-tight space-y-2 bg-white max-h-96 overflow-y-auto">
                    <div class="text-center space-y-0.5 border-b border-dashed border-slate-300 pb-2">
                        <h2 class="text-sm font-black uppercase">{{ $lastSale->branch?->name ?? 'EXPERT PHARMACY' }}</h2>
                        <p class="text-[10px]">{{ $lastSale->branch?->address ?? 'Dhaka, Bangladesh' }}</p>
                        <p class="text-[10px]">Tel: {{ $lastSale->branch?->phone ?? '01700-000000' }}</p>
                    </div>

                    <div class="border-b border-dashed border-slate-300 py-1 space-y-0.5 text-[10px]">
                        <div>Invoice: <strong>{{ $lastSale->invoice_no }}</strong></div>
                        <div>Date: {{ $lastSale->sale_date->format('d/m/Y h:i A') }}</div>
                        <div>Cashier: {{ $lastSale->user?->name ?? 'Admin' }}</div>
                        @if($lastSale->customer)
                            <div>Customer: {{ $lastSale->customer->name }} ({{ $lastSale->customer->phone }})</div>
                        @endif
                        @if($lastSale->doctor)
                            <div>Prescribed By: {{ $lastSale->doctor->name }}</div>
                        @endif
                    </div>

                    <div class="border-b border-dashed border-slate-300 py-1">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-slate-200 text-[10px]">
                                    <th class="py-1">Item</th>
                                    <th class="py-1 text-center">Qty</th>
                                    <th class="py-1 text-right">Price</th>
                                    <th class="py-1 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lastSale->items as $sItem)
                                    <tr>
                                        <td class="py-1">{{ $sItem->product->name }} ({{ $sItem->unit?->name ?? 'pcs' }})</td>
                                        <td class="py-1 text-center">{{ $sItem->quantity }}</td>
                                        <td class="py-1 text-right">{{ number_format($sItem->unit_price, 1) }}</td>
                                        <td class="py-1 text-right font-bold">{{ number_format($sItem->total_amount, 1) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="space-y-0.5 text-right font-mono text-[10px] pt-1 border-b border-dashed border-slate-300 pb-2">
                        <div>Subtotal: ৳{{ number_format($lastSale->sub_total, 2) }}</div>
                        @if($lastSale->discount_amount > 0)
                            <div>Discount: -৳{{ number_format($lastSale->discount_amount, 2) }}</div>
                        @endif
                        @if($lastSale->tax_amount > 0)
                            <div>VAT/Tax: +৳{{ number_format($lastSale->tax_amount, 2) }}</div>
                        @endif
                        <div class="text-xs font-bold text-slate-900 pt-1">Grand Total: ৳{{ number_format($lastSale->grand_total, 2) }}</div>
                        <div>Paid Amount: ৳{{ number_format($lastSale->paid_amount, 2) }}</div>
                        @if($lastSale->due_amount > 0)
                            <div class="text-rose-600 font-bold">Due Credit: ৳{{ number_format($lastSale->due_amount, 2) }}</div>
                        @endif
                        @if($lastSale->change_amount > 0)
                            <div>Change: ৳{{ number_format($lastSale->change_amount, 2) }}</div>
                        @endif
                    </div>

                    <div class="text-center text-[9px] text-slate-500 pt-1">
                        <p>Thank you for choosing us!</p>
                        <p>Goods once sold can be returned within 7 days with invoice.</p>
                    </div>
                </div>

                <div class="p-3 bg-slate-50 border-t border-slate-200 flex justify-end space-x-2">
                    <button wire:click="$set('showReceiptModal', false)" class="px-3 py-1.5 bg-slate-200 text-slate-700 rounded-lg text-xs font-bold">Close</button>
                    <button onclick="window.print()" class="px-4 py-1.5 bg-sky-700 hover:bg-sky-800 text-white rounded-lg text-xs font-bold flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        <span>Print Receipt</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
