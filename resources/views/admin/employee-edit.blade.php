@extends('layouts.app')
@section('title', 'Edit Employee')
@section('content')
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Employee Details</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.employees.update', $employee) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label small">Name</label>
                        <input name="name" class="form-control" value="{{ old('name', $employee->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $employee->email) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Department</label>
                        <input name="department" class="form-control" value="{{ old('department', $employee->department) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Job title</label>
                        <input name="job_title" class="form-control" value="{{ old('job_title', $employee->job_title) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Role</label>
                        <select name="role" class="form-select">
                            <option value="employee" @selected(old('role', $employee->role) === 'employee')>Employee</option>
                            <option value="manager" @selected(old('role', $employee->role) === 'manager')>Manager</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">NBR Tax Category</label>
                        <select name="tax_category" class="form-select" required>
                            @foreach(\App\Services\TaxService::categoryOptions() as $value => $label)
                                <option value="{{ $value }}" @selected(old('tax_category', $employee->tax_category ?? 'general') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">TIN / eTIN</label>
                        <input name="tin" class="form-control" value="{{ old('tin', $employee->tin) }}" placeholder="Optional">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Status</label>
                        <select name="status" class="form-select">
                            <option value="Active" @selected(old('status', $employee->status) === 'Active')>Active</option>
                            <option value="Inactive" @selected(old('status', $employee->status) === 'Inactive')>Inactive</option>
                        </select>
                    </div>
                    <button class="btn btn-primary btn-sm">Save</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Current Payroll</div>
            <div class="card-body">
                <p class="small mb-2">Salary: <strong>{{ inr($employee->salary) }}</strong></p>
                <p class="small mb-2">Join date: <strong>{{ $employee->join_date?->format('d M Y') ?? '—' }}</strong></p>
                <p class="small mb-2">Last payslip: <strong>{{ $lastPayslip?->month ?? 'None' }}</strong></p>
            </div>
        </div>
    </div>
</div>
@endsection
