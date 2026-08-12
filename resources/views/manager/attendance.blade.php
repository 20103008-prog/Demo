@extends('layouts.app')
@section('title', 'Team Attendance')
@section('content')
<form method="GET" class="card card-panel mb-3">
    <div class="card-body d-flex gap-2 align-items-end flex-wrap">
        <div>
            <label class="form-label small mb-1">Date</label>
            <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm">
        </div>
        <button class="btn btn-sm btn-primary">Review</button>
        <div class="ms-auto small text-muted">{{ $dept }} · {{ $date }}</div>
    </div>
</form>
<div class="row g-3 mb-3">
    <div class="col-md-4">@include('components.kpi', ['title'=>'Present','value'=>$presentCount,'icon'=>'bi-check-circle','color'=>'success'])</div>
    <div class="col-md-4">@include('components.kpi', ['title'=>'Late','value'=>$lateCount,'icon'=>'bi-alarm','color'=>'warning'])</div>
    <div class="col-md-4">@include('components.kpi', ['title'=>'Absent','value'=>$absentCount,'icon'=>'bi-x-circle','color'=>'danger'])</div>
</div>
<div class="card card-panel">
    <div class="card-header bg-white border-0 fw-semibold">Team Attendance Records</div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Employee</th><th>In</th><th>Out</th><th>Hours</th><th>Status</th></tr></thead>
            <tbody>
            @foreach($team as $member)
                @php $r = $records->get($member->id); @endphp
                <tr>
                    <td>{{ $member->name }}</td>
                    <td>{{ $r?->check_in ?? '—' }}</td>
                    <td>{{ $r?->check_out ?? '—' }}</td>
                    <td>{{ $r?->hours ?? '—' }}</td>
                    <td>{!! $r ? status_badge($r->status) : status_badge('Not Punched') !!}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
