@extends('layouts.app')
@section('title', 'Team Reports')
@section('content')
<form method="GET" action="{{ route('manager.reports') }}" class="card card-panel mb-3 no-print">
    <div class="card-body d-flex flex-wrap gap-2 align-items-end">
        <div>
            <label class="form-label small mb-1">Month</label>
            <select name="month" class="form-select form-select-sm">
                @for($m=1;$m<=12;$m++)
                    <option value="{{ $m }}" @selected($month==$m)>{{ DateTime::createFromFormat('!m', $m)->format('M') }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="form-label small mb-1">Year</label>
            <select name="year" class="form-select form-select-sm">
                @for($y=now()->year-1;$y<=now()->year+1;$y++)
                    <option value="{{ $y }}" @selected($year==$y)>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <button class="btn btn-sm btn-primary">Apply</button>
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('manager.reports.export', ['month' => $month, 'year' => $year]) }}">
            <i class="bi bi-download me-1"></i>CSV
        </a>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i>Print
        </button>
        <div class="ms-auto small text-muted">{{ $dept ?: 'No department' }} · {{ $periodLabel }}</div>
    </div>
</form>

@if($team->isEmpty())
    <div class="alert alert-warning">No active team members found for your department. Reports will appear once employees are assigned.</div>
@endif

<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">@include('components.kpi', ['title'=>'Team size','value'=>$team->count(),'icon'=>'bi-people','color'=>'primary'])</div>
    <div class="col-6 col-md-3">@include('components.kpi', ['title'=>'Attendance %','value'=>$attendancePct.'%','icon'=>'bi-graph-up','color'=>'success'])</div>
    <div class="col-6 col-md-3">@include('components.kpi', ['title'=>'Late arrivals','value'=>$lateCount,'icon'=>'bi-alarm','color'=>'warning'])</div>
    <div class="col-6 col-md-3">@include('components.kpi', ['title'=>'Absences','value'=>$absentCount,'icon'=>'bi-x-circle','color'=>'danger'])</div>
    <div class="col-6 col-md-3">@include('components.kpi', ['title'=>'Dept payroll','value'=>inr($payroll),'icon'=>'bi-cash-stack','color'=>'success'])</div>
    <div class="col-6 col-md-3">@include('components.kpi', ['title'=>'Pending leaves','value'=>$pendingLeaves,'icon'=>'bi-calendar-check','color'=>'warning'])</div>
    <div class="col-6 col-md-3">@include('components.kpi', ['title'=>'Pending OT','value'=>$pendingOt,'icon'=>'bi-stopwatch','color'=>'info'])</div>
    <div class="col-6 col-md-3">@include('components.kpi', ['title'=>'TDS','value'=>inr($tdsTotal),'icon'=>'bi-percent','color'=>'warning'])</div>
    <div class="col-6 col-md-3">@include('components.kpi', ['title'=>'PF','value'=>inr($pfTotal),'icon'=>'bi-safe','color'=>'info'])</div>
    <div class="col-6 col-md-3">@include('components.kpi', ['title'=>'Loan deducted','value'=>inr($loanDeducted),'icon'=>'bi-credit-card','color'=>'secondary'])</div>
    <div class="col-6 col-md-3">@include('components.kpi', ['title'=>'Loan outstanding','value'=>inr($loanOutstanding),'icon'=>'bi-wallet2','color'=>'danger'])</div>
    <div class="col-6 col-md-3">@include('components.kpi', ['title'=>'Bonus / settlements','value'=>inr($bonusTotal + $settlementTotal),'icon'=>'bi-award','color'=>'primary'])</div>
</div>

<div class="card card-panel mb-3">
    <div class="card-header bg-white border-0 fw-semibold">Team attendance — {{ $periodLabel }}</div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Employee</th>
                    <th>Title</th>
                    <th class="text-end">Present</th>
                    <th class="text-end">Late</th>
                    <th class="text-end">Absent</th>
                    <th class="text-end">Leave days</th>
                    <th class="text-end">Pending leave</th>
                    <th class="text-end">Net pay</th>
                </tr>
            </thead>
            <tbody>
            @forelse($rows as $row)
                <tr>
                    <td class="fw-semibold">{{ $row['name'] }}</td>
                    <td class="text-muted small">{{ $row['title'] }}</td>
                    <td class="text-end">{{ $row['present'] }}</td>
                    <td class="text-end">{{ $row['late'] }}</td>
                    <td class="text-end">{{ $row['absent'] }}</td>
                    <td class="text-end">{{ $row['leave_days'] }}</td>
                    <td class="text-end">{{ $row['pending_leave'] }}</td>
                    <td class="text-end">{{ inr($row['net']) }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No team data for this period.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card card-panel">
    <div class="card-body">
        <h2 class="h6 fw-semibold mb-3">Overtime hours (last 6 months)</h2>
        <div class="chart-box">
            <canvas id="managerOtChart"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const el = document.getElementById('managerOtChart');
    if (!el || !window.Chart) return;
    new window.Chart(el, @json($otChart));
})();
</script>
@endpush
