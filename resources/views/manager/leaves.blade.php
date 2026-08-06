@extends('layouts.app')
@section('title', 'Leave Approvals')
@section('content')
<div class="card card-panel">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Team Leave Requests</span>
        <form method="POST" action="{{ route('manager.leaves.bulk') }}" id="bulkForm">
            @csrf
            <button class="btn btn-sm btn-success" type="submit">Approve Selected</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
                <tr><th></th><th>Code</th><th>Employee</th><th>Type</th><th>Dates</th><th>Days</th><th>Reason</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            @foreach($leaves as $l)
                <tr>
                    <td>
                        @if($l->status === 'Pending')
                            <input form="bulkForm" type="checkbox" name="ids[]" value="{{ $l->id }}" class="form-check-input">
                        @endif
                    </td>
                    <td>{{ $l->code }}</td>
                    <td>{{ $l->user->name }}</td>
                    <td>{{ $l->type }}</td>
                    <td>{{ $l->from_date->format('d M') }} – {{ $l->to_date->format('d M') }}</td>
                    <td>{{ $l->days }}</td>
                    <td class="small">{{ Str::limit($l->reason, 40) }}</td>
                    <td>{!! status_badge($l->status) !!}</td>
                    <td class="text-nowrap">
                        @if($l->status === 'Pending')
                            <form method="POST" action="{{ route('manager.leaves.review', $l) }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="Approved">
                                <button class="btn btn-sm btn-outline-success">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('manager.leaves.review', $l) }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="Rejected">
                                <button class="btn btn-sm btn-outline-danger">Reject</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
