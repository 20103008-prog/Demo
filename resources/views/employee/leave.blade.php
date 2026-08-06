@extends('layouts.app')
@section('title', 'Leave')
@section('content')
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
                            @foreach(['Casual','Sick','Earned','Compensatory'] as $t)
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
                        <div class="form-text text-muted">Leave cannot include Fridays because Fridays are treated as non-working days.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Reason</label>
                        <textarea name="reason" class="form-control" rows="3" required></textarea>
                    </div>
                    <button class="btn btn-primary w-100">Submit Request</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Leave History</div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light"><tr><th>Code</th><th>Type</th><th>Dates</th><th>Days</th><th>Status</th></tr></thead>
                    <tbody>
                    @foreach($leaves as $l)
                        <tr>
                            <td>{{ $l->code }}</td>
                            <td>{{ $l->type }}</td>
                            <td>{{ $l->from_date->format('d M') }} – {{ $l->to_date->format('d M Y') }}</td>
                            <td>{{ $l->days }}</td>
                            <td>{!! status_badge($l->status) !!}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
