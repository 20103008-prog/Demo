@extends('layouts.app')
@section('title', 'Appraisals')
@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">New Appraisal</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.appraisals.store') }}">@csrf
                    <select name="user_id" class="form-select mb-2" required>@foreach($employees as $e)<option value="{{ $e->id }}">{{ $e->name }}</option>@endforeach</select>
                    <input type="number" name="year" value="{{ now()->year }}" class="form-control mb-2" required>
                    <select name="period" class="form-select mb-2"><option>Annual</option><option>Mid-year</option></select>
                    <input type="number" step="0.1" name="score" class="form-control mb-2" placeholder="Score 0-100" required>
                    <input type="number" step="0.1" name="recommended_increment_pct" class="form-control mb-2" placeholder="Increment %" required>
                    <textarea name="comments" class="form-control mb-2" rows="2"></textarea>
                    <button class="btn btn-primary btn-sm w-100">Submit</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card card-panel">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light"><tr><th>Code</th><th>Employee</th><th>Score</th><th>Inc %</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    @foreach($reviews as $r)
                        <tr>
                            <td>{{ $r->code }}</td>
                            <td>{{ $r->user->name }}</td>
                            <td>{{ $r->score }}</td>
                            <td>{{ $r->recommended_increment_pct }}%</td>
                            <td>{!! status_badge($r->status) !!}</td>
                            <td>
                                @if($r->status !== 'Applied')
                                <form method="POST" action="{{ route('admin.appraisals.apply', $r) }}">@csrf
                                    <button class="btn btn-sm btn-success">Apply Increment</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
