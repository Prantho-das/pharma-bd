<?php

namespace App\Livewire\Reports;

use App\Models\Expense;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Profit & Loss List - PMS')]
class LossProfitManager extends Component
{
    public string $startDate = '';

    public string $endDate = '';

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function render()
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        $totalSales = (float) Sale::whereBetween('created_at', [$start, $end])->where('status', '!=', 'CANCELLED')->sum('grand_total');
        $totalCogs = (float) SaleItem::whereHas('sale', fn ($q) => $q->whereBetween('created_at', [$start, $end])->where('status', '!=', 'CANCELLED'))->sum(DB::raw('total_pieces * unit_cost'));
        $grossProfit = $totalSales - $totalCogs;
        $totalExpenses = (float) Expense::whereBetween('date', [$this->startDate, $this->endDate])->sum('amount');
        $netProfit = $grossProfit - $totalExpenses;

        $invoices = Sale::with(['items.product'])
            ->whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'CANCELLED')
            ->latest('id')
            ->paginate(15);

        return view('livewire.reports.loss-profit-manager', [
            'totalSales' => $totalSales,
            'totalCogs' => $totalCogs,
            'grossProfit' => $grossProfit,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $netProfit,
            'sales' => $invoices,
        ]);
    }
}
