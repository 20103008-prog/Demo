@extends('layouts.app')
@section('title', 'Manager Reports')
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
        <div class="ms-auto small text-muted">{{ $dept }} · {{ $periodLabel }}</div>
    </div>
</form>
<div class="row g-3 mb-3">
    <div class="col-md-3">@include('components.kpi', ['title'=>'Dept Payroll','value'=>inr($payroll),'icon'=>'bi-cash-stack','color'=>'success'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'Attendance %','value'=>$attendancePct.'%','icon'=>'bi-graph-up','color'=>'primary'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'Late Arrivals','value'=>$lateCount,'icon'=>'bi-alarm','color'=>'warning'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'Absences','value'=>$absentCount,'icon'=>'bi-x-circle','color'=>'danger'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'TDS','value'=>inr($tdsTotal),'icon'=>'bi-percent','color'=>'warning'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'PF','value'=>inr($pfTotal),'icon'=>'bi-safe','color'=>'info'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'Loan Deducted','value'=>inr($loanDeducted),'icon'=>'bi-credit-card','color'=>'secondary'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'Loan Outstanding','value'=>inr($loanOutstanding),'icon'=>'bi-wallet2','color'=>'danger'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'Bonus','value'=>inr($bonusTotal),'icon'=>'bi-award','color'=>'success'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'Settlements','value'=>inr($settlementTotal),'icon'=>'bi-bank','color'=>'primary'])</div>
</div>
<div class="card card-panel">
    <div class="card-body">
        <h2 class="h6 fw-semibold">Department OT Trends</h2>
        <div class="chart-box">
            <canvas data-chart='@json($otChart)'></canvas>
        </div>
    </div>
</div>
@endsection
