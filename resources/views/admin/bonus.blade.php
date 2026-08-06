@extends('layouts.app')
@section('title', 'Bonus & Increment')
@section('content')
<div class="card card-panel mb-3">
    <div class="card-header bg-white border-0 fw-semibold">Festival Bonus</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.bonus.store') }}" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-8">
                <p class="small mb-0">Calculate festival bonus for all active employees at once. The system will use today's date to determine service tenure and save the calculation date automatically.</p>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-primary btn-sm">Calculate Festival Bonus for All</button>
            </div>
        </form>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Code</th><th>Employee</th><th>Festival Bonus</th><th>Service (yrs)</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @foreach($bonuses as $b)
                <tr>
                    <td>{{ $b->code }}</td>
                    <td>{{ $b->user->name }}</td>
                    <td>{{ inr($b->festival_bonus) }}</td>
                    <td>{{ $b->years_of_service }}</td>
                    <td>{!! status_badge($b->status) !!}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.bonus.update', $b) }}" class="d-flex gap-1">
                            @csrf
                            <select name="status" class="form-select form-select-sm" style="width:110px;">
                                @foreach(['Pending','Approved','Paid'] as $s)
                                    <option @selected($b->status===$s)>{{ $s }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-sm btn-outline-primary">Save</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="card card-panel">
    <div class="card-header bg-white border-0 fw-semibold">Salary Increments</div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Code</th><th>Employee</th><th>Current</th><th>%</th><th>New</th><th>Effective</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @foreach($increments as $i)
                <tr>
                    <td>{{ $i->code }}</td>
                    <td>{{ $i->user->name }}</td>
                    <td>{{ inr($i->current_salary) }}</td>
                    <td>{{ $i->increment_pct }}%</td>
                    <td>{{ inr($i->new_salary) }}</td>
                    <td>{{ $i->effective_date->format('d M Y') }}</td>
                    <td>{!! status_badge($i->status) !!}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.increment.update', $i) }}" class="d-flex gap-1">
                            @csrf
                            <select name="status" class="form-select form-select-sm" style="width:110px;">
                                @foreach(['Draft','Approved','Applied'] as $s)
                                    <option @selected($i->status===$s)>{{ $s }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-sm btn-outline-primary">Save</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
