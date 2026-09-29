<div class="py-6 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                <svg class="w-7 h-7 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Customer Due List & Receivables
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Track total account dues and invoice-specific cash dues across all branches.
            </p>
        </div>

        <div class="flex items-center gap-2 bg-slate-100 p-1 rounded-lg border border-slate-200">
            <button wire:click="$set('view', 'total')" class="px-4 py-2 text-sm font-semibold rounded-lg transition {{ $view === 'total' ? 'bg-white text-sky-700 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900' }}">Total Due (৳{{ number_format($totalDueSum, 2) }})</button>
            <button wire:click="$set('view', 'walk')" class="px-4 py-2 text-sm font-semibold rounded-lg transition {{ $view === 'walk' ? 'bg-white text-sky-700 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900' }}">Cash Invoice Due (৳{{ number_format($walkDueSum, 2) }})</button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-5 border-b border-slate-200 bg-slate-50">
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search"
                placeholder="{{ $view === 'total' ? 'Search by customer name or phone...' : 'Search by invoice number or customer...' }}"
                class="w-full max-w-md pl-4 pr-4 py-2 text-sm rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-sky-600"
            />
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                @if ($view === 'total')
                    <thead>
                        <tr class="border-b border-slate-200 text-xs font-semibold uppercase text-slate-600 bg-slate-50">
                            <th class="px-5 py-3.5">Customer Name</th>
                            <th class="px-5 py-3.5">Phone Number</th>
                            <th class="px-5 py-3.5 text-right">Credit Limit</th>
                            <th class="px-5 py-3.5 text-right">Total Outstanding Due</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        @forelse ($dues as $c)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-3.5 font-bold text-slate-900">{{ $c->name }}</td>
                                <td class="px-5 py-3.5 font-mono text-xs text-slate-600">{{ $c->phone }}</td>
                                <td class="px-5 py-3.5 text-right font-mono text-xs text-slate-500">৳{{ number_format($c->credit_limit, 2) }}</td>
                                <td class="px-5 py-3.5 text-right font-mono font-bold text-red-600 text-base">৳{{ number_format($c->current_due, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-center text-slate-400">No customers with outstanding due.</td></tr>
                        @endforelse
                    </tbody>
                @else
                    <thead>
                        <tr class="border-b border-slate-200 text-xs font-semibold uppercase text-slate-600 bg-slate-50">
                            <th class="px-5 py-3.5">Invoice No</th>
                            <th class="px-5 py-3.5">Customer</th>
                            <th class="px-5 py-3.5">Date</th>
                            <th class="px-5 py-3.5 text-right">Total Bill</th>
                            <th class="px-5 py-3.5 text-right">Paid</th>
                            <th class="px-5 py-3.5 text-right">Unpaid Due</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        @forelse ($dues as $s)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-3.5 font-mono font-bold text-sky-700">{{ $s->invoice_no }}</td>
                                <td class="px-5 py-3.5 font-bold text-slate-900">{{ $s->customer?->name ?? 'Walk-in Customer' }}</td>
                                <td class="px-5 py-3.5 text-xs text-slate-500">{{ $s->created_at->format('d/m/Y') }}</td>
                                <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-900">৳{{ number_format($s->grand_total, 2) }}</td>
                                <td class="px-5 py-3.5 text-right font-mono text-emerald-600 font-semibold">৳{{ number_format($s->paid_amount, 2) }}</td>
                                <td class="px-5 py-3.5 text-right font-mono font-bold text-red-600">৳{{ number_format($s->due_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-8 text-center text-slate-400">No unpaid invoices found.</td></tr>
                        @endforelse
                    </tbody>
                @endif
            </table>
        </div>

        <div class="p-4 border-t border-slate-200">
            {{ $dues->links() }}
        </div>
    </div>
</div>
