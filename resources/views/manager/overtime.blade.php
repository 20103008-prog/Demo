@extends('layouts.app')
@section('title', 'Overtime')
@section('content')
<div class="card card-panel">
    <div class="card-header bg-white border-0 fw-semibold">Overtime Requests</div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Code</th><th>Employee</th><th>Date</th><th>Hours</th><th>Reason</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @foreach($items as $o)
                <tr>
                    <td>{{ $o->code }}</td>
                    <td>{{ $o->user->name }}</td>
                    <td>{{ $o->date->format('d M Y') }}</td>
                    <td>{{ $o->hours }}</td>
                    <td class="small">{{ $o->reason }}</td>
                    <td>{!! status_badge($o->status) !!}</td>
                    <td>
                        @if($o->status === 'Pending')
                            <form method="POST" action="{{ route('manager.overtime.review', $o) }}" class="d-inline">@csrf<input type="hidden" name="status" value="Approved"><button class="btn btn-sm btn-outline-success">Approve</button></form>
                            <form method="POST" action="{{ route('manager.overtime.review', $o) }}" class="d-inline">@csrf<input type="hidden" name="status" value="Rejected"><button class="btn btn-sm btn-outline-danger">Reject</button></form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
