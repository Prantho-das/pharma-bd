<div class="py-6 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <svg class="w-7 h-7 text-[#00897B]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                </svg>
                Inter-Branch Stock Transfers
            </h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                Move medicine batches between branches with full transit traceability, locking, and destination verification.
            </p>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-2 bg-zinc-100 dark:bg-zinc-800/60 p-1 rounded-lg border border-zinc-200 dark:border-zinc-700">
            <button 
                wire:click="$set('activeTab', 'dispatch')"
                class="px-4 py-2 text-sm font-medium rounded-lg transition-all {{ $activeTab === 'dispatch' ? 'bg-white dark:bg-zinc-700 text-[#00897B] dark:text-sky-400 shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200' }}">
                Dispatch Transfer
            </button>
            <button 
                wire:click="$set('activeTab', 'incoming')"
                class="px-4 py-2 text-sm font-medium rounded-lg transition-all relative {{ $activeTab === 'incoming' ? 'bg-white dark:bg-zinc-700 text-[#00897B] dark:text-sky-400 shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200' }}">
                Incoming Requests
                @if (count($incomingTransfers) > 0)
                    <span class="ml-1.5 px-2 py-0.5 text-xs bg-amber-500 text-white font-bold rounded-full">
                        {{ count($incomingTransfers) }}
                    </span>
                @endif
            </button>
            <button 
                wire:click="$set('activeTab', 'history')"
                class="px-4 py-2 text-sm font-medium rounded-lg transition-all {{ $activeTab === 'history' ? 'bg-white dark:bg-zinc-700 text-[#00897B] dark:text-sky-400 shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200' }}">
                Transfer Logs
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900 text-emerald-700 dark:text-emerald-300 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->has('general'))
        <div class="p-4 rounded-lg bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900 text-red-700 dark:text-red-300 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <span>{{ $errors->first('general') }}</span>
        </div>
    @endif

    @if ($activeTab === 'dispatch')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Form & Item Selection -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Branch Configuration -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg p-6 border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-500 mb-1.5">
                                Source Branch (Transfer From)
                            </label>
                            <select 
                                wire:model.live="fromBranchId"
                                class="w-full px-4 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 text-sm focus:ring-2 focus:ring-[#00897B]">
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }} ({{ $branch->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-500 mb-1.5">
                                Destination Branch (Transfer To) <span class="text-red-500">*</span>
                            </label>
                            <select 
                                wire:model="toBranchId"
                                class="w-full px-4 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 text-sm focus:ring-2 focus:ring-[#00897B]">
                                <option value="">-- Choose Target Branch --</option>
                                @foreach ($branches as $branch)
                                    @if ($branch->id != $fromBranchId)
                                        <option value="{{ $branch->id }}">{{ $branch->name }} ({{ $branch->code }})</option>
                                    @endif
                                @endforeach
                            </select>
                            @error('toBranchId')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Search Batch -->
                    <div class="relative pt-2">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-500 mb-1.5">
                            Search Source Medicine Batch to Dispatch
                        </label>
                        <div class="relative">
                            <input 
                                type="text" 
                                wire:model.live.debounce.300ms="productSearch"
                                placeholder="Search by medicine name, SKU or barcode..."
                                class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 text-sm focus:ring-2 focus:ring-[#00897B]"
                            />
                            <svg class="w-4 h-4 absolute left-3.5 top-3.5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>

                        <!-- Dropdown Results -->
                        @if (!empty($searchBatches))
                            <div class="absolute z-20 top-full left-0 right-0 mt-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-xl max-h-60 overflow-y-auto divide-y divide-zinc-100 dark:divide-zinc-700">
                                @foreach ($searchBatches as $b)
                                    <button 
                                        type="button" 
                                        wire:click="addBatch({{ $b['id'] }})"
                                        class="w-full px-4 py-3 text-left hover:bg-sky-50 dark:hover:bg-zinc-700/50 flex items-center justify-between transition-colors">
                                        <div>
                                            <span class="font-semibold text-zinc-900 dark:text-zinc-100 text-sm block">
                                                {{ $b['product']['name'] }}
                                            </span>
                                            <span class="text-xs text-zinc-500">
                                                Batch: <span class="font-mono font-bold text-zinc-700 dark:text-zinc-300">{{ $b['batch_number'] }}</span> • Exp: {{ \Carbon\Carbon::parse($b['expiry_date'])->format('d/m/Y') }}
                                            </span>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-xs font-semibold px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300">
                                                Avail: {{ $b['current_stock'] }}
                                            </span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Manifest Table -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                    <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/50 flex items-center justify-between">
                        <h3 class="font-semibold text-zinc-900 dark:text-zinc-100 text-sm">Transfer Manifest Items</h3>
                        <span class="text-xs text-zinc-500">{{ count($items) }} batch item(s) to move</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="border-b border-zinc-200 dark:border-zinc-700 text-xs font-semibold uppercase tracking-wider text-zinc-500 bg-zinc-50/50 dark:bg-zinc-800/50">
                                    <th class="px-4 py-3">Medicine</th>
                                    <th class="px-4 py-3">Batch & Expiry</th>
                                    <th class="px-4 py-3">Source Stock</th>
                                    <th class="px-4 py-3 w-32">Transfer Qty</th>
                                    <th class="px-4 py-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 text-zinc-800 dark:text-zinc-200">
                                @forelse ($items as $idx => $item)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-750 transition-colors">
                                        <td class="px-4 py-3 font-medium">
                                            {{ $item['product_name'] }}
                                            <span class="text-xs text-zinc-400 block">{{ $item['unit_name'] }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="font-mono text-xs font-bold block">{{ $item['batch_number'] }}</span>
                                            <span class="text-xs text-zinc-400">{{ $item['expiry_date'] }}</span>
                                        </td>
                                        <td class="px-4 py-3 font-semibold text-zinc-600 dark:text-zinc-400">
                                            {{ $item['available_stock'] }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <input 
                                                type="number" 
                                                min="1" 
                                                max="{{ $item['available_stock'] }}"
                                                wire:model.live="items.{{ $idx }}.quantity"
                                                class="w-24 px-2.5 py-1 text-center rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 font-bold focus:ring-2 focus:ring-[#00897B]"
                                            />
                                            @error("items.{$idx}.quantity")
                                                <span class="text-xs text-red-500 block">{{ $message }}</span>
                                            @enderror
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
                                        <td colspan="5" class="px-4 py-8 text-center text-zinc-400">
                                            No items added yet. Search and select batches above.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right 1 Col: Summary & Dispatch Button -->
            <div class="space-y-6">
                <div class="bg-white dark:bg-zinc-800 rounded-lg p-6 border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-5">
                    <h3 class="font-bold text-zinc-900 dark:text-zinc-100 text-base border-b border-zinc-200 dark:border-zinc-700 pb-3">
                        Dispatch Confirmation
                    </h3>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-500 mb-1">
                            Transfer Notes / Dispatch Courier
                        </label>
                        <textarea 
                            wire:model="notes" 
                            rows="3"
                            placeholder="e.g. Sent via courier / urgent stock requisition"
                            class="w-full px-3.5 py-2 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 text-sm focus:ring-2 focus:ring-[#00897B]"></textarea>
                    </div>

                    <button 
                        type="button" 
                        wire:click="dispatchTransfer"
                        @disabled(count($items) === 0 || !$toBranchId)
                        class="w-full py-3 bg-[#00897B] hover:bg-sky-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold rounded-lg transition-all shadow-md flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                        </svg>
                        Dispatch Stock
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($activeTab === 'incoming')
        <!-- Incoming Transfers Tab -->
        <div class="space-y-4">
            @forelse ($incomingTransfers as $transfer)
                <div class="bg-white dark:bg-zinc-800 rounded-lg p-6 border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-100 dark:border-zinc-700 pb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-lg text-zinc-900 dark:text-zinc-100">{{ $transfer->transfer_no }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                                    {{ $transfer->status }}
                                </span>
                            </div>
                            <p class="text-xs text-zinc-500 mt-1">
                                Dispatched from <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $transfer->fromBranch->name }}</span> by {{ $transfer->sentBy?->name ?? 'Staff' }} on {{ $transfer->dispatched_at?->format('d M Y, h:i A') }}
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <button 
                                wire:click="handleTransfer({{ $transfer->id }}, 'RECEIVED')"
                                class="px-4 py-2 bg-[#00897B] hover:bg-sky-700 text-white font-semibold rounded-lg text-sm transition-all shadow flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                Accept & Restock
                            </button>
                            <button 
                                wire:click="handleTransfer({{ $transfer->id }}, 'REJECTED')"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg text-sm transition-all shadow flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                                Reject
                            </button>
                        </div>
                    </div>

                    <!-- Items Preview -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach ($transfer->items as $tItem)
                            <div class="p-3 bg-zinc-50 dark:bg-zinc-900/50 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                <span class="font-semibold text-sm text-zinc-900 dark:text-zinc-100 block">{{ $tItem->product->name }}</span>
                                <div class="flex justify-between text-xs text-zinc-500 mt-1">
                                    <span>Batch: <span class="font-mono font-bold text-zinc-700 dark:text-zinc-300">{{ $tItem->batch?->batch_number }}</span></span>
                                    <span class="font-bold text-sky-600 dark:text-sky-400">{{ $tItem->quantity }} {{ $tItem->unit?->name ?? 'Pcs' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-zinc-800 rounded-lg p-12 border border-zinc-200 dark:border-zinc-700 text-center text-zinc-500">
                    No pending incoming stock transfers for this branch.
                </div>
            @endforelse
        </div>
    @endif

    @if ($activeTab === 'history')
        <!-- History Table -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
            <div class="p-5 border-b border-zinc-200 dark:border-zinc-700 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-zinc-50/50 dark:bg-zinc-900/50">
                <div class="relative flex-1 max-w-md">
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="historySearch"
                        placeholder="Search by Transfer No, Source or Destination Branch..."
                        class="w-full pl-10 pr-4 py-2 text-sm rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:ring-2 focus:ring-[#00897B]"
                    />
                    <svg class="w-4 h-4 absolute left-3.5 top-3 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700 text-xs font-semibold uppercase tracking-wider text-zinc-500 bg-zinc-50/50 dark:bg-zinc-800/50">
                            <th class="px-5 py-3.5">Transfer No</th>
                            <th class="px-5 py-3.5">From Branch</th>
                            <th class="px-5 py-3.5">To Branch</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5">Dispatched By</th>
                            <th class="px-5 py-3.5">Received By</th>
                            <th class="px-5 py-3.5">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 text-zinc-800 dark:text-zinc-200">
                        @forelse ($historyTransfers as $tf)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-750 transition-colors">
                                <td class="px-5 py-3.5 font-mono font-bold text-[#00897B] dark:text-sky-400">
                                    {{ $tf->transfer_no }}
                                </td>
                                <td class="px-5 py-3.5 font-medium">
                                    {{ $tf->fromBranch->name }}
                                </td>
                                <td class="px-5 py-3.5 font-medium">
                                    {{ $tf->toBranch->name }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $tf->status === 'RECEIVED' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' : ($tf->status === 'REJECTED' ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300') }}">
                                        {{ $tf->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-zinc-500">
                                    {{ $tf->sentBy?->name ?? 'Staff' }}
                                </td>
                                <td class="px-5 py-3.5 text-xs text-zinc-500">
                                    {{ $tf->receivedBy?->name ?? '-' }}
                                </td>
                                <td class="px-5 py-3.5 text-xs text-zinc-500">
                                    {{ $tf->created_at->format('d M Y, h:i A') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-zinc-500">
                                    No stock transfers recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
                {{ $historyTransfers->links() }}
            </div>
        </div>
    @endif
</div>
