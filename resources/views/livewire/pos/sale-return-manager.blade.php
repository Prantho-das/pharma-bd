<div class="py-6 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                <svg class="w-7 h-7 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                </svg>
                Customer Sales Returns & Refunds
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Lookup sold customer invoices, restock active batches or log damaged items, and issue instant refunds or account credit.
            </p>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-2 bg-slate-100 p-1 rounded-lg border border-slate-200">
            <button 
                wire:click="$set('activeTab', 'create')"
                class="px-4 py-2 text-sm font-semibold rounded-lg transition-all {{ $activeTab === 'create' ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                New Return Voucher
            </button>
            <button 
                wire:click="$set('activeTab', 'history')"
                class="px-4 py-2 text-sm font-semibold rounded-lg transition-all {{ $activeTab === 'history' ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                Return History & Slips
            </button>
        </div>
    </div>

    @if (session('error') || $errors->has('general'))
        <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 flex-shrink-0 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <span>{{ session('error') ?? $errors->first('general') }}</span>
        </div>
    @endif

    @if ($activeTab === 'create')
        <!-- Invoice Search Card -->
        <div class="bg-white rounded-lg p-6 border border-slate-200 shadow-sm">
            <div class="max-w-2xl">
                <label class="block text-sm font-bold text-slate-700 mb-2">
                    Search Invoice Number
                </label>
                <form wire:submit="searchSale" class="flex gap-3">
                    <div class="relative flex-1">
                        <input 
                            type="text" 
                            wire:model="searchInvoice" 
                            placeholder="e.g. INV-20260928-0001 or scan barcode..."
                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-600 text-sm font-medium"
                            autofocus
                        />
                    </div>
                    <button 
                        type="submit"
                        class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white text-sm font-bold rounded-lg transition-all shadow-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        Lookup Invoice
                    </button>
                </form>
                @error('searchInvoice')
                    <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                @enderror
            </div>
        </div>

        @if ($selectedSale)
            <!-- Invoice Details & Item Return Matrix -->
            <div class="space-y-6">
                <!-- Sale Meta Header -->
                <div class="bg-sky-50/70 border border-sky-200 rounded-lg p-5 flex flex-wrap items-center justify-between gap-4 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-sky-600/10 flex items-center justify-center text-sky-700 font-bold text-lg">
                            #
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-lg font-bold text-slate-900">{{ $selectedSale->invoice_no }}</h2>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $selectedSale->payment_status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $selectedSale->payment_status }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Date: {{ $selectedSale->sale_date?->format('d M Y, h:i A') ?? $selectedSale->created_at->format('d M Y, h:i A') }} • Branch: {{ $selectedSale->branch?->name ?? 'Main' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-6 text-sm">
                        <div>
                            <span class="text-xs text-slate-500 block">Customer</span>
                            <span class="font-bold text-slate-900">{{ $selectedSale->customer?->name ?? 'Walk-in Customer' }}</span>
                            @if ($selectedSale->customer?->phone)
                                <span class="text-xs text-slate-400 block">{{ $selectedSale->customer->phone }}</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-xs text-slate-500 block">Invoice Total</span>
                            <span class="font-bold text-slate-900">৳{{ number_format($selectedSale->grand_total, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Return Items Table -->
                <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm">
                    <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                        <h3 class="font-bold text-slate-900 text-sm">Select Items & Quantities to Return</h3>
                        <span class="text-xs text-slate-500">Uncheck damaged if item can be restocked to batch</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-600 bg-slate-50">
                                    <th class="px-5 py-3.5">Medicine Name</th>
                                    <th class="px-5 py-3.5">Batch</th>
                                    <th class="px-5 py-3.5">Sold Qty</th>
                                    <th class="px-5 py-3.5">Unit Price</th>
                                    <th class="px-5 py-3.5 w-32">Return Qty</th>
                                    <th class="px-5 py-3.5 text-center">Condition</th>
                                    <th class="px-5 py-3.5 text-right">Refund Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-800">
                                @foreach ($returnItems as $key => $item)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-5 py-3.5 font-medium">
                                            {{ $item['product_name'] }}
                                            <span class="text-xs text-slate-400 block">Unit: {{ $item['unit_name'] }}</span>
                                        </td>
                                        <td class="px-5 py-3.5 font-mono text-xs text-slate-500">
                                            {{ $item['batch_number'] }}
                                        </td>
                                        <td class="px-5 py-3.5 font-semibold text-slate-700">
                                            {{ $item['max_qty'] }} {{ $item['unit_name'] }}
                                        </td>
                                        <td class="px-5 py-3.5 font-mono">
                                            ৳{{ number_format($item['unit_price'], 2) }}
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <input 
                                                type="number" 
                                                min="0" 
                                                max="{{ $item['max_qty'] }}"
                                                wire:model.live="returnItems.{{ $key }}.qty"
                                                class="w-24 px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-slate-900 font-semibold focus:ring-2 focus:ring-sky-600 text-center"
                                            />
                                        </td>
                                        <td class="px-5 py-3.5 text-center">
                                            <label class="inline-flex items-center cursor-pointer gap-1.5 text-xs font-medium">
                                                <input 
                                                    type="checkbox" 
                                                    wire:model.live="returnItems.{{ $key }}.is_damaged"
                                                    class="rounded text-red-600 focus:ring-red-500 border-slate-300"
                                                />
                                                <span class="{{ $item['is_damaged'] ? 'text-red-600 font-bold' : 'text-emerald-600 font-bold' }}">
                                                    {{ $item['is_damaged'] ? 'Damaged (Discard)' : 'Good (Restock)' }}
                                                </span>
                                            </label>
                                        </td>
                                        <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-900">
                                            ৳{{ number_format(((int)$item['qty']) * ((float)$item['unit_price']), 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Return Summary & Submission Form -->
                    <div class="p-6 bg-slate-50 border-t border-slate-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                                        Refund Settlement Method
                                    </label>
                                    <div class="flex items-center gap-4">
                                        <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-slate-700">
                                            <input type="radio" value="CASH" wire:model="refundType" class="text-sky-600 focus:ring-sky-600" />
                                            Cash Refund (Pay Customer Cash)
                                        </label>
                                        @if ($selectedSale->customer_id)
                                            <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-slate-700">
                                                <input type="radio" value="DUE_ADJUSTMENT" wire:model="refundType" class="text-sky-600 focus:ring-sky-600" />
                                                Adjust Customer Account Due
                                            </label>
                                        @endif
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                                        Reason / Return Note
                                    </label>
                                    <input 
                                        type="text" 
                                        wire:model="reason" 
                                        placeholder="e.g. Wrong dosage purchased / Doctor changed prescription"
                                        class="w-full px-3.5 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 text-sm focus:ring-2 focus:ring-sky-600"
                                    />
                                </div>
                            </div>

                            <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm flex flex-col justify-between">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-sm font-semibold text-slate-600">Total Refund Payable:</span>
                                    <span class="text-2xl font-black text-sky-700 font-mono">
                                        ৳{{ number_format($totalRefundCalculated, 2) }}
                                    </span>
                                </div>

                                <button 
                                    wire:click="submitReturn"
                                    type="button"
                                    @disabled($totalRefundCalculated <= 0)
                                    class="w-full py-3 bg-sky-600 hover:bg-sky-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold rounded-lg transition-all shadow-md flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                    Process Refund & Restock
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

    @if ($activeTab === 'history')
        <!-- History Search & Table -->
        <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm">
            <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50">
                <div class="relative flex-1 max-w-md">
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="historySearch"
                        placeholder="Search by Return No, Invoice No or Customer..."
                        class="w-full pl-10 pr-4 py-2 text-sm rounded-lg border border-slate-300 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-sky-600"
                    />
                    <svg class="w-4 h-4 absolute left-3.5 top-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-600 bg-slate-50">
                            <th class="px-5 py-3.5">Return No</th>
                            <th class="px-5 py-3.5">Original Invoice</th>
                            <th class="px-5 py-3.5">Customer</th>
                            <th class="px-5 py-3.5">Refund Type</th>
                            <th class="px-5 py-3.5">Total Refund</th>
                            <th class="px-5 py-3.5">Issued By</th>
                            <th class="px-5 py-3.5">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        @forelse ($returns as $ret)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-5 py-3.5 font-mono font-bold text-sky-700">
                                    {{ $ret->return_no }}
                                </td>
                                <td class="px-5 py-3.5 font-mono text-slate-600">
                                    {{ $ret->sale?->invoice_no ?? 'N/A' }}
                                </td>
                                <td class="px-5 py-3.5 font-medium">
                                    {{ $ret->customer?->name ?? 'Walk-in Customer' }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $ret->refund_type === 'CASH' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $ret->refund_type }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 font-mono font-bold text-slate-900">
                                    ৳{{ number_format($ret->total_refund_amount, 2) }}
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-500">
                                    {{ $ret->user?->name ?? 'System' }}
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-500">
                                    {{ $ret->created_at->format('d M Y, h:i A') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                    No sales returns found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-200">
                {{ $returns->links() }}
            </div>
        </div>
    @endif

    <!-- Success Modal & Print Slip -->
    @if ($showSuccessModal && $latestReturn)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-lg max-w-md w-full p-6 border border-slate-200 shadow-2xl space-y-5">
                <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 mx-auto">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                </div>

                <div class="text-center">
                    <h3 class="text-lg font-bold text-slate-900">Sales Return Processed!</h3>
                    <p class="text-xs text-slate-500 mt-1 font-mono">Voucher: {{ $latestReturn->return_no }}</p>
                </div>

                <div class="bg-slate-50 p-4 rounded-lg space-y-2 text-sm border border-slate-100">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Refund Amount:</span>
                        <span class="font-bold text-slate-900">৳{{ number_format($latestReturn->total_refund_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Method:</span>
                        <span class="font-bold text-emerald-600">{{ $latestReturn->refund_type }}</span>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button 
                        type="button" 
                        onclick="window.print()"
                        class="flex-1 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg text-sm transition-all shadow flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.077-.44-2.18-.592-3.3m11.744 0c-.152 1.12-.352 2.223-.592 3.3m-10.56 0A23.447 23.447 0 0 0 3 14.25v3.75a2.25 2.25 0 0 0 2.25 2.25h13.5A2.25 2.25 0 0 0 21 18V14.25c-1.026.16-2.072.26-3.13.3m-10.56 0A23.003 23.003 0 0 1 12 14.25c2.052 0 4.043-.223 5.92-.647M12 9V3m0 0 3 3m-3-3-3 3" />
                        </svg>
                        Print Slip
                    </button>
                    <button 
                        type="button" 
                        wire:click="closeSuccessModal"
                        class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-sm transition-all border border-slate-200">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
