@extends('layouts.app')
@section('title', 'Manager Dashboard')
@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">@include('components.kpi', ['title'=>'Team Size','value'=>$team->count(),'icon'=>'bi-people','color'=>'primary'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'Present Today','value'=>$presentToday,'sub'=>$presentPct.'%','icon'=>'bi-person-check','color'=>'success'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'Late Today','value'=>$lateToday,'icon'=>'bi-alarm','color'=>'warning'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'Absent Today','value'=>$absentToday,'icon'=>'bi-x-circle','color'=>'danger'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'Pending Leaves','value'=>$pendingLeaves,'icon'=>'bi-calendar-check','color'=>'warning'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'Pending OT','value'=>$pendingOt,'icon'=>'bi-stopwatch','color'=>'info'])</div>
</div>
<div class="card card-panel">
    <div class="card-header bg-white border-0 fw-semibold">Team Snapshot — {{ auth()->user()->department }}</div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Employee</th><th>Title</th><th>Salary</th><th>Status</th></tr></thead>
            <tbody>
            @foreach($team as $m)
                <tr>
                    <td>
                        <span class="avatar-circle me-2" style="background:{{ $m->avatarColor() }}">{{ $m->initials() }}</span>
                        {{ $m->name }}
                    </td>
                    <td>{{ $m->job_title }}</td>
                    <td>{{ inr($m->salary) }}</td>
                    <td>{!! status_badge($m->status) !!}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
