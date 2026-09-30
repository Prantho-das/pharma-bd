<?php

use App\Livewire\Accounts\CustomerLedgerTable;
use App\Livewire\Accounts\DailyCashRegisterReport;
use App\Livewire\Accounts\DueListManager;
use App\Livewire\Accounts\ExpenseManager;
use App\Livewire\Accounts\SupplierLedgerTable;
use App\Livewire\Customers\CustomerManager;
use App\Livewire\Customers\SmsRefillManager;
use App\Livewire\Dashboard\Overview;
use App\Livewire\Hrm\HrmManager;
use App\Livewire\Incomes\IncomeManager;
use App\Livewire\Inventory\BarcodeGenerator;
use App\Livewire\Inventory\BatchTable;
use App\Livewire\Inventory\ExpiryDamageManager;
use App\Livewire\Inventory\ReorderShortageManager;
use App\Livewire\Inventory\StockAdjustmentForm;
use App\Livewire\Inventory\StockListManager;
use App\Livewire\Inventory\StockTransferManager;
use App\Livewire\Masters\MasterIndex;
use App\Livewire\Pos\SaleReturnManager;
use App\Livewire\Pos\Terminal;
use App\Livewire\Products\ProductManager;
use App\Livewire\Purchases\PurchaseCreateForm;
use App\Livewire\Purchases\PurchaseReturnManager;
use App\Livewire\Purchases\PurchaseTable;
use App\Livewire\Reports\LossProfitManager;
use App\Livewire\Reports\MasterReportManager;
use App\Livewire\Reports\ReportIndex;
use App\Livewire\Roles\RolePermissionManager;
use App\Livewire\Sales\SaleTable;
use App\Livewire\Settings\ManageSettings;
use App\Livewire\Suppliers\SupplierManager;
use App\Livewire\Taxes\TaxManager;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // 1. Dashboard
    Route::get('/dashboard', Overview::class)->name('dashboard');

    // 2. Sales
    Route::get('/sales/create', Terminal::class)->name('sales.create');
    Route::get('/pos', Terminal::class)->name('pos.terminal');
    Route::get('/sales', SaleTable::class)->name('sales.index');
    Route::get('/sale-returns', SaleReturnManager::class)->name('sales.returns');
    Route::get('/pos/returns', SaleReturnManager::class)->name('pos.returns');

    // 3. Purchases
    Route::get('/purchases/create', PurchaseCreateForm::class)->name('purchases.create');
    Route::get('/purchases', PurchaseTable::class)->name('purchases.index');
    Route::get('/purchase-returns', PurchaseReturnManager::class)->name('purchases.returns');

    // 4. Products
    Route::get('/products', ProductManager::class)->name('products.index');
    Route::get('/products/create', ProductManager::class)->name('products.create');
    Route::get('/expired-products', ProductManager::class)->name('products.expired');
    Route::get('/barcodes', BarcodeGenerator::class)->name('products.barcodes');
    Route::get('/bulk-uploads', ProductManager::class)->name('products.bulk-upload');
    Route::get('/categories', ProductManager::class)->name('products.categories');
    Route::get('/units', ProductManager::class)->name('products.units');
    Route::get('/medicine-types', ProductManager::class)->name('products.medicine-types');
    Route::get('/manufacturers', ProductManager::class)->name('products.manufacturers');
    Route::get('/box-sizes', ProductManager::class)->name('products.box-sizes');
    Route::get('/generics', ProductManager::class)->name('products.generics');

    // 5. Stock List & Transfers
    Route::get('/stocks', StockListManager::class)->name('stocks.index');
    Route::get('/batches', BatchTable::class)->name('batches.index');
    Route::get('/stock-adjustments', StockAdjustmentForm::class)->name('inventory.stock-adjustments');
    Route::get('/stock-transfers', StockTransferManager::class)->name('inventory.transfers');
    Route::get('/inventory/reorder-shortage', ReorderShortageManager::class)->name('inventory.reorder-shortage');
    Route::get('/inventory/expiry-damage', ExpiryDamageManager::class)->name('inventory.expiry-damage');

    // 6. Customers
    Route::get('/customers', CustomerManager::class)->name('customers.index');
    Route::get('/customers/sms-refill', SmsRefillManager::class)->name('customers.sms-refill');

    // 7. Suppliers
    Route::get('/suppliers', SupplierManager::class)->name('suppliers.index');

    // 8. Incomes
    Route::get('/incomes', IncomeManager::class)->name('incomes.index');
    Route::get('/income-categories', IncomeManager::class)->name('incomes.categories');

    // 9. Expenses
    Route::get('/expenses', ExpenseManager::class)->name('expenses.index');
    Route::get('/expense-categories', ExpenseManager::class)->name('expenses.categories');

    // 10. Tax
    Route::get('/taxes', TaxManager::class)->name('taxes.index');

    // 11. Due List
    Route::get('/dues', DueListManager::class)->name('dues.index');
    Route::get('/walk-dues', DueListManager::class)->name('dues.walk');

    // 13. Profit & Loss List
    Route::get('/loss-profits', LossProfitManager::class)->name('loss-profits.index');

    // 14. HRM
    Route::get('/hrm', HrmManager::class)->name('hrm.index');
    Route::get('/hrm/department', HrmManager::class)->name('hrm.departments');
    Route::get('/hrm/designations', HrmManager::class)->name('hrm.designations');
    Route::get('/hrm/shifts', HrmManager::class)->name('hrm.shifts');
    Route::get('/hrm/employees', HrmManager::class)->name('hrm.employees');
    Route::get('/hrm/leave-types', HrmManager::class)->name('hrm.leave-types');
    Route::get('/hrm/leaves', HrmManager::class)->name('hrm.leaves');
    Route::get('/hrm/holidays', HrmManager::class)->name('hrm.holidays');
    Route::get('/hrm/attendances', HrmManager::class)->name('hrm.attendances');
    Route::get('/hrm/payrolls', HrmManager::class)->name('hrm.payrolls');
    Route::get('/hrm/reports', HrmManager::class)->name('hrm.reports');

    // 15. Reports Master Hub
    Route::get('/reports', MasterReportManager::class)->name('reports.index');
    Route::get('/reports/analytics', ReportIndex::class)->name('reports.analytics');
    Route::get('/reports/{type}', MasterReportManager::class)->name('reports.show');

    // 16. Settings & Masters
    Route::get('/manage-settings', ManageSettings::class)->name('settings.manage');
    Route::get('/settings/roles', RolePermissionManager::class)->name('roles.index');
    Route::get('/masters', MasterIndex::class)->name('masters.index');
    Route::get('/accounts/customers', CustomerLedgerTable::class)->name('accounts.customer-ledgers');
    Route::get('/accounts/suppliers', SupplierLedgerTable::class)->name('accounts.supplier-ledgers');
    Route::get('/accounts/cash-registers', DailyCashRegisterReport::class)->name('accounts.cash-registers');
});

require __DIR__.'/settings.php';
