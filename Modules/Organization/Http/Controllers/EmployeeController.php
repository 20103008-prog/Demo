<?php

namespace Modules\Organization\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Payslip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function employees(): View
    {
        $employees = User::where('role', '!=', 'admin')->orderBy('employee_code')->get();
        $departments = User::whereNotNull('department')->distinct()->pluck('department');
        $designations = User::whereNotNull('job_title')->distinct()->pluck('job_title');

        $employees->each(function ($employee) {
            $recentAttendances = $employee->attendanceRecords()
                ->orderByDesc('date')
                ->limit(3)
                ->get(['check_in', 'date']);

            $employee->late_flagged = $recentAttendances->count() === 3 && $recentAttendances->every(function ($attendance) {
                return $attendance->check_in && $attendance->check_in > '09:15';
            });
        });

        $departments = Department::orderBy('name')->pluck('name');
        $designations = Designation::orderBy('name')->pluck('name');

        return view('admin.employees', compact('employees', 'departments', 'designations'));
    }

    public function createEmployee(): View
    {
        $departments = Department::orderBy('name')->pluck('name');
        $designations = Designation::orderBy('name')->pluck('name');

        return view('admin.employee-create', compact('departments', 'designations'));
    }

    public function storeEmployee(Request $request)
    {
        $portalLogin = $request->boolean('portal_login');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'department' => 'nullable|string',
            'employee_code' => 'nullable|string|unique:users,employee_code',
            'job_title' => 'nullable|string',
            'role' => 'nullable|in:employee,manager',
            'salary' => 'nullable|numeric|min:0',
            'join_date' => 'nullable|date',
            'tax_category' => 'nullable|in:general,woman,senior,disabled,freedom_fighter',
            'tin' => 'nullable|string|max:20',
            'status' => 'required|in:Active,Inactive',
            'address' => 'nullable|string|max:1000',
            'weekly_off' => 'nullable|array',
            'weekly_off.*' => 'string',
            'portal_login' => 'nullable|boolean',
            'login_email' => 'nullable|email|unique:users,email',
            'password' => $portalLogin ? 'nullable|string|min:6|confirmed' : 'nullable',
        ]);

        $next = User::whereNotNull('employee_code')->count() + 1;
        $code = !empty($data['employee_code']) ? $data['employee_code'] : 'EMP'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);

        $email = !empty($data['login_email']) ? $data['login_email'] : ($data['email'] ?? null);

        $data['employee_code'] = $code;
        $data['email'] = $email;
        $data['role'] = $data['role'] ?? 'employee';
        $data['salary'] = $data['salary'] ?? 0;
        $data['tax_category'] = $data['tax_category'] ?? 'general';
        $data['portal_login'] = $portalLogin;
        $data['weekly_off'] = $request->input('weekly_off', []);

        $passwordStr = !empty($request->input('password')) ? $request->input('password') : 'demo1234';

        unset($data['login_email'], $data['password_confirmation']);

        User::create([
            ...$data,
            'password' => Hash::make($passwordStr),
        ]);

        AuditLog::create([
            'action' => 'Employee Added',
            'module' => 'Employees',
            'user_name' => Auth::user()->name,
            'role' => 'Admin',
            'details' => 'Added employee '.$data['name'],
            'severity' => 'info',
            'logged_at' => now(),
        ]);

        return redirect()->route('admin.employees')->with('success', 'Employee created successfully (password: '.$passwordStr.').');
    }

    public function editEmployee(User $employee): View
    {
        $lastPayslip = Payslip::where('user_id', $employee->id)
            ->orderByDesc('year')
            ->orderByDesc('month_num')
            ->first();

        return view('admin.employee-edit', compact('employee', 'lastPayslip'));
    }

    public function updateEmployee(Request $request, User $employee)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$employee->id,
            'department' => 'required|string',
            'job_title' => 'required|string',
            'role' => 'required|in:employee,manager',
            'join_date' => 'nullable|date',
            'tax_category' => 'required|in:general,woman,senior,disabled,freedom_fighter',
            'tin' => 'nullable|string|max:20',
            'status' => 'required|in:Active,Inactive',
        ]);

        $employee->update($data);

        return back()->with('success', 'Employee updated.');
    }

    public function destroyEmployee(User $employee)
    {
        if ($employee->role === 'admin') {
            return back()->with('error', 'Cannot delete admin.');
        }

        if ($employee->status === 'Active') {
            return back()->with('error', 'Process final settlement before deleting an active employee.');
        }

        $employee->delete();

        return back()->with('success', 'Employee deleted.');
    }

    public function departments(): View
    {
        $departments = Department::orderBy('name')->get();
        $designations = Designation::orderBy('name')->get();

        return view('admin.departments', compact('departments', 'designations'));
    }

    public function storeDepartment(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:departments,name']);
        Department::create(['name' => trim($request->name)]);

        return back()->with('success', 'Department created successfully.');
    }

    public function updateDepartment(Request $request, Department $department)
    {
        $request->validate(['name' => 'required|string|max:255|unique:departments,name,'.$department->id]);
        $oldName = $department->name;
        $newName = trim($request->name);

        $department->update(['name' => $newName]);
        User::where('department', $oldName)->update(['department' => $newName]);

        return back()->with('success', 'Department updated successfully.');
    }

    public function destroyDepartment(Department $department)
    {
        $department->delete();

        return back()->with('success', 'Department deleted successfully.');
    }

    public function storeDesignation(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:designations,name']);
        Designation::create(['name' => trim($request->name)]);

        return back()->with('success', 'Designation created successfully.');
    }

    public function updateDesignation(Request $request, Designation $designation)
    {
        $request->validate(['name' => 'required|string|max:255|unique:designations,name,'.$designation->id]);
        $oldName = $designation->name;
        $newName = trim($request->name);

        $designation->update(['name' => $newName]);
        User::where('job_title', $oldName)->update(['job_title' => $newName]);

        return back()->with('success', 'Designation updated successfully.');
    }

    public function destroyDesignation(Designation $designation)
    {
        $designation->delete();

        return back()->with('success', 'Designation deleted successfully.');
    }
}
