@extends('layouts.app')
@section('title', 'Leave')
@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-4">@include('components.kpi', ['title'=>'Casual Left','value'=>$balance->casual,'icon'=>'bi-calendar-check','color'=>'primary'])</div>
    <div class="col-md-4">@include('components.kpi', ['title'=>'Sick Left','value'=>$balance->sick,'icon'=>'bi-thermometer','color'=>'warning'])</div>
    <div class="col-md-4">@include('components.kpi', ['title'=>'Earned Left','value'=>$balance->earned,'icon'=>'bi-award','color'=>'success'])</div>
</div>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Apply for Leave</div>
            <div class="card-body">
                <form method="POST" action="{{ route('employee.leave.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small">Type</label>
                        <select name="type" class="form-select" required>
                            @foreach(['Casual','Sick','Earned','Compensatory','Unpaid'] as $t)
                                <option>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">From</label>
                        <input type="date" name="from_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">To</label>
                        <input type="date" name="to_date" class="form-control" required>
                        <div class="form-text text-muted">Leave cannot include weekends/holidays from the holiday calendar.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Reason</label>
                        <textarea name="reason" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="is_half_day" value="1" id="half">
                        <label class="form-check-label" for="half">Half-day leave</label>
                    </div>
                    <select name="half_day_session" class="form-select mb-3">
                        <option value="AM">AM</option>
                        <option value="PM">PM</option>
                    </select>
                    <button class="btn btn-primary w-100">Submit Request</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Leave History & Status</div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light"><tr><th>Code</th><th>Type</th><th>Dates</th><th>Days</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($leaves as $l)
                        <tr>
                            <td>{{ $l->code }}</td>
                            <td>{{ $l->type }}</td>
                            <td>{{ $l->from_date->format('d M') }} – {{ $l->to_date->format('d M Y') }}</td>
                            <td>{{ $l->days }}</td>
                            <td>{!! status_badge($l->status) !!}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No leave requests yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
