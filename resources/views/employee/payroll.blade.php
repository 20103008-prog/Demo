@extends('layouts.app')
@section('title', 'Payroll')
@section('content')
@if($latest)
<div class="row g-3 mb-3">
    <div class="col-md-3">@include('components.kpi', ['title'=>'Gross','value'=>inr($latest->gross),'icon'=>'bi-wallet2','color'=>'primary'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'TDS','value'=>inr($latest->tds),'icon'=>'bi-percent','color'=>'warning'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'PF (Employee)','value'=>inr($latest->pf_employee),'icon'=>'bi-safe','color'=>'info'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'Net Pay','value'=>inr($latest->net),'sub'=>$latest->month,'icon'=>'bi-cash-coin','color'=>'success'])</div>
</div>
@endif

<div class="card card-panel">
    <div class="card-header bg-white border-0 fw-semibold">Payslips</div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Month</th><th>Gross</th><th>Deductions</th><th>Net</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($payslips as $p)
                <tr>
                    <td>{{ $p->month }}</td>
                    <td>{{ inr($p->gross) }}</td>
                    <td>{{ inr($p->tds + $p->pf_employee + $p->loan_deduction + $p->other_deductions) }}</td>
                    <td class="fw-semibold">{{ inr($p->net) }}</td>
                    <td>{!! status_badge($p->status) !!}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No payslips yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($latest)
<div class="card card-panel mt-3">
    <div class="card-header bg-white border-0 fw-semibold">Tax & PF Summary ({{ $latest->month }})</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Basic</span><strong>{{ inr($latest->basic) }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>HRA</span><strong>{{ inr($latest->hra) }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>DA</span><strong>{{ inr($latest->da) }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Allowances</span><strong>{{ inr($latest->allowances) }}</strong></li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Employee PF (12%)</span><strong>{{ inr($latest->pf_employee) }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Employer PF (12%)</span><strong>{{ inr($latest->pf_employer) }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>TDS</span><strong>{{ inr($latest->tds) }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Loan EMI</span><strong>{{ inr($latest->loan_deduction) }}</strong></li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
