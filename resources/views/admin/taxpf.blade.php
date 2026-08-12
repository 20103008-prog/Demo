@extends('layouts.app')
@section('title', 'NBR Tax & PF')
@section('content')
<div class="alert alert-primary small mb-3">
    <strong>NBR Income Tax (Bangladesh)</strong> — FY 2025–26 / AY 2026–27 slabs.
    Employment deduction = min(1/3 of annual salary, ৳5,00,000). Minimum tax ৳5,000 when income exceeds tax-free limit.
    Category-wise tax-free: General ৳3,75,000 · Woman/Senior ৳4,25,000 · Disabled ৳5,00,000 · Freedom Fighter ৳5,25,000.
</div>
<div class="row g-3 mb-3">
    <div class="col-lg-5">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">NBR Tax Slabs (General)</div>
            <ul class="list-group list-group-flush">
                @forelse($slabs as $slab)
                    <li class="list-group-item d-flex justify-content-between"><span>{{ $slab['label'] }}</span><strong>{{ $slab['rate'] }}</strong></li>
                @empty
                    <li class="list-group-item text-muted">No tax slabs configured.</li>
                @endforelse
            </ul>
            <div class="card-footer bg-white small text-muted">PF rates — Employee: {{ $pfRates['employee'] }}% · Employer: {{ $pfRates['employer'] }}% (of basic)</div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Employee NBR Tax / PF Snapshot</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Category</th>
                            <th>Annual</th>
                            <th>Emp. Deduction</th>
                            <th>Assessable</th>
                            <th>Annual Tax</th>
                            <th>Monthly TDS</th>
                            <th>PF Emp</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($taxRows as $row)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td class="small">{{ $row['category'] }}</td>
                            <td>{{ money($row['annual']) }}</td>
                            <td>{{ money($row['employment_deduction']) }}</td>
                            <td>{{ money($row['taxable']) }}</td>
                            <td>{{ money($row['annual_tax']) }}</td>
                            <td class="fw-semibold">{{ money($row['monthly_tds']) }}</td>
                            <td>{{ money($row['pf_employee']) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white small text-muted">TDS is computed from NBR progressive slabs and deducted monthly via payroll.</div>
        </div>
    </div>
</div>
@endsection
