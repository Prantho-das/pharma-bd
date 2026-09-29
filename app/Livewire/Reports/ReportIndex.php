<?php

namespace App\Livewire\Reports;

use App\Models\Batch;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Advanced Reports & Analytics - PMS')]
class ReportIndex extends Component
{
    public string $activeTab = 'pnl'; // 'pnl', 'movement', 'narcotics', 'expiry'

    // Date Filters
    public string $dateRange = 'month'; // 'today', 'week', 'month', 'custom'

    public string $startDate = '';

    public string $endDate = '';

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function updatedDateRange()
    {
        if ($this->dateRange === 'today') {
            $this->startDate = now()->format('Y-m-d');
            $this->endDate = now()->format('Y-m-d');
        } elseif ($this->dateRange === 'week') {
            $this->startDate = now()->subDays(7)->format('Y-m-d');
            $this->endDate = now()->format('Y-m-d');
        } elseif ($this->dateRange === 'month') {
            $this->startDate = now()->startOfMonth()->format('Y-m-d');
            $this->endDate = now()->format('Y-m-d');
        }
    }

    public function render()
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        // 1. P&L / COGS Analysis
        $salesQuery = Sale::whereBetween('created_at', [$start, $end])->where('status', '!=', 'CANCELLED');
        $totalRevenue = (float) $salesQuery->sum('grand_total');

        $saleItemCogs = (float) SaleItem::whereHas('sale', function ($q) use ($start, $end) {
            $q->whereBetween('created_at', [$start, $end])->where('status', '!=', 'CANCELLED');
        })->sum(DB::raw('total_pieces * unit_cost'));

        $grossProfit = $totalRevenue - $saleItemCogs;

        $totalExpenses = (float) Expense::whereBetween('date', [$this->startDate, $this->endDate])->sum('amount');
        $netProfit = $grossProfit - $totalExpenses;
        $netMargin = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 2) : 0;

        // Daily P&L breakdown
        $dailyBreakdown = Sale::whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'CANCELLED')
            ->selectRaw('DATE(created_at) as date, SUM(grand_total) as daily_revenue, COUNT(id) as total_invoices')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'desc')
            ->get();

        // 2. Fast Moving / Top Selling Medicines
        $topSelling = SaleItem::select('product_id', DB::raw('SUM(total_pieces) as total_units_sold'), DB::raw('SUM(total_amount) as total_sales_val'))
            ->whereHas('sale', fn ($q) => $q->whereBetween('created_at', [$start, $end])->where('status', '!=', 'CANCELLED'))
            ->groupBy('product_id')
            ->orderByDesc('total_units_sold')
            ->with(['product.generic', 'product.manufacturer'])
            ->take(10)
            ->get();

        // 3. Narcotics / Controlled Substance Register
        $narcotics = SaleItem::whereHas('product', fn ($p) => $p->where('is_narcotic', true))
            ->whereHas('sale', fn ($q) => $q->whereBetween('created_at', [$start, $end])->where('status', '!=', 'CANCELLED'))
            ->with(['sale.customer', 'sale.doctor', 'sale.user', 'product.generic', 'batch'])
            ->latest('id')
            ->get();

        // 4. Expiry Audit & Risk Valuation
        $today = now()->toDateString();
        $in30 = now()->addDays(30)->toDateString();
        $in60 = now()->addDays(60)->toDateString();
        $in90 = now()->addDays(90)->toDateString();

        $expiredBatches = Batch::with(['product'])->where('current_stock', '>', 0)->where('expiry_date', '<', $today)->get();
        $expiring30 = Batch::with(['product'])->where('current_stock', '>', 0)->whereBetween('expiry_date', [$today, $in30])->get();
        $expiring60 = Batch::with(['product'])->where('current_stock', '>', 0)->whereBetween('expiry_date', [$in30, $in60])->get();
        $expiring90 = Batch::with(['product'])->where('current_stock', '>', 0)->whereBetween('expiry_date', [$in60, $in90])->get();

        $totalExpiredVal = $expiredBatches->sum(fn ($b) => $b->current_stock * $b->purchase_rate);
        $totalRisk30Val = $expiring30->sum(fn ($b) => $b->current_stock * $b->purchase_rate);

        return view('livewire.reports.report-index', [
            'totalRevenue' => $totalRevenue,
            'saleItemCogs' => $saleItemCogs,
            'grossProfit' => $grossProfit,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $netProfit,
            'netMargin' => $netMargin,
            'dailyBreakdown' => $dailyBreakdown,
            'topSelling' => $topSelling,
            'narcotics' => $narcotics,
            'expiredBatches' => $expiredBatches,
            'expiring30' => $expiring30,
            'expiring60' => $expiring60,
            'expiring90' => $expiring90,
            'totalExpiredVal' => $totalExpiredVal,
            'totalRisk30Val' => $totalRisk30Val,
        ]);
    }
}
