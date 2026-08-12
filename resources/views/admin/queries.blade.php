@extends('layouts.app')
@section('title', 'AI Queries')
@section('content')
@foreach($queries as $q)
<div class="card card-panel mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <div>
                <div class="fw-semibold">{{ $q->subject }} <span class="text-muted small">{{ $q->code }}</span></div>
                <div class="small text-muted">{{ $q->user->name }} · {{ $q->category }} · {{ $q->submitted_on->format('d M Y') }}</div>
            </div>
            <div>{!! status_badge($q->status) !!} {!! status_badge($q->priority) !!}</div>
        </div>
        <p class="small mt-2">{{ $q->description }}</p>
        @if($q->ai_draft)
            <div class="alert alert-info small py-2">
                <strong>AI Draft</strong>
                @if($q->ai_confidence)
                    <span class="badge text-bg-secondary">{{ round($q->ai_confidence * 100) }}% · {{ $q->ai_category }}</span>
                @endif
                @if($q->needs_manual_review)
                    <span class="badge text-bg-warning">Needs manual review</span>
                @endif
                <div class="mt-1">{{ $q->ai_draft }}</div>
            </div>
        @elseif($q->needs_manual_review)
            <div class="alert alert-warning small py-2">Low AI confidence — manual review required.</div>
        @endif
        @if($q->status === 'Pending')
            <form method="POST" action="{{ route('admin.queries.reply', $q) }}">
                @csrf
                <textarea name="response" class="form-control form-control-sm mb-2" rows="3" placeholder="Write reply (AI draft can be edited)..." required>{{ $q->ai_draft }}</textarea>
                <button class="btn btn-sm btn-primary">Send Reply & Resolve</button>
            </form>
        @else
            <div class="alert alert-success small py-2 mb-0"><strong>Reply:</strong> {{ $q->response }}</div>
        @endif
    </div>
</div>
@endforeach
@endsection
