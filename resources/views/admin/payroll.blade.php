@extends('layouts.app')
@section('title', 'Payroll Processing')
@section('content')
<div class="card card-panel mb-3">
    <div class="card-header bg-white border-0 fw-semibold">Payroll Processing</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.payroll.process') }}">
            @csrf
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light"><tr><th>Employee</th><th>Salary</th><th>Current Increment</th><th>Set Rate</th></tr></thead>
                    <tbody>
                    @foreach($employees as $employee)
                        <tr>
                            <td>{{ $employee->name }}</td>
                            <td>{{ inr($employee->salary) }}</td>
                            <td>{{ $employeeIncrements[$employee->id] ?? 10 }}%</td>
                            <td style="width:180px;">
                                <input type="number" name="increment_pct[{{ $employee->id }}]" class="form-control form-control-sm" min="10" max="100" step="0.1" value="{{ old('increment_pct.'.$employee->id, $employeeIncrements[$employee->id] ?? 10) }}">
                                <div class="form-text">Minimum 10%.</div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <button class="btn btn-primary"><i class="bi bi-play-fill me-1"></i> Process Payroll</button>
            </div>
            <p class="text-muted small mt-2">Each employee has an editable increment field. If left unchanged, it defaults to 10%.</p>
        </form>
    </div>
</div>
<div class="card card-panel">
    <div class="card-header bg-white border-0 fw-semibold">July 2025 Payslips</div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Employee</th><th>Gross</th><th>TDS</th><th>PF</th><th>Loan</th><th>Net</th></tr></thead>
            <tbody>
            @forelse($payslips as $p)
                <tr>
                    <td>{{ $p->user->name }}</td>
                    <td>{{ inr($p->gross) }}</td>
                    <td>{{ inr($p->tds) }}</td>
                    <td>{{ inr($p->pf_employee) }}</td>
                    <td>{{ inr($p->loan_deduction) }}</td>
                    <td class="fw-semibold">{{ inr($p->net) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No July payslips yet. Click Process Payroll.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
