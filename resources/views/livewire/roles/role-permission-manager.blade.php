<div class="p-6 space-y-6 max-w-[1600px] mx-auto">
    {{-- Header Banner --}}
    <div class="bg-gradient-to-r from-sky-800 via-sky-700 to-indigo-800 rounded-2xl p-6 text-white shadow-lg flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-white/10 backdrop-blur-md border border-white/20">
                    <svg class="w-6 h-6 text-sky-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </span>
                <div>
                    <h1 class="text-2xl font-black tracking-tight">Role & Permission Control Center</h1>
                    <p class="text-sky-100 text-xs mt-0.5">Define granular access privileges, RBAC rules, and feature visibility for pharmacy staff</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button 
                wire:click="openCreateRoleModal" 
                type="button"
                class="px-4 py-2.5 rounded-xl bg-white text-sky-900 hover:bg-sky-50 font-bold text-xs shadow-md transition flex items-center gap-2 cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Custom Role</span>
            </button>
        </div>
    </div>

    {{-- Session Flash Alerts --}}
    @if (session()->has('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- Tabs Navigation --}}
    <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
        <button 
            wire:click="$set('activeTab', 'roles')" 
            class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer {{ $activeTab === 'roles' ? 'bg-sky-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <span>Role Profiles ({{ $roles->count() }})</span>
        </button>
        <button 
            wire:click="$set('activeTab', 'matrix')" 
            class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer {{ $activeTab === 'matrix' ? 'bg-sky-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <span>Permission Matrix (Grid)</span>
        </button>
        <button 
            wire:click="$set('activeTab', 'user_assignment')" 
            class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer {{ $activeTab === 'user_assignment' ? 'bg-sky-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <span>User Role Assignments</span>
        </button>
    </div>

    {{-- TAB 1: Role Profiles Cards --}}
    @if ($activeTab === 'roles')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($roles as $role)
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full {{ $role->name === 'Super Admin' ? 'bg-amber-500 ring-4 ring-amber-100' : 'bg-sky-500 ring-4 ring-sky-100' }}"></span>
                                <h3 class="font-black text-slate-800 text-base">{{ $role->name }}</h3>
                            </div>
                            <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-[11px] font-bold">
                                {{ $role->users->count() }} Users
                            </span>
                        </div>

                        <div class="mt-4 space-y-2">
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Assigned Privileges</div>
                            <div class="flex flex-wrap gap-1.5 max-h-36 overflow-y-auto pr-1">
                                @if ($role->name === 'Super Admin')
                                    <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 text-xs font-bold">
                                        ⚡ Full System Access (All Permissions)
                                    </span>
                                @else
                                    @forelse ($role->permissions as $perm)
                                        <span class="px-2 py-0.5 rounded-md bg-sky-50 text-sky-800 border border-sky-200 text-[11px] font-medium">
                                            {{ str_replace('_', ' ', $perm->name) }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-400 italic">No permissions assigned yet</span>
                                    @endforelse
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[11px] text-slate-400 font-medium">
                            {{ $role->name === 'Super Admin' ? 'All' : $role->permissions->count() }} total permissions
                        </span>
                        <div class="flex items-center gap-2">
                            <button 
                                wire:click="editRole({{ $role->id }})" 
                                type="button"
                                class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-sky-50 hover:text-sky-700 text-slate-700 text-xs font-bold transition cursor-pointer flex items-center gap-1.5"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                <span>Edit</span>
                            </button>
                            @if ($role->name !== 'Super Admin')
                                <button 
                                    wire:click="deleteRole({{ $role->id }})" 
                                    wire:confirm="Are you sure you want to delete role '{{ $role->name }}'?"
                                    type="button"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                                    title="Delete Role"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- TAB 2: Permission Matrix Grid --}}
    @if ($activeTab === 'matrix')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
                <div>
                    <h2 class="font-bold text-slate-800 text-sm">Interactive Permission Matrix</h2>
                    <p class="text-xs text-slate-500">Click any checkbox to immediately grant or revoke permissions for that role in real-time.</p>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <span class="inline-flex items-center gap-1.5 text-slate-600 font-semibold"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Enabled</span>
                    <span class="inline-flex items-center gap-1.5 text-slate-400 font-semibold"><span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span> Restricted</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-100/80 text-slate-700 font-bold border-b border-slate-200">
                            <th class="p-3.5 pl-5 min-w-[280px]">Permission Feature / Module</th>
                            @foreach ($roles as $role)
                                <th class="p-3.5 text-center min-w-[120px] {{ $role->name === 'Super Admin' ? 'bg-amber-50/50 text-amber-900' : '' }}">
                                    <div class="font-black">{{ $role->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-normal">({{ $role->users->count() }} users)</div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($permissionGroups as $groupTitle => $groupPerms)
                            <tr class="bg-sky-50/60 font-bold text-sky-900 border-t-2 border-sky-100">
                                <td colspan="{{ 1 + $roles->count() }}" class="p-2.5 pl-5 text-xs tracking-wide">
                                    📁 {{ $groupTitle }} Module
                                </td>
                            </tr>
                            @foreach ($groupPerms as $perm)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="p-3 pl-6 font-medium text-slate-800">
                                        <div class="font-bold text-slate-800">{{ str_replace('_', ' ', $perm->name) }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $perm->name }}</div>
                                    </td>
                                    @foreach ($roles as $role)
                                        <td class="p-3 text-center {{ $role->name === 'Super Admin' ? 'bg-amber-50/20' : '' }}">
                                            @if ($role->name === 'Super Admin')
                                                <span class="inline-block p-1 rounded-md bg-amber-100 text-amber-800" title="Super Admin has full access">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                </span>
                                            @else
                                                <input 
                                                    type="checkbox"
                                                    wire:click="togglePermissionMatrix({{ $role->id }}, '{{ $perm->name }}')"
                                                    {{ $role->hasPermissionTo($perm->name) ? 'checked' : '' }}
                                                    class="w-4 h-4 rounded text-sky-600 focus:ring-sky-500 border-slate-300 cursor-pointer"
                                                />
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- TAB 3: User Role Assignments --}}
    @if ($activeTab === 'user_assignment')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
                <div class="w-full md:w-80">
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Search employee / user name or email..." 
                        class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:border-sky-500 shadow-2xs"
                    />
                </div>
                <div class="text-xs text-slate-500 font-medium">
                    Total staff members: {{ $users->total() }}
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-100/80 text-slate-700 font-bold border-b border-slate-200">
                            <th class="p-3.5 pl-5">Staff Member</th>
                            <th class="p-3.5">Email / Phone</th>
                            <th class="p-3.5">Assigned Spatie Role</th>
                            <th class="p-3.5">Branch</th>
                            <th class="p-3.5 text-right pr-5">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($users as $user)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="p-3.5 pl-5 font-bold text-slate-800 flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-sky-100 text-sky-800 font-black flex items-center justify-center text-xs">
                                        {{ $user->initials() }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800">{{ $user->name }}</div>
                                        <div class="text-[10px] text-slate-400">ID #{{ $user->id }}</div>
                                    </div>
                                </td>
                                <td class="p-3.5 text-slate-600 font-medium">
                                    <div>{{ $user->email }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $user->phone ?? 'No phone' }}</div>
                                </td>
                                <td class="p-3.5">
                                    @php
                                        $userRoleName = $user->roles->first()?->name ?? $user->role ?? 'Unassigned';
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $userRoleName === 'Super Admin' ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-sky-50 text-sky-800 border border-sky-200' }}">
                                        {{ $userRoleName }}
                                    </span>
                                </td>
                                <td class="p-3.5 text-slate-600 font-medium">
                                    {{ $user->branch?->name ?? 'Main Branch' }}
                                </td>
                                <td class="p-3.5 text-right pr-5">
                                    <button 
                                        wire:click="openAssignUserModal({{ $user->id }})" 
                                        type="button"
                                        class="px-3 py-1.5 rounded-lg bg-sky-50 text-sky-700 hover:bg-sky-600 hover:text-white font-bold text-xs transition cursor-pointer"
                                    >
                                        Change Role
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-400 text-xs font-medium">
                                    No staff users found matching your search.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-200">
                {{ $users->links() }}
            </div>
        </div>
    @endif

    {{-- MODAL: Create / Edit Role --}}
    @if ($showRoleModal)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200 space-y-5 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="font-black text-slate-800 text-lg">
                        {{ $editingRoleId ? 'Edit Role: ' . $roleName : 'Create New Role' }}
                    </h3>
                    <button wire:click="$set('showRoleModal', false)" type="button" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Role Title / Name</label>
                        <input 
                            type="text" 
                            wire:model="roleName" 
                            placeholder="e.g. Branch Supervisor, Senior Cashier..." 
                            class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-500 focus:outline-none"
                        />
                        @error('roleName') <span class="text-rose-600 text-xs font-bold">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-slate-700">Assign Feature Permissions</label>
                        
                        <div class="space-y-4 max-h-80 overflow-y-auto border border-slate-200 rounded-xl p-3 bg-slate-50/50">
                            @foreach ($permissionGroups as $grpName => $perms)
                                <div class="bg-white rounded-lg p-3 border border-slate-200 space-y-2">
                                    <div class="font-bold text-xs text-sky-800 flex items-center justify-between">
                                        <span>📁 {{ $grpName }}</span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                                        @foreach ($perms as $p)
                                            <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                                                <input 
                                                    type="checkbox" 
                                                    wire:model="selectedPermissions" 
                                                    value="{{ $p->name }}"
                                                    class="rounded text-sky-600 focus:ring-sky-500 border-slate-300"
                                                />
                                                <span>{{ str_replace('_', ' ', $p->name) }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button 
                        wire:click="$set('showRoleModal', false)" 
                        type="button" 
                        class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button 
                        wire:click="saveRole" 
                        type="button" 
                        class="px-5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-md transition cursor-pointer"
                    >
                        Save Role & Permissions
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL: Change User Role --}}
    @if ($showUserModal)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="font-black text-slate-800 text-base">Assign User Role</h3>
                    <button wire:click="$set('showUserModal', false)" type="button" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
                </div>

                <div class="space-y-3">
                    <label class="block text-xs font-bold text-slate-700">Select Role</label>
                    <select 
                        wire:model="assignedRole" 
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white font-medium"
                    >
                        @foreach ($roles as $r)
                            <option value="{{ $r->name }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button 
                        wire:click="$set('showUserModal', false)" 
                        type="button" 
                        class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button 
                        wire:click="saveUserRole" 
                        type="button" 
                        class="px-5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-md transition cursor-pointer"
                    >
                        Apply Role
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
