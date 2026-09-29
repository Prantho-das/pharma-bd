<?php

namespace App\Livewire\Reports;

use App\Models\Batch;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Supplier;
use App\Models\SupplierLedger;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Reports & Business Intelligence - PMS')]
class MasterReportManager extends Component
{
    use WithPagination;

    public string $type = 'sales'; // 'sales', 'sale-returns', 'purchases', 'purchase-returns', 'incomes', 'expenses', 'stocks', 'customer-ledger', 'supplier-ledger', 'customer-due', 'supplier-due', 'loss-profit', 'transactions', 'expired'

    public string $startDate = '';

    public string $endDate = '';

    public int $perPage = 15;

    public function mount(?string $type = null)
    {
        $this->type = $type ?? request()->route('type') ?? request()->query('type', 'sales');
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function render()
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        $data = match ($this->type) {
            'sales' => Sale::with(['customer', 'user'])->whereBetween('created_at', [$start, $end])->latest('id')->paginate($this->perPage),
            'sale-returns' => SaleReturn::with(['customer', 'sale'])->whereBetween('created_at', [$start, $end])->latest('id')->paginate($this->perPage),
            'purchases' => Purchase::with(['supplier'])->whereBetween('created_at', [$start, $end])->latest('id')->paginate($this->perPage),
            'purchase-returns' => PurchaseReturn::with(['supplier'])->whereBetween('created_at', [$start, $end])->latest('id')->paginate($this->perPage),
            'incomes' => Income::with(['category'])->whereBetween('date', [$this->startDate, $this->endDate])->latest('date')->paginate($this->perPage),
            'expenses' => Expense::with(['category'])->whereBetween('date', [$this->startDate, $this->endDate])->latest('date')->paginate($this->perPage),
            'stocks' => Product::with(['baseUnit', 'category'])->withSum('batches as total_stock', 'current_stock')->orderBy('name')->paginate($this->perPage),
            'customer-ledger' => CustomerLedger::with(['customer'])->whereBetween('transaction_date', [$start, $end])->latest('id')->paginate($this->perPage),
            'supplier-ledger' => SupplierLedger::with(['supplier'])->whereBetween('transaction_date', [$start, $end])->latest('id')->paginate($this->perPage),
            'customer-due' => Customer::where('current_due', '>', 0)->orderByDesc('current_due')->paginate($this->perPage),
            'supplier-due' => Supplier::where('current_due', '>', 0)->orderByDesc('current_due')->paginate($this->perPage),
            'loss-profit' => Sale::whereBetween('created_at', [$start, $end])->where('status', '!=', 'CANCELLED')->latest('id')->paginate($this->perPage),
            'transactions' => CustomerLedger::with(['customer'])->whereBetween('transaction_date', [$start, $end])->latest('id')->paginate($this->perPage),
            'expired' => Batch::with(['product'])->where('expiry_date', '<', now()->toDateString())->paginate($this->perPage),
            default => Sale::latest('id')->paginate($this->perPage),
        };

        return view('livewire.reports.master-report-manager', [
            'data' => $data,
        ]);
    }
}
