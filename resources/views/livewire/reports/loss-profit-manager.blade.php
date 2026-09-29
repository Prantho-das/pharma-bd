<div class="py-6 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                <svg class="w-7 h-7 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                </svg>
                Profit & Loss (P&L) Ledger
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Real-time invoice profit margins calculated from actual batch acquisition costs.
            </p>
        </div>

        <div class="flex items-center gap-2 bg-white p-2 rounded-lg border border-slate-200 shadow-sm">
            <input type="date" wire:model.live="startDate" class="px-2.5 py-1 text-xs rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-sky-600" />
            <span class="text-xs text-slate-400">to</span>
            <input type="date" wire:model.live="endDate" class="px-2.5 py-1 text-xs rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-sky-600" />
        </div>
    </div>

    <!-- Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase text-slate-500">Gross Sales</span>
            <span class="text-2xl font-black font-mono text-slate-900 block mt-1">৳{{ number_format($totalSales, 2) }}</span>
        </div>
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase text-slate-500">Batch Cost (COGS)</span>
            <span class="text-2xl font-black font-mono text-amber-600 block mt-1">৳{{ number_format($totalCogs, 2) }}</span>
        </div>
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase text-slate-500">Gross Margin</span>
            <span class="text-2xl font-black font-mono text-sky-700 block mt-1">৳{{ number_format($grossProfit, 2) }}</span>
        </div>
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase text-slate-500">Expenses</span>
            <span class="text-2xl font-black font-mono text-red-600 block mt-1">৳{{ number_format($totalExpenses, 2) }}</span>
        </div>
        <div class="bg-emerald-50 p-5 rounded-lg border border-emerald-200 shadow-sm">
            <span class="text-xs font-black uppercase text-emerald-800">Net Profit</span>
            <span class="text-2xl font-black font-mono text-emerald-700 block mt-1">৳{{ number_format($netProfit, 2) }}</span>
        </div>
    </div>

    <!-- Per-Invoice Margin Table -->
    <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-4 border-b border-slate-200 bg-slate-50 font-bold text-slate-800 text-sm">Invoice Profit Breakdown</div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-xs font-semibold uppercase text-slate-600 bg-slate-50">
                        <th class="px-5 py-3.5">Invoice No</th>
                        <th class="px-5 py-3.5">Date</th>
                        <th class="px-5 py-3.5 text-right">Selling Price</th>
                        <th class="px-5 py-3.5 text-right">Batch Cost</th>
                        <th class="px-5 py-3.5 text-right">Gross Profit</th>
                        <th class="px-5 py-3.5 text-center">Margin %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800">
                    @forelse ($sales as $sale)
                        @php
                            $cogs = $sale->items->sum(function($item) {
                                return $item->quantity * ($item->batch?->purchase_rate ?? 0);
                            });
                            $profit = $sale->grand_total - $cogs;
                            $margin = $sale->grand_total > 0 ? ($profit / $sale->grand_total) * 100 : 0;
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3.5 font-mono font-bold text-sky-700">{{ $sale->invoice_no }}</td>
                            <td class="px-5 py-3.5 text-xs text-slate-500">{{ $sale->sale_date?->format('d/m/Y') ?? $sale->created_at->format('d/m/Y') }}</td>
                            <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-900">৳{{ number_format($sale->grand_total, 2) }}</td>
                            <td class="px-5 py-3.5 text-right font-mono text-amber-600">৳{{ number_format($cogs, 2) }}</td>
                            <td class="px-5 py-3.5 text-right font-mono font-bold text-emerald-600">৳{{ number_format($profit, 2) }}</td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $margin >= 20 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ number_format($margin, 1) }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-slate-400">No transactions recorded in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">{{ $sales->links() }}</div>
    </div>
</div>
