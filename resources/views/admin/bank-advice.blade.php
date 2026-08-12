@extends('layouts.app')
@section('title', 'Bank Salary Advice')
@section('content')
<form class="card card-panel mb-3">
    <div class="card-body d-flex gap-2 align-items-end flex-wrap">
        <div><label class="form-label small mb-1">Month</label>
            <select name="month" class="form-select form-select-sm">@for($m=1;$m<=12;$m++)<option value="{{ $m }}" @selected($month==$m)>{{ $m }}</option>@endfor</select>
        </div>
        <div><label class="form-label small mb-1">Year</label>
            <select name="year" class="form-select form-select-sm">@for($y=now()->year-1;$y<=now()->year+1;$y++)<option value="{{ $y }}" @selected($year==$y)>{{ $y }}</option>@endfor</select>
        </div>
        <button class="btn btn-sm btn-outline-primary">Load</button>
        <a class="btn btn-sm btn-primary" href="{{ route('admin.bank.advice', ['year'=>$year,'month'=>$month,'download'=>1]) }}">Download BEFTN CSV</a>
    </div>
</form>
<div class="card card-panel">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead class="table-light"><tr><th>Code</th><th>Name</th><th>Bank</th><th>Account</th><th>Routing</th><th>Net</th></tr></thead>
            <tbody>
            @forelse($payslips as $p)
                <tr>
                    <td>{{ $p->user->employee_code }}</td>
                    <td>{{ $p->user->name }}</td>
                    <td>{{ $p->user->bank_name ?: '—' }}</td>
                    <td>{{ $p->user->bank_account ?: '—' }}</td>
                    <td>{{ $p->user->bank_routing ?: '—' }}</td>
                    <td>{{ money($p->net) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No payslips for period.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
