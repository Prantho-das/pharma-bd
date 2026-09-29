<div class="py-6 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                <svg class="w-7 h-7 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                Purchase Returns & Debit Notes
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Return damaged, recalled, or expired batches back to pharmaceutical suppliers and automatically issue supplier debit notes.
            </p>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-2 bg-slate-100 p-1 rounded-lg border border-slate-200">
            <button 
                wire:click="$set('activeTab', 'create')"
                class="px-4 py-2 text-sm font-semibold rounded-lg transition-all {{ $activeTab === 'create' ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                New Purchase Return
            </button>
            <button 
                wire:click="$set('activeTab', 'history')"
                class="px-4 py-2 text-sm font-semibold rounded-lg transition-all {{ $activeTab === 'history' ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                Return Logs & Debit Notes
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->has('general'))
        <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5 flex-shrink-0 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <span>{{ $errors->first('general') }}</span>
        </div>
    @endif

    @if ($activeTab === 'create')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Item selection & Matrix -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Supplier Selection & Search -->
                <div class="bg-white rounded-lg p-6 border border-slate-200 shadow-sm space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Select Supplier <span class="text-red-500">*</span>
                        </label>
                        <select 
                            wire:model="selectedSupplierId"
                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 text-sm font-medium focus:ring-2 focus:ring-sky-600">
                            <option value="">-- Choose Pharmaceutical Supplier --</option>
                            @foreach ($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->company_name ?: $sup->name }} ({{ $sup->name }})</option>
                            @endforeach
                        </select>
                        @error('selectedSupplierId')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Search Batch to Add -->
                    <div class="relative">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Search Medicine Batch to Return
                        </label>
                        <div class="relative">
                            <input 
                                type="text" 
                                wire:model.live.debounce.300ms="productSearch"
                                placeholder="Type medicine name, barcode, or batch..."
                                class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 text-sm font-medium focus:ring-2 focus:ring-sky-600"
                            />
                            <svg class="w-4 h-4 absolute left-3.5 top-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>

                        <!-- Dropdown Results -->
                        @if (!empty($searchBatches))
                            <div class="absolute z-20 top-full left-0 right-0 mt-1 bg-white border border-slate-200 rounded-lg shadow-xl max-h-60 overflow-y-auto divide-y divide-slate-100">
                                @foreach ($searchBatches as $b)
                                    <button 
                                        type="button" 
                                        wire:click="addBatchToReturn({{ $b['id'] }})"
                                        class="w-full px-4 py-3 text-left hover:bg-sky-50 flex items-center justify-between transition-colors">
                                        <div>
                                            <span class="font-bold text-slate-900 text-sm block">
                                                {{ $b['product']['name'] }}
                                            </span>
                                            <span class="text-xs text-slate-500">
                                                Batch: <span class="font-mono text-slate-700 font-bold">{{ $b['batch_number'] }}</span> • Exp: {{ \Carbon\Carbon::parse($b['expiry_date'])->format('d/m/Y') }}
                                            </span>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-xs font-semibold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">
                                                Stock: {{ $b['current_stock'] }}
                                            </span>
                                            <span class="text-xs text-slate-400 block mt-0.5">Rate: ৳{{ number_format($b['purchase_rate'], 2) }}</span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Selected Return Items Table -->
                <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm">
                    <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                        <h3 class="font-bold text-slate-900 text-sm">Return Items Manifest</h3>
                        <span class="text-xs text-slate-500">{{ count($items) }} batch item(s) selected</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-600 bg-slate-50">
                                    <th class="px-4 py-3">Medicine</th>
                                    <th class="px-4 py-3">Batch & Expiry</th>
                                    <th class="px-4 py-3">In-Stock</th>
                                    <th class="px-4 py-3 w-28">Return Qty</th>
                                    <th class="px-4 py-3 w-32">Return Rate</th>
                                    <th class="px-4 py-3 text-right">Debit Total</th>
                                    <th class="px-4 py-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-800">
                                @forelse ($items as $idx => $item)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-4 py-3 font-medium">
                                            <span class="font-bold text-slate-900">{{ $item['product_name'] }}</span>
                                            <span class="text-xs text-slate-400 block">{{ $item['unit_name'] }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="font-mono text-xs font-bold block text-slate-800">{{ $item['batch_number'] }}</span>
                                            <span class="text-xs text-slate-400">{{ $item['expiry_date'] }}</span>
                                        </td>
                                        <td class="px-4 py-3 font-semibold text-slate-600">
                                            {{ $item['available_stock'] }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <input 
                                                type="number" 
                                                min="1" 
                                                max="{{ $item['available_stock'] }}"
                                                wire:model.live="items.{{ $idx }}.quantity"
                                                class="w-20 px-2.5 py-1 text-center rounded-lg border border-slate-300 bg-white text-slate-900 font-bold focus:ring-2 focus:ring-sky-600"
                                            />
                                            @error("items.{$idx}.quantity")
                                                <span class="text-xs text-red-500 block">{{ $message }}</span>
                                            @enderror
                                        </td>
                                        <td class="px-4 py-3">
                                            <input 
                                                type="number" 
                                                step="0.01"
                                                wire:model.live="items.{{ $idx }}.return_price_per_unit"
                                                class="w-24 px-2.5 py-1 text-center rounded-lg border border-slate-300 bg-white text-slate-900 font-mono focus:ring-2 focus:ring-sky-600"
                                            />
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono font-bold text-slate-900">
                                            ৳{{ number_format(((int)$item['quantity']) * ((float)$item['return_price_per_unit']), 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <button 
                                                type="button" 
                                                wire:click="removeItem({{ $idx }})"
                                                class="p-1 text-red-500 hover:text-red-700 rounded transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                                            No items added yet. Search and select medicine batches above.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right 1 Col: Voucher Summary & Action -->
            <div class="space-y-6">
                <div class="bg-white rounded-lg p-6 border border-slate-200 shadow-sm space-y-5">
                    <h3 class="font-bold text-slate-900 text-base border-b border-slate-200 pb-3">
                        Debit Note Summary
                    </h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-slate-600">
                            <span>Total Items:</span>
                            <span class="font-bold text-slate-900">{{ count($items) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-900 border-t border-slate-200 pt-3">
                            <span class="font-bold">Total Debit Amount:</span>
                            <span class="text-2xl font-black text-sky-700 font-mono">
                                ৳{{ number_format($totalRefundCalculated, 2) }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-3 pt-2">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                Status / Action
                            </label>
                            <select 
                                wire:model="refundStatus"
                                class="w-full px-3.5 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 text-sm focus:ring-2 focus:ring-sky-600">
                                <option value="ADJUSTED">ADJUSTED (Deduct from supplier balance)</option>
                                <option value="PENDING">PENDING (Waiting for supplier verification)</option>
                                <option value="REFUNDED">REFUNDED (Direct Cash/Bank refund)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                Reason for Return
                            </label>
                            <textarea 
                                wire:model="reason" 
                                rows="3"
                                placeholder="e.g. Near expiry batch returned / Company recall"
                                class="w-full px-3.5 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 text-sm focus:ring-2 focus:ring-sky-600"></textarea>
                        </div>
                    </div>

                    <button 
                        type="button" 
                        wire:click="submitReturn"
                        @disabled(count($items) === 0 || $totalRefundCalculated <= 0)
                        class="w-full py-3 bg-sky-600 hover:bg-sky-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold rounded-lg transition-all shadow-md flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        Post Purchase Return
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($activeTab === 'history')
        <!-- History Table -->
        <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm">
            <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50">
                <div class="relative flex-1 max-w-md">
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="historySearch"
                        placeholder="Search by Return No or Supplier..."
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
                            <th class="px-5 py-3.5">Debit Note / Return No</th>
                            <th class="px-5 py-3.5">Supplier</th>
                            <th class="px-5 py-3.5">Total Refund</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5">Reason</th>
                            <th class="px-5 py-3.5">Created By</th>
                            <th class="px-5 py-3.5">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        @forelse ($returns as $ret)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-5 py-3.5 font-mono font-bold text-sky-700">
                                    {{ $ret->return_no }}
                                </td>
                                <td class="px-5 py-3.5 font-medium">
                                    {{ $ret->supplier->company_name ?: $ret->supplier->name }}
                                </td>
                                <td class="px-5 py-3.5 font-mono font-bold text-slate-900">
                                    ৳{{ number_format($ret->total_refund_amount, 2) }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $ret->refund_status === 'ADJUSTED' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $ret->refund_status }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-500">
                                    {{ $ret->reason ?: '-' }}
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
                                    No purchase returns found.
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
</div>
