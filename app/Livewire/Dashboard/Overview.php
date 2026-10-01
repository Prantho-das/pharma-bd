<?php

namespace App\Livewire\Dashboard;

use App\Models\Batch;
use App\Models\CashRegister;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Executive Dashboard - Pharmacy Enterprise PMS')]
class Overview extends Component
{
    public function render()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();
        $startOfYear = Carbon::now()->startOfYear();

        // 1. Today Core Metrics
        $todaySales = (float) Sale::whereDate('sale_date', $today)->sum('grand_total');
        $todayInvoicesCount = Sale::whereDate('sale_date', $today)->count();
        $todayUnitsSold = (int) SaleItem::whereHas('sale', fn ($q) => $q->whereDate('sale_date', $today))->sum('quantity');

        $todaySaleItems = SaleItem::whereHas('sale', fn ($q) => $q->whereDate('sale_date', $today))->get();
        $todayGrossProfit = (float) $todaySaleItems->sum(function ($item) {
            return (float) $item->total_amount - ((float) $item->unit_cost * (int) $item->total_pieces);
        });

        $todayExpenses = (float) Expense::whereDate('date', $today)->sum('amount');
        $todayNetProfit = $todayGrossProfit - $todayExpenses;
        $profitMargin = $todaySales > 0 ? round(($todayGrossProfit / $todaySales) * 100, 1) : 0;
        $todayAvgBasket = $todayInvoicesCount > 0 ? round($todaySales / $todayInvoicesCount, 2) : 0;
        $todayUniqueCustomers = Sale::whereDate('sale_date', $today)->distinct('customer_id')->whereNotNull('customer_id')->count('customer_id');

        // Yesterday vs Today
        $yesterdaySales = (float) Sale::whereDate('sale_date', $yesterday)->sum('grand_total');
        $salesVsYesterdayPercent = $yesterdaySales > 0 ? round((($todaySales - $yesterdaySales) / $yesterdaySales) * 100, 1) : ($todaySales > 0 ? 100 : 0);

        // 2. Month Performance
        $monthSales = (float) Sale::whereBetween('sale_date', [$startOfMonth, $endOfMonth])->sum('grand_total');
        $monthInvoicesCount = Sale::whereBetween('sale_date', [$startOfMonth, $endOfMonth])->count();
        $lastMonthSales = (float) Sale::whereBetween('sale_date', [$startOfLastMonth, $endOfLastMonth])->sum('grand_total');
        $salesVsLastMonthPercent = $lastMonthSales > 0 ? round((($monthSales - $lastMonthSales) / $lastMonthSales) * 100, 1) : 0;

        $monthTax = (float) Sale::whereBetween('sale_date', [$startOfMonth, $endOfMonth])->sum('tax_amount');
        $monthExpenses = (float) Expense::whereBetween('date', [$startOfMonth, $endOfMonth])->sum('amount');
        $monthSaleItems = SaleItem::whereHas('sale', fn ($q) => $q->whereBetween('sale_date', [$startOfMonth, $endOfMonth]))->get();
        $monthGrossProfit = (float) $monthSaleItems->sum(function ($item) {
            return (float) $item->total_amount - ((float) $item->unit_cost * (int) $item->total_pieces);
        });
        $monthEstNet = $monthGrossProfit - $monthExpenses;

        // Credit sales today
        $todayCreditSales = (float) Sale::whereDate('sale_date', $today)->sum('due_amount');
        $todayCreditTxnCount = Sale::whereDate('sale_date', $today)->where('due_amount', '>', 0)->count();

        // 3. Customers & PO Snapshot
        $totalCustomers = Customer::count();
        $newCustomersThisMonth = Customer::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
        $customersWithDueCount = Customer::where('current_due', '>', 0)->count();
        $openPOsCount = PurchaseOrder::whereIn('status', ['ORDERED', 'DRAFT', 'PARTIALLY_RECEIVED'])->count();

        // 4. Revenue & Value Highlights
        $yearToDateSales = (float) Sale::where('sale_date', '>=', $startOfYear)->sum('grand_total');
        $last7DaysSales = (float) Sale::where('sale_date', '>=', Carbon::today()->subDays(6))->sum('grand_total');
        $totalStockCost = (float) Batch::where('current_stock', '>', 0)->sum(DB::raw('current_stock * purchase_rate'));
        $totalStockMrp = (float) Batch::where('current_stock', '>', 0)->sum(DB::raw('current_stock * mrp_rate'));
        $totalMedicinesCount = Product::count();

        // 5. Stock & Expiry Alerts
        $todayStr = $today->toDateString();
        $lowStockQuery = Product::with(['baseUnit'])
            ->where('status', 'active')
            ->whereRaw('(SELECT COALESCE(SUM(current_stock), 0) FROM batches WHERE batches.product_id = products.id AND expiry_date > ?) <= products.min_stock_alert', [$todayStr]);
        $lowStockCount = $lowStockQuery->count();

        $nearExpiryCount = Batch::where('current_stock', '>', 0)
            ->where('expiry_date', '>=', $today)
            ->where('expiry_date', '<=', Carbon::now()->addDays(90))
            ->count();

        $expiredCount = Batch::where('current_stock', '>', 0)
            ->where('expiry_date', '<', $today)
            ->count();

        // 6. Active Shift Status
        $activeShift = CashRegister::where('user_id', Auth::id())
            ->where('status', 'OPEN')
            ->latest('id')
            ->first();

