@extends('layouts.app')
@section('title', 'Investment Proofs')
@section('content')
@foreach($proofs as $p)
<div class="card card-panel mb-2">
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <strong>{{ $p->user->name }}</strong> · {{ $p->category }} · FY {{ $p->fiscal_year }} · {{ money($p->amount) }}
            {!! status_badge($p->status) !!}
            @if($p->file_path)<a class="small ms-2" href="{{ asset('storage/'.$p->file_path) }}" target="_blank">File</a>@endif
        </div>
        @if($p->status === 'Pending')
        <form method="POST" action="{{ route('admin.investments.review', $p) }}" class="d-flex gap-1">@csrf
            <button name="status" value="Approved" class="btn btn-sm btn-success">Approve</button>
            <button name="status" value="Rejected" class="btn btn-sm btn-outline-danger">Reject</button>
        </form>
        @endif
    </div>
</div>
@endforeach
@endsection
