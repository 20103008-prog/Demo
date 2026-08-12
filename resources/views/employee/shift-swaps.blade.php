@extends('layouts.app')
@section('title', 'Shift Swap')
@section('content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card card-panel">
            <div class="card-body">
                <form method="POST" action="{{ route('employee.swaps.store') }}">@csrf
                    <select name="target_user_id" class="form-select mb-2" required>@foreach($colleagues as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>
                    <input type="date" name="date" class="form-control mb-2" required>
                    <textarea name="reason" class="form-control mb-2" rows="2"></textarea>
                    <button class="btn btn-primary btn-sm w-100">Request Swap</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        @foreach($swaps as $s)
            <div class="border rounded-3 p-3 mb-2">{{ $s->requester_id === auth()->id() ? 'You → '.$s->targetUser->name : $s->requester->name.' → You' }} · {{ $s->date->format('d M Y') }} {!! status_badge($s->status) !!}</div>
        @endforeach
    </div>
</div>
@endsection
