@extends('layouts.app')
@section('title', 'Employees')
@section('content')
<div class="row g-3">
    <div class="col-lg-4">





    <a href="{{ route('admin.employees.create') }}" class="btn btn-primary btn-sm mb-2">
    <i class="bi bi-person-plus"></i> Add Employee
</a>









        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Add Employee</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.employees.store') }}">
                    @csrf
                    <div class="mb-2"><input name="name" class="form-control form-control-sm" placeholder="Full name" required></div>
                    <div class="mb-2"><input name="email" type="email" class="form-control form-control-sm" placeholder="Email"></div>
                    <div class="row g-2 mb-2">
                        <div class="col-5"><input name="employee_code" class="form-control form-control-sm" placeholder="Employee code (device PIN)"></div>
                        <div class="col-7">
                            <select name="department" class="form-select form-select-sm">
                                <option value="">— select department —</option>
                                @if(isset($departments) && $departments->count())
                                    @foreach($departments as $d)
                                        <option value="{{ $d }}">{{ $d }}</option>
                                    @endforeach
                                @else
                                    @foreach(['Engineering','Finance','HR','Marketing'] as $d)
                                        <option value="{{ $d }}">{{ $d }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <select name="job_title" class="form-select form-select-sm">
                            <option value="">— select designation —</option>
                            @if(isset($designations) && $designations->count())
                                @foreach($designations as $t)
                                    <option value="{{ $t }}">{{ $t }}</option>
                                @endforeach
                            @else
                                <option value="Staff">Staff</option>
                                <option value="Senior Developer">Senior Developer</option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-2">
                        <select name="role" class="form-select form-select-sm"><option value="employee">Employee</option><option value="manager">Manager</option></select>
                    </div>
                    <div class="mb-2"><input name="salary" type="number" class="form-control form-control-sm" placeholder="Salary" required></div>
                    <div class="mb-2"><input name="join_date" type="date" class="form-control form-control-sm"></div>
                    <div class="mb-3">
                        <select name="status" class="form-select form-select-sm"><option>Active</option><option>Inactive</option></select>
                    </div>
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary btn-sm">Create</button>
                        <a href="#" class="btn btn-outline-secondary btn-sm">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Employee Directory</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light"><tr><th>Code</th><th>Name</th><th>Dept</th><th>Role</th><th>Salary</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                    @foreach($employees as $e)
                        <tr>
                            <td>{{ $e->employee_code }}</td>
                            <td>
                                {{ $e->name }}
                                <div class="text-muted" style="font-size:11px;">{{ $e->email }}</div>
                                @if(isset($e->late_flagged) && $e->late_flagged)
                                    <span class="badge bg-warning text-dark">3-Day Late Flag</span>
                                @endif
                            </td>
                            <td>{{ $e->department }}</td>
                            <td class="text-capitalize">{{ $e->role }}</td>
                            <td>{{ inr($e->salary) }}</td>
                            <td>{!! status_badge($e->status) !!}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.employees.edit', $e) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    @if($e->status === 'Active')
                                        <a href="{{ route('admin.employees.settlement', $e) }}" class="btn btn-sm btn-outline-danger">Remove</a>
                                    @else
                                        <form method="POST" action="{{ route('admin.employees.destroy', $e) }}" onsubmit="return confirm('Delete employee?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
