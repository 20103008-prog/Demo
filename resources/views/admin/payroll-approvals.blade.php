@extends('layouts.app')
@section('title', 'Payroll Approvals')
@section('content')
<div class="card card-panel">
    <div class="card-header bg-white border-0 fw-semibold">Maker-Checker Payroll Runs</div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Period</th><th>Employees</th><th>Total Net</th><th>Prepared By</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($runs as $run)
                <tr>
                    <td>{{ $run->label() }}</td>
                    <td>{{ $run->employee_count }}</td>
                    <td>{{ money($run->total_net) }}</td>
                    <td>{{ $run->preparedBy?->name ?? '—' }}</td>
                    <td>{!! status_badge($run->status) !!}</td>
                    <td>
                        @if($run->status === 'Pending Approval')
                        <form method="POST" action="{{ route('admin.payroll.approve', $run) }}" class="d-flex gap-1">
                            @csrf
                            <button name="action" value="Approved" class="btn btn-sm btn-success">Approve</button>
                            <button name="action" value="Rejected" class="btn btn-sm btn-outline-danger">Reject</button>
                        </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No payroll runs yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
