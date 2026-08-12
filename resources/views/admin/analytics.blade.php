@extends('layouts.app')
@section('title', 'Analytics')
@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3">@include('components.kpi', ['title'=>'Headcount','value'=>$headcount,'icon'=>'bi-people','color'=>'primary'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'Present Today','value'=>$present_today,'icon'=>'bi-person-check','color'=>'success'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'High Attrition Risk','value'=>$high_risk,'icon'=>'bi-exclamation-triangle','color'=>'danger'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'Departments','value'=>$dept_cost->count(),'icon'=>'bi-building','color'=>'info'])</div>
</div>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Attrition Risk (heuristic)</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Employee</th><th>Dept</th><th>Score</th><th>Level</th></tr></thead>
                    <tbody>
                    @foreach($risks as $r)
                        <tr>
                            <td>{{ $r['name'] }}</td>
                            <td>{{ $r['department'] }}</td>
                            <td>{{ $r['score'] }}</td>
                            <td>{!! status_badge($r['level']) !!}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Dept Cost</div>
            <ul class="list-group list-group-flush">
                @foreach($dept_cost as $d)
                    <li class="list-group-item d-flex justify-content-between"><span>{{ $d->department }} ({{ $d->headcount }})</span><strong>{{ money($d->cost) }}</strong></li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
