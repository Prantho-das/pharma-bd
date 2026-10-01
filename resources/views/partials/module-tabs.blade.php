@php
    $currentRoute = request()->route()?->getName() ?? '';
    $currentFullUrl = request()->fullUrl();
    $currentType = request()->query('type');
    $currentTab = request()->query('tab');
    $currentAction = request()->query('action');
    $currentBulk = request()->query('bulk');

    $currentReportType = request()->route('type') ?? request()->query('type', 'sales');

    // Define module configurations
    $modules = [
        'sales' => [
            'matches' => ['sales.*', 'pos.*'],
            'tabs' => [
                ['label' => 'Sale New (POS)', 'route' => 'sales.create', 'icon' => '⚡', 'can' => 'create_sales', 'active' => request()->routeIs('sales.create', 'pos.terminal')],
                ['label' => 'Sale List & Invoices', 'route' => 'sales.index', 'icon' => '📋', 'can' => 'view_sales', 'active' => request()->routeIs('sales.index')],
                ['label' => 'Sales Return', 'route' => 'sales.returns', 'icon' => '🔄', 'can' => 'return_sales', 'active' => request()->routeIs('sales.returns', 'pos.returns')],
            ]
        ],
        'purchases' => [
            'matches' => ['purchases.*', 'purchase-orders.*'],
            'tabs' => [
                ['label' => 'Purchase Orders (PO)', 'route' => 'purchase-orders.index', 'icon' => '📑', 'can' => 'view_purchases', 'active' => request()->routeIs('purchase-orders.index')],
                ['label' => 'Create PO', 'route' => 'purchase-orders.create', 'icon' => '📝', 'can' => 'create_purchases', 'active' => request()->routeIs('purchase-orders.create')],
                ['label' => 'Purchase New (GRN)', 'route' => 'purchases.create', 'icon' => '➕', 'can' => 'create_purchases', 'active' => request()->routeIs('purchases.create')],
                ['label' => 'Purchase List', 'route' => 'purchases.index', 'icon' => '📦', 'can' => 'view_purchases', 'active' => request()->routeIs('purchases.index')],
                ['label' => 'Purchase Return', 'route' => 'purchases.returns', 'icon' => '↩️', 'can' => 'return_purchases', 'active' => request()->routeIs('purchases.returns')],
            ]
        ],
        'products' => [
            'matches' => ['products.*'],
            'tabs' => [
                ['label' => 'All Products', 'route' => 'products.index', 'icon' => '💊', 'can' => 'view_products', 'active' => request()->routeIs('products.index')],
                ['label' => 'Add Product', 'route' => 'products.create', 'icon' => '➕', 'can' => 'create_products', 'active' => request()->routeIs('products.create')],
                ['label' => 'Cloud Directory', 'route' => 'products.directory', 'icon' => '🌐', 'can' => 'create_products', 'active' => request()->routeIs('products.directory')],
                ['label' => 'Generics', 'route' => 'products.generics', 'icon' => '🧪', 'can' => 'manage_categories', 'active' => request()->routeIs('products.generics')],
                ['label' => 'Expired Products', 'route' => 'products.expired', 'icon' => '⚠️', 'can' => 'view_expired_products', 'active' => request()->routeIs('products.expired')],
                ['label' => 'Print Barcodes', 'route' => 'products.barcodes', 'icon' => '🏷️', 'can' => 'print_barcodes', 'active' => request()->routeIs('products.barcodes')],
                ['label' => 'Bulk CSV Upload', 'route' => 'products.bulk-upload', 'icon' => '📤', 'can' => 'bulk_upload_products', 'active' => request()->routeIs('products.bulk-upload')],
                ['label' => 'Categories', 'route' => 'products.categories', 'icon' => '📁', 'can' => 'manage_categories', 'active' => request()->routeIs('products.categories')],
                ['label' => 'Units', 'route' => 'products.units', 'icon' => '📦', 'can' => 'manage_units', 'active' => request()->routeIs('products.units')],
                ['label' => 'Medicine Types', 'route' => 'products.medicine-types', 'icon' => '💊', 'can' => 'manage_medicine_types', 'active' => request()->routeIs('products.medicine-types')],
                ['label' => 'Manufacturers', 'route' => 'products.manufacturers', 'icon' => '🏭', 'can' => 'manage_manufacturers', 'active' => request()->routeIs('products.manufacturers')],
                ['label' => 'Box Sizes', 'route' => 'products.box-sizes', 'icon' => '📐', 'can' => 'manage_box_sizes', 'active' => request()->routeIs('products.box-sizes')],
            ]
        ],
        'stocks' => [
            'matches' => ['stocks.*', 'inventory.*', 'batches.*'],
            'tabs' => [
                ['label' => 'All Stock', 'route' => 'stocks.index', 'icon' => '📊', 'can' => 'view_stocks', 'active' => request()->routeIs('stocks.index')],
                ['label' => 'Reorder & Shortage', 'route' => 'inventory.reorder-shortage', 'icon' => '⚡', 'can' => 'manage_reorder', 'active' => request()->routeIs('inventory.reorder-shortage')],
                ['label' => 'Expiry & Damage Claim', 'route' => 'inventory.expiry-damage', 'icon' => '⏳', 'can' => 'manage_expiry_damage', 'active' => request()->routeIs('inventory.expiry-damage')],
                ['label' => 'Batch Tracking (FEFO)', 'route' => 'batches.index', 'icon' => '🔢', 'can' => 'view_batches', 'active' => request()->routeIs('batches.index')],
                ['label' => 'Stock Adjustment', 'route' => 'inventory.stock-adjustments', 'icon' => '🛠️', 'can' => 'manage_stock_adjustments', 'active' => request()->routeIs('inventory.stock-adjustments')],
                ['label' => 'Stock Transfer', 'route' => 'inventory.transfers', 'icon' => '🚚', 'can' => 'manage_stock_transfers', 'active' => request()->routeIs('inventory.transfers')],
            ]
        ],
        'customers' => [
            'matches' => ['customers.*'],
            'tabs' => [
                ['label' => 'All Customers', 'url' => route('customers.index'), 'icon' => '👥', 'can' => 'view_customers', 'active' => request()->routeIs('customers.index') && !$currentAction && !$currentBulk],
                ['label' => 'SMS & Refill Alerts', 'url' => route('customers.sms-refill'), 'icon' => '📱', 'can' => 'sms_refill_alerts', 'active' => request()->routeIs('customers.sms-refill')],
                ['label' => 'Add Customer', 'url' => route('customers.index').'?action=create', 'icon' => '➕', 'can' => 'manage_customers', 'active' => $currentAction === 'create'],
                ['label' => 'Bulk Upload', 'url' => route('customers.index').'?type=Customer&bulk=1', 'icon' => '📤', 'can' => 'manage_customers', 'active' => (bool)$currentBulk],
            ]
        ],
        'suppliers' => [
            'matches' => ['suppliers.*'],
            'tabs' => [
                ['label' => 'All Suppliers', 'url' => route('suppliers.index'), 'icon' => '🏢', 'can' => 'view_suppliers', 'active' => request()->routeIs('suppliers.index') && !$currentAction && !$currentBulk],
                ['label' => 'Add Supplier', 'url' => route('suppliers.index').'?action=create', 'icon' => '➕', 'can' => 'manage_suppliers', 'active' => $currentAction === 'create'],
                ['label' => 'Bulk Upload', 'url' => route('suppliers.index').'?type=Supplier&bulk=1', 'icon' => '📤', 'can' => 'manage_suppliers', 'active' => (bool)$currentBulk],
            ]
        ],
        'incomes' => [
            'matches' => ['incomes.*'],
            'tabs' => [
                ['label' => 'Income List', 'route' => 'incomes.index', 'icon' => '💰', 'can' => 'view_incomes', 'active' => request()->routeIs('incomes.index')],
                ['label' => 'Income Categories', 'route' => 'incomes.categories', 'icon' => '🏷️', 'can' => 'manage_income_categories', 'active' => request()->routeIs('incomes.categories')],
            ]
        ],
        'expenses' => [
            'matches' => ['expenses.*'],
            'tabs' => [
                ['label' => 'Expense List', 'route' => 'expenses.index', 'icon' => '💸', 'can' => 'view_expenses', 'active' => request()->routeIs('expenses.index')],
                ['label' => 'Expense Categories', 'route' => 'expenses.categories', 'icon' => '🏷️', 'can' => 'manage_expense_categories', 'active' => request()->routeIs('expenses.categories')],
            ]
        ],
        'accounts' => [
            'matches' => ['dues.*', 'accounts.*', 'loss-profits.*'],
            'tabs' => [
                ['label' => 'Total Due List', 'route' => 'dues.index', 'icon' => '📑', 'can' => 'view_dues', 'active' => request()->routeIs('dues.index')],
                ['label' => 'Walk-in Cash Due', 'route' => 'dues.walk', 'icon' => '🚶', 'can' => 'view_dues', 'active' => request()->routeIs('dues.walk')],
                ['label' => 'Shift & Cash Register', 'route' => 'accounts.cash-registers', 'icon' => '💼', 'can' => 'manage_cash_registers', 'active' => request()->routeIs('accounts.cash-registers')],
                ['label' => 'Customer Ledger', 'route' => 'accounts.customer-ledgers', 'icon' => '👥', 'can' => 'view_customer_ledger', 'active' => request()->routeIs('accounts.customer-ledgers')],
                ['label' => 'Supplier Ledger', 'route' => 'accounts.supplier-ledgers', 'icon' => '🏢', 'can' => 'view_supplier_ledger', 'active' => request()->routeIs('accounts.supplier-ledgers')],
                ['label' => 'Profit & Loss', 'route' => 'loss-profits.index', 'icon' => '📈', 'can' => 'view_profit_loss', 'active' => request()->routeIs('loss-profits.index')],
            ]
        ],
        'hrm' => [
            'matches' => ['hrm.*'],
            'tabs' => [
                ['label' => 'Department', 'url' => route('hrm.departments').'?tab=departments', 'icon' => '🏛️', 'can' => 'view_hrm_departments', 'active' => $currentTab === 'departments' || request()->routeIs('hrm.departments')],
                ['label' => 'Designation', 'url' => route('hrm.designations').'?tab=designations', 'icon' => '🎖️', 'can' => 'view_hrm_designations', 'active' => $currentTab === 'designations' || request()->routeIs('hrm.designations')],
                ['label' => 'Shift Roster', 'url' => route('hrm.shifts').'?tab=shifts', 'icon' => '⏱️', 'can' => 'view_hrm_shifts', 'active' => $currentTab === 'shifts' || request()->routeIs('hrm.shifts')],
                ['label' => 'Employees', 'url' => route('hrm.employees').'?tab=employees', 'icon' => '👨‍⚕️', 'can' => 'view_hrm_employees', 'active' => $currentTab === 'employees' || request()->routeIs('hrm.employees', 'hrm.index')],
                ['label' => 'Leave Requests', 'url' => route('hrm.leaves').'?tab=leaves', 'icon' => '🏖️', 'can' => 'view_hrm_leaves', 'active' => $currentTab === 'leaves' || request()->routeIs('hrm.leaves')],
                ['label' => 'Holidays', 'url' => route('hrm.holidays').'?tab=holidays', 'icon' => '📅', 'can' => 'view_hrm_holidays', 'active' => $currentTab === 'holidays' || request()->routeIs('hrm.holidays')],
                ['label' => 'Attendance', 'url' => route('hrm.attendances').'?tab=attendances', 'icon' => '⏱️', 'can' => 'view_hrm_attendances', 'active' => $currentTab === 'attendances' || request()->routeIs('hrm.attendances')],
                ['label' => 'Payroll', 'url' => route('hrm.payrolls').'?tab=payrolls', 'icon' => '💵', 'can' => 'view_hrm_payrolls', 'active' => $currentTab === 'payrolls' || request()->routeIs('hrm.payrolls')],
            ]
        ],
        'reports' => [
            'matches' => ['reports.*'],
            'tabs' => [
                ['label' => 'Sale Report', 'url' => url('/reports/sales'), 'icon' => '📈', 'can' => 'view_sales_reports', 'active' => $currentReportType === 'sales'],
                ['label' => 'Sale Return', 'url' => url('/reports/sale-returns'), 'icon' => '↩️', 'can' => 'view_sales_reports', 'active' => $currentReportType === 'sale-returns'],
                ['label' => 'Purchase Report', 'url' => url('/reports/purchases'), 'icon' => '📑', 'can' => 'view_purchase_reports', 'active' => $currentReportType === 'purchases'],
                ['label' => 'Purchase Return', 'url' => url('/reports/purchase-returns'), 'icon' => '↩️', 'can' => 'view_purchase_reports', 'active' => $currentReportType === 'purchase-returns'],
                ['label' => 'All Income', 'url' => url('/reports/incomes'), 'icon' => '💰', 'can' => 'view_income_reports', 'active' => $currentReportType === 'incomes'],
                ['label' => 'All Expense', 'url' => url('/reports/expenses'), 'icon' => '💸', 'can' => 'view_expense_reports', 'active' => $currentReportType === 'expenses'],
                ['label' => 'Current Stock', 'url' => url('/reports/stocks'), 'icon' => '📦', 'can' => 'view_stock_reports', 'active' => $currentReportType === 'stocks'],
                ['label' => 'Customer Ledger', 'url' => url('/reports/customer-ledger'), 'icon' => '👥', 'can' => 'view_customer_ledger', 'active' => $currentReportType === 'customer-ledger'],
                ['label' => 'Supplier Ledger', 'url' => url('/reports/supplier-ledger'), 'icon' => '🏢', 'can' => 'view_supplier_ledger', 'active' => $currentReportType === 'supplier-ledger'],
                ['label' => 'Customer Due', 'url' => url('/reports/customer-due'), 'icon' => '💳', 'can' => 'view_due_reports', 'active' => $currentReportType === 'customer-due'],
                ['label' => 'Supplier Due', 'url' => url('/reports/supplier-due'), 'icon' => '💼', 'can' => 'view_due_reports', 'active' => $currentReportType === 'supplier-due'],
                ['label' => 'Loss & Profit', 'url' => url('/reports/loss-profit'), 'icon' => '📊', 'can' => 'view_loss_profit_reports', 'active' => $currentReportType === 'loss-profit'],
                ['label' => 'Transaction', 'url' => url('/reports/transactions'), 'icon' => '🔄', 'can' => 'view_transaction_reports', 'active' => $currentReportType === 'transactions'],
                ['label' => 'Expired Product', 'url' => url('/reports/expired'), 'icon' => '⚠️', 'can' => 'view_expired_reports', 'active' => $currentReportType === 'expired'],
            ]
        ],
        'settings' => [
            'matches' => ['settings.*', 'roles.*', 'taxes.*', 'masters.*'],
            'tabs' => [
                ['label' => 'General Settings', 'route' => 'settings.manage', 'icon' => '⚙️', 'can' => 'manage_settings', 'active' => request()->routeIs('settings.manage')],
                ['label' => 'Roles & Permissions', 'route' => 'roles.index', 'icon' => '🛡️', 'can' => 'manage_roles_permissions', 'active' => request()->routeIs('roles.index')],
                ['label' => 'Tax & VAT Configuration', 'route' => 'taxes.index', 'icon' => '🧾', 'can' => 'manage_taxes', 'active' => request()->routeIs('taxes.index')],
            ]
        ],
    ];

    // Find current active module
    $activeModule = null;
    foreach ($modules as $key => $mod) {
        if (request()->routeIs(...$mod['matches'])) {
            $activeModule = $mod;
            break;
        }
    }
