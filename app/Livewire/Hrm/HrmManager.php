<?php

namespace App\Livewire\Hrm;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Payroll;
use App\Models\Shift;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('HRM & Staff Management - PMS')]
class HrmManager extends Component
{
    use WithPagination;

    public string $tab = 'employees'; // 'departments', 'designations', 'shifts', 'employees', 'leave_types', 'leaves', 'holidays', 'attendances', 'payrolls', 'reports'

    public string $reportType = 'attendance'; // 'attendance', 'payroll', 'leave'

    // Employee Form
    public string $employeeName = '';

    public string $employeePhone = '';

    public string $employeeEmail = '';

    public ?int $departmentId = null;

    public ?int $designationId = null;

    public ?int $shiftId = null;

    public float $salary = 0.00;

    // Quick Masters Form
    public string $masterTitle = '';

    public string $masterNote = '';

    // Attendance Form
    public ?int $attEmployeeId = null;

    public string $attDate = '';

    public string $attStatus = 'PRESENT';

    // Leave Form
    public ?int $leaveEmpId = null;

    public ?int $leaveTypeId = null;

    public string $leaveStartDate = '';

    public string $leaveEndDate = '';

    public string $leaveReason = '';

    // Payroll Form
    public ?int $payEmpId = null;

    public string $payMonth = '';

    public float $payBonus = 0.00;

    public float $payDeduction = 0.00;

    public function mount()
    {
        $this->tab = request()->query('tab', 'employees');
        $this->attDate = now()->format('Y-m-d');
        $this->leaveStartDate = now()->format('Y-m-d');
        $this->leaveEndDate = now()->format('Y-m-d');
        $this->payMonth = now()->format('Y-m');
    }

    public function saveEmployee()
    {
        $this->validate([
            'employeeName' => 'required|string|max:255',
            'employeePhone' => 'required|string|max:50',
            'salary' => 'required|numeric|min:0',
        ]);

        Employee::create([
            'branch_id' => auth()->user()?->branch_id,
            'department_id' => $this->departmentId,
            'designation_id' => $this->designationId,
            'shift_id' => $this->shiftId,
            'employee_code' => 'EMP-'.strtoupper(substr(uniqid(), -5)),
            'name' => $this->employeeName,
            'phone' => $this->employeePhone,
            'email' => $this->employeeEmail,
            'salary' => $this->salary,
            'joining_date' => now(),
            'status' => 'ACTIVE',
        ]);

        session()->flash('success', 'Employee registered successfully!');
        $this->reset(['employeeName', 'employeePhone', 'employeeEmail', 'salary']);
    }

    public function saveMaster(string $type)
    {
        $this->validate(['masterTitle' => 'required|string|max:255']);

        if ($type === 'dept') {
            Department::create(['name' => $this->masterTitle, 'description' => $this->masterNote]);
        } elseif ($type === 'desig') {
            Designation::create(['name' => $this->masterTitle, 'description' => $this->masterNote]);
        } elseif ($type === 'shift') {
            Shift::create(['name' => $this->masterTitle, 'start_time' => '08:00', 'end_time' => '16:00']);
        } elseif ($type === 'leave_type') {
            LeaveType::create(['name' => $this->masterTitle, 'max_days_per_year' => (int) $this->masterNote ?: 14]);
        } elseif ($type === 'holiday') {
            Holiday::create(['title' => $this->masterTitle, 'start_date' => now(), 'end_date' => now(), 'note' => $this->masterNote]);
        }

        $this->reset(['masterTitle', 'masterNote']);
        session()->flash('success', 'Master record added successfully!');
    }

    public function recordAttendance()
    {
        $this->validate(['attEmployeeId' => 'required|exists:employees,id']);

        Attendance::updateOrCreate(
            ['employee_id' => $this->attEmployeeId, 'date' => $this->attDate],
            ['status' => $this->attStatus, 'clock_in' => now()->format('H:i:s')]
        );

        session()->flash('success', 'Attendance logged!');
    }

    public function applyLeave()
    {
        $this->validate([
            'leaveEmpId' => 'required|exists:employees,id',
            'leaveTypeId' => 'required|exists:leave_types,id',
            'leaveStartDate' => 'required|date',
            'leaveEndDate' => 'required|date',
        ]);

        Leave::create([
            'employee_id' => $this->leaveEmpId,
            'leave_type_id' => $this->leaveTypeId,
            'start_date' => $this->leaveStartDate,
            'end_date' => $this->leaveEndDate,
            'reason' => $this->leaveReason,
            'status' => 'APPROVED',
        ]);

        session()->flash('success', 'Leave application recorded!');
        $this->reset(['leaveReason']);
    }

    public function generatePayroll()
    {
        $this->validate(['payEmpId' => 'required|exists:employees,id']);

        $emp = Employee::findOrFail($this->payEmpId);
        $net = $emp->salary + $this->payBonus - $this->payDeduction;

        Payroll::updateOrCreate(
            ['employee_id' => $emp->id, 'month_year' => $this->payMonth],
            [
                'basic_salary' => $emp->salary,
                'bonus' => $this->payBonus,
                'deduction' => $this->payDeduction,
                'net_salary' => max(0, $net),
                'payment_status' => 'PAID',
                'payment_date' => now(),
            ]
        );

        session()->flash('success', 'Salary payroll voucher generated!');
    }

    public function render()
    {
        return view('livewire.hrm.hrm-manager', [
            'employees' => Employee::with(['department', 'designation', 'shift'])->latest('id')->paginate(15),
            'departments' => Department::orderBy('name')->get(),
            'designations' => Designation::orderBy('name')->get(),
            'shifts' => Shift::orderBy('name')->get(),
            'leaveTypes' => LeaveType::orderBy('name')->get(),
            'leaves' => Leave::with(['employee', 'leaveType'])->latest('id')->paginate(15),
            'holidays' => Holiday::latest('start_date')->get(),
            'attendances' => Attendance::with(['employee'])->latest('date')->paginate(15),
            'payrolls' => Payroll::with(['employee'])->latest('month_year')->paginate(15),
        ]);
    }
}
