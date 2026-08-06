@extends('layouts.app')
@section('title', 'Reports')
@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-4">@include('components.kpi', ['title'=>'Jul Payroll Total','value'=>inr($payrollTotal),'icon'=>'bi-cash-stack','color'=>'success'])</div>
    <div class="col-md-4">@include('components.kpi', ['title'=>'Loan Outstanding','value'=>inr($loanOutstanding),'icon'=>'bi-credit-card','color'=>'warning'])</div>
    <div class="col-md-4">@include('components.kpi', ['title'=>'Departments','value'=>$byDept->count(),'icon'=>'bi-building','color'=>'primary'])</div>
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
