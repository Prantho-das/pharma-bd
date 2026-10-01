<div class="p-6 space-y-6 max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Create Purchase Order (PO)') }}</h1>
            <p class="text-xs text-slate-500 mt-1">{{ __('Place procurement demand orders to suppliers before final inventory reception.') }}</p>
        </div>
        <a href="{{ route('purchase-orders.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1.5 self-start sm:self-auto" wire:navigate>
            &larr; {{ __('All Purchase Orders') }}
        </a>
    </div>

    {{-- System Alerts --}}
    @if($successMessage)
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-lg flex items-center justify-between shadow-xs">
            <span>{{ $successMessage }}</span>
            <button wire:click="$set('successMessage', null)" class="text-emerald-600 hover:text-emerald-900">&times;</button>
        </div>
    @endif
    @if($errorMessage)
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold rounded-lg flex items-center justify-between shadow-xs">
            <span>{{ $errorMessage }}</span>
            <button wire:click="$set('errorMessage', null)" class="text-rose-600 hover:text-rose-900">&times;</button>
        </div>
    @endif

    <form wire:submit.prevent="savePurchaseOrder">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Form: Supplier & Items (Left 2 Columns) --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Supplier & Order Details Card --}}
                <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-xs space-y-4">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-500">{{ __('Supplier & PO Information') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                        <div class="md:col-span-2">
                            <label class="font-bold text-slate-700 block mb-1">{{ __('Supplier / Distributor') }} *</label>
                            <select wire:model="supplier_id" class="w-full py-2 px-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-medium">
                                <option value="">-- {{ __('Choose Supplier') }} --</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->company_name ?: $s->phone }})</option>
                                @endforeach
                            </select>
                            @error('supplier_id') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="font-bold text-slate-700 block mb-1">{{ __('Order Status') }}</label>
                            <select wire:model="status" class="w-full py-2 px-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-bold text-indigo-700">
                                <option value="ORDERED">{{ __('ORDERED') }}</option>
                                <option value="DRAFT">{{ __('DRAFT') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="font-bold text-slate-700 block mb-1">{{ __('PO Order Date') }} *</label>
                            <input wire:model="order_date" type="date" class="w-full py-2 px-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-medium">
                            @error('order_date') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="font-bold text-slate-700 block mb-1">{{ __('Expected Delivery Date') }}</label>
                            <input wire:model="expected_delivery_date" type="date" class="w-full py-2 px-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-medium">
                        </div>
                        <div>
                            <label class="font-bold text-slate-700 block mb-1">{{ __('PO Notes / Remarks') }}</label>
                            <input wire:model="notes" type="text" placeholder="{{ __('Urgent replenishment') }}..." class="w-full py-2 px-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                {{-- Line Items Table Card --}}
                <div class="bg-white rounded-lg border border-slate-200 shadow-xs overflow-hidden">
                    <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <h3 class="text-xs font-black uppercase tracking-wider text-slate-600">{{ __('Ordered Medicines / Products') }}</h3>
                        <button wire:click="addItemRow" type="button" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition flex items-center gap-1 cursor-pointer">
                            <span>+ {{ __('Add Medicine Row') }}</span>
                        </button>
                    </div>

                    <div class="overflow-x-auto p-4 space-y-3">
                        @foreach($items as $i => $item)
                            <div class="p-3 bg-slate-50/70 border border-slate-200 rounded-lg space-y-3">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
                                    {{-- Product Selection --}}
                                    <div class="md:col-span-2">
                                        <label class="font-bold text-slate-700 block mb-1">{{ __('Medicine') }} *</label>
                                        <select 
                                            wire:change="productSelected({{ $i }}, $event.target.value)"
                                            class="w-full py-1.5 px-2.5 bg-white border border-slate-300 rounded-lg font-medium"
                                        >
                                            <option value="">-- {{ __('Choose Medicine') }} --</option>
                                            @foreach($products as $prod)
                                                <option value="{{ $prod->id }}" {{ ($item['product_id'] ?? null) == $prod->id ? 'selected' : '' }}>
                                                    {{ $prod->name }} (SKU: {{ $prod->sku }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("items.{$i}.product_id") <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- Unit Packaging --}}
                                    <div>
                                        <label class="font-bold text-slate-700 block mb-1">{{ __('Unit') }}</label>
                                        <select wire:model="items.{{ $i }}.unit_id" class="w-full py-1.5 px-2 bg-white border border-slate-300 rounded-lg">
                                            <option value="">-- {{ __('Default') }} --</option>
                                            @foreach($units as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Remove Button --}}
                                    <div class="flex items-end justify-end">
                                        @if(count($items) > 1)
                                            <button wire:click="removeItemRow({{ $i }})" type="button" class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg text-xs font-bold transition">
                                                🗑 {{ __('Remove') }}
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                {{-- Quantity & Rates --}}
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs pt-2 border-t border-slate-200">
                                    <div>
                                        <label class="font-bold text-slate-700 block mb-1">{{ __('Ordered Qty') }} *</label>
                                        <input 
                                            wire:model.live.debounce.300ms="items.{{ $i }}.quantity_ordered"
                                            wire:change="recalculateRow({{ $i }})"
                                            type="number" step="any" min="0.01" class="w-full py-1 px-2 border border-slate-300 rounded bg-white font-black text-indigo-700 focus:ring-1 focus:ring-indigo-500"
                                        >
                                        @error("items.{$i}.quantity_ordered") <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="font-bold text-slate-700 block mb-1">{{ __('Unit Purchase Cost (৳)') }}</label>
                                        <input 
                                            wire:model.live.debounce.300ms="items.{{ $i }}.unit_price"
                                            wire:change="recalculateRow({{ $i }})"
                                            type="number" step="0.01" min="0" class="w-full py-1 px-2 border border-slate-300 rounded bg-white font-mono"
                                        >
                                    </div>
                                    <div>
                                        <label class="font-bold text-slate-700 block mb-1">{{ __('MRP / Sales Price (৳)') }}</label>
                                        <input 
                                            wire:model="items.{{ $i }}.mrp_per_unit"
                                            type="number" step="0.01" min="0" class="w-full py-1 px-2 border border-slate-300 rounded bg-white font-mono"
                                        >
                                    </div>
                                    <div class="bg-indigo-50/50 p-2 rounded border border-indigo-100">
                                        <span class="text-[10px] font-bold text-indigo-600 uppercase block">{{ __('Line Cost') }}</span>
                                        <span class="text-sm font-black text-indigo-950 font-mono">৳{{ number_format($item['total_cost'] ?? 0, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Summary & Submission (Right Column) --}}
            <div class="space-y-6">
                <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-xs space-y-4">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-500">{{ __('Purchase Order Summary') }}</h3>

                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between items-center py-1 border-b border-slate-100 text-slate-600">
                            <span>{{ __('Total Line Items') }}:</span>
                            <span class="font-bold text-slate-800">{{ count($items) }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1 border-b border-slate-100 text-slate-600">
                            <span>{{ __('Sub-Total') }}:</span>
                            <span class="font-mono font-bold text-slate-800">৳{{ number_format($this->subTotal, 2) }}</span>
                        </div>

                        {{-- Discount --}}
                        <div class="space-y-1">
                            <label class="font-bold text-slate-700 flex justify-between">
                                <span>{{ __('Estimated Discount (৳)') }}</span>
                                <span class="font-mono text-slate-500">-৳{{ number_format($discount, 2) }}</span>
                            </label>
                            <input wire:model.live.debounce.300ms="discount" type="number" step="0.01" min="0" class="w-full py-1 px-2 border border-slate-300 rounded font-mono">
                        </div>

                        {{-- Tax --}}
                        <div class="space-y-1">
                            <label class="font-bold text-slate-700 flex justify-between">
                                <span>{{ __('Estimated Tax / VAT (৳)') }}</span>
                                <span class="font-mono text-slate-500">+৳{{ number_format($tax_amount, 2) }}</span>
                            </label>
                            <input wire:model.live.debounce.300ms="tax_amount" type="number" step="0.01" min="0" class="w-full py-1 px-2 border border-slate-300 rounded font-mono">
                        </div>

                        {{-- Grand Total --}}
                        <div class="p-3 bg-indigo-50 border border-indigo-200 rounded-lg flex justify-between items-center">
                            <span class="text-xs font-black text-indigo-900 uppercase">{{ __('PO Grand Total') }}</span>
                            <span class="text-lg font-black text-indigo-900 font-mono">৳{{ number_format($this->grandTotal, 2) }}</span>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="pt-4 border-t border-slate-200 space-y-2">
                        <button type="submit" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-lg text-xs font-black shadow-xs transition cursor-pointer flex justify-center items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>{{ __('Confirm & Issue Purchase Order') }}</span>
                        </button>
                        <a href="{{ route('purchase-orders.index') }}" class="block text-center w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs font-bold transition">
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
