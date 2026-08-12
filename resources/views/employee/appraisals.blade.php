@extends('layouts.app')
@section('title', 'My Appraisals')
@section('content')
<div class="card card-panel">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead class="table-light"><tr><th>Code</th><th>Year</th><th>Score</th><th>Recommended Inc</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($reviews as $r)
                <tr><td>{{ $r->code }}</td><td>{{ $r->year }} / {{ $r->period }}</td><td>{{ $r->score }}</td><td>{{ $r->recommended_increment_pct }}%</td><td>{!! status_badge($r->status) !!}</td></tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No appraisals yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
