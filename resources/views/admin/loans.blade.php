@extends('layouts.app')
@section('title', 'Loans')
@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Register Loan</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.loans.store') }}">
                    @csrf
                    <div class="mb-2">
                        <select name="user_id" class="form-select form-select-sm" required>
                            @foreach($employees as $e)<option value="{{ $e->id }}">{{ $e->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <select name="type" class="form-select form-select-sm">
                            @foreach(['Personal','Housing','Car','Education'] as $t)<option>{{ $t }}</option>@endforeach
                        </select>
                    </div>
                    <div class="mb-2"><input name="amount" type="number" class="form-control form-control-sm" placeholder="Amount" required></div>
                    <div class="mb-2"><input name="installments" type="number" class="form-control form-control-sm" placeholder="Installments" min="1" required></div>
                    <div class="mb-2"><input name="start_date" type="date" class="form-control form-control-sm" required></div>
                    <button class="btn btn-primary btn-sm w-100">Save Loan</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Loan Register</div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light"><tr><th>Code</th><th>Employee</th><th>Type</th><th>Amount</th><th>Installments</th><th>EMI</th><th>Outstanding</th><th>Status</th></tr></thead>
                    <tbody>
                    @foreach($loans as $l)
                        <tr>
                            <td>{{ $l->code }}</td>
                            <td>{{ $l->user->name }}</td>
                            <td>{{ $l->type }}</td>
                            <td>{{ inr($l->amount) }}</td>
                            <td>{{ $l->installments }}</td>
                            <td>{{ inr($l->emi) }}</td>
                            <td>{{ inr($l->outstanding) }}</td>
                            <td>{!! status_badge($l->status) !!}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
