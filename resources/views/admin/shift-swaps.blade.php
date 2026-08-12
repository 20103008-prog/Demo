@extends('layouts.app')
@section('title', 'Shift Swaps')
@section('content')
@foreach($swaps as $s)
<div class="card card-panel mb-2">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>{{ $s->requester->name }} ↔ {{ $s->targetUser->name }} on {{ $s->date->format('d M Y') }} {!! status_badge($s->status) !!}</div>
        @if($s->status==='Pending')
        <form method="POST" action="{{ route('admin.swaps.review', $s) }}" class="d-flex gap-1">@csrf
            <button name="status" value="Approved" class="btn btn-sm btn-success">Approve</button>
            <button name="status" value="Rejected" class="btn btn-sm btn-outline-danger">Reject</button>
        </form>
        @endif
    </div>
</div>
@endforeach
@endsection
