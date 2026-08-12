@extends('layouts.app')
@section('title', 'Payroll Processing')
@section('content')
<div class="card card-panel mb-3">
    <div class="card-header bg-white border-0 fw-semibold d-flex justify-content-between align-items-center">
        <span>Payroll Processing</span>
        <form method="GET" class="d-flex gap-2 align-items-center">
            <select name="month" class="form-select form-select-sm" style="width:auto">
                @for($m=1;$m<=12;$m++)
                    <option value="{{ $m }}" @selected($month==$m)>{{ DateTime::createFromFormat('!m', $m)->format('M') }}</option>
                @endfor
            </select>
            <select name="year" class="form-select form-select-sm" style="width:auto">
                @for($y=now()->year-1;$y<=now()->year+1;$y++)
                    <option value="{{ $y }}" @selected($year==$y)>{{ $y }}</option>
                @endfor
            </select>
            <button class="btn btn-sm btn-outline-primary">Load</button>
        </form>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.payroll.process') }}">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="month" value="{{ $month }}">
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
                <button class="btn btn-primary"><i class="bi bi-play-fill me-1"></i> Process Payroll ({{ $periodLabel }})</button>
            </div>
            <p class="text-muted small mt-2">Payroll uses DB settings for PF, tax slabs, OT, attendance deductions (3 late = 1 absence), and loan salary protection.</p>
        </form>
    </div>
</div>
<div class="card card-panel">
    <div class="card-header bg-white border-0 fw-semibold">{{ $periodLabel }} Payslips</div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Employee</th><th>Gross</th><th>OT</th><th>TDS</th><th>PF</th><th>Loan</th><th>Att. Ded.</th><th>Net</th></tr></thead>
            <tbody>
            @forelse($payslips as $p)
                <tr>
                    <td>{{ $p->user->name }}</td>
                    <td>{{ inr($p->gross) }}</td>
                    <td>{{ inr($p->overtime_pay) }}</td>
                    <td>{{ inr($p->tds) }}</td>
                    <td>{{ inr($p->pf_employee) }}</td>
                    <td>{{ inr($p->loan_deduction) }}</td>
                    <td>{{ inr(($p->attendance_deduction ?? 0) + ($p->unpaid_leave_deduction ?? 0)) }}</td>
                    <td class="fw-semibold">{{ inr($p->net) }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No payslips for {{ $periodLabel }}. Click Process Payroll.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
