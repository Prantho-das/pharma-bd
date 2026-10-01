<div class="p-4 sm:p-6 space-y-6 max-w-7xl mx-auto" x-data="dashboardAnalytics()">
    {{-- 1. Hero Dark Emerald Banner --}}
    <div class="rounded-2xl p-6 text-white shadow-xl relative overflow-hidden" style="background: linear-gradient(135deg, #022c22 0%, #064e3b 50%, #065f46 100%);">
        {{-- Top Info & Actions --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-emerald-700/40">
            <div>
                <span class="text-[10px] font-black uppercase tracking-widest text-emerald-400 bg-emerald-950/60 px-2.5 py-1 rounded-full border border-emerald-700/60">
                    {{ __('DASHBOARD') }}
                </span>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight mt-2">
                    {{ config('app.name', 'Demo Pharmacy') }}
                </h1>
                <p class="text-xs text-emerald-200/80 mt-1 flex items-center gap-1.5 font-medium">
                    <span>{{ __('Welcome back') }}, <strong>{{ auth()->user()->name ?? 'Pharmacy Admin' }}</strong></span>
                    <span>&bull;</span>
                    <span class="font-mono text-emerald-300">{{ now()->format('d/m/Y H:i:s') }}</span>
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('inventory.reorder-shortage') }}" class="px-3.5 py-2 bg-emerald-900/80 hover:bg-emerald-800 text-emerald-200 border border-emerald-700/60 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs" wire:navigate>
                    <span>🔔</span>
                    <span>{{ $lowStockCount + $nearExpiryCount }} {{ __('unread') }}</span>
                </a>
                <a href="{{ route('pos.terminal') }}" class="px-5 py-2.5 bg-white hover:bg-emerald-50 text-emerald-950 rounded-xl text-xs font-black shadow-lg hover:shadow-xl transition flex items-center gap-2 cursor-pointer" wire:navigate>
                    <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span>{{ __('Open POS') }}</span>
                </a>
            </div>
        </div>

        {{-- 4 Embedded Core Metric Cards inside Header --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 pt-6 text-xs">
            {{-- Metric 1: Today --}}
            <div class="bg-emerald-950/50 p-4 rounded-xl border border-emerald-700/50 space-y-1 backdrop-blur-xs">
                <div class="flex items-center justify-between text-emerald-300 font-bold uppercase tracking-wider text-[10px]">
                    <span>{{ __('TODAY') }}</span>
                    @if($salesVsYesterdayPercent >= 0)
                        <span class="text-[10px] font-black text-emerald-300 bg-emerald-900/80 px-1.5 py-0.5 rounded border border-emerald-600/50">
                            &uarr; {{ abs($salesVsYesterdayPercent) }}%
                        </span>
                    @else
                        <span class="text-[10px] font-black text-rose-300 bg-rose-950/80 px-1.5 py-0.5 rounded border border-rose-700/50">
                            &darr; {{ abs($salesVsYesterdayPercent) }}%
                        </span>
                    @endif
                </div>
                <div class="text-xl sm:text-2xl font-black font-mono text-white">৳{{ number_format($todaySales, 2) }}</div>
                <div class="text-[10px] text-emerald-300/70">{{ __('vs yesterday') }}</div>
            </div>

            {{-- Metric 2: Transactions --}}
            <div class="bg-emerald-950/50 p-4 rounded-xl border border-emerald-700/50 space-y-1 backdrop-blur-xs">
                <div class="text-emerald-300 font-bold uppercase tracking-wider text-[10px]">{{ __('TRANSACTIONS') }}</div>
                <div class="text-xl sm:text-2xl font-black font-mono text-white">{{ $todayInvoicesCount }}</div>
                <div class="text-[10px] text-emerald-300/70">{{ $todayUnitsSold }} {{ __('units') }}</div>
            </div>

            {{-- Metric 3: Gross Profit --}}
            <div class="bg-emerald-950/50 p-4 rounded-xl border border-emerald-700/50 space-y-1 backdrop-blur-xs">
                <div class="text-emerald-300 font-bold uppercase tracking-wider text-[10px]">{{ __('GROSS PROFIT') }}</div>
                <div class="text-xl sm:text-2xl font-black font-mono text-white">৳{{ number_format($todayGrossProfit, 2) }}</div>
                <div class="text-[10px] text-emerald-300/70">{{ $profitMargin }}% {{ __('margin') }}</div>
            </div>

            {{-- Metric 4: Avg Basket --}}
            <div class="bg-emerald-950/50 p-4 rounded-xl border border-emerald-700/50 space-y-1 backdrop-blur-xs">
                <div class="text-emerald-300 font-bold uppercase tracking-wider text-[10px]">{{ __('AVG BASKET') }}</div>
                <div class="text-xl sm:text-2xl font-black font-mono text-white">৳{{ number_format($todayAvgBasket, 2) }}</div>
                <div class="text-[10px] text-emerald-300/70">{{ $todayUniqueCustomers }} {{ __('customers today') }}</div>
            </div>
        </div>
    </div>

    {{-- 2. Anomaly Alert Box --}}
    @if($lowStockCount > 0 || $nearExpiryCount > 0 || $expiredCount > 0)
        <div class="bg-amber-50 border border-amber-200 p-4 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center font-bold text-sm shrink-0">
                    🛡️
                </div>
                <div>
                    <h4 class="text-xs font-black text-amber-950">
                        {{ $lowStockCount + $nearExpiryCount }} {{ __('anomalies / risks detected') }}
                    </h4>
                    <p class="text-[11px] text-amber-800">
                        {{ __('Review unusual patterns, stock shortages, or upcoming batch expiries.') }}
                    </p>
                </div>
            </div>
            <a href="{{ route('inventory.reorder-shortage') }}" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-black transition shadow-xs self-start sm:self-auto" wire:navigate>
                {{ __('Review anomalies') }}
            </a>
        </div>
    @endif

    {{-- 3. Business Snapshot Section --}}
    <div class="space-y-3">
        <div class="flex items-center gap-2">
            <span class="w-1.5 h-4 rounded-full bg-emerald-600"></span>
            <h2 class="text-xs font-black uppercase tracking-wider text-slate-700">{{ __('BUSINESS SNAPSHOT') }}</h2>
        </div>

        {{-- Top 5 Snapshot Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 text-xs">
            {{-- Yesterday --}}
            <div class="bg-white p-4 rounded-xl border border-slate-200/90 shadow-xs space-y-1">
                <span class="text-[10px] font-bold text-slate-400 block">{{ __('Yesterday') }}</span>
                <div class="text-base sm:text-lg font-black font-mono text-slate-900">৳{{ number_format($yesterdaySales, 2) }}</div>
                <span class="text-[10px] text-slate-400 block">{{ __('Full day') }}</span>
            </div>

            {{-- Tax (month) --}}
            <div class="bg-white p-4 rounded-xl border border-slate-200/90 shadow-xs space-y-1">
                <span class="text-[10px] font-bold text-slate-400 block">{{ __('Tax (month)') }}</span>
                <div class="text-base sm:text-lg font-black font-mono text-slate-900">৳{{ number_format($monthTax, 2) }}</div>
                <span class="text-[10px] text-slate-400 block">{{ __('Collected on sales') }}</span>
            </div>

            {{-- Expenses (month) --}}
            <div class="bg-white p-4 rounded-xl border border-slate-200/90 shadow-xs space-y-1">
                <span class="text-[10px] font-bold text-slate-400 block">{{ __('Expenses (month)') }}</span>
                <div class="text-base sm:text-lg font-black font-mono text-rose-600">৳{{ number_format($monthExpenses, 2) }}</div>
                <a href="{{ route('expenses.index') }}" class="text-[10px] text-sky-700 font-bold hover:underline block" wire:navigate>{{ __('View ledger') }}</a>
            </div>

            {{-- Est. Net (month) --}}
            <div class="bg-white p-4 rounded-xl border border-slate-200/90 shadow-xs space-y-1">
                <span class="text-[10px] font-bold text-slate-400 block">{{ __('Est. net (month)') }}</span>
                <div class="text-base sm:text-lg font-black font-mono text-emerald-700">৳{{ number_format($monthEstNet, 2) }}</div>
                <span class="text-[10px] text-slate-400 block">{{ __('Revenue - expenses') }}</span>
            </div>

            {{-- Credit sales today --}}
            <div class="bg-white p-4 rounded-xl border border-slate-200/90 shadow-xs space-y-1">
                <span class="text-[10px] font-bold text-slate-400 block">{{ __('Credit sales today') }}</span>
                <div class="text-base sm:text-lg font-black font-mono text-amber-700">৳{{ number_format($todayCreditSales, 2) }}</div>
                <span class="text-[10px] text-slate-400 block">{{ $todayCreditTxnCount }} {{ __('transactions') }}</span>
            </div>
        </div>

        {{-- Bottom 4 Snapshot Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
            {{-- Customers --}}
            <div class="bg-white p-4 rounded-xl border border-slate-200/90 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 block">{{ __('Customers') }}</span>
                    <div class="text-lg font-black text-slate-900 font-mono">{{ $totalCustomers }}</div>
                </div>
                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">+{{ $newCustomersThisMonth }}</span>
            </div>

            {{-- New this month --}}
            <div class="bg-white p-4 rounded-xl border border-slate-200/90 shadow-xs">
                <span class="text-[10px] font-bold text-slate-400 block">{{ __('New this month') }}</span>
                <div class="text-lg font-black text-slate-900 font-mono">{{ $newCustomersThisMonth }}</div>
            </div>

            {{-- AR Balance (Receivable) --}}
            <div class="bg-white p-4 rounded-xl border border-slate-200/90 shadow-xs">
                <span class="text-[10px] font-bold text-slate-400 block">{{ __('AR balance') }}</span>
                <div class="text-lg font-black text-slate-900 font-mono">{{ $customersWithDueCount }}</div>
                <span class="text-[10px] text-slate-400">{{ __('With balance > 0') }}</span>
            </div>

            {{-- Open POs --}}
            <div class="bg-white p-4 rounded-xl border border-slate-200/90 shadow-xs">
                <span class="text-[10px] font-bold text-slate-400 block">{{ __('Open POs') }}</span>
                <div class="text-lg font-black text-slate-900 font-mono">{{ $openPOsCount }}</div>
                <span class="text-[10px] text-slate-400">{{ __('Draft / ordered / partial') }}</span>
            </div>
        </div>
    </div>

    {{-- 4. Revenue & Value Highlights --}}
    <div class="space-y-3">
        <div class="flex items-center gap-2">
            <span class="w-1.5 h-4 rounded-full bg-emerald-600"></span>
            <h2 class="text-xs font-black uppercase tracking-wider text-slate-700">{{ __('REVENUE & VALUE') }}</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs text-white">
            {{-- Year to date --}}
            <div class="p-5 rounded-2xl shadow-sm space-y-1.5" style="background: linear-gradient(135deg, #064e3b 0%, #047857 100%);">
                <div class="flex items-center gap-1.5 text-emerald-200 text-[10px] font-bold uppercase tracking-wider">
                    <span>📅</span>
                    <span>{{ __('Year to date') }}</span>
                </div>
                <div class="text-xl sm:text-2xl font-black font-mono">৳{{ number_format($yearToDateSales, 2) }}</div>
                <span class="text-[10px] text-emerald-200/80 block">{{ __('Since Jan 1') }}</span>
            </div>

            {{-- This month --}}
            <div class="p-5 rounded-2xl shadow-sm space-y-1.5" style="background: linear-gradient(135deg, #047857 0%, #059669 100%);">
                <div class="flex items-center justify-between text-emerald-200 text-[10px] font-bold uppercase tracking-wider">
                    <span class="flex items-center gap-1.5">
                        <span>📊</span>
                        <span>{{ __('This month') }}</span>
                    </span>
                    <span class="text-[9px] bg-emerald-950/60 text-emerald-300 px-1.5 py-0.5 rounded font-black border border-emerald-500/40">
                        &uarr; {{ abs($salesVsLastMonthPercent) }}% {{ __('vs last month') }}
                    </span>
                </div>
                <div class="text-xl sm:text-2xl font-black font-mono">৳{{ number_format($monthSales, 2) }}</div>
                <span class="text-[10px] text-emerald-200/80 block">{{ $monthInvoicesCount }} {{ __('completed orders') }}</span>
            </div>

            {{-- Last 7 days --}}
            <div class="p-5 rounded-2xl shadow-sm space-y-1.5" style="background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%);">
                <div class="flex items-center gap-1.5 text-teal-200 text-[10px] font-bold uppercase tracking-wider">
                    <span>📈</span>
                    <span>{{ __('Last 7 days') }}</span>
                </div>
                <div class="text-xl sm:text-2xl font-black font-mono">৳{{ number_format($last7DaysSales, 2) }}</div>
                <span class="text-[10px] text-teal-200/80 block">{{ __('Sum of completed sales in trend window') }}</span>
            </div>

            {{-- Inventory value --}}
            <div class="p-5 rounded-2xl shadow-sm space-y-1.5" style="background: linear-gradient(135deg, #022c22 0%, #064e3b 100%);">
                <div class="flex items-center gap-1.5 text-emerald-200 text-[10px] font-bold uppercase tracking-wider">
                    <span>📦</span>
                    <span>{{ __('Inventory value') }}</span>
                </div>
                <div class="text-xl sm:text-2xl font-black font-mono">৳{{ number_format($totalStockMrp, 2) }}</div>
                <span class="text-[10px] text-emerald-200/80 block">{{ __('At retail [qty × price]') }} (Cost: ৳{{ number_format($totalStockCost, 2) }})</span>
            </div>
        </div>
    </div>

    {{-- 5. Today's Operations Section --}}
    <div class="space-y-3">
        <div class="flex items-center gap-2">
            <span class="w-1.5 h-4 rounded-full bg-emerald-600"></span>
            <h2 class="text-xs font-black uppercase tracking-wider text-slate-700">{{ __('TODAY\'S OPERATIONS') }}</h2>
        </div>

        {{-- Operations 5 KPIs --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 text-xs">
            <div class="bg-white p-4 rounded-xl border border-slate-200/90 shadow-xs space-y-1">
                <span class="text-[10px] font-bold text-slate-400 block">{{ __('Transactions') }}</span>
                <div class="text-lg font-black text-slate-900 font-mono">{{ $todayInvoicesCount }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200/90 shadow-xs space-y-1">
                <span class="text-[10px] font-bold text-slate-400 block">{{ __('Gross profit') }}</span>
                <div class="text-lg font-black text-slate-900 font-mono">৳{{ number_format($todayGrossProfit, 2) }}</div>
                <span class="text-[10px] text-emerald-600 font-bold">{{ $profitMargin }}% {{ __('margin') }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200/90 shadow-xs space-y-1">
                <span class="text-[10px] font-bold text-slate-400 block">{{ __('Units sold') }}</span>
                <div class="text-lg font-black text-slate-900 font-mono">{{ $todayUnitsSold }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200/90 shadow-xs space-y-1">
                <span class="text-[10px] font-bold text-slate-400 block">{{ __('Avg basket') }}</span>
                <div class="text-lg font-black text-slate-900 font-mono">৳{{ number_format($todayAvgBasket, 2) }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200/90 shadow-xs space-y-1">
                <span class="text-[10px] font-bold text-slate-400 block">{{ __('Customers today') }}</span>
                <div class="text-lg font-black text-slate-900 font-mono">{{ $todayUniqueCustomers }}</div>
                <span class="text-[10px] text-slate-400">{{ __('Unique on receipts') }}</span>
            </div>
        </div>

        {{-- Low Stock / Expiring Status Bar --}}
        <div class="bg-white p-3.5 rounded-xl border border-slate-200/90 shadow-xs grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div class="flex items-center justify-between px-3 py-1 bg-rose-50/50 rounded-lg border border-rose-100">
                <span class="text-rose-900 font-bold text-[11px]">{{ __('Low stock SKUs') }}</span>
                <span class="text-rose-700 font-black font-mono text-sm">{{ $lowStockCount }}</span>
            </div>
            <div class="flex items-center justify-between px-3 py-1 bg-amber-50/50 rounded-lg border border-amber-100">
                <span class="text-amber-900 font-bold text-[11px]">{{ __('Expiring (3 mo)') }}</span>
                <span class="text-amber-700 font-black font-mono text-sm">{{ $nearExpiryCount }}</span>
            </div>
            <div class="flex items-center justify-between px-3 py-1 bg-slate-100 rounded-lg border border-slate-200">
                <span class="text-slate-800 font-bold text-[11px]">{{ __('Expired batches') }}</span>
                <span class="text-slate-900 font-black font-mono text-sm">{{ $expiredCount }}</span>
            </div>
        </div>
    </div>

    {{-- 6. Interactive Charts: Sales Trend & Payment Mix --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Chart 1: Sales Trend (2 Columns Wide) --}}
        <div class="lg:col-span-2 bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-800">{{ __('Sales trend') }}</h3>
                    <p class="text-[11px] text-slate-400">{{ __('Completed sales — last 14 days Revenue + transactions') }}</p>
                </div>
                <a href="{{ route('reports.analytics') }}" class="text-[11px] text-emerald-700 font-bold hover:underline" wire:navigate>{{ __('Full reports') }} &rarr;</a>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>

        {{-- Chart 2: Payment Mix Today (1 Column Wide) --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs space-y-4 flex flex-col justify-between">
            <div>
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-800">{{ __('Payment mix today') }}</h3>
                <p class="text-[11px] text-slate-400">{{ __('By revenue') }}</p>
            </div>
            <div class="relative h-56 w-full flex items-center justify-center">
                <canvas id="paymentMixChart"></canvas>
            </div>
            <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs text-slate-600">
                <span class="font-bold flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                    <span>{{ __('Cash') }}</span>
                </span>
                <span class="font-mono font-bold text-slate-900">৳{{ number_format($paymentData[0] ?? 0, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- 7. AI Insights Row --}}
    <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-sm font-bold">✨</span>
                <div>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-800">{{ __('AI Insights') }}</h3>
                    <p class="text-[11px] text-slate-400">{{ __('Built-in machine intelligence — anomaly detection, demand forecasting, smart reorders.') }}</p>
                </div>
            </div>
            <a href="{{ route('ai.assistant') }}" class="text-xs font-bold text-purple-700 hover:underline flex items-center gap-1" wire:navigate>
                <span>{{ __('View all') }}</span>
                <span>&rarr;</span>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            {{-- Card 1: Anomaly detection --}}
            <div class="p-4 bg-slate-50 hover:bg-purple-50/50 rounded-xl border border-slate-200 transition space-y-2">
                <div class="flex items-center justify-between">
                    <span class="w-6 h-6 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center text-xs">🛡️</span>
                    <span class="text-[9px] font-black text-emerald-800 bg-emerald-100 px-1.5 py-0.5 rounded uppercase">{{ __('Verified') }}</span>
                </div>
                <div class="font-bold text-slate-900">{{ __('Anomaly detection') }}</div>
                <p class="text-[11px] text-slate-500 leading-relaxed">{{ __('Spot unusual returns, dead stock, refund spikes.') }}</p>
            </div>

            {{-- Card 2: Demand forecast --}}
            <div class="p-4 bg-slate-50 hover:bg-purple-50/50 rounded-xl border border-slate-200 transition space-y-2">
                <div class="flex items-center justify-between">
                    <span class="w-6 h-6 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center text-xs">📈</span>
                    <span class="text-[9px] font-black text-sky-800 bg-sky-100 px-1.5 py-0.5 rounded uppercase">{{ __('Live') }}</span>
                </div>
                <div class="font-bold text-slate-900">{{ __('Demand forecast') }}</div>
                <p class="text-[11px] text-slate-500 leading-relaxed">{{ __('Predict per-product demand for the next weeks.') }}</p>
            </div>

            {{-- Card 3: Reorder suggestions --}}
            <div class="p-4 bg-slate-50 hover:bg-purple-50/50 rounded-xl border border-slate-200 transition space-y-2">
                <div class="flex items-center justify-between">
                    <span class="w-6 h-6 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-xs">📦</span>
                    <span class="text-[9px] font-black text-amber-800 bg-amber-100 px-1.5 py-0.5 rounded uppercase">{{ __('Smart') }}</span>
                </div>
                <div class="font-bold text-slate-900">{{ __('Reorder suggestions') }}</div>
                <p class="text-[11px] text-slate-500 leading-relaxed">{{ __('ML-driven PO drafts based on velocity & lead time.') }}</p>
            </div>

            {{-- Card 4: AI assistant --}}
            <a href="{{ route('ai.assistant') }}" class="p-4 bg-slate-50 hover:bg-purple-50/50 rounded-xl border border-slate-200 transition space-y-2 block" wire:navigate>
                <div class="flex items-center justify-between">
                    <span class="w-6 h-6 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs">💬</span>
                    <span class="text-[9px] font-black text-purple-800 bg-purple-100 px-1.5 py-0.5 rounded uppercase">{{ __('Ask') }}</span>
                </div>
                <div class="font-bold text-slate-900">{{ __('AI assistant') }}</div>
                <p class="text-[11px] text-slate-500 leading-relaxed">{{ __('Natural language Q&A across your pharmacy data.') }}</p>
            </a>
        </div>
    </div>

    {{-- 8. Today's sales by hour (24-Hour Bar Chart) --}}
    <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs space-y-4">
        <div>
            <div class="flex items-center gap-1.5 text-xs font-black uppercase tracking-wider text-slate-800">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>{{ __('Today\'s sales by hour') }}</span>
            </div>
            <p class="text-[11px] text-slate-400">{{ __('Revenue per clock hour (Server UTC+6). Use for staffing patterns.') }}</p>
        </div>
        <div class="relative h-48 w-full">
            <canvas id="hourlySalesChart"></canvas>
        </div>
    </div>

    {{-- 9. Top Sellers & Quick Shortcuts Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Top Sellers Table --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-800">{{ __('Top sellers') }}</h3>
                <a href="{{ route('products.index') }}" class="text-[11px] text-emerald-700 font-bold hover:underline" wire:navigate>{{ __('Products') }} &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="border-b border-slate-100 text-[10px] uppercase font-bold text-slate-400">
                        <tr>
                            <th class="py-2.5 px-2">#</th>
                            <th class="py-2.5 px-2">{{ __('PRODUCT') }}</th>
                            <th class="py-2.5 px-2 text-center">{{ __('QTY') }}</th>
                            <th class="py-2.5 px-2 text-right">{{ __('REVENUE') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                        @forelse($topSellers as $idx => $ts)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-2 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                                <td class="py-3 px-2 font-bold text-slate-900">
                                    {{ $ts->product?->name ?? 'N/A' }}
                                    <span class="text-[10px] text-slate-400 block">{{ $ts->product?->sku }}</span>
                                </td>
                                <td class="py-3 px-2 text-center font-mono font-bold">{{ $ts->total_qty }}</td>
                                <td class="py-3 px-2 text-right font-mono font-black text-slate-900">৳{{ number_format($ts->total_revenue, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400">{{ __('No sales completed yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Shortcuts 4x4 Grid --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs space-y-4 flex flex-col justify-between">
            <div>
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-800 mb-3">{{ __('Shortcuts') }}</h3>
                <div class="grid grid-cols-4 gap-2.5 text-center text-[10px]">
                    <a href="{{ route('pos.terminal') }}" class="p-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50 hover:text-emerald-800 border border-slate-200/80 transition flex flex-col items-center gap-1 group" wire:navigate>
                        <span class="text-base group-hover:scale-110 transition">🛒</span>
                        <span class="font-bold">{{ __('POS') }}</span>
                    </a>
                    <a href="{{ route('sales.index') }}" class="p-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50 hover:text-emerald-800 border border-slate-200/80 transition flex flex-col items-center gap-1 group" wire:navigate>
                        <span class="text-base group-hover:scale-110 transition">📋</span>
                        <span class="font-bold">{{ __('Sales History') }}</span>
                    </a>
                    <a href="{{ route('stocks.index') }}" class="p-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50 hover:text-emerald-800 border border-slate-200/80 transition flex flex-col items-center gap-1 group" wire:navigate>
                        <span class="text-base group-hover:scale-110 transition">📦</span>
                        <span class="font-bold">{{ __('Inventory') }}</span>
                    </a>
                    <a href="{{ route('reports.index') }}" class="p-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50 hover:text-emerald-800 border border-slate-200/80 transition flex flex-col items-center gap-1 group" wire:navigate>
                        <span class="text-base group-hover:scale-110 transition">📊</span>
                        <span class="font-bold">{{ __('Reports') }}</span>
                    </a>
                    <a href="{{ route('reports.analytics') }}" class="p-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50 hover:text-emerald-800 border border-slate-200/80 transition flex flex-col items-center gap-1 group" wire:navigate>
                        <span class="text-base group-hover:scale-110 transition">📈</span>
                        <span class="font-bold">{{ __('Analytics') }}</span>
                    </a>
                    <a href="{{ route('expenses.index') }}" class="p-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50 hover:text-emerald-800 border border-slate-200/80 transition flex flex-col items-center gap-1 group" wire:navigate>
                        <span class="text-base group-hover:scale-110 transition">💸</span>
                        <span class="font-bold">{{ __('Expenses') }}</span>
                    </a>
                    <a href="{{ route('customers.index') }}" class="p-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50 hover:text-emerald-800 border border-slate-200/80 transition flex flex-col items-center gap-1 group" wire:navigate>
                        <span class="text-base group-hover:scale-110 transition">👥</span>
                        <span class="font-bold">{{ __('Customers') }}</span>
                    </a>
                    <a href="{{ route('suppliers.index') }}" class="p-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50 hover:text-emerald-800 border border-slate-200/80 transition flex flex-col items-center gap-1 group" wire:navigate>
                        <span class="text-base group-hover:scale-110 transition">🏢</span>
                        <span class="font-bold">{{ __('Suppliers') }}</span>
                    </a>
                    <a href="{{ route('purchase-orders.index') }}" class="p-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50 hover:text-emerald-800 border border-slate-200/80 transition flex flex-col items-center gap-1 group" wire:navigate>
                        <span class="text-base group-hover:scale-110 transition">📑</span>
                        <span class="font-bold">{{ __('Purchases') }}</span>
                    </a>
                    <a href="{{ route('accounts.cash-registers') }}" class="p-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50 hover:text-emerald-800 border border-slate-200/80 transition flex flex-col items-center gap-1 group" wire:navigate>
                        <span class="text-base group-hover:scale-110 transition">💼</span>
                        <span class="font-bold">{{ __('Cash Drawer') }}</span>
                    </a>
                    <a href="{{ route('products.directory') }}" class="p-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50 hover:text-emerald-800 border border-slate-200/80 transition flex flex-col items-center gap-1 group" wire:navigate>
                        <span class="text-base group-hover:scale-110 transition">💊</span>
                        <span class="font-bold">{{ __('Directory') }}</span>
                    </a>
                    <a href="{{ route('customers.sms-refill') }}" class="p-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50 hover:text-emerald-800 border border-slate-200/80 transition flex flex-col items-center gap-1 group" wire:navigate>
                        <span class="text-base group-hover:scale-110 transition">⏰</span>
                        <span class="font-bold">{{ __('SMS Refill') }}</span>
                    </a>
                    <a href="{{ route('inventory.expiry-damage') }}" class="p-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50 hover:text-emerald-800 border border-slate-200/80 transition flex flex-col items-center gap-1 group" wire:navigate>
                        <span class="text-base group-hover:scale-110 transition">⏳</span>
                        <span class="font-bold">{{ __('Expiry Claim') }}</span>
                    </a>
                    <a href="{{ route('inventory.reorder-shortage') }}" class="p-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50 hover:text-emerald-800 border border-slate-200/80 transition flex flex-col items-center gap-1 group" wire:navigate>
                        <span class="text-base group-hover:scale-110 transition">⚡</span>
                        <span class="font-bold">{{ __('Reorder') }}</span>
                    </a>
                    <a href="{{ route('ai.assistant') }}" class="p-2.5 rounded-xl bg-slate-50 hover:bg-purple-50 hover:text-purple-800 border border-slate-200/80 transition flex flex-col items-center gap-1 group" wire:navigate>
                        <span class="text-base group-hover:scale-110 transition">🤖</span>
                        <span class="font-bold">{{ __('AI Assistant') }}</span>
                    </a>
                    <a href="{{ route('settings.manage') }}" class="p-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50 hover:text-emerald-800 border border-slate-200/80 transition flex flex-col items-center gap-1 group" wire:navigate>
                        <span class="text-base group-hover:scale-110 transition">⚙️</span>
                        <span class="font-bold">{{ __('Settings') }}</span>
                    </a>
                </div>
            </div>
            <div class="text-center pt-2 border-t border-slate-100 text-[10px] text-slate-400">
                <span>{{ __('Press') }} <kbd class="px-1 py-0.5 bg-slate-100 border border-slate-300 rounded font-mono text-slate-600">Ctrl + K</kbd> {{ __('for command palette') }}</span>
            </div>
        </div>
    </div>

    {{-- 10. Bottom Row: Recent Sales & Stock & Expiry Lists --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Sales Card --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-800">{{ __('Recent sales') }}</h3>
                <a href="{{ route('sales.index') }}" class="text-[11px] text-emerald-700 font-bold hover:underline" wire:navigate>{{ __('View all') }} &rarr;</a>
            </div>

            <div class="space-y-2 max-h-96 overflow-y-auto pr-1">
                @forelse($recentSales as $sale)
                    <div class="p-3 bg-slate-50 hover:bg-slate-100/80 rounded-xl border border-slate-200/80 space-y-1 transition text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-black font-mono text-slate-900 text-sm">৳{{ number_format($sale->grand_total, 2) }}</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $sale->payment_status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $sale->payment_status }}
                            </span>
                        </div>
                        <div class="text-[11px] text-slate-600 font-medium">
                            @foreach($sale->saleItems->take(3) as $si)
                                <span>{{ $si->quantity }}x {{ $si->product?->name }}@if(!$loop->last), @endif</span>
                            @endforeach
                            @if($sale->saleItems->count() > 3)
                                <span class="text-slate-400">(+{{ $sale->saleItems->count() - 3 }} {{ __('more') }})</span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-slate-400 pt-1 border-t border-slate-200/50">
                            <span>{{ $sale->user?->name ?? 'Admin' }} &bull; {{ $sale->customer?->name ?? 'Walk-in' }}</span>
                            <span class="font-mono">{{ $sale->sale_date ? Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y H:i:s') : 'N/A' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center text-slate-400 text-xs">
                        {{ __('No recent sales transactions.') }}
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Stock & Expiry Alerts List --}}
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-800">{{ __('Stock & expiry') }}</h3>
                <a href="{{ route('inventory.expiry-damage') }}" class="text-[11px] text-emerald-700 font-bold hover:underline" wire:navigate>{{ __('Stock alerts') }} &rarr;</a>
            </div>

            <div class="space-y-2 max-h-96 overflow-y-auto pr-1">
                @forelse($stockAlerts as $batch)
                    <div class="p-3 bg-slate-50 hover:bg-slate-100/80 rounded-xl border border-slate-200/80 flex items-center justify-between transition text-xs">
                        <div class="space-y-0.5">
                            <div class="font-bold text-slate-900">
                                {{ $batch->product?->name ?? 'N/A' }}
                            </div>
                            <div class="text-[11px] text-slate-500 font-medium">
                                {{ __('Stock') }}: <strong class="text-slate-800 font-mono">{{ $batch->current_stock }}</strong> &bull; 
                                {{ __('Reorder') }}: <span class="font-mono">{{ $batch->product?->min_stock_alert ?? 10 }}</span>
                            </div>
                            <div class="text-[10px] text-slate-400 font-mono">
                                {{ __('Expires') }}: {{ Carbon\Carbon::parse($batch->expiry_date)->format('d/m/Y') }}
                            </div>
                        </div>

                        <div>
                            @if(Carbon\Carbon::parse($batch->expiry_date)->isPast())
                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-rose-100 text-rose-800 uppercase">{{ __('Expired') }}</span>
                            @elseif(Carbon\Carbon::parse($batch->expiry_date)->diffInDays(now()) <= 90)
                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-amber-100 text-amber-800 uppercase">{{ __('Expiring') }}</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-rose-50 text-rose-700 border border-rose-200 uppercase">{{ __('Low') }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center text-slate-400 text-xs">
                        {{ __('No stock or expiry alerts currently.') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Chart.js CDN & Alpine Reactive Initialization --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function dashboardAnalytics() {
        return {
            init() {
                this.$nextTick(() => {
                    this.renderSalesTrendChart();
                    this.renderPaymentMixChart();
                    this.renderHourlySalesChart();
                });
            },
            renderSalesTrendChart() {
                const ctx = document.getElementById('salesTrendChart');
                if (!ctx) return;
                
                const labels = @json($trendLabels);
                const sales = @json($trendSales);
                const txns = @json($trendTxns);

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Revenue (৳)',
                                data: sales,
                                borderColor: '#059669',
                                backgroundColor: 'rgba(16, 185, 129, 0.12)',
                                fill: true,
                                tension: 0.4,
                                borderWidth: 3,
                                pointBackgroundColor: '#059669',
                                pointRadius: 3,
                                yAxisID: 'y',
                            },
                            {
                                label: 'Transactions',
                                data: txns,
                                borderColor: '#94a3b8',
                                borderDash: [4, 4],
                                fill: false,
                                tension: 0.3,
                                borderWidth: 2,
                                pointRadius: 2,
                                pointBackgroundColor: '#64748b',
                                yAxisID: 'y1',
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: { position: 'top', labels: { boxWidth: 12, font: { size: 10, weight: 'bold' } } },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        if (context.dataset.yAxisID === 'y') {
                                            return 'Revenue: ৳' + Number(context.raw).toLocaleString();
                                        }
                                        return 'Transactions: ' + context.raw;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                beginAtZero: true,
                                grid: { color: '#f1f5f9' },
                                ticks: { font: { size: 10 } }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                beginAtZero: true,
                                grid: { drawOnChartArea: false },
                                ticks: { font: { size: 10 }, stepSize: 1 }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 10 } }
                            }
                        }
                    }
                });
            },
            renderPaymentMixChart() {
                const ctx = document.getElementById('paymentMixChart');
                if (!ctx) return;

                const labels = @json($paymentLabels);
                const data = @json($paymentData);

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data.some(v => v > 0) ? data : [1],
                            backgroundColor: [
                                '#059669', // Cash
                                '#ec4899', // bKash
                                '#f97316', // Nagad
                                '#0284c7', // Card
                                '#8b5cf6', // Rocket
                                '#f43f5e'  // Due
                            ],
                            borderWidth: 3,
                            borderColor: '#ffffff',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ৳' + Number(context.raw).toLocaleString();
                                    }
                                }
                            }
                        },
                        cutout: '70%'
                    }
                });
            },
            renderHourlySalesChart() {
                const ctx = document.getElementById('hourlySalesChart');
                if (!ctx) return;

                const labels = @json($hourlyLabels);
                const data = @json($hourlyData);

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Hourly Sales (৳)',
                            data: data,
                            backgroundColor: '#059669',
                            hoverBackgroundColor: '#047857',
                            borderRadius: 4,
                            barPercentage: 0.5,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Sales: ৳' + Number(context.raw).toLocaleString();
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 10 } }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f1f5f9' },
                                ticks: { font: { size: 10 } }
                            }
                        }
                    }
                });
            }
        };
    }
</script>
