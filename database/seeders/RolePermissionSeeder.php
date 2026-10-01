<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // All system permissions grouped by module
        $modules = [
            'Dashboard' => [
                'view_dashboard' => 'View main analytics dashboard',
            ],
            'Sales' => [
                'view_sales' => 'View sales list and invoices',
                'create_sales' => 'Create new sale (POS terminal)',
                'return_sales' => 'Process sales return & refund',
            ],
            'Purchases' => [
                'view_purchases' => 'View purchase list and bills',
                'create_purchases' => 'Create new purchase entry',
                'edit_purchases' => 'Edit purchase bill & details',
                'delete_purchases' => 'Delete purchase & reverse stock',
                'return_purchases' => 'Process purchase return to supplier',
                'manage_purchase_payments' => 'Record multiple split payments for purchases',
            ],
            'Products' => [
                'view_products' => 'View all products list',
                'create_products' => 'Add new product / medicine',
                'edit_products' => 'Edit product details',
                'delete_products' => 'Delete product',
                'view_expired_products' => 'View expired products',
                'print_barcodes' => 'Print medicine barcodes and labels',
                'bulk_upload_products' => 'Bulk CSV import products',
                'manage_categories' => 'Manage product categories',
                'manage_units' => 'Manage packaging units (Strip/Box/Piece)',
                'manage_medicine_types' => 'Manage medicine types / dosage forms',
                'manage_manufacturers' => 'Manage pharmaceutical manufacturers',
                'manage_box_sizes' => 'Manage box sizes & packaging',
            ],
            'Inventory & Stock' => [
                'view_stocks' => 'View current stock inventory',
                'manage_reorder' => 'Manage reorder & shortage predictions',
                'manage_expiry_damage' => 'Manage near expiry & damage claims',
                'view_batches' => 'Track batch numbers and FEFO inventory',
                'manage_stock_adjustments' => 'Create stock adjustments (damage/breakage)',
                'manage_stock_transfers' => 'Transfer stock between branch/store',
            ],
            'Customers & Doctors' => [
                'view_customers' => 'View customer / patient list',
                'manage_customers' => 'Create & edit customer records',
                'sms_refill_alerts' => 'Manage SMS & Chronic refill alerts',
                'view_doctors' => 'View prescribing doctors',
                'manage_doctors' => 'Manage prescribing doctors',
            ],
            'Suppliers' => [
                'view_suppliers' => 'View suppliers list',
                'manage_suppliers' => 'Create & edit supplier profiles',
            ],
            'Incomes' => [
                'view_incomes' => 'View income list',
                'manage_incomes' => 'Add & edit income entries',
                'manage_income_categories' => 'Manage income categories',
            ],
            'Expenses' => [
                'view_expenses' => 'View expense list',
                'manage_expenses' => 'Add & edit expense entries',
                'manage_expense_categories' => 'Manage expense category heads',
            ],
            'Tax' => [
                'manage_taxes' => 'Manage VAT & Tax configurations',
            ],
            'Accounts & Dues' => [
                'view_dues' => 'View customer & walk-in due lists',
                'manage_cash_registers' => 'Open, shift-close & reconcile cash registers',
            ],
            'Profit & Loss' => [
                'view_profit_loss' => 'View profit and loss statement',
            ],
            'HRM' => [
                'view_hrm_departments' => 'View & manage departments',
                'view_hrm_designations' => 'View & manage designations',
                'view_hrm_shifts' => 'View & manage work shifts',
                'view_hrm_employees' => 'View employees list',
                'manage_hrm_employees' => 'Create & manage employee profiles',
                'view_hrm_leaves' => 'View leave applications',
                'manage_hrm_leaves' => 'Approve/reject leave applications',
                'view_hrm_holidays' => 'View & manage holiday calendar',
                'view_hrm_attendances' => 'View attendance logs',
                'manage_hrm_attendances' => 'Punch/record attendance manually',
                'view_hrm_payrolls' => 'View payroll & payslips',
                'manage_hrm_payrolls' => 'Generate & pay employee payroll',
            ],
            'Reports' => [
                'view_sales_reports' => 'View sales & return reports',
                'view_purchase_reports' => 'View purchase & return reports',
                'view_income_reports' => 'View income reports',
                'view_expense_reports' => 'View expense reports',
                'view_stock_reports' => 'View stock reports',
                'view_customer_ledger' => 'View customer ledger',
                'view_supplier_ledger' => 'View supplier ledger',
                'view_due_reports' => 'View due balance reports',
                'view_loss_profit_reports' => 'View profit & loss reports',
                'view_transaction_reports' => 'View transaction & cash flow reports',
                'view_expired_reports' => 'View expired medicine reports',
            ],
            'Settings & Security' => [
                'manage_settings' => 'Manage system and pharmacy setup',
                'manage_roles_permissions' => 'Manage user roles and permission matrix',
            ],
        ];

        // Create or update permissions
        $createdPermissions = [];
        foreach ($modules as $group => $perms) {
            foreach ($perms as $permissionName => $description) {
                $permission = Permission::firstOrCreate(
                    ['name' => $permissionName, 'guard_name' => 'web']
                );
                $createdPermissions[$permissionName] = $permission;
            }
        }

        // 1. Super Admin Role
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::all());

        // 2. Branch Admin / Manager Role
        $managerRole = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $managerRole->syncPermissions([
            'view_dashboard',
            'view_sales', 'create_sales', 'return_sales',
            'view_purchases', 'create_purchases', 'edit_purchases', 'delete_purchases', 'return_purchases', 'manage_purchase_payments',
            'view_products', 'create_products', 'edit_products', 'view_expired_products', 'print_barcodes', 'bulk_upload_products',
            'manage_categories', 'manage_units', 'manage_medicine_types', 'manage_manufacturers', 'manage_box_sizes',
            'view_stocks', 'manage_reorder', 'manage_expiry_damage', 'view_batches', 'manage_stock_adjustments', 'manage_stock_transfers',
            'view_customers', 'manage_customers', 'sms_refill_alerts', 'view_doctors', 'manage_doctors',
            'view_suppliers', 'manage_suppliers',
            'view_incomes', 'manage_incomes', 'manage_income_categories',
            'view_expenses', 'manage_expenses', 'manage_expense_categories',
            'view_dues', 'manage_cash_registers',
            'view_profit_loss',
            'view_hrm_departments', 'view_hrm_designations', 'view_hrm_shifts', 'view_hrm_employees', 'manage_hrm_employees',
            'view_hrm_leaves', 'manage_hrm_leaves', 'view_hrm_holidays', 'view_hrm_attendances', 'manage_hrm_attendances',
            'view_hrm_payrolls', 'manage_hrm_payrolls',
            'view_sales_reports', 'view_purchase_reports', 'view_income_reports', 'view_expense_reports', 'view_stock_reports',
            'view_customer_ledger', 'view_supplier_ledger', 'view_due_reports', 'view_loss_profit_reports', 'view_transaction_reports', 'view_expired_reports',
        ]);

        // 3. Pharmacist / Salesman Role
        $pharmacistRole = Role::firstOrCreate(['name' => 'Pharmacist', 'guard_name' => 'web']);
        $pharmacistRole->syncPermissions([
            'view_dashboard',
            'view_sales', 'create_sales', 'return_sales',
            'view_products', 'print_barcodes', 'view_expired_products',
            'view_stocks', 'manage_reorder', 'view_batches',
            'view_customers', 'manage_customers', 'sms_refill_alerts', 'view_doctors',
            'view_dues', 'manage_cash_registers',
        ]);

        // 4. Accountant Role
        $accountantRole = Role::firstOrCreate(['name' => 'Accountant', 'guard_name' => 'web']);
        $accountantRole->syncPermissions([
            'view_dashboard',
            'view_sales', 'view_purchases', 'manage_purchase_payments',
            'view_incomes', 'manage_incomes', 'manage_income_categories',
            'view_expenses', 'manage_expenses', 'manage_expense_categories',
            'manage_taxes',
            'view_dues', 'manage_cash_registers',
            'view_profit_loss',
            'view_sales_reports', 'view_purchase_reports', 'view_income_reports', 'view_expense_reports',
            'view_customer_ledger', 'view_supplier_ledger', 'view_due_reports', 'view_loss_profit_reports', 'view_transaction_reports',
        ]);

        // 5. Inventory Manager Role
        $inventoryRole = Role::firstOrCreate(['name' => 'Inventory Manager', 'guard_name' => 'web']);
        $inventoryRole->syncPermissions([
            'view_dashboard',
            'view_products', 'create_products', 'edit_products', 'view_expired_products', 'print_barcodes', 'bulk_upload_products',
            'manage_categories', 'manage_units', 'manage_medicine_types', 'manage_manufacturers', 'manage_box_sizes',
            'view_stocks', 'manage_reorder', 'manage_expiry_damage', 'view_batches', 'manage_stock_adjustments', 'manage_stock_transfers',
            'view_purchases', 'create_purchases', 'edit_purchases', 'return_purchases', 'manage_purchase_payments',
            'view_suppliers', 'manage_suppliers',
            'view_stock_reports', 'view_purchase_reports', 'view_supplier_ledger', 'view_expired_reports',
        ]);

        // Assign default Super Admin role to existing users who are super admin or all existing users if none
        $users = User::all();
        foreach ($users as $user) {
            if ($user->isSuperAdmin() || $users->count() === 1) {
                $user->assignRole($superAdminRole);
            } else {
                // Assign role based on user->role column
                $roleName = ucwords(str_replace(['_', '-'], ' ', $user->role ?? 'Pharmacist'));
                $matchingRole = Role::where('name', $roleName)->first() ?? $pharmacistRole;
                $user->assignRole($matchingRole);
            }
        }
    }
}
