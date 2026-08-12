@extends('layouts.app')
@section('title', 'Reports')
@section('content')
<form method="GET" class="card card-panel mb-3">
    <div class="card-body d-flex flex-wrap gap-2 align-items-end">
        <div>
            <label class="form-label small mb-1">Month</label>
            <select name="month" class="form-select form-select-sm">
                @for($m=1;$m<=12;$m++)
                    <option value="{{ $m }}" @selected($month==$m)>{{ DateTime::createFromFormat('!m', $m)->format('M') }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="form-label small mb-1">Year</label>
            <select name="year" class="form-select form-select-sm">
                @for($y=now()->year-1;$y<=now()->year+1;$y++)
                    <option value="{{ $y }}" @selected($year==$y)>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <button class="btn btn-sm btn-primary">Filter</button>
        <div class="ms-auto small text-muted">Period: {{ $periodLabel }}</div>
    </div>
</form>
<div class="row g-3 mb-3">
    <div class="col-md-3">@include('components.kpi', ['title'=>'Payroll Total','value'=>inr($payrollTotal),'icon'=>'bi-cash-stack','color'=>'success'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'TDS','value'=>inr($tdsTotal),'icon'=>'bi-percent','color'=>'warning'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'PF (Employee)','value'=>inr($pfTotal),'icon'=>'bi-safe','color'=>'info'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'Loan Outstanding','value'=>inr($loanOutstanding),'icon'=>'bi-credit-card','color'=>'danger'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'Loan Deducted','value'=>inr($loanDeducted),'icon'=>'bi-cash','color'=>'primary'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'Festival Bonus','value'=>inr($bonusTotal),'icon'=>'bi-award','color'=>'success'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'Settlements','value'=>inr($settlementTotal),'icon'=>'bi-bank','color'=>'secondary'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'AI Queries','value'=>$queryCount,'icon'=>'bi-robot','color'=>'info'])</div>
</div>
<div class="card card-panel">
    <div class="card-header bg-white border-0 fw-semibold">Department Cost Centres</div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead class="table-light"><tr><th>Department</th><th>Headcount</th><th>Salary Cost</th></tr></thead>
            <tbody>
            @foreach($byDept as $d)
                <tr>
                    <td>{{ $d->department }}</td>
                    <td>{{ $d->total }}</td>
                    <td>{{ inr($d->salary_cost) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
