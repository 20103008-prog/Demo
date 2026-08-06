@extends('layouts.app')
@section('title', 'Tax & PF')
@section('content')
<div class="row g-3 mb-3">
    <div class="col-lg-5">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">FY Tax Slabs (New Regime)</div>
            <ul class="list-group list-group-flush">
                @foreach([
                    ['Up to ₹3L','Nil'],['₹3L – ₹7L','5%'],['₹7L – ₹10L','10%'],
                    ['₹10L – ₹12L','15%'],['₹12L – ₹15L','20%'],['Above ₹15L','30%'],
                ] as [$slab,$rate])
                    <li class="list-group-item d-flex justify-content-between"><span>{{ $slab }}</span><strong>{{ $rate }}</strong></li>
                @endforeach
            </ul>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Employee PF / TDS Snapshot</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Employee</th><th>Annual Est.</th><th>Monthly TDS*</th><th>PF (12% basic)</th></tr></thead>
                    <tbody>
                    @foreach($employees as $e)
                        @php $basic = $e->salary * 0.6; $annual = $e->salary * 12; @endphp
                        <tr>
                            <td>{{ $e->name }}</td>
                            <td>{{ inr($annual) }}</td>
                            <td>{{ inr($e->salary * 0.08) }}</td>
                            <td>{{ inr($basic * 0.12) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white small text-muted">* Illustrative monthly TDS estimate based on salary.</div>
        </div>
    </div>
</div>
@endsection
