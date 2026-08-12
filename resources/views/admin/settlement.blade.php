@extends('layouts.app')
@section('title', 'Settlement')
@section('content')
<div class="card card-panel">
    <div class="card-header bg-white border-0 fw-semibold">Final Settlement</div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Employee</th><th>Exit</th><th>Years</th><th>Increment</th><th>Final Salary</th><th>Gratuity</th><th>PF</th><th>TDS</th><th>Leave Encash</th><th>Loan</th><th>Net</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            @foreach($settlements as $s)
                <tr>
                    <td>{{ $s->user->name }}</td>
                    <td>{{ $s->exit_date->format('d M Y') }}</td>
                    <td>{{ $s->years_of_service }}</td>
                    <td>{{ $s->last_increment_pct }}%</td>
                    <td>{{ inr($s->final_month_salary) }}</td>
                    <td>{{ inr($s->gratuity ?? 0) }}</td>
                    <td>{{ inr($s->pf_employee) }}</td>
                    <td>{{ inr($s->tds) }}</td>
                    <td>{{ inr($s->leave_encashment) }}</td>
                    <td>{{ inr($s->outstanding_loan) }}</td>
                    <td class="fw-semibold">{{ inr($s->net_settlement) }}</td>
                    <td>{!! status_badge($s->status) !!}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.settlement.update', $s) }}" class="d-flex gap-1">
                            @csrf
                            <select name="status" class="form-select form-select-sm">
                                @foreach(['Initiated','Approved','Paid'] as $st)
                                    <option @selected($s->status===$st)>{{ $st }}</option>
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
