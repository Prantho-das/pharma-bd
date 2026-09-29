<?php

namespace App\Livewire\Dashboard;

use App\Models\Batch;
use App\Models\CashRegister;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
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
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // 1. Today's Core KPIs
        $todaySales = (float) Sale::whereDate('sale_date', $today)->sum('grand_total');
        $todayInvoicesCount = Sale::whereDate('sale_date', $today)->count();

        $todaySaleItems = SaleItem::whereHas('sale', fn ($q) => $q->whereDate('sale_date', $today))->get();
        $todayGrossProfit = (float) $todaySaleItems->sum(function ($item) {
            return (float) $item->total_amount - ((float) $item->unit_cost * (int) $item->total_pieces);
        });

        $todayExpenses = (float) Expense::whereDate('date', $today)->sum('amount');
        $todayNetProfit = $todayGrossProfit - $todayExpenses;
        $profitMargin = $todaySales > 0 ? round(($todayGrossProfit / $todaySales) * 100, 1) : 0;

        // 2. Month-to-Date Performance
        $monthSales = (float) Sale::whereBetween('sale_date', [$startOfMonth, $endOfMonth])->sum('grand_total');
        $monthInvoicesCount = Sale::whereBetween('sale_date', [$startOfMonth, $endOfMonth])->count();

        // 3. Stock & Valuation
        $totalStockCost = (float) Batch::where('current_stock', '>', 0)->sum(DB::raw('current_stock * purchase_rate'));
        $totalStockMrp = (float) Batch::where('current_stock', '>', 0)->sum(DB::raw('current_stock * mrp_rate'));
        $totalMedicinesCount = Product::count();

        // Low stock products
        $allActiveProducts = Product::with(['baseUnit'])->where('status', 'active')->get();
        $lowStockProducts = $allActiveProducts->filter(function ($p) {
            return $p->totalStockPieces() <= ($p->min_stock_alert ?? 10);
        });
        $lowStockCount = $lowStockProducts->count();
        $topLowStockList = $lowStockProducts->take(6);

        // 4. Receivables & Payables
        $totalCustomersDue = (float) Customer::sum('current_due');
        $totalSuppliersDue = (float) Purchase::where('due_amount', '>', 0)->sum('due_amount');

        // 5. Expiry Risk
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
            ->latest()
            ->first();

        // 7. Last 7 Days Sales & Profit Trend (Chart 1)
        $sevenDaysLabels = [];
        $sevenDaysSales = [];
        $sevenDaysProfits = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateStr = $date->toDateString();
            $sevenDaysLabels[] = $date->format('d M');

            $daySales = (float) Sale::whereDate('sale_date', $dateStr)->sum('grand_total');
            $sevenDaysSales[] = round($daySales, 2);

            $dayItems = SaleItem::whereHas('sale', fn ($q) => $q->whereDate('sale_date', $dateStr))->get();
            $dayProfit = (float) $dayItems->sum(function ($item) {
                return (float) $item->total_amount - ((float) $item->unit_cost * (int) $item->total_pieces);
            });
            $sevenDaysProfits[] = round($dayProfit, 2);
        }

        // 8. Payment Method Distribution (Chart 2)
        $paymentDistribution = SalePayment::select('payment_method', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method')
            ->toArray();

        $paymentLabels = ['Cash', 'bKash', 'Nagad', 'Card', 'Rocket', 'Due/Credit'];
        $paymentData = [
            round($paymentDistribution['CASH'] ?? 0, 2),
            round($paymentDistribution['BKASH'] ?? 0, 2),
            round($paymentDistribution['NAGAD'] ?? 0, 2),
            round($paymentDistribution['CARD'] ?? 0, 2),
            round($paymentDistribution['ROCKET'] ?? 0, 2),
            round($paymentDistribution['DUE'] ?? 0, 2),
        ];

        // 9. Top 5 Best Selling Medicines (Chart 3)
        $topMedicines = SaleItem::select('product_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(total_amount) as total_revenue'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->with(['product.baseUnit'])
            ->limit(5)
            ->get();

        $topMedLabels = $topMedicines->map(fn ($m) => $m->product?->name ?? 'Medicine #'.$m->product_id)->toArray();
        $topMedQuantities = $topMedicines->map(fn ($m) => (int) $m->total_sold)->toArray();

        // 10. Recent Sales
        $recentSales = Sale::with(['customer', 'doctor', 'user', 'payments'])
            ->latest('sale_date')
            ->limit(8)
            ->get();

        return view('livewire.dashboard.overview', [
            'todaySales' => $todaySales,
            'todayInvoicesCount' => $todayInvoicesCount,
            'todayGrossProfit' => $todayGrossProfit,
            'todayExpenses' => $todayExpenses,
            'todayNetProfit' => $todayNetProfit,
            'profitMargin' => $profitMargin,
            'monthSales' => $monthSales,
            'monthInvoicesCount' => $monthInvoicesCount,
            'totalStockCost' => $totalStockCost,
            'totalStockMrp' => $totalStockMrp,
            'totalMedicinesCount' => $totalMedicinesCount,
            'lowStockCount' => $lowStockCount,
            'topLowStockList' => $topLowStockList,
            'totalCustomersDue' => $totalCustomersDue,
            'totalSuppliersDue' => $totalSuppliersDue,
            'nearExpiryCount' => $nearExpiryCount,
            'expiredCount' => $expiredCount,
            'activeShift' => $activeShift,
            'sevenDaysLabels' => $sevenDaysLabels,
            'sevenDaysSales' => $sevenDaysSales,
            'sevenDaysProfits' => $sevenDaysProfits,
            'paymentLabels' => $paymentLabels,
            'paymentData' => $paymentData,
            'topMedLabels' => $topMedLabels,
            'topMedQuantities' => $topMedQuantities,
            'recentSales' => $recentSales,
        ]);
    }
}
