@extends('layouts.app')
@section('title', 'Manager Reports')
@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-4">@include('components.kpi', ['title'=>'Dept Payroll (Jul)','value'=>inr($payroll),'icon'=>'bi-cash-stack','color'=>'success'])</div>
    <div class="col-md-4">@include('components.kpi', ['title'=>'Attendance %','value'=>$attendancePct.'%','icon'=>'bi-graph-up','color'=>'primary'])</div>
    <div class="col-md-4">@include('components.kpi', ['title'=>'Department','value'=>$dept,'icon'=>'bi-building','color'=>'info'])</div>
</div>
<div class="card card-panel">
    <div class="card-body">
        <h2 class="h6 fw-semibold">Department Trends</h2>
        <div class="chart-box">
            <canvas data-chart='@json($otChart)'></canvas>
        </div>
    </div>
</div>
@endsection
