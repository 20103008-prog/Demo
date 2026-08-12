@extends('layouts.app')
@section('title', 'HR Queries')
@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Submit Query</div>
            <div class="card-body">
                <form method="POST" action="{{ route('employee.queries.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small">Category</label>
                        <select name="category" class="form-select" required>
                            @foreach(['Payroll','Leave','Attendance','Loan','Tax & PF','Other'] as $c)
                                <option>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Priority</label>
                        <select name="priority" class="form-select">
                            <option>Medium</option><option>High</option><option>Low</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Subject</label>
                        <input name="subject" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Description</label>
                        <textarea name="description" class="form-control" rows="4" required></textarea>
                    </div>
                    <button class="btn btn-primary w-100">Submit</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">My Queries</div>
            <div class="card-body">
                @forelse($queries as $q)
                    <div class="border rounded-3 p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold">{{ $q->subject }} <span class="text-muted small">{{ $q->code }}</span></div>
                                <div class="small text-muted">{{ $q->category }} · {{ $q->submitted_on->format('d M Y') }}</div>
                            </div>
                            <div>{!! status_badge($q->status) !!} {!! status_badge($q->priority) !!}</div>
                        </div>
                        <p class="small mt-2 mb-0">{{ $q->description }}</p>
                        @if($q->ai_draft)
                            <div class="alert alert-info py-2 small mt-2 mb-0">
                                <strong>AI draft</strong>
                                @if($q->ai_confidence)
                                    <span class="badge text-bg-secondary">{{ round($q->ai_confidence * 100) }}%</span>
                                @endif
                                : {{ $q->ai_draft }}
                            </div>
                        @endif
                        @if($q->response)
                            <div class="alert alert-success py-2 small mt-2 mb-0"><strong>HR reply:</strong> {{ $q->response }}</div>
                        @endif
                    </div>
                @empty
                    <p class="text-muted mb-0">No queries yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
