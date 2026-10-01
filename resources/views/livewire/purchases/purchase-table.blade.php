<div class="p-6 space-y-6 max-w-7xl mx-auto">
    {{-- Top Header Banner --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-sky-500 animate-pulse"></span>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Procurement & Purchase Invoices') }}</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                {{ __('Goods Received Notes (GRN), Supplier Bills, Split Payments, and Aging Tracking.') }}
            </p>
        </div>

        @can('create_purchases')
            <a href="{{ route('purchases.create') }}" class="px-4 py-2.5 bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-700 hover:to-blue-800 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition flex items-center gap-2 self-start sm:self-auto cursor-pointer" wire:navigate>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>+ {{ __('New GRN Purchase') }}</span>
            </a>
        @endcan
    </div>

    {{-- Feedback Alerts --}}
    @if ($feedbackMessage)
        <div x-data="{ show: true }" x-show="show" class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold text-sm">✓</div>
                <p class="text-xs font-bold text-emerald-900">{{ $feedbackMessage }}</p>
            </div>
            <button @click="show = false" type="button" class="text-xs font-bold text-emerald-700 hover:text-emerald-900">✕ {{ __('Close') }}</button>
        </div>
    @endif

    @if ($errorMessage)
        <div x-data="{ show: true }" x-show="show" class="p-4 rounded-xl bg-rose-50 border border-rose-200 flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-rose-600 text-white flex items-center justify-center font-bold text-sm">✕</div>
                <p class="text-xs font-bold text-rose-900">{{ $errorMessage }}</p>
            </div>
            <button @click="show = false" type="button" class="text-xs font-bold text-rose-700 hover:text-rose-900">✕ {{ __('Close') }}</button>
        </div>
    @endif

    {{-- Filter Bar --}}
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <div>
            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">{{ __('Search PO / Challan') }}</label>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('PO #, Supplier, Challan...') }}" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500/20">
        </div>
        <div>
            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">{{ __('Supplier') }}</label>
            <select wire:model.live="selectedSupplier" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500/20">
                <option value="">{{ __('All Suppliers') }}</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->company_name }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">{{ __('Payment Status') }}</label>
            <select wire:model.live="paymentStatus" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500/20">
                <option value="all">{{ __('All Payment Status') }}</option>
                <option value="PAID">{{ __('Paid') }}</option>
                <option value="PARTIAL">{{ __('Partial') }}</option>
                <option value="DUE">{{ __('Due') }}</option>
            </select>
        </div>
        <div>
            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">{{ __('Bill Aging') }}</label>
            <select wire:model.live="agingFilter" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500/20">
                <option value="all">{{ __('All Aging Periods') }}</option>
                <option value="0-30">🟢 0 - 30 Days (Current)</option>
                <option value="31-60">🟡 31 - 60 Days</option>
                <option value="61-90">🟠 61 - 90 Days</option>
                <option value="90+">🔴 90+ Days (Overdue)</option>
            </select>
        </div>
    </div>

    {{-- Purchases Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                    <tr>
                        <th class="py-3.5 px-4">{{ __('PO Number') }}</th>
                        <th class="py-3.5 px-3">{{ __('Supplier') }}</th>
                        <th class="py-3.5 px-3">{{ __('Invoice Date') }}</th>
                        <th class="py-3.5 px-3">{{ __('Bill Aging') }}</th>
                        <th class="py-3.5 px-3 text-right">{{ __('Grand Total') }}</th>
                        <th class="py-3.5 px-3 text-right">{{ __('Paid') }}</th>
                        <th class="py-3.5 px-3 text-right">{{ __('Due') }}</th>
                        <th class="py-3.5 px-3 text-center">{{ __('Status') }}</th>
                        <th class="py-3.5 px-4 text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($purchases as $po)
                        @php
                            $agingDays = $po->aging_days;
                            $agingBucket = $po->aging_bucket;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            {{-- PO Number & Challan --}}
                            <td class="py-3.5 px-4 font-mono font-bold text-sky-800">
                                <div class="flex items-center gap-1.5">
                                    <span>{{ $po->purchase_no }}</span>
                                </div>
                                @if($po->supplier_invoice_no)
                                    <div class="text-[10px] text-slate-400 font-sans font-normal">Challan: {{ $po->supplier_invoice_no }}</div>
                                @endif
                            </td>

                            {{-- Supplier Info --}}
                            <td class="py-3.5 px-3">
                                <div class="font-bold text-slate-900">{{ $po->supplier?->name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $po->supplier?->company_name }}</div>
                            </td>

                            {{-- Invoice Date --}}
                            <td class="py-3.5 px-3 text-slate-700 whitespace-nowrap">
                                {{ $po->invoice_date?->format('d M, Y') ?? 'N/A' }}
                            </td>

                            {{-- Aging Column --}}
                            <td class="py-3.5 px-3 whitespace-nowrap">
                                @if($agingDays <= 30)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        {{ $agingDays }}d (0-30d)
                                    </span>
                                @elseif($agingDays <= 60)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        {{ $agingDays }}d (31-60d)
                                    </span>
                                @elseif($agingDays <= 90)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-orange-50 text-orange-700 border border-orange-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                                        {{ $agingDays }}d (61-90d)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                        {{ $agingDays }}d (Overdue)
                                    </span>
                                @endif
                            </td>

                            {{-- Financials --}}
                            <td class="py-3.5 px-3 text-right font-mono font-bold text-slate-900 whitespace-nowrap">
                                ৳{{ number_format($po->grand_total, 2) }}
                            </td>
                            <td class="py-3.5 px-3 text-right font-mono font-bold text-emerald-700 whitespace-nowrap">
                                ৳{{ number_format($po->paid_amount, 2) }}
                                @if($po->payments->count() > 1)
                                    <div class="text-[9px] text-emerald-600 font-sans font-normal">({{ $po->payments->count() }} split payments)</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-3 text-right font-mono font-bold text-rose-600 whitespace-nowrap">
                                ৳{{ number_format($po->due_amount, 2) }}
                            </td>

                            {{-- Payment Status Badge --}}
                            <td class="py-3.5 px-3 text-center whitespace-nowrap">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-black {{ $po->payment_status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : ($po->payment_status === 'PARTIAL' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ $po->payment_status }}
                                </span>
                            </td>

                            {{-- Action Buttons with Permissions --}}
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    {{-- 1. View / Print Invoice --}}
                                    @can('view_purchases')
                                        <button 
                                            wire:click="viewInvoice({{ $po->id }})" 
                                            type="button" 
                                            class="p-1.5 rounded-lg bg-sky-50 text-sky-700 hover:bg-sky-100 transition cursor-pointer"
                                            title="View Invoice & GRN Details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                    @endcan

                                    {{-- 2. Add / Split Payment --}}
                                    @canany(['manage_purchase_payments', 'edit_purchases'])
                                        <button 
                                            wire:click="openPaymentModal({{ $po->id }})" 
                                            type="button" 
                                            class="p-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition cursor-pointer"
                                            title="Add Split / Installment Payment">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        </button>
                                    @endcanany

                                    {{-- 3. Return Purchase --}}
                                    @can('return_purchases')
                                        <a 
                                            href="{{ route('purchases.returns') }}" 
                                            wire:navigate
                                            class="p-1.5 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 transition cursor-pointer"
                                            title="Return Products to Supplier">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                        </a>
                                    @endcan

                                    {{-- 4. Edit Purchase --}}
                                    @can('edit_purchases')
                                        <button 
                                            wire:click="openEditModal({{ $po->id }})" 
                                            type="button" 
                                            class="p-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition cursor-pointer"
                                            title="Edit Purchase Information">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                    @endcan

                                    {{-- 5. Delete Purchase --}}
                                    @can('delete_purchases')
                                        <button 
                                            wire:click="confirmDelete({{ $po->id }})" 
                                            type="button" 
                                            class="p-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition cursor-pointer"
                                            title="Delete / Cancel Purchase Bill">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400 text-xs">
                                কোনো পারচেজ রেকর্ড পাওয়া যায়নি।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $purchases->links() }}
        </div>
    </div>

    {{-- =============================================================== --}}
    {{-- MODAL 1: VIEW INVOICE & GRN DETAILS                             --}}
    {{-- =============================================================== --}}
    @if ($showInvoiceModal && $viewingPurchase)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-3xl w-full p-6 shadow-2xl border border-slate-100 space-y-5 max-h-[90vh] overflow-y-auto animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-sky-600 text-white flex items-center justify-center font-bold text-xs">PO</div>
                        <div>
                            <h3 class="text-base font-black text-slate-900">Purchase Invoice #{{ $viewingPurchase->purchase_no }}</h3>
                            <p class="text-xs text-slate-500">Date: {{ $viewingPurchase->invoice_date?->format('d M, Y') }} | Challan: {{ $viewingPurchase->supplier_invoice_no ?: 'N/A' }}</p>
                        </div>
                    </div>
                    <button wire:click="$set('showInvoiceModal', false)" type="button" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
                </div>

                {{-- Supplier & Branch Details --}}
                <div class="grid grid-cols-2 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase">Supplier Details</div>
                        <div class="font-bold text-slate-900 text-sm mt-0.5">{{ $viewingPurchase->supplier?->name }}</div>
                        <div class="text-slate-600">{{ $viewingPurchase->supplier?->company_name }}</div>
                        <div class="text-slate-500 mt-1">Phone: {{ $viewingPurchase->supplier?->phone ?: 'N/A' }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] font-bold text-slate-400 uppercase">Status & Aging</div>
                        <div class="mt-1">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-black {{ $viewingPurchase->payment_status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $viewingPurchase->payment_status }}
                            </span>
                        </div>
                        <div class="text-xs font-bold text-slate-700 mt-1">Bill Aging: {{ $viewingPurchase->aging_days }} days ({{ $viewingPurchase->aging_bucket }})</div>
                    </div>
                </div>

                {{-- Items Table --}}
                <div class="rounded-xl border border-slate-200 overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                            <tr>
                                <th class="p-2.5">Medicine / Item</th>
                                <th class="p-2.5">Batch</th>
                                <th class="p-2.5">Expiry</th>
                                <th class="p-2.5 text-center">Qty</th>
                                <th class="p-2.5 text-right">Unit Buy</th>
                                <th class="p-2.5 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($viewingPurchase->items as $item)
                                <tr>
                                    <td class="p-2.5 font-bold text-slate-900">
                                        {{ $item->product?->name }}
                                        <span class="text-[10px] font-normal text-slate-500">({{ $item->unit?->name }})</span>
                                    </td>
                                    <td class="p-2.5 font-mono text-slate-700">{{ $item->batch_number }}</td>
                                    <td class="p-2.5 font-mono text-rose-600">{{ $item->expiry_date?->format('d M, Y') ?: 'N/A' }}</td>
                                    <td class="p-2.5 text-center font-bold font-mono">
                                        {{ $item->quantity }}
                                        @if($item->bonus_quantity > 0)
                                            <span class="text-emerald-600 text-[10px]">+{{ $item->bonus_quantity }} free</span>
                                        @endif
                                    </td>
                                    <td class="p-2.5 text-right font-mono">৳{{ number_format($item->purchase_price_per_unit, 2) }}</td>
                                    <td class="p-2.5 text-right font-mono font-bold text-slate-900">৳{{ number_format($item->total_cost, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Financials Summary --}}
                <div class="grid grid-cols-2 gap-4 pt-2">
                    <div class="space-y-2">
                        <div class="text-xs font-bold text-slate-700">Split Payment History ({{ $viewingPurchase->payments->count() }})</div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-1.5 max-h-32 overflow-y-auto">
                            @forelse ($viewingPurchase->payments as $pay)
                                <div class="flex items-center justify-between border-b border-slate-200/50 pb-1">
                                    <div>
                                        <span class="font-bold text-slate-800">৳{{ number_format($pay->amount, 2) }}</span>
                                        <span class="text-[10px] text-slate-500">via {{ $pay->payment_method }}</span>
                                        @if($pay->transaction_reference)
                                            <span class="text-[10px] text-sky-700 font-mono">({{ $pay->transaction_reference }})</span>
                                        @endif
                                    </div>
                                    <span class="text-[10px] text-slate-400">{{ $pay->payment_date?->format('d M, Y') }}</span>
                                </div>
                            @empty
                                <div class="text-slate-400 text-center py-2">No payment transactions recorded.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2 text-xs">
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Sub Total:</span>
                            <span class="font-mono font-bold">৳{{ number_format($viewingPurchase->sub_total, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Discount / Tax:</span>
                            <span class="font-mono">৳{{ number_format($viewingPurchase->discount, 2) }} / ৳{{ number_format($viewingPurchase->tax_amount, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-900 font-bold border-t border-slate-200 pt-1 text-sm">
                            <span>Grand Total:</span>
                            <span class="font-mono">৳{{ number_format($viewingPurchase->grand_total, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-emerald-700 font-bold">
                            <span>Paid Amount:</span>
                            <span class="font-mono">৳{{ number_format($viewingPurchase->paid_amount, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-rose-600 font-bold">
                            <span>Remaining Due:</span>
                            <span class="font-mono">৳{{ number_format($viewingPurchase->due_amount, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-3">
                    <button type="button" onclick="window.print()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center gap-1.5 cursor-pointer">
                        🖨️ Print Invoice
                    </button>
                    <button wire:click="$set('showInvoiceModal', false)" type="button" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs cursor-pointer">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- =============================================================== --}}
    {{-- MODAL 2: ADD SPLIT / INSTALLMENT PAYMENT                         --}}
    {{-- =============================================================== --}}
    @if ($showPaymentModal && $paymentPurchase)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-5 animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-base font-black text-slate-900">💳 Record Installment / Split Payment</h3>
                        <p class="text-xs text-slate-500">PO #{{ $paymentPurchase->purchase_no }} ({{ $paymentPurchase->supplier?->name }})</p>
                    </div>
                    <button wire:click="$set('showPaymentModal', false)" type="button" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
                </div>

                {{-- Due Summary Badge --}}
                <div class="p-3.5 bg-rose-50 rounded-xl border border-rose-100 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-rose-600 block text-[10px] font-bold uppercase">Current Outstanding Due</span>
                        <span class="text-lg font-black font-mono text-rose-700">৳{{ number_format($paymentPurchase->due_amount, 2) }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-slate-500 block text-[10px]">Total Bill: ৳{{ number_format($paymentPurchase->grand_total, 2) }}</span>
                        <span class="text-emerald-700 block text-[10px] font-bold">Already Paid: ৳{{ number_format($paymentPurchase->paid_amount, 2) }}</span>
                    </div>
                </div>

                <div class="space-y-3.5 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">পেমেন্টের পরিমাণ (Payment Amount ৳) <span class="text-rose-500">*</span></label>
                        <input 
                            wire:model.live="newPaymentAmount" 
                            type="number" 
                            step="0.01" 
                            max="{{ $paymentPurchase->due_amount }}"
                            class="w-full text-base font-bold font-mono rounded-xl border-slate-300 focus:ring-sky-500 focus:border-sky-500 py-2 px-3"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">পেমেন্ট মেথড <span class="text-rose-500">*</span></label>
                            <select wire:model="newPaymentMethod" class="w-full text-xs font-bold rounded-xl border-slate-300 py-2 px-2.5 focus:ring-sky-500">
                                <option value="CASH">Cash (নগদ)</option>
                                <option value="BANK_TRANSFER">Bank Transfer (ব্যাংক)</option>
                                <option value="CHEQUE">Cheque (চেক)</option>
                                <option value="BKASH">bKash</option>
                                <option value="NAGAD">Nagad</option>
                                <option value="ROCKET">Rocket</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">তারিখ <span class="text-rose-500">*</span></label>
                            <input wire:model="newPaymentDate" type="date" class="w-full text-xs font-bold rounded-xl border-slate-300 py-2 px-2.5 focus:ring-sky-500" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">চেক / ট্রানজেকশন রেফারেন্স নং</label>
                        <input wire:model="newPaymentRef" type="text" placeholder="e.g. Cheque #49281 or Txn ID" class="w-full text-xs rounded-xl border-slate-300 py-2 px-3" />
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">নোট / মন্তব্য</label>
                        <input wire:model="newPaymentNotes" type="text" placeholder="Payment remarks..." class="w-full text-xs rounded-xl border-slate-300 py-2 px-3" />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button wire:click="$set('showPaymentModal', false)" type="button" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 cursor-pointer">
                        Cancel
                    </button>
                    <button wire:click="recordSplitPayment" type="button" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md hover:shadow-lg transition cursor-pointer">
                        ✓ Confirm & Add Payment
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- =============================================================== --}}
    {{-- MODAL 3: EDIT PURCHASE HEADER                                   --}}
    {{-- =============================================================== --}}
    @if ($showEditModal)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4 animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-black text-slate-900">✏️ Edit Purchase Bill Details</h3>
                    <button wire:click="$set('showEditModal', false)" type="button" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">সাপ্লায়ার চালান নং (Challan / Invoice No)</label>
                        <input wire:model="editSupplierInvoiceNo" type="text" class="w-full text-xs rounded-xl border-slate-300 py-2 px-3" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">ইনভয়েস তারিখ</label>
                            <input wire:model="editInvoiceDate" type="date" class="w-full text-xs font-bold rounded-xl border-slate-300 py-2 px-3" />
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">ডেলিভারি তারিখ</label>
                            <input wire:model="editDeliveryDate" type="date" class="w-full text-xs font-bold rounded-xl border-slate-300 py-2 px-3" />
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">নোট / রিমার্কস</label>
                        <textarea wire:model="editNotes" rows="2" class="w-full text-xs rounded-xl border-slate-300 py-2 px-3"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button wire:click="$set('showEditModal', false)" type="button" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 cursor-pointer">
                        Cancel
                    </button>
                    <button wire:click="updatePurchase" type="button" class="px-5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-md cursor-pointer">
                        ✓ Update Bill
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- =============================================================== --}}
    {{-- MODAL 4: DELETE CONFIRMATION                                    --}}
    {{-- =============================================================== --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-rose-100 space-y-4 animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center gap-3 text-rose-600 border-b border-rose-100 pb-3">
                    <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center font-black text-lg">⚠️</div>
                    <div>
                        <h3 class="text-base font-black text-slate-900">Confirm Purchase Deletion</h3>
                        <p class="text-xs text-rose-600 font-bold">Purchase #{{ $deletePurchaseNo }}</p>
                    </div>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed">
                    এই পারচেজটি ডিলিট করলে এর মাধ্যমে যুক্ত হওয়া সকল <strong>ব্যাচ ইনভেন্টরি স্টক বাতিল</strong> হবে এবং <strong>সাপ্লায়ার লেজার লেনদেন রিভার্স</strong> করা হবে। আপনি কি নিশ্চিত?
                </p>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button wire:click="$set('showDeleteModal', false)" type="button" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 cursor-pointer">
                        না, বাতিল করুন
                    </button>
                    <button wire:click="deletePurchase" type="button" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md cursor-pointer">
                        🗑️ হ্যাঁ, মুছে ফেলুন
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
