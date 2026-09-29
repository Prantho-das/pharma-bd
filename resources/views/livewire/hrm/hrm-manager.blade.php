<div class="py-6 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                <svg class="w-7 h-7 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                </svg>
                Human Resource Management (HRM)
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Employee database, shifts, biometric/manual attendance, leave approvals, and monthly salary payrolls.
            </p>
        </div>
    </div>

    @if (session('success'))
        <div class="p-3.5 rounded-lg bg-emerald-50 text-emerald-800 text-xs font-semibold flex items-center gap-2 border border-emerald-200 shadow-xs">
            <svg class="w-4 h-4 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($tab === 'employees')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-xs space-y-3.5">
                <h3 class="font-bold text-sm text-slate-900 border-b border-slate-100 pb-2">Register Employee</h3>
                <form wire:submit="saveEmployee" class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Full Name <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="employeeName" placeholder="Employee Name" class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-slate-300 bg-white text-slate-800 placeholder:text-slate-400 focus:border-sky-600 focus:ring-2 focus:ring-sky-500/15 outline-none shadow-xs" />
                        @error('employeeName') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Phone Number <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="employeePhone" placeholder="017xxxxxxxx" class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-slate-300 bg-white text-slate-800 placeholder:text-slate-400 focus:border-sky-600 focus:ring-2 focus:ring-sky-500/15 outline-none shadow-xs" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Department</label>
                        <select wire:model="departmentId" class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-slate-300 bg-white text-slate-800 focus:border-sky-600 focus:ring-2 focus:ring-sky-500/15 outline-none shadow-xs">
                            <option value="">-- Choose Department --</option>
                            @foreach ($departments as $d) <option value="{{ $d->id }}">{{ $d->name }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Designation</label>
                        <select wire:model="designationId" class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-slate-300 bg-white text-slate-800 focus:border-sky-600 focus:ring-2 focus:ring-sky-500/15 outline-none shadow-xs">
                            <option value="">-- Choose Designation --</option>
                            @foreach ($designations as $des) <option value="{{ $des->id }}">{{ $des->name }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Shift</label>
                        <select wire:model="shiftId" class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-slate-300 bg-white text-slate-800 focus:border-sky-600 focus:ring-2 focus:ring-sky-500/15 outline-none shadow-xs">
                            <option value="">-- Choose Shift --</option>
                            @foreach ($shifts as $s) <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->start_time }}-{{ $s->end_time }})</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Basic Monthly Salary (৳)</label>
                        <input type="number" step="0.01" wire:model="salary" placeholder="0.00" class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-slate-300 bg-white text-slate-900 font-bold focus:border-sky-600 focus:ring-2 focus:ring-sky-500/15 outline-none shadow-xs" />
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg text-xs transition shadow-xs cursor-pointer">
                        Save Employee
                    </button>
                </form>
            </div>

            <div class="lg:col-span-2 bg-white rounded-lg border border-slate-200 overflow-hidden shadow-xs">
                <div class="p-3.5 border-b border-slate-200 bg-slate-50 font-bold text-slate-800 text-xs">Staff Directory</div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500 bg-slate-50">
                                <th class="px-4 py-3">Code / Name</th>
                                <th class="px-4 py-3">Phone</th>
                                <th class="px-4 py-3">Dept & Designation</th>
                                <th class="px-4 py-3 text-right">Salary</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-800">
                            @forelse ($employees as $emp)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-4 py-3 font-bold text-slate-900">
                                        {{ $emp->name }}
                                        <span class="block text-[10px] font-mono text-slate-400 font-normal">{{ $emp->employee_code }}</span>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-slate-600">{{ $emp->phone }}</td>
                                    <td class="px-4 py-3">
                                        <span class="font-semibold text-slate-800 block">{{ $emp->designation?->name ?? 'Staff' }}</span>
                                        <span class="text-slate-500 text-[11px]">{{ $emp->department?->name ?? 'Pharmacy' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-slate-900">৳{{ number_format($emp->salary, 2) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">{{ $emp->status }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">No employees registered yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3.5 border-t border-slate-200">{{ $employees->links() }}</div>
            </div>
        </div>
    @endif

    @if (in_array($tab, ['departments', 'designations', 'shifts', 'holidays']))
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-xs space-y-3.5">
                <h3 class="font-bold text-sm text-slate-900 border-b border-slate-100 pb-2">Add {{ ucfirst(rtrim($tab, 's')) }}</h3>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Title / Name</label>
                    <input type="text" wire:model="masterTitle" placeholder="Enter title..." />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Details / Note</label>
                    <input type="text" wire:model="masterNote" placeholder="Details or notes..." />
                </div>
                <button type="button" wire:click="saveMaster('{{ $tab === 'departments' ? 'dept' : ($tab === 'designations' ? 'desig' : ($tab === 'shifts' ? 'shift' : 'holiday')) }}')" class="w-full py-2 bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-lg text-xs transition shadow-xs cursor-pointer">
                    Save Record
                </button>
            </div>

            <div class="md:col-span-2 bg-white rounded-lg border border-slate-200 overflow-hidden shadow-xs">
                <div class="p-3.5 border-b border-slate-200 bg-slate-50 font-bold text-slate-800 text-xs">Configured {{ ucfirst($tab) }}</div>
                <div class="p-3 divide-y divide-slate-100">
                    @php $list = $tab === 'departments' ? $departments : ($tab === 'designations' ? $designations : ($tab === 'shifts' ? $shifts : $holidays)); @endphp
                    @foreach ($list as $item)
                        <div class="py-2.5 px-2 flex justify-between items-center text-xs hover:bg-slate-50/80 rounded transition">
                            <span class="font-semibold text-slate-900">{{ $item->name ?? $item->title }}</span>
                            <span class="text-[11px] text-slate-500">{{ $item->description ?? $item->note ?? '' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if ($tab === 'attendances')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-xs space-y-3.5">
                <h3 class="font-bold text-sm text-slate-900 border-b border-slate-100 pb-2">Log Daily Attendance</h3>
                <form wire:submit="recordAttendance" class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Employee</label>
                        <select wire:model="attEmployeeId">
                            <option value="">-- Choose Staff --</option>
                            @foreach ($employees as $e) <option value="{{ $e->id }}">{{ $e->name }} ({{ $e->employee_code }})</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Date</label>
                        <input type="date" wire:model="attDate" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Status</label>
                        <select wire:model="attStatus">
                            <option value="PRESENT">PRESENT</option>
                            <option value="LATE">LATE</option>
                            <option value="ABSENT">ABSENT</option>
                            <option value="ON_LEAVE">ON LEAVE</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full py-2 bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-lg text-xs transition shadow-xs cursor-pointer">Record Attendance</button>
                </form>
            </div>

            <div class="lg:col-span-2 bg-white rounded-lg border border-slate-200 overflow-hidden shadow-xs">
                <div class="p-3.5 border-b border-slate-200 bg-slate-50 font-bold text-slate-800 text-xs">Attendance Records</div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500 bg-slate-50">
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Employee</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-right">Time In</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-800">
                            @forelse ($attendances as $att)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-4 py-3 font-mono text-slate-600">{{ $att->date->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $att->employee->name }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $att->status === 'PRESENT' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">{{ $att->status }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-slate-500">{{ $att->clock_in ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">No attendance logs found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if ($tab === 'payrolls')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-xs space-y-3.5">
                <h3 class="font-bold text-sm text-slate-900 border-b border-slate-100 pb-2">Disburse Monthly Salary</h3>
                <form wire:submit="generatePayroll" class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Employee</label>
                        <select wire:model="payEmpId">
                            <option value="">-- Choose Employee --</option>
                            @foreach ($employees as $e) <option value="{{ $e->id }}">{{ $e->name }} (৳{{ number_format($e->salary, 2) }})</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Month (YYYY-MM)</label>
                        <input type="month" wire:model="payMonth" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Bonus (৳)</label>
                            <input type="number" step="0.01" wire:model="payBonus" placeholder="0.00" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Deduction (৳)</label>
                            <input type="number" step="0.01" wire:model="payDeduction" placeholder="0.00" />
                        </div>
                    </div>
                    <button type="submit" class="w-full py-2 bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-lg text-xs transition shadow-xs cursor-pointer">Pay Salary Voucher</button>
                </form>
            </div>

            <div class="lg:col-span-2 bg-white rounded-lg border border-slate-200 overflow-hidden shadow-xs">
                <div class="p-3.5 border-b border-slate-200 bg-slate-50 font-bold text-slate-800 text-xs">Disbursed Salary Payrolls</div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500 bg-slate-50">
                                <th class="px-4 py-3">Month</th>
                                <th class="px-4 py-3">Employee</th>
                                <th class="px-4 py-3 text-right">Basic</th>
                                <th class="px-4 py-3 text-right">Net Paid</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-800">
                            @forelse ($payrolls as $pay)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-4 py-3 font-mono font-semibold text-sky-700">{{ $pay->month_year }}</td>
                                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $pay->employee->name }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-slate-600">৳{{ number_format($pay->basic_salary, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-emerald-600">৳{{ number_format($pay->net_salary, 2) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">{{ $pay->payment_status }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">No payroll vouchers disbursed yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if ($tab === 'leaves')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-xs space-y-3.5">
                <h3 class="font-bold text-sm text-slate-900 border-b border-slate-100 pb-2">Apply Leave Request</h3>
                <form wire:submit="applyLeave" class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Employee</label>
                        <select wire:model="leaveEmpId">
                            <option value="">-- Choose Employee --</option>
                            @foreach ($employees as $e) <option value="{{ $e->id }}">{{ $e->name }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Leave Type</label>
                        <select wire:model="leaveTypeId">
                            <option value="">-- Choose Type --</option>
                            @foreach ($leaveTypes as $lt) <option value="{{ $lt->id }}">{{ $lt->name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Start Date</label>
                            <input type="date" wire:model="leaveStartDate" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">End Date</label>
                            <input type="date" wire:model="leaveEndDate" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Reason</label>
                        <input type="text" wire:model="leaveReason" placeholder="Reason for leave..." />
                    </div>
                    <button type="submit" class="w-full py-2 bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-lg text-xs transition shadow-xs cursor-pointer">Submit Leave</button>
                </form>
            </div>

            <div class="lg:col-span-2 bg-white rounded-lg border border-slate-200 overflow-hidden shadow-xs">
                <div class="p-3.5 border-b border-slate-200 bg-slate-50 font-bold text-slate-800 text-xs">Leave History</div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500 bg-slate-50">
                                <th class="px-4 py-3">Employee</th>
                                <th class="px-4 py-3">Leave Type</th>
                                <th class="px-4 py-3">Period</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-800">
                            @forelse ($leaves as $lv)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $lv->employee->name }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $lv->leaveType->name }}</td>
                                    <td class="px-4 py-3 text-slate-500 font-mono text-[11px]">{{ $lv->start_date->format('d/m') }} - {{ $lv->end_date->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">{{ $lv->status }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">No leaves logged yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
