@extends('layouts.app')
@section('title', 'Final Settlement')
@section('content')
<div class="card card-panel">
    <div class="card-header bg-white border-0 fw-semibold">Final Payslip & Settlement</div>
    <div class="card-body">
        <div class="mb-3"><strong>{{ $employee->name }}</strong> ({{ $employee->employee_code }})</div>
        <ul class="list-group mb-3">
            <li class="list-group-item d-flex justify-content-between"><span>Years of service</span><strong>{{ $yearsOfService }}</strong></li>
            <li class="list-group-item d-flex justify-content-between"><span>Last increment rate</span><strong>{{ $lastIncrementPct }}%</strong></li>
            <li class="list-group-item d-flex justify-content-between"><span>Last basic salary</span><strong>{{ inr($employee->salary) }}</strong></li>
            <li class="list-group-item d-flex justify-content-between"><span>Final salary</span><strong>{{ inr($finalSalary) }}</strong></li>
            <li class="list-group-item d-flex justify-content-between"><span>PF (Employee)</span><strong>{{ inr($pfEmployee) }}</strong></li>
            <li class="list-group-item d-flex justify-content-between"><span>TDS</span><strong>{{ inr($tds) }}</strong></li>
            <li class="list-group-item d-flex justify-content-between"><span>Leave encashment</span><strong>{{ inr($leaveEncashment) }}</strong></li>
            <li class="list-group-item d-flex justify-content-between"><span>Outstanding loan</span><strong>{{ inr($outstandingLoan) }}</strong></li>
            <li class="list-group-item d-flex justify-content-between"><span class="fw-semibold">Net settlement</span><strong>{{ inr($netSettlement) }}</strong></li>
        </ul>
        <form method="POST" action="{{ route('admin.employees.settle', $employee) }}">
            @csrf
            <button class="btn btn-danger">Confirm Final Settlement & Remove</button>
        </form>
    </div>
</div>
@endsection
