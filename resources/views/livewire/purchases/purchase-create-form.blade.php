<div class="p-6 space-y-6 max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Receive Goods Note (GRN Purchase)') }}</h1>
            <p class="text-xs text-slate-500 mt-1">{{ __('Receive procurement orders, calculate bonus trade schemes, and generate FEFO inventory batches.') }}</p>
        </div>
        <a href="{{ route('purchases.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1.5 self-start sm:self-auto" wire:navigate>
            &larr; {{ __('View All Invoices') }}
        </a>
    </div>

    {{-- PO Source Banner --}}
    @if($sourcePo)
        <div class="p-4 bg-indigo-50 border border-indigo-200 text-indigo-900 rounded-xl flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 animate-ping"></span>
                <div>
                    <h4 class="text-xs font-black">{{ __('Converting from Purchase Order') }}: <span class="font-mono text-indigo-700 font-bold">{{ $sourcePo->po_number }}</span></h4>
                    <p class="text-[11px] text-indigo-600">{{ __('Supplier and ordered line items are auto-populated. Fill batch numbers & expiry dates to receive goods into stock.') }}</p>
                </div>
            </div>
            <a href="{{ route('purchase-orders.index') }}" class="px-2.5 py-1 bg-white border border-indigo-200 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-bold transition" wire:navigate>
                {{ __('Back to PO List') }}
            </a>
        </div>
    @endif

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

    <form wire:submit.prevent="savePurchase">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Form: Supplier & Items (Left 2 Columns) --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Supplier & Invoice Details Card --}}
                <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-xs space-y-4">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-500">{{ __('Supplier & Invoice Information') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                        <div class="md:col-span-2">
                            <div class="flex justify-between items-center mb-1">
                                <label class="font-bold text-slate-700">{{ __('Supplier / Distributor') }} *</label>
                                <button wire:click="$set('showSupplierModal', true)" type="button" class="text-[11px] text-sky-700 font-bold hover:underline">+ {{ __('New Supplier') }}</button>
                            </div>
                            <select wire:model="supplier_id" class="w-full py-2 px-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500 font-medium">
                                <option value="">-- {{ __('Choose Supplier') }} --</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->company_name ?: $s->phone }}) - {{ __('Due') }}: ৳{{ number_format($s->current_due, 2) }}</option>
                                @endforeach
                            </select>
                            @error('supplier_id') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="font-bold text-slate-700 block mb-1">{{ __('Supplier Challan / Inv #') }}</label>
                            <input wire:model="supplier_invoice_no" type="text" placeholder="CH-98124" class="w-full py-2 px-3 border border-slate-300 rounded-lg font-mono focus:ring-2 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="font-bold text-slate-700 block mb-1">{{ __('Invoice Date') }}</label>
                            <input wire:model="invoice_date" type="date" class="w-full py-2 px-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="font-bold text-slate-700 block mb-1">{{ __('Delivery / Receiving Date') }}</label>
                            <input wire:model="delivery_date" type="date" class="w-full py-2 px-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500">
                        </div>
                    </div>
                </div>

                {{-- Line Items Table Card --}}
                <div class="bg-white rounded-lg border border-slate-200 shadow-xs overflow-hidden">
                    <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <h3 class="text-xs font-black uppercase tracking-wider text-slate-600">{{ __('Procured Medicine Items') }}</h3>
                        <button wire:click="addItemRow" type="button" class="px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-bold transition flex items-center gap-1 cursor-pointer">
                            <span>+ {{ __('Add Medicine Row') }}</span>
                        </button>
                    </div>

                    <div class="overflow-x-auto p-4 space-y-3">
                        @foreach($items as $i => $item)
                            <div class="p-3 bg-slate-50/70 border border-slate-200 rounded-lg space-y-3">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
                                    {{-- Product Selection --}}
                                    <div class="md:col-span-2">
                                        <div class="flex items-center justify-between mb-1">
                                            <label class="font-bold text-slate-700 block">{{ __('Medicine') }} *</label>
                                            @if(isset($item['has_batch_tracking']) && !$item['has_batch_tracking'])
                                                <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">{{ __('Non-Batch Item') }}</span>
                                            @endif
                                        </div>
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
                                    </div>

                                    {{-- Unit Packaging --}}
                                    <div>
                                        <label class="font-bold text-slate-700 block mb-1">{{ __('Packaging Unit') }}</label>
                                        <select wire:model="items.{{ $i }}.unit_id" class="w-full py-1.5 px-2 bg-white border border-slate-300 rounded-lg">
                                            @foreach($units as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Batch Number --}}
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label class="font-bold text-slate-700 block">{{ __('Batch Number') }}</label>
                                            @if(!($item['has_batch_tracking'] ?? true))
                                                <span class="text-[9px] font-bold text-amber-600">{{ __('Auto Default') }}</span>
                                            @endif
                                        </div>
                                        <input wire:model="items.{{ $i }}.batch_number" type="text" placeholder="{{ !($item['has_batch_tracking'] ?? true) ? 'DEFAULT' : 'B-...' }}" class="w-full py-1.5 px-2 bg-white border border-slate-300 rounded-lg font-mono uppercase font-bold text-xs">
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-xs items-center">
                                    {{-- Expiry Date --}}
                                    <div>
                                        <label class="font-bold text-slate-700 block mb-1">
                                            {{ __('Expiry Date') }}
                                            @if($item['has_batch_tracking'] ?? true)
                                                <span class="text-rose-500">*</span>
                                            @else
                                                <span class="text-[10px] text-slate-400 font-normal">({{ __('Optional') ?? 'Optional' }})</span>
                                            @endif
                                        </label>
                                        <input wire:model="items.{{ $i }}.expiry_date" type="date" class="w-full py-1.5 px-2 bg-white border border-slate-300 rounded-lg font-medium text-xs">
                                    </div>

                                    {{-- Billed Qty --}}
                                    <div>
                                        <label class="font-bold text-slate-700 block mb-1">{{ __('Billed Qty') }} *</label>
                                        <input wire:model="items.{{ $i }}.quantity" wire:keyup="recalculateRow({{ $i }})" type="number" min="1" class="w-full py-1.5 px-2 bg-white border border-slate-300 rounded-lg font-bold font-mono">
                                    </div>

                                    {{-- Bonus Qty Scheme --}}
                                    <div>
                                        <label class="font-bold text-emerald-700 block mb-1">{{ __('Bonus / Free Qty') }}</label>
                                        <input wire:model="items.{{ $i }}.bonus_quantity" wire:keyup="recalculateRow({{ $i }})" type="number" min="0" class="w-full py-1.5 px-2 bg-emerald-50 border border-emerald-300 rounded-lg font-bold font-mono">
                                    </div>

                                    {{-- Purchase Price --}}
                                    <div>
                                        <label class="font-bold text-slate-700 block mb-1">{{ __('Unit Buy Rate (৳)') }}</label>
                                        <input wire:model="items.{{ $i }}.purchase_price_per_unit" wire:keyup="recalculateRow({{ $i }})" type="number" min="0" step="0.01" class="w-full py-1.5 px-2 bg-white border border-slate-300 rounded-lg font-mono">
                                    </div>

                                    {{-- MRP Price --}}
                                    <div>
                                        <label class="font-bold text-slate-700 block mb-1">{{ __('Unit MRP (৳)') }}</label>
                                        <input wire:model="items.{{ $i }}.mrp_per_unit" type="number" min="0" step="0.01" class="w-full py-1.5 px-2 bg-white border border-slate-300 rounded-lg font-mono">
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-1 border-t border-slate-200 text-xs">
                                    <div class="font-mono font-bold text-slate-700">
                                        {{ __('Line Total') }}: <span class="text-sky-800 text-sm">৳{{ number_format($item['total_cost'], 2) }}</span>
                                    </div>
                                    <button wire:click="removeItemRow({{ $i }})" type="button" class="text-rose-500 hover:text-rose-700 font-bold cursor-pointer">{{ __('Remove Row') }}</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Right 1 Column: Calculation & Payment --}}
            <div class="space-y-4">
                <div class="bg-white rounded-lg border border-slate-200 shadow-xs p-5 space-y-4">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-600 border-b border-slate-200 pb-2">
                        {{ __('Summary & Settlement') }}
                    </h3>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600">{{ __('Subtotal') }}</span>
                            <span class="font-bold text-slate-800 font-mono">৳{{ number_format($this->subTotal, 2) }}</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-slate-600">{{ __('Discount') }} (৳)</span>
                            <input wire:model.live.debounce.300ms="discount" type="number" min="0" class="w-24 text-right py-1 px-2 border border-slate-300 rounded-lg font-mono">
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-slate-600">{{ __('Tax') }} / {{ __('VAT') }} (৳)</span>
                            <input wire:model.live.debounce.300ms="tax_amount" type="number" min="0" class="w-24 text-right py-1 px-2 border border-slate-300 rounded-lg font-mono">
                        </div>

                        <div class="pt-3 border-t border-slate-200 flex justify-between items-center">
                            <span class="font-extrabold text-sm text-slate-800">{{ __('Grand Total') }}</span>
                            <span class="font-black text-xl text-sky-800 font-mono">৳{{ number_format($this->grandTotal, 2) }}</span>
                        </div>

                        <div class="pt-3 border-t border-slate-200 flex justify-between items-center">
                            <span class="font-bold text-slate-700">{{ __('Paid Amount') }} (৳)</span>
                            <input wire:model.live.debounce.300ms="paid_amount" type="number" min="0" class="w-28 text-right py-1.5 px-2 bg-sky-50 border border-sky-300 rounded-lg font-bold font-mono text-sky-900">
                        </div>

                        @if($this->paid_amount > 0)
                            <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-200 space-y-2">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 mb-1">{{ __('Payment Method') }}</label>
                                    <select wire:model="payment_method" class="w-full text-xs font-bold py-1.5 px-2 bg-white border border-slate-300 rounded-lg">
                                        <option value="CASH">{{ __('Cash') }}</option>
                                        <option value="BANK_TRANSFER">{{ __('Bank') }}</option>
                                        <option value="CHEQUE">{{ __('Cheque') }}</option>
                                        <option value="BKASH">bKash (বিকাশ)</option>
                                        <option value="NAGAD">Nagad (নগদ)</option>
                                        <option value="ROCKET">Rocket (রকেট)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 mb-1">{{ __('Txn / Cheque Ref #') }}</label>
                                    <input wire:model="transaction_reference" type="text" placeholder="e.g. Cheque # / Txn ID" class="w-full text-xs py-1 px-2 bg-white border border-slate-300 rounded-lg">
                                </div>
                            </div>
                        @endif

                        @if($this->dueAmount > 0)
                            <div class="bg-amber-50 p-2.5 rounded-lg border border-amber-200 flex justify-between items-center text-amber-900 font-bold">
                                <span>{{ __('Payable Due') }}:</span>
                                <span class="font-mono text-sm">৳{{ number_format($this->dueAmount, 2) }}</span>
                            </div>
                        @endif

                        <div>
                            <label class="font-bold text-slate-700 block mb-1">{{ __('Notes / Remarks') }}</label>
                            <textarea wire:model="notes" rows="2" class="w-full py-1.5 px-2 border border-slate-300 rounded-lg text-xs"></textarea>
                        </div>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full py-3.5 px-4 bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-700 hover:to-blue-800 text-white rounded-lg font-bold text-sm shadow-md hover:shadow-lg transition cursor-pointer"
                    >
                        {{ __('Save GRN & Generate Batches') }}
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- Quick Add Supplier Modal --}}
    @if($showSupplierModal)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-md rounded-lg shadow-2xl border border-slate-200 overflow-hidden">
                <div class="bg-sky-800 px-5 py-3.5 text-white flex items-center justify-between">
                    <h3 class="text-sm font-bold">{{ __('Add Supplier / Distributor') }}</h3>
                    <button wire:click="$set('showSupplierModal', false)" class="text-white font-bold">&times;</button>
                </div>
                <div class="p-5 space-y-3 text-xs">
                    <div>
                        <label class="font-bold text-slate-700">{{ __('Contact Person Name') }} *</label>
                        <input wire:model="newSupplierName" type="text" class="w-full py-1.5 px-2 mt-1 border border-slate-300 rounded-lg font-medium">
                        @error('newSupplierName') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="font-bold text-slate-700">{{ __('Company Name') }}</label>
                        <input wire:model="newSupplierCompany" type="text" placeholder="e.g. Square Pharmaceuticals Ltd." class="w-full py-1.5 px-2 mt-1 border border-slate-300 rounded-lg font-medium">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700">{{ __('Phone') }} *</label>
                        <input wire:model="newSupplierPhone" type="text" class="w-full py-1.5 px-2 mt-1 border border-slate-300 rounded-lg font-mono">
                        @error('newSupplierPhone') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end space-x-2">
                    <button wire:click="$set('showSupplierModal', false)" class="px-3 py-1.5 bg-slate-200 text-slate-700 rounded-lg text-xs font-bold">{{ __('Cancel') }}</button>
                    <button wire:click="saveSupplier" class="px-4 py-1.5 bg-sky-700 hover:bg-sky-800 text-white rounded-lg text-xs font-bold">{{ __('Save Supplier') }}</button>
                </div>
            </div>
        </div>
    @endif
</div>
