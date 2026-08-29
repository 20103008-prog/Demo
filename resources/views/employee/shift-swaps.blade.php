@extends('layouts.app')
@section('title', 'Shift Swap')
@section('content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Request a swap</div>
            <div class="card-body">
                @if($colleagues->isEmpty())
                    <p class="small text-muted mb-0">No colleagues in your department are available for a swap.</p>
                @else
                <form method="POST" action="{{ route('employee.swaps.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small">Swap with</label>
                        <select name="target_user_id" class="form-select" required>
                            <option value="">Select colleague</option>
                            @foreach($colleagues as $c)
                                <option value="{{ $c->id }}" @selected((string) old('target_user_id') === (string) $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Date</label>
                        <input type="date" name="date" class="form-control"
                            min="{{ today()->toDateString() }}"
                            value="{{ old('date') }}"
                            required>
                        <div class="form-text">Past dates are disabled. Request only for today or a future shift.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Reason <span class="text-muted">(optional)</span></label>
                        <textarea name="reason" class="form-control" rows="2">{{ old('reason') }}</textarea>
                    </div>
                    <button class="btn btn-primary w-100">Request Swap</button>
                </form>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Swap requests</div>
            <div class="card-body">
                @forelse($swaps as $s)
                    <div class="border rounded-3 p-3 mb-2 d-flex justify-content-between align-items-center gap-2">
                        <div>
                            <div class="fw-semibold">
                                {{ $s->requester_id === auth()->id() ? 'You → '.$s->targetUser->name : $s->requester->name.' → You' }}
                            </div>
                            <div class="small text-muted">{{ $s->date->format('d M Y') }}@if($s->reason) · {{ $s->reason }}@endif</div>
                        </div>
                        {!! status_badge($s->status) !!}
                    </div>
                @empty
                    <p class="text-muted small mb-0">No shift swap requests yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