@endphp

@if ($activeModule && !empty($activeModule['tabs']))
    <div class="sticky top-0 z-10 bg-white border-b border-slate-200 px-6 py-2 shadow-2xs">
        <div class="max-w-[1700px] mx-auto">
            {{-- Clean Submenu Tabs (No scrollbar, auto-wrapped & instant prefetch) --}}
            <nav 
                class="flex flex-wrap items-center gap-1.5 overflow-x-auto no-scrollbar" 
                style="scrollbar-width: none; -ms-overflow-style: none;"
                aria-label="Sub Navigation Tabs"
            >
                @foreach ($activeModule['tabs'] as $tab)
                    @php
                        $canAccess = !isset($tab['can']) || auth()->user()?->can($tab['can']);
                        $targetUrl = isset($tab['url']) ? $tab['url'] : (isset($tab['route']) && Route::has($tab['route']) ? route($tab['route']) : '#');
                        $isActive = $tab['active'] ?? false;
                    @endphp

                    @if ($canAccess)
                        <a 
                            href="{{ $targetUrl }}" 
                            wire:navigate.hover
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition whitespace-nowrap cursor-pointer select-none {{ $isActive ? 'bg-sky-600 text-white shadow-xs' : 'text-slate-600 bg-slate-100/90 hover:bg-sky-50 hover:text-sky-700 border border-slate-200/70' }}"
                        >
                            <span class="text-xs shrink-0">{{ $tab['icon'] ?? '•' }}</span>
                            <span>{{ __($tab['label']) }}</span>
                        </a>
                    @endif
                @endforeach
            </nav>
        </div>
    </div>
@endif
