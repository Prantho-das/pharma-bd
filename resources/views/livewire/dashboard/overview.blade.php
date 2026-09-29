<div class="p-6 space-y-6 max-w-7xl mx-auto" x-data="dashboardAnalytics()">
    {{-- Top Welcome & Quick Actions Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <span>Enterprise Pharmacy Dashboard</span>
                <span class="bg-sky-100 text-sky-800 text-xs px-2.5 py-0.5 rounded-full font-bold">Live FEFO Engine</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">রিয়েল-টাইম স্টক ইনভেন্টরি মূল্যায়ন, COGS গ্রস লাভ অ্যানালিটিক্স, বকেয়া ও এক্সপায়ারি ট্র্যাকিং।</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('pos.terminal') }}" 
                class="px-4 py-2.5 text-white rounded-lg text-xs font-black shadow-md hover:shadow-lg transition flex items-center gap-2 cursor-pointer"
                style="background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%) !important; color: #ffffff !important;"
                wire:navigate
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                <span>OPEN POS TERMINAL</span>
            </a>
            <a href="{{ route('inventory.reorder-shortage') }}" 
                class="px-3.5 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 rounded-lg text-xs font-bold shadow-xs transition flex items-center gap-1.5 cursor-pointer"
                wire:navigate
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                <span>Auto Reorder ({{ $lowStockCount }})</span>
            </a>
            <a href="{{ route('inventory.expiry-damage') }}" 
                class="px-3.5 py-2.5 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 rounded-lg text-xs font-bold shadow-xs transition flex items-center gap-1.5 cursor-pointer"
                wire:navigate
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Expiry Claims ({{ $nearExpiryCount }})</span>
            </a>
        </div>
    </div>

    {{-- KPI Metric Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Today's Sales & Month --}}
        <div class="bg-white p-5 rounded-xl border border-slate-200/90 shadow-xs space-y-2">
            <div class="flex items-center justify-between text-xs font-bold text-slate-500">
                <span>আজকের বিক্রয় (Today Sales)</span>
                <span class="flex items-center gap-1 text-[10px] text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                    {{ $todayInvoicesCount }} Invoices
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900 font-mono">৳{{ number_format($todaySales, 2) }}</div>
            <div class="text-[11px] text-slate-500 border-t border-slate-100 pt-1.5 flex justify-between">
                <span>চলতি মাস: <strong>৳{{ number_format($monthSales, 2) }}</strong></span>
                <span class="font-mono text-slate-400">({{ $monthInvoicesCount }} Inv)</span>
            </div>
        </div>

        {{-- Card 2: Gross & Net Profit --}}
        <div class="p-5 rounded-xl text-white shadow-sm space-y-2" style="background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%);">
            <div class="flex items-center justify-between text-xs font-bold text-sky-100">
                <span>আজকের গ্রস লাভ (COGS Profit)</span>
                <span class="text-[10px] bg-sky-900/40 text-sky-100 font-bold px-2 py-0.5 rounded-full border border-sky-300/30">
                    Margin: {{ $profitMargin }}%
                </span>
            </div>
            <div class="text-2xl font-black text-white font-mono">৳{{ number_format($todayGrossProfit, 2) }}</div>
            <div class="text-[11px] text-sky-100/90 border-t border-sky-500/30 pt-1.5 flex justify-between">
                <span>আজকের খরচ: ৳{{ number_format($todayExpenses, 2) }}</span>
                <span>নিট লাভ: <strong>৳{{ number_format($todayNetProfit, 2) }}</strong></span>
            </div>
        </div>

        {{-- Card 3: Stock Inventory Valuation --}}
        <div class="bg-white p-5 rounded-xl border border-slate-200/90 shadow-xs space-y-2">
            <div class="flex items-center justify-between text-xs font-bold text-slate-500">
                <span>মোট ইনভেন্টরি স্টক মূল্য</span>
                <span class="text-[10px] bg-slate-100 text-slate-700 font-bold px-2 py-0.5 rounded border border-slate-200">
                    {{ $totalMedicinesCount }} Items
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900 font-mono">৳{{ number_format($totalStockCost, 2) }}</div>
            <div class="text-[11px] text-slate-500 border-t border-slate-100 pt-1.5 flex justify-between">
                <span>বিক্রয় মূল্য (MRP):</span>
                <strong class="text-sky-700 font-mono">৳{{ number_format($totalStockMrp, 2) }}</strong>
            </div>
        </div>

        {{-- Card 4: Customer Due & Supplier Payables --}}
        <div class="bg-white p-5 rounded-xl border border-slate-200/90 shadow-xs space-y-2">
            <div class="flex items-center justify-between text-xs font-bold text-slate-500">
                <span>গ্রাহক বকেয়া (Receivables)</span>
                <span class="text-[10px] bg-rose-50 text-rose-700 font-bold px-1.5 py-0.5 rounded border border-rose-200">বকেয়া</span>
            </div>
            <div class="text-2xl font-black text-rose-600 font-mono">৳{{ number_format($totalCustomersDue, 2) }}</div>
            <div class="text-[11px] text-slate-500 border-t border-slate-100 pt-1.5 flex justify-between">
                <span>সাপ্লায়ার দেনা (Payables):</span>
                <strong class="text-slate-800 font-mono">৳{{ number_format($totalSuppliersDue, 2) }}</strong>
            </div>
        </div>
    </div>

    {{-- Interactive Analytics Charts Grid (3 Charts) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Chart 1: 7-Day Sales & Profit Trends (2 Columns Wide) --}}
        <div class="lg:col-span-2 bg-white p-5 rounded-xl border border-slate-200/90 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-700">গত ৭ দিনের বিক্রয় ও লাভের ট্রেন্ড (7-Day Sales Trend)</h3>
                    <p class="text-[11px] text-slate-400">দৈনিক মোট সেলস এবং ব্যাচ COGS অনুযায়ী গ্রস লাভের গ্রাফ</p>
                </div>
                <span class="text-xs font-bold text-sky-700 font-mono">Last 7 Days</span>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>

        {{-- Chart 2: Payment Methods Distribution (1 Column Wide) --}}
        <div class="bg-white p-5 rounded-xl border border-slate-200/90 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-700">পেমেন্ট মেথড বিভাজন (Payment Split)</h3>
                    <p class="text-[11px] text-slate-400">ক্যাশ, বিকাশ, নগদ, কার্ড ও বকেয়া</p>
                </div>
            </div>
            <div class="relative h-64 w-full flex items-center justify-center">
                <canvas id="paymentMethodsChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Chart 3 & Top Medicines / Low Stock Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Chart 3: Top 5 Selling Medicines --}}
        <div class="bg-white p-5 rounded-xl border border-slate-200/90 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-700">শীর্ষ বিক্রিত ৫টি ওষুধ (Top Medicines)</h3>
                    <p class="text-[11px] text-slate-400">সর্বোচ্চ পিস বিক্রয়ের তালিকা</p>
                </div>
            </div>
            <div class="relative h-60 w-full">
                <canvas id="topMedicinesChart"></canvas>
            </div>
        </div>

        {{-- Low Stock & Reorder Alerts List --}}
        <div class="bg-white p-5 rounded-xl border border-slate-200/90 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-black uppercase tracking-wider text-amber-800 flex items-center gap-1.5">
                        <span class="w-2 h-2 bg-amber-500 rounded-full animate-ping"></span>
                        <span>শর্টেজ / রি-অর্ডার এলার্ট ({{ $lowStockCount }})</span>
                    </h3>
                    <a href="{{ route('inventory.reorder-shortage') }}" class="text-[11px] text-sky-700 font-bold hover:underline" wire:navigate>সব দেখুন &rarr;</a>
                </div>

                <div class="divide-y divide-slate-100 text-xs">
                    @forelse($topLowStockList as $prod)
                        <div class="py-2.5 flex items-center justify-between">
                            <div>
                                <div class="font-bold text-slate-800">{{ $prod->name }}</div>
                                <div class="text-[10px] text-slate-400">Alert Limit: {{ $prod->min_stock_alert ?? 10 }} {{ $prod->baseUnit?->name ?? 'pcs' }}</div>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 font-mono font-bold text-[11px]">
                                    {{ $prod->totalStockPieces() }} {{ $prod->baseUnit?->name ?? 'pcs' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="py-10 text-center text-slate-400 text-xs">
                            বর্তমানে কোনো ওষুধের স্টক সংকট নেই।
                        </div>
                    @endforelse
                </div>
            </div>

            <a href="{{ route('inventory.reorder-shortage') }}" class="mt-3 block text-center py-2 bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 rounded-lg text-xs font-bold transition cursor-pointer" wire:navigate>
                + ১-ক্লিকে পারচেজ অর্ডার তৈরি করুন
            </a>
        </div>

        {{-- Quick Shift & Expiry Status Card --}}
        <div class="bg-white p-5 rounded-xl border border-slate-200/90 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-700">বর্তমান শিফট ও ঝুঁকি মনিটরিং</h3>
                    <span class="text-[10px] font-mono text-slate-400">{{ now()->format('h:i A') }}</span>
                </div>

                <div class="space-y-3 text-xs">
                    {{-- Active Register --}}
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-700">ক্যাশ কাউন্টার শিফট:</span>
                            @if($activeShift)
                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded text-[10px]">OPEN</span>
                            @else
                                <span class="px-2 py-0.5 bg-amber-100 text-amber-800 font-bold rounded text-[10px]">NO SHIFT OPEN</span>
                            @endif
                        </div>
                        <div class="text-[11px] text-slate-500">
                            ক্যাশিয়ার: <strong>{{ auth()->user()->name ?? 'Pharmacist' }}</strong>
                        </div>
                    </div>

                    {{-- Expiry Alerts --}}
                    <div class="p-3 bg-rose-50 border border-rose-200 rounded-lg space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-rose-900">মেয়াদ ঝুঁকি (&le; ৯০ দিন):</span>
                            <span class="font-mono font-bold text-rose-700 text-sm">{{ $nearExpiryCount }} ব্যাচ</span>
                        </div>
                        <div class="text-[11px] text-rose-700">
                            মেয়াদোত্তীর্ণ লকড: <strong>{{ $expiredCount }} ব্যাচ</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 mt-3">
                <a href="{{ route('accounts.cash-registers') }}" class="text-center py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-lg text-xs font-bold transition cursor-pointer" wire:navigate>
                    শিফট ক্লোজিং রিপোর্ট
                </a>
                <a href="{{ route('customers.sms-refill') }}" class="text-center py-2 bg-sky-50 hover:bg-sky-100 text-sky-800 border border-sky-200 rounded-lg text-xs font-bold transition cursor-pointer" wire:navigate>
                    এসএমএস রিফিল
                </a>
            </div>
        </div>
    </div>

    {{-- Recent Completed POS Sales Table --}}
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-700">সাম্প্রতিক সম্পন্নকৃত বিক্রয় ইনভয়েস (Recent Sales)</h3>
                <p class="text-[11px] text-slate-400">সর্বশেষ ৮টি বিক্রয় লেনদেন ও পেমেন্ট স্ট্যাটাস</p>
            </div>
            <a href="{{ route('sales.index') }}" class="text-xs text-sky-700 font-bold hover:underline" wire:navigate>সকল বিক্রয় ইতিহাস &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-600 font-bold">
                    <tr>
                        <th class="py-3 px-4">ইনভয়েস #</th>
                        <th class="py-3 px-3">তারিখ ও সময়</th>
                        <th class="py-3 px-3">গ্রাহক (Customer)</th>
                        <th class="py-3 px-3">ডাক্তার (Doctor)</th>
                        <th class="py-3 px-3 text-right">মোট বিল (Grand Total)</th>
                        <th class="py-3 px-3 text-right">গৃহীত টাকা (Paid)</th>
                        <th class="py-3 px-3 text-center">পেমেন্ট স্ট্যাটাস</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentSales as $sale)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 font-mono font-bold text-sky-800">{{ $sale->invoice_no }}</td>
                            <td class="py-3 px-3 font-mono text-slate-500">{{ $sale->sale_date ? $sale->sale_date->format('d M, h:i A') : 'N/A' }}</td>
                            <td class="py-3 px-3 font-medium text-slate-800">{{ $sale->customer?->name ?? 'Walk-in Regular' }}</td>
                            <td class="py-3 px-3 text-slate-600">{{ $sale->doctor?->name ?? 'OTC Sale' }}</td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-slate-900">৳{{ number_format($sale->grand_total, 2) }}</td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-emerald-700">৳{{ number_format($sale->paid_amount, 2) }}</td>
                            <td class="py-3 px-3 text-center">
                                <span class="px-2.5 py-0.5 rounded text-[10px] font-bold {{ $sale->payment_status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : ($sale->payment_status === 'PARTIAL' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ $sale->payment_status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">আজকে এখনো কোনো বিক্রয় সম্পন্ন হয়নি।</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
                    this.renderPaymentMethodsChart();
                    this.renderTopMedicinesChart();
                });
            },
            renderSalesTrendChart() {
                const ctx = document.getElementById('salesTrendChart');
                if (!ctx) return;
                
                const labels = @json($sevenDaysLabels);
                const sales = @json($sevenDaysSales);
                const profits = @json($sevenDaysProfits);

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Sales (বিক্রয় ৳)',
                                data: sales,
                                borderColor: '#0284c7',
                                backgroundColor: 'rgba(2, 132, 199, 0.1)',
                                fill: true,
                                tension: 0.35,
                                borderWidth: 2.5,
                                pointBackgroundColor: '#0284c7',
                            },
                            {
                                label: 'Gross Profit (লাভ ৳)',
                                data: profits,
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.05)',
                                fill: true,
                                tension: 0.35,
                                borderWidth: 2,
                                pointBackgroundColor: '#10b981',
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11, weight: 'bold' } } },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ৳' + Number(context.raw).toLocaleString();
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f1f5f9' },
                                ticks: { font: { size: 10 } }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 10 } }
                            }
                        }
                    }
                });
            },
            renderPaymentMethodsChart() {
                const ctx = document.getElementById('paymentMethodsChart');
                if (!ctx) return;

                const labels = @json($paymentLabels);
                const data = @json($paymentData);

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: [
                                '#10b981', // Cash
                                '#ec4899', // bKash
                                '#f97316', // Nagad
                                '#0284c7', // Card
                                '#8b5cf6', // Rocket
                                '#f43f5e'  // Due
                            ],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ৳' + Number(context.raw).toLocaleString();
                                    }
                                }
                            }
                        },
                        cutout: '65%'
                    }
                });
            },
            renderTopMedicinesChart() {
                const ctx = document.getElementById('topMedicinesChart');
                if (!ctx) return;

                const labels = @json($topMedLabels);
                const data = @json($topMedQuantities);

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels.length > 0 ? labels : ['No sales'],
                        datasets: [{
                            label: 'Quantity Sold (পিস)',
                            data: data.length > 0 ? data : [0],
                            backgroundColor: '#38bdf8',
                            hoverBackgroundColor: '#0284c7',
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: {
                            legend: { display: false },
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: '#f1f5f9' },
                                ticks: { font: { size: 10 } }
                            },
                            y: {
                                grid: { display: false },
                                ticks: { font: { size: 10 } }
                            }
                        }
                    }
                });
            }
        };
    }
</script>
