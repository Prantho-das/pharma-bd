<div class="py-6 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                <svg class="w-7 h-7 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                </svg>
                Inventory Stock List
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Real-time inventory levels, reorder thresholds, and low-stock replenishment warnings.
            </p>
        </div>

        <div class="flex items-center gap-2 bg-slate-100 p-1 rounded-lg border border-slate-200">
            <button wire:click="$set('view', 'all')" class="px-4 py-2 text-sm font-semibold rounded-lg transition {{ $view === 'all' ? 'bg-white text-sky-700 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900' }}">All Stock</button>
            <button wire:click="$set('view', 'low')" class="px-4 py-2 text-sm font-semibold rounded-lg transition {{ $view === 'low' ? 'bg-white text-amber-600 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900' }}">Low Stock Alerts</button>
        </div>
    </div>

    <!-- Stock Table -->
    <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50">
            <div class="relative flex-1 max-w-md">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search medicines by name or SKU..."
                    class="w-full pl-10 pr-4 py-2 text-sm rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-sky-600"
                />
                <svg class="w-4 h-4 absolute left-3.5 top-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-xs font-semibold uppercase text-slate-600 bg-slate-50">
                        <th class="px-5 py-3.5">Medicine</th>
                        <th class="px-5 py-3.5">Category</th>
                        <th class="px-5 py-3.5">Manufacturer</th>
                        <th class="px-5 py-3.5 text-center">Reorder Level</th>
                        <th class="px-5 py-3.5 text-center">Current Total Stock</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800">
                    @forelse ($stocks as $stock)
                        @php $tot = (int) ($stock->total_stock ?? 0); @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3.5 font-bold text-slate-900">{{ $stock->name }}</td>
                            <td class="px-5 py-3.5 text-xs text-slate-600 font-medium">{{ $stock->category?->name ?? 'General' }}</td>
                            <td class="px-5 py-3.5 text-xs text-slate-500">{{ $stock->manufacturer?->name ?? 'N/A' }}</td>
                            <td class="px-5 py-3.5 text-center font-mono text-xs text-slate-500">{{ $stock->min_stock_alert }} Pcs</td>
                            <td class="px-5 py-3.5 text-center font-bold font-mono text-base {{ $tot <= $stock->min_stock_alert ? 'text-red-600' : 'text-emerald-600' }}">
                                {{ $tot }} {{ $stock->baseUnit?->name ?? 'Pcs' }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if ($tot <= 0)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">Out of Stock</span>
                                @elseif ($tot <= $stock->min_stock_alert)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Low Stock Alert</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">In Stock</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-slate-400">No matching items found in stock.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200">{{ $stocks->links() }}</div>
    </div>
</div>
