@extends('layouts.app')
@section('title', 'Investment Proofs')
@section('content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card card-panel">
            <div class="card-body">
                <form method="POST" action="{{ route('employee.investments.store') }}" enctype="multipart/form-data">@csrf
                    <input type="number" name="fiscal_year" value="{{ now()->year }}" class="form-control mb-2" required>
                    <select name="category" class="form-select mb-2">@foreach(['dps','life_insurance','donation','sanchaypatra','other'] as $c)<option>{{ $c }}</option>@endforeach</select>
                    <input type="number" step="0.01" name="amount" class="form-control mb-2" placeholder="Amount" required>
                    <input type="file" name="file" class="form-control mb-2">
                    <button class="btn btn-primary btn-sm w-100">Submit Proof</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        @foreach($proofs as $p)
            <div class="border rounded-3 p-3 mb-2">{{ $p->category }} · FY {{ $p->fiscal_year }} · {{ money($p->amount) }} {!! status_badge($p->status) !!}</div>
        @endforeach
    </div>
</div>
@endsection
