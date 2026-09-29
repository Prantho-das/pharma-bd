<div class="py-6 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                <svg class="w-7 h-7 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
                Sale List
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Comprehensive archive of POS transactions, customer invoices, and split settlements.
            </p>
        </div>
        <a href="{{ route('pos.terminal') }}" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg text-sm transition shadow flex items-center gap-2" wire:navigate>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Sale New (POS)
        </a>
    </div>

    <!-- Filters & Table -->
    <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50">
            <div class="relative flex-1 max-w-md">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by invoice no, customer name or phone..."
                    class="w-full pl-10 pr-4 py-2 text-sm rounded-lg border border-slate-300 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-sky-600"
                />
                <svg class="w-4 h-4 absolute left-3.5 top-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </div>

            <div class="flex items-center gap-3">
                <select 
                    wire:model.live="paymentStatus"
                    class="px-3.5 py-2 text-xs font-semibold rounded-lg border border-slate-300 bg-white text-slate-800 focus:ring-2 focus:ring-sky-600">
                    <option value="all">All Payment Status</option>
                    <option value="PAID">PAID</option>
                    <option value="PARTIAL">PARTIAL</option>
                    <option value="DUE">DUE</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-600 bg-slate-50">
                        <th class="px-5 py-3.5">Invoice No</th>
                        <th class="px-5 py-3.5">Date</th>
                        <th class="px-5 py-3.5">Customer</th>
                        <th class="px-5 py-3.5">Total Amount</th>
                        <th class="px-5 py-3.5">Paid</th>
                        <th class="px-5 py-3.5">Due</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Cashier</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800">
                    @forelse ($sales as $sale)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3.5 font-mono font-bold text-sky-700">
                                {{ $sale->invoice_no }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-500">
                                {{ $sale->sale_date?->format('d/m/Y h:i A') ?? $sale->created_at->format('d/m/Y h:i A') }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-bold text-slate-900 block">{{ $sale->customer?->name ?? 'Walk-in Customer' }}</span>
                                @if ($sale->customer?->phone)
                                    <span class="text-xs text-slate-400">{{ $sale->customer->phone }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 font-mono font-bold text-slate-900">
                                ৳{{ number_format($sale->grand_total, 2) }}
                            </td>
                            <td class="px-5 py-3.5 font-mono font-semibold text-emerald-600">
                                ৳{{ number_format($sale->paid_amount, 2) }}
                            </td>
                            <td class="px-5 py-3.5 font-mono font-semibold {{ $sale->due_amount > 0 ? 'text-amber-600 font-bold' : 'text-slate-400' }}">
                                ৳{{ number_format($sale->due_amount, 2) }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $sale->payment_status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : ($sale->payment_status === 'PARTIAL' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $sale->payment_status }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-500">
                                {{ $sale->user?->name ?? 'Cashier' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-slate-400">
                                No sales transactions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200">
            {{ $sales->links() }}
        </div>
    </div>
</div>
