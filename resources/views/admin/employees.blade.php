@extends('layouts.app')
@section('title', 'Employees')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0">Employee Directory</h4>
        <p class="text-muted small mb-0">Manage employees, profiles, and status</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.departments') }}" class="btn btn-outline-secondary">
            <i class="bi bi-diagram-3 me-1"></i> Departments & Designations
        </a>
        <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i> Add Employee
        </a>
    </div>
</div>

<div class="card card-panel border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Dept</th>
                    <th>Designation</th>
                    <th>Role</th>
                    <th>Salary</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($employees as $e)
                <tr>
                    <td><span class="badge bg-light text-dark border">{{ $e->employee_code }}</span></td>
                    <td>
                        <div class="fw-semibold">{{ $e->name }}</div>
                        <div class="text-muted" style="font-size:11px;">{{ $e->email }}</div>
                        @if(isset($e->late_flagged) && $e->late_flagged)
                            <span class="badge bg-warning text-dark mt-1">3-Day Late Flag</span>
                        @endif
                    </td>
                    <td>{{ $e->department ?: '—' }}</td>
                    <td>{{ $e->job_title ?: '—' }}</td>
                    <td class="text-capitalize">{{ $e->role }}</td>
                    <td>{{ inr($e->salary) }}</td>
                    <td>{!! status_badge($e->status) !!}</td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-1">
                            <a href="{{ route('admin.employees.edit', $e) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            @if($e->status === 'Active')
                                <a href="{{ route('admin.employees.settlement', $e) }}" class="btn btn-sm btn-outline-danger">Remove</a>
                            @else
                                <form method="POST" action="{{ route('admin.employees.destroy', $e) }}" onsubmit="return confirm('Delete employee?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
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
@endsection
