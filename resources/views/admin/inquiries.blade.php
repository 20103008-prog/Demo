@extends('layouts.app')
@section('title', 'Client Inquiries')
@section('content')
<div class="card card-panel">
    <div class="card-header bg-white border-0 fw-semibold">Website contact form submissions</div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
                <tr><th>When</th><th>Client</th><th>Product</th><th>Message</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($inquiries as $i)
                <tr>
                    <td class="small text-nowrap">{{ $i->created_at->format('d M Y H:i') }}</td>
                    <td>
                        <div class="fw-semibold">{{ $i->name }}</div>
                        <div class="small text-muted">{{ $i->email }} @if($i->company)· {{ $i->company }}@endif</div>
                        @if($i->phone)<div class="small">{{ $i->phone }}</div>@endif
                    </td>
                    <td>{{ $i->product?->name ?? 'General' }}</td>
                    <td class="small"><strong>{{ $i->subject }}</strong><br>{{ Str::limit($i->message, 80) }}</td>
                    <td>{!! status_badge($i->status) !!}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.inquiries.update', $i) }}" class="d-flex gap-1">
                            @csrf
                            <select name="status" class="form-select form-select-sm">
                                @foreach(['New','Contacted','Closed'] as $s)
                                    <option @selected($i->status===$s)>{{ $s }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-sm btn-outline-primary">Save</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No inquiries yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
