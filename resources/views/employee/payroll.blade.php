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

<div class="row g-3 mb-3">
    <div class="col-md-4">@include('components.kpi', ['title'=>'Est. Annual Income','value'=>money($annualEst),'icon'=>'bi-calendar3','color'=>'secondary'])</div>
    <div class="col-md-4">@include('components.kpi', ['title'=>'NBR Monthly TDS','value'=>money($monthlyTdsEst),'icon'=>'bi-receipt','color'=>'warning'])</div>
    <div class="col-md-4">@include('components.kpi', ['title'=>'PF Rates','value'=>$pfRates['employee'].'% / '.$pfRates['employer'].'%','sub'=>'Emp / Employer','icon'=>'bi-percent','color'=>'info'])</div>
</div>

<div class="card card-panel mb-3">
    <div class="card-header bg-white border-0 fw-semibold">NBR Tax Estimate ({{ $categories[$nbr['category']] ?? $nbr['category'] }})</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Tax-free limit</span><strong>{{ money($nbr['tax_free_limit']) }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Annual income</span><strong>{{ money($nbr['annual_income']) }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Employment deduction (1/3 max ৳5L)</span><strong>{{ money($nbr['employment_deduction']) }}</strong></li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Assessable income</span><strong>{{ money($nbr['assessable_income']) }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Annual NBR tax</span><strong>{{ money($nbr['annual_tax']) }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Monthly TDS</span><strong>{{ money($nbr['monthly_tds']) }}</strong></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="card card-panel mb-3">
    <div class="card-header bg-white border-0 fw-semibold">Payslips</div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Month</th><th>Gross</th><th>OT</th><th>Deductions</th><th>Net</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($payslips as $p)
                <tr>
                    <td>{{ $p->month }}</td>
                    <td>{{ inr($p->gross) }}</td>
                    <td>{{ inr($p->overtime_pay) }}</td>
                    <td>{{ inr($p->tds + $p->pf_employee + $p->loan_deduction + $p->other_deductions) }}</td>
                    <td class="fw-semibold">{{ inr($p->net) }}</td>
                    <td>{!! status_badge($p->status) !!}</td>
                    <td><a class="btn btn-sm btn-outline-primary" href="{{ route('employee.payslip.pdf', $p) }}">PDF</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No payslips yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="card card-panel h-100">
            <div class="card-header bg-white border-0 fw-semibold">Loans</div>
            <div class="table-responsive">
                <table class="table mb-0 table-sm">
                    <thead class="table-light"><tr><th>Code</th><th>EMI</th><th>Outstanding</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($loans as $loan)
                        <tr>
                            <td>{{ $loan->code }}</td>
                            <td>{{ inr($loan->emi) }}</td>
                            <td>{{ inr($loan->outstanding) }}</td>
                            <td>{!! status_badge($loan->status) !!}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No loans.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card card-panel h-100">
            <div class="card-header bg-white border-0 fw-semibold">Bonus & Increment</div>
            <div class="table-responsive">
                <table class="table mb-0 table-sm">
                    <thead class="table-light"><tr><th>Type</th><th>Detail</th><th>Amount / %</th><th>Status</th></tr></thead>
                    <tbody>
                    @foreach($bonuses as $b)
                        <tr>
                            <td>Festival Bonus</td>
                            <td>{{ $b->code }}</td>
                            <td>{{ inr($b->festival_bonus) }}</td>
                            <td>{!! status_badge($b->status) !!}</td>
                        </tr>
                    @endforeach
                    @foreach($increments as $inc)
                        <tr>
                            <td>Increment</td>
                            <td>{{ $inc->code }}</td>
                            <td>{{ $inc->increment_pct }}% → {{ inr($inc->new_salary) }}</td>
                            <td>{!! status_badge($inc->status) !!}</td>
                        </tr>
                    @endforeach
                    @if($bonuses->isEmpty() && $increments->isEmpty())
                        <tr><td colspan="4" class="text-center text-muted py-3">No bonus/increment records.</td></tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@if($settlement)
<div class="card card-panel mb-3">
    <div class="card-header bg-white border-0 fw-semibold">Final Settlement Statement</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Exit date</span><strong>{{ $settlement->exit_date->format('d M Y') }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Years of service</span><strong>{{ $settlement->years_of_service }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Final month salary</span><strong>{{ inr($settlement->final_month_salary) }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Leave encashment</span><strong>{{ inr($settlement->leave_encashment) }}</strong></li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Gratuity</span><strong>{{ inr($settlement->gratuity ?? 0) }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Outstanding loan</span><strong>{{ inr($settlement->outstanding_loan) }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Net settlement</span><strong>{{ inr($settlement->net_settlement) }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Status</span><strong>{!! status_badge($settlement->status) !!}</strong></li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endif

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
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Overtime</span><strong>{{ inr($latest->overtime_pay) }}</strong></li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Employee PF</span><strong>{{ inr($latest->pf_employee) }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Employer PF</span><strong>{{ inr($latest->pf_employer) }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>TDS</span><strong>{{ inr($latest->tds) }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Loan EMI</span><strong>{{ inr($latest->loan_deduction) }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between px-0"><span>Attendance / Leave Ded.</span><strong>{{ inr(($latest->attendance_deduction ?? 0) + ($latest->unpaid_leave_deduction ?? 0)) }}</strong></li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
