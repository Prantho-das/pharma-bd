<?php

namespace App\Livewire\Roles;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

#[Layout('layouts.app.sidebar')]
#[Title('Roles & Permission Matrix')]
class RolePermissionManager extends Component
{
    use WithPagination;

    public string $activeTab = 'roles'; // 'roles', 'matrix', 'user_assignment'
    public string $search = '';

    // Role Form
    public ?int $editingRoleId = null;
    public string $roleName = '';
    public array $selectedPermissions = [];
    public bool $showRoleModal = false;

    // User Role Assign Form
    public ?int $selectedUserId = null;
    public string $assignedRole = '';
    public bool $showUserModal = false;

    protected $rules = [
        'roleName' => 'required|string|min:2|max:50',
    ];

    public function openCreateRoleModal(): void
    {
        $this->reset(['editingRoleId', 'roleName', 'selectedPermissions']);
        $this->showRoleModal = true;
    }

    public function editRole(int $id): void
    {
        $role = Role::with('permissions')->findOrFail($id);
        $this->editingRoleId = $role->id;
        $this->roleName = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        $this->showRoleModal = true;
    }

    public function saveRole(): void
    {
        $this->validate();

        if ($this->editingRoleId) {
            $role = Role::findOrFail($this->editingRoleId);
            // Protect Super Admin name change
            if ($role->name === 'Super Admin' && $this->roleName !== 'Super Admin') {
                session()->flash('error', 'Super Admin role name cannot be modified.');
                return;
            }
            $role->update(['name' => trim($this->roleName)]);
        } else {
            $role = Role::firstOrCreate([
                'name' => trim($this->roleName),
                'guard_name' => 'web',
            ]);
        }

        $role->syncPermissions($this->selectedPermissions);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->showRoleModal = false;
        $this->reset(['editingRoleId', 'roleName', 'selectedPermissions']);
        session()->flash('success', 'Role and permissions saved successfully!');
    }

    public function deleteRole(int $id): void
    {
        $role = Role::findOrFail($id);
        if ($role->name === 'Super Admin') {
            session()->flash('error', 'Super Admin role cannot be deleted.');
            return;
        }

        $role->delete();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        session()->flash('success', 'Role deleted successfully!');
    }

    public function togglePermissionMatrix(int $roleId, string $permissionName): void
    {
        $role = Role::findOrFail($roleId);
        if ($role->name === 'Super Admin') {
            session()->flash('error', 'Super Admin inherently maintains all system permissions.');
            return;
        }

        if ($role->hasPermissionTo($permissionName)) {
            $role->revokePermissionTo($permissionName);
        } else {
            $role->givePermissionTo($permissionName);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function openAssignUserModal(int $userId): void
    {
        $user = User::with('roles')->findOrFail($userId);
        $this->selectedUserId = $user->id;
        $this->assignedRole = $user->roles->first()?->name ?? $user->role ?? 'Pharmacist';
        $this->showUserModal = true;
    }

    public function saveUserRole(): void
    {
        if (!$this->selectedUserId || !$this->assignedRole) {
            return;
        }

        $user = User::findOrFail($this->selectedUserId);
        $user->syncRoles([$this->assignedRole]);
        $user->update(['role' => $this->assignedRole]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->showUserModal = false;
        session()->flash('success', "Role for {$user->name} updated to {$this->assignedRole}!");
    }

    public function render()
    {
        $roles = Role::with(['permissions', 'users'])->get();
        $allPermissions = Permission::all()->groupBy(function ($perm) {
            $parts = explode('_', $perm->name);
            return ucfirst($parts[1] ?? $parts[0] ?? 'General');
        });

        // Group permissions logically for UI
        $permissionGroups = [
            'Dashboard' => Permission::whereIn('name', ['view_dashboard'])->get(),
            'POS & Sales' => Permission::whereIn('name', ['view_sales', 'create_sales', 'return_sales'])->get(),
            'Purchases' => Permission::whereIn('name', ['view_purchases', 'create_purchases', 'return_purchases'])->get(),
            'Products & Inventory' => Permission::whereIn('name', [
                'view_products', 'create_products', 'edit_products', 'delete_products',
                'view_expired_products', 'print_barcodes', 'bulk_upload_products',
                'manage_categories', 'manage_units', 'manage_medicine_types', 'manage_manufacturers', 'manage_box_sizes'
            ])->get(),
            'Stock & FEFO' => Permission::whereIn('name', [
                'view_stocks', 'manage_reorder', 'manage_expiry_damage', 'view_batches',
                'manage_stock_adjustments', 'manage_stock_transfers'
            ])->get(),
            'Customers & Doctors' => Permission::whereIn('name', [
                'view_customers', 'manage_customers', 'sms_refill_alerts', 'view_doctors', 'manage_doctors'
            ])->get(),
            'Suppliers' => Permission::whereIn('name', ['view_suppliers', 'manage_suppliers'])->get(),
            'Accounts & Finance' => Permission::whereIn('name', [
                'view_incomes', 'manage_incomes', 'manage_income_categories',
                'view_expenses', 'manage_expenses', 'manage_expense_categories',
                'manage_taxes', 'view_dues', 'manage_cash_registers', 'view_profit_loss'
            ])->get(),
            'HRM' => Permission::whereIn('name', [
                'view_hrm_departments', 'view_hrm_designations', 'view_hrm_shifts',
                'view_hrm_employees', 'manage_hrm_employees',
                'view_hrm_leaves', 'manage_hrm_leaves', 'view_hrm_holidays',
                'view_hrm_attendances', 'manage_hrm_attendances',
                'view_hrm_payrolls', 'manage_hrm_payrolls'
            ])->get(),
            'Reports' => Permission::whereIn('name', [
                'view_sales_reports', 'view_purchase_reports', 'view_income_reports',
                'view_expense_reports', 'view_stock_reports', 'view_customer_ledger',
                'view_supplier_ledger', 'view_due_reports', 'view_loss_profit_reports',
                'view_transaction_reports', 'view_expired_reports'
            ])->get(),
            'System & Settings' => Permission::whereIn('name', [
                'manage_settings', 'manage_roles_permissions'
            ])->get(),
        ];

        $users = User::with('roles')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%"))
            ->paginate(15);

        return view('livewire.roles.role-permission-manager', [
            'roles' => $roles,
            'permissionGroups' => $permissionGroups,
            'users' => $users,
        ]);
    }
}
