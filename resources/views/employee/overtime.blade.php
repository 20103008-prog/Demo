@extends('layouts.app')
@section('title', 'Overtime')
@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Request Overtime</div>
            <div class="card-body">
                <form method="POST" action="{{ route('employee.overtime.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small">Date</label>
                        <input type="date" name="date" class="form-control" max="{{ today()->toDateString() }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Hours</label>
                        <input type="number" name="hours" class="form-control" min="0.5" max="12" step="0.5" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Reason</label>
                        <textarea name="reason" class="form-control" rows="3" required></textarea>
                    </div>
                    <button class="btn btn-primary w-100">Submit OT Request</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Overtime History</div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light"><tr><th>Code</th><th>Date</th><th>Hours</th><th>Reason</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $item->code }}</td>
                            <td>{{ $item->date->format('d M Y') }}</td>
                            <td>{{ $item->hours }}</td>
                            <td class="small">{{ $item->reason }}</td>
                            <td>{!! status_badge($item->status) !!}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No overtime requests yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