        // 7. Last 14 Days Sales & Transactions Trend (Dual Axis Chart)
        $fourteenDaysAgo = Carbon::today()->subDays(13)->toDateString();
        $salesByDay = Sale::selectRaw('DATE(sale_date) as s_date, SUM(grand_total) as total, COUNT(id) as txn_count')
            ->where('sale_date', '>=', $fourteenDaysAgo)
            ->groupBy('s_date')
            ->get()
            ->keyBy('s_date');

        $trendLabels = [];
        $trendSales = [];
        $trendTxns = [];

        for ($i = 13; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i);
            $dStr = $d->toDateString();
            $trendLabels[] = $d->format('M d');
            $trendSales[] = round((float) ($salesByDay[$dStr]->total ?? 0), 2);
            $trendTxns[] = (int) ($salesByDay[$dStr]->txn_count ?? 0);
        }

        // 8. Payment Method Distribution (Donut Chart)
        $paymentDistribution = SalePayment::select('payment_method', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method')
            ->toArray();

        $paymentLabels = ['Cash', 'bKash', 'Nagad', 'Card', 'Rocket', 'Due'];
        $paymentData = [
            round($paymentDistribution['CASH'] ?? 0, 2),
            round($paymentDistribution['BKASH'] ?? 0, 2),
            round($paymentDistribution['NAGAD'] ?? 0, 2),
            round($paymentDistribution['CARD'] ?? 0, 2),
            round($paymentDistribution['ROCKET'] ?? 0, 2),
            round($paymentDistribution['DUE'] ?? 0, 2),
        ];

        // 9. Today's Hourly Sales (24-Hour Distribution Bar Chart)
        $hourlyBuckets = [
            '0:00' => 0, '2:00' => 0, '4:00' => 0, '6:00' => 0, '8:00' => 0, '10:00' => 0,
            '12:00' => 0, '14:00' => 0, '16:00' => 0, '18:00' => 0, '20:00' => 0, '22:00' => 0,
        ];

        $todaySalesList = Sale::whereDate('sale_date', $today)->get();
        foreach ($todaySalesList as $s) {
            $h = (int) Carbon::parse($s->sale_date)->format('H');
            $bucketKey = (intdiv($h, 2) * 2) . ':00';
            if (isset($hourlyBuckets[$bucketKey])) {
                $hourlyBuckets[$bucketKey] += (float) $s->grand_total;
            }
        }

        $hourlyLabels = array_keys($hourlyBuckets);
        $hourlyData = array_values($hourlyBuckets);

        // 10. Top Sellers Table
        $topSellers = SaleItem::select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(total_amount) as total_revenue'))
            ->groupBy('product_id')
            ->orderByDesc('total_revenue')
            ->with(['product.baseUnit'])
            ->limit(5)
            ->get();

        // 11. Recent Sales with Items
        $recentSales = Sale::with(['customer', 'doctor', 'user', 'saleItems.product'])
            ->latest('sale_date')
            ->limit(6)
            ->get();

        // 12. Combined Stock & Expiry Alerts List
        $stockAlerts = Batch::with(['product.baseUnit'])
            ->where('current_stock', '>', 0)
            ->where(function ($q) use ($today) {
                $q->where('expiry_date', '<=', Carbon::now()->addDays(90))
                  ->orWhereHas('product', function ($pq) {
                      $pq->where('min_stock_alert', '>', 0);
                  });
            })
            ->latest('expiry_date')
            ->limit(6)
            ->get();

        return view('livewire.dashboard.overview', [
            'todaySales' => $todaySales,
            'todayInvoicesCount' => $todayInvoicesCount,
            'todayUnitsSold' => $todayUnitsSold,
            'todayGrossProfit' => $todayGrossProfit,
            'todayExpenses' => $todayExpenses,
            'todayNetProfit' => $todayNetProfit,
            'profitMargin' => $profitMargin,
            'todayAvgBasket' => $todayAvgBasket,
            'todayUniqueCustomers' => $todayUniqueCustomers,
            'yesterdaySales' => $yesterdaySales,
            'salesVsYesterdayPercent' => $salesVsYesterdayPercent,
            'monthSales' => $monthSales,
            'monthInvoicesCount' => $monthInvoicesCount,
            'salesVsLastMonthPercent' => $salesVsLastMonthPercent,
            'monthTax' => $monthTax,
            'monthExpenses' => $monthExpenses,
            'monthEstNet' => $monthEstNet,
            'todayCreditSales' => $todayCreditSales,
            'todayCreditTxnCount' => $todayCreditTxnCount,
            'totalCustomers' => $totalCustomers,
            'newCustomersThisMonth' => $newCustomersThisMonth,
            'customersWithDueCount' => $customersWithDueCount,
            'openPOsCount' => $openPOsCount,
            'yearToDateSales' => $yearToDateSales,
            'last7DaysSales' => $last7DaysSales,
            'totalStockCost' => $totalStockCost,
            'totalStockMrp' => $totalStockMrp,
            'totalMedicinesCount' => $totalMedicinesCount,
            'lowStockCount' => $lowStockCount,
            'nearExpiryCount' => $nearExpiryCount,
            'expiredCount' => $expiredCount,
            'activeShift' => $activeShift,
            'trendLabels' => $trendLabels,
            'trendSales' => $trendSales,
            'trendTxns' => $trendTxns,
            'paymentLabels' => $paymentLabels,
            'paymentData' => $paymentData,
            'hourlyLabels' => $hourlyLabels,
            'hourlyData' => $hourlyData,
            'topSellers' => $topSellers,
            'recentSales' => $recentSales,
            'stockAlerts' => $stockAlerts,
        ]);
    }
}
