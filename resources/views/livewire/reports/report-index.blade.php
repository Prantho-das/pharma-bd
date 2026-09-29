<div class="py-6 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header & Date Controls -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <svg class="w-7 h-7 text-[#00897B]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                </svg>
                Pharmacy Analytics & Audit Reports
            </h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                Real-time Cost of Goods Sold (COGS), P&L statement, drug movement trends, and controlled substance compliance registers.
            </p>
        </div>

        <!-- Date Range Filter -->
        <div class="flex flex-wrap items-center gap-2 bg-white dark:bg-zinc-800 p-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700 shadow-sm">
            <select 
                wire:model.live="dateRange"
                class="px-3 py-1.5 text-xs font-semibold rounded-lg border-zinc-300 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200">
                <option value="today">Today</option>
                <option value="week">Last 7 Days</option>
                <option value="month">This Month</option>
                <option value="custom">Custom Range</option>
            </select>

            @if ($dateRange === 'custom')
                <div class="flex items-center gap-1">
                    <input type="date" wire:model.live="startDate" class="px-2 py-1 text-xs rounded-lg border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200" />
                    <span class="text-xs text-zinc-400">to</span>
                    <input type="date" wire:model.live="endDate" class="px-2 py-1 text-xs rounded-lg border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200" />
                </div>
            @endif

            <button 
                type="button" 
                onclick="window.print()"
                class="px-3 py-1.5 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-700 dark:text-zinc-200 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.077-.44-2.18-.592-3.3m11.744 0c-.152 1.12-.352 2.223-.592 3.3m-10.56 0A23.447 23.447 0 0 0 3 14.25v3.75a2.25 2.25 0 0 0 2.25 2.25h13.5A2.25 2.25 0 0 0 21 18V14.25c-1.026.16-2.072.26-3.13.3m-10.56 0A23.003 23.003 0 0 1 12 14.25c2.052 0 4.043-.223 5.92-.647M12 9V3m0 0 3 3m-3-3-3 3" />
                </svg>
                Print Report
            </button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-zinc-200 dark:border-zinc-700">
        <nav class="flex space-x-6">
            <button 
                wire:click="$set('activeTab', 'pnl')"
                class="py-3 px-1 border-b-2 font-medium text-sm transition-all {{ $activeTab === 'pnl' ? 'border-[#00897B] text-[#00897B] dark:text-sky-400 font-bold' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
                Profit & Loss (P&L) & COGS
            </button>
            <button 
                wire:click="$set('activeTab', 'movement')"
                class="py-3 px-1 border-b-2 font-medium text-sm transition-all {{ $activeTab === 'movement' ? 'border-[#00897B] text-[#00897B] dark:text-sky-400 font-bold' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
                Fast & Slow Moving Items
            </button>
            <button 
                wire:click="$set('activeTab', 'narcotics')"
                class="py-3 px-1 border-b-2 font-medium text-sm transition-all {{ $activeTab === 'narcotics' ? 'border-[#00897B] text-[#00897B] dark:text-sky-400 font-bold' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
                Narcotic & Schedule-H Register
            </button>
            <button 
                wire:click="$set('activeTab', 'expiry')"
                class="py-3 px-1 border-b-2 font-medium text-sm transition-all {{ $activeTab === 'expiry' ? 'border-[#00897B] text-[#00897B] dark:text-sky-400 font-bold' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
                Expiry Risk & Loss Valuation
            </button>
        </nav>
    </div>

    @if ($activeTab === 'pnl')
        <!-- Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Gross Revenue -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg p-5 border border-zinc-200 dark:border-zinc-700 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 block">Total Sales Revenue</span>
                <span class="text-2xl font-black text-zinc-900 dark:text-zinc-100 font-mono mt-1 block">
                    ৳{{ number_format($totalRevenue, 2) }}
                </span>
                <span class="text-xs text-zinc-400 mt-2 block">Billed customer volume</span>
            </div>

            <!-- Total Batch COGS -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg p-5 border border-zinc-200 dark:border-zinc-700 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 block">Cost of Goods Sold (COGS)</span>
                <span class="text-2xl font-black text-amber-600 dark:text-amber-400 font-mono mt-1 block">
                    ৳{{ number_format($saleItemCogs, 2) }}
                </span>
                <span class="text-xs text-zinc-400 mt-2 block">Actual batch purchase cost</span>
            </div>

            <!-- Operating Expenses -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg p-5 border border-zinc-200 dark:border-zinc-700 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 block">Operating Expenses</span>
                <span class="text-2xl font-black text-red-600 dark:text-red-400 font-mono mt-1 block">
                    ৳{{ number_format($totalExpenses, 2) }}
                </span>
                <span class="text-xs text-zinc-400 mt-2 block">Vouchers & utility payouts</span>
            </div>

            <!-- Net Profit -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg p-5 border border-emerald-200 dark:border-emerald-900 shadow-sm bg-gradient-to-br from-sky-600/50 to-blue-700/30 dark:from-sky-600/20 dark:to-blue-700/20">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Net Profit</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">
                        {{ $netMargin }}% Margin
                    </span>
                </div>
                <span class="text-2xl font-black text-emerald-700 dark:text-emerald-400 font-mono mt-1 block">
                    ৳{{ number_format($netProfit, 2) }}
                </span>
                <span class="text-xs text-emerald-600 dark:text-emerald-500 mt-2 block">Gross: ৳{{ number_format($grossProfit, 2) }}</span>
            </div>
        </div>

        <!-- Daily Revenue Table -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/50 font-semibold text-sm text-zinc-900 dark:text-zinc-100">
                Daily Sales & Revenue Timeline
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700 text-xs font-semibold uppercase tracking-wider text-zinc-500 bg-zinc-50/50 dark:bg-zinc-800/50">
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Total Invoices</th>
                            <th class="px-5 py-3 text-right">Daily Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 text-zinc-800 dark:text-zinc-200">
                        @forelse ($dailyBreakdown as $row)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-750 transition-colors">
                                <td class="px-5 py-3 font-medium">
                                    {{ \Carbon\Carbon::parse($row->date)->format('d M Y (D)') }}
                                </td>
                                <td class="px-5 py-3 font-semibold">
                                    {{ $row->total_invoices }}
                                </td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-zinc-900 dark:text-zinc-100">
                                    ৳{{ number_format($row->daily_revenue, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-6 text-center text-zinc-500">
                                    No sales data found for the selected period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($activeTab === 'movement')
        <!-- Fast Moving Medicines Table -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/50 font-semibold text-sm text-zinc-900 dark:text-zinc-100">
                Top 10 Fast-Moving Medicines (Volume & Revenue)
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700 text-xs font-semibold uppercase tracking-wider text-zinc-500 bg-zinc-50/50 dark:bg-zinc-800/50">
                            <th class="px-5 py-3">Medicine Name</th>
                            <th class="px-5 py-3">Generic Formulation</th>
                            <th class="px-5 py-3">Manufacturer</th>
                            <th class="px-5 py-3 text-center">Total Units Sold (Pcs)</th>
                            <th class="px-5 py-3 text-right">Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 text-zinc-800 dark:text-zinc-200">
                        @forelse ($topSelling as $idx => $ts)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-750 transition-colors">
                                <td class="px-5 py-3.5 font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-sky-100 dark:bg-sky-950 text-[#00897B] dark:text-sky-400 font-bold text-xs flex items-center justify-center">
                                        {{ $idx + 1 }}
                                    </span>
                                    {{ $ts->product->name }}
                                </td>
                                <td class="px-5 py-3.5 text-xs text-zinc-600 dark:text-zinc-400">
                                    {{ $ts->product->generic?->name ?? 'N/A' }}
                                </td>
                                <td class="px-5 py-3.5 text-xs text-zinc-600 dark:text-zinc-400">
                                    {{ $ts->product->manufacturer?->name ?? 'N/A' }}
                                </td>
                                <td class="px-5 py-3.5 text-center font-bold text-[#00897B] dark:text-sky-400 font-mono">
                                    {{ number_format($ts->total_units_sold) }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono font-bold text-zinc-900 dark:text-zinc-100">
                                    ৳{{ number_format($ts->total_sales_val, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-6 text-center text-zinc-500">
                                    No item sales recorded in this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($activeTab === 'narcotics')
        <!-- Narcotics & Schedule-H Register -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm space-y-4">
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 bg-red-50/50 dark:bg-red-950/20 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-red-900 dark:text-red-300 text-sm">Controlled Substances & Narcotic Register Log</h3>
                    <p class="text-xs text-red-700/80 dark:text-red-400/80">Regulatory record for Schedule-H / Sedative drug administration compliance</p>
                </div>
                <span class="px-3 py-1 bg-red-600 text-white font-bold text-xs rounded-full">
                    {{ count($narcotics) }} Dispensed Entry(s)
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700 text-xs font-semibold uppercase tracking-wider text-zinc-500 bg-zinc-50/50 dark:bg-zinc-800/50">
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Invoice No</th>
                            <th class="px-4 py-3">Medicine (Schedule-H)</th>
                            <th class="px-4 py-3">Batch</th>
                            <th class="px-4 py-3">Patient / Customer</th>
                            <th class="px-4 py-3">Prescribing Doctor</th>
                            <th class="px-4 py-3 text-center">Qty Dispensed</th>
                            <th class="px-4 py-3">Dispensed By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 text-zinc-800 dark:text-zinc-200">
                        @forelse ($narcotics as $nItem)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-750 transition-colors">
                                <td class="px-4 py-3 text-xs text-zinc-500">
                                    {{ $nItem->created_at->format('d/m/Y h:i A') }}
                                </td>
                                <td class="px-4 py-3 font-mono font-bold text-[#00897B] dark:text-sky-400">
                                    {{ $nItem->sale->invoice_no }}
                                </td>
                                <td class="px-4 py-3 font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $nItem->product->name }}
                                    <span class="text-xs text-zinc-400 block">{{ $nItem->product->generic?->name }}</span>
                                </td>
                                <td class="px-4 py-3 font-mono text-xs">
                                    {{ $nItem->batch?->batch_number ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-medium text-xs block">{{ $nItem->sale->customer?->name ?? 'Direct Counter' }}</span>
                                    <span class="text-xs text-zinc-400">{{ $nItem->sale->customer?->phone ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-medium text-xs block">{{ $nItem->sale->doctor?->name ?? 'External Doctor' }}</span>
                                    <span class="text-xs text-zinc-400 font-mono">BMDC: {{ $nItem->sale->doctor?->bmdc_reg_no ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-red-600 dark:text-red-400 font-mono">
                                    {{ $nItem->total_pieces }} Pcs
                                </td>
                                <td class="px-4 py-3 text-xs text-zinc-500">
                                    {{ $nItem->sale->user?->name ?? 'Cashier' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-zinc-500">
                                    No narcotic or Schedule-H drug sales recorded in this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($activeTab === 'expiry')
        <!-- Expiry Risk & Loss Valuation -->
        <div class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-red-50 dark:bg-red-950/40 p-5 rounded-lg border border-red-200 dark:border-red-900">
                    <span class="text-xs font-bold uppercase tracking-wider text-red-700 dark:text-red-300">Expired Batches</span>
                    <span class="text-2xl font-black text-red-700 dark:text-red-400 block mt-1 font-mono">{{ count($expiredBatches) }} Batches</span>
                    <span class="text-xs text-red-600 mt-2 block">Total Loss: ৳{{ number_format($totalExpiredVal, 2) }}</span>
                </div>

                <div class="bg-amber-50 dark:bg-amber-950/40 p-5 rounded-lg border border-amber-200 dark:border-amber-900">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300">Expiring in 30 Days</span>
                    <span class="text-2xl font-black text-amber-700 dark:text-amber-400 block mt-1 font-mono">{{ count($expiring30) }} Batches</span>
                    <span class="text-xs text-amber-600 mt-2 block">Risk Value: ৳{{ number_format($totalRisk30Val, 2) }}</span>
                </div>

                <div class="bg-yellow-50 dark:bg-yellow-950/40 p-5 rounded-lg border border-yellow-200 dark:border-yellow-900">
                    <span class="text-xs font-bold uppercase tracking-wider text-yellow-700 dark:text-yellow-300">Expiring in 60 Days</span>
                    <span class="text-2xl font-black text-yellow-700 dark:text-yellow-400 block mt-1 font-mono">{{ count($expiring60) }} Batches</span>
                </div>

                <div class="bg-sky-50 dark:bg-sky-950/40 p-5 rounded-lg border border-sky-200 dark:border-sky-900">
                    <span class="text-xs font-bold uppercase tracking-wider text-sky-700 dark:text-sky-300">Expiring in 90 Days</span>
                    <span class="text-2xl font-black text-sky-700 dark:text-sky-400 block mt-1 font-mono">{{ count($expiring90) }} Batches</span>
                </div>
            </div>

            <!-- Expired Batches Detail -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden shadow-sm">
                <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/50 font-semibold text-sm text-zinc-900 dark:text-zinc-100">
                    Expired & High-Risk Batches Inventory
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700 text-xs font-semibold uppercase tracking-wider text-zinc-500 bg-zinc-50/50 dark:bg-zinc-800/50">
                                <th class="px-5 py-3">Medicine</th>
                                <th class="px-5 py-3">Batch Number</th>
                                <th class="px-5 py-3">Expiry Date</th>
                                <th class="px-5 py-3">Remaining Stock</th>
                                <th class="px-5 py-3">Purchase Rate</th>
                                <th class="px-5 py-3 text-right">Potential Loss</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 text-zinc-800 dark:text-zinc-200">
                            @forelse ($expiredBatches->merge($expiring30) as $b)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-750 transition-colors">
                                    <td class="px-5 py-3 font-semibold text-zinc-900 dark:text-zinc-100">
                                        {{ $b->product->name }}
                                    </td>
                                    <td class="px-5 py-3 font-mono text-xs font-bold">
                                        {{ $b->batch_number }}
                                    </td>
                                    <td class="px-5 py-3 text-xs font-bold {{ $b->expiry_date < now() ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400' }}">
                                        {{ \Carbon\Carbon::parse($b->expiry_date)->format('d M Y') }}
                                        ({{ \Carbon\Carbon::parse($b->expiry_date)->diffForHumans() }})
                                    </td>
                                    <td class="px-5 py-3 font-bold font-mono">
                                        {{ $b->current_stock }} Pcs
                                    </td>
                                    <td class="px-5 py-3 font-mono text-xs">
                                        ৳{{ number_format($b->purchase_rate, 2) }}
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono font-bold text-red-600 dark:text-red-400">
                                        ৳{{ number_format($b->current_stock * $b->purchase_rate, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-6 text-center text-zinc-500">
                                        No expired or 30-day risk batches found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
