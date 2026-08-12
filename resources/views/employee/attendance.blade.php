@extends('layouts.app')
@section('title', 'Attendance')
@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3">@include('components.kpi', ['title'=>'This Month %','value'=>$attendancePct.'%','icon'=>'bi-graph-up','color'=>'primary'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'Late After','value'=>$lateThreshold,'icon'=>'bi-alarm','color'=>'warning'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'Approved OT Hrs','value'=>$otHours,'icon'=>'bi-hourglass','color'=>'info'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'Rule','value'=>'3 late = 1 absence','icon'=>'bi-exclamation-triangle','color'=>'danger'])</div>
</div>
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card card-panel">
            <div class="card-body">
                <h2 class="h6 fw-semibold">Today — {{ now()->format('d M Y') }}</h2>
                <p class="mb-1 small">Check-in: <strong>{{ $today?->check_in ?? '—' }}</strong></p>
                <p class="mb-3 small">Check-out: <strong>{{ $today?->check_out ?? '—' }}</strong></p>
                @if($holidayLabel)
                    <div class="alert alert-info py-2 mb-3">
                        Today is a <strong>{{ $holidayLabel }}</strong>. No absence will be recorded for this non-working day.
                    </div>
                @endif
                <form method="POST" action="{{ route('employee.attendance.punch') }}">
                    @csrf
                    <button class="btn btn-primary w-100" @disabled($holidayLabel || ($today?->check_in && $today?->check_out))>
                        @if(!$today?->check_in) Punch In
                        @elseif(!$today?->check_out) Punch Out
                        @else Already Complete
                        @endif
                    </button>
                </form>
                <button type="button" class="btn btn-outline-secondary btn-sm w-100 mt-2" id="offlinePunchBtn">Queue Offline Punch</button>
                <script>
                document.getElementById('offlinePunchBtn')?.addEventListener('click', async () => {
                    const type = @json(!$today?->check_in ? 'in' : 'out');
                    const body = {
                        client_punched_at: new Date().toISOString(),
                        punch_type: type,
                        device_id: 'web-pwa'
                    };
                    try {
                        const res = await fetch(@json(route('employee.attendance.offline')), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(body)
                        });
                        const data = await res.json();
                        alert(data.ok ? 'Offline punch applied ('+data.status+')' : (data.message || 'Failed'));
                        if (data.ok) location.reload();
                    } catch (e) { alert('Network error — punch will retry when online in a full PWA build.'); }
                });
                </script>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Attendance History</div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light"><tr><th>Date</th><th>In</th><th>Out</th><th>Hours</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($records as $r)
                        <tr>
                            <td>{{ $r->date->format('D, d M Y') }}</td>
                            <td>{{ $r->check_in ?: '—' }}</td>
                            <td>{{ $r->check_out ?: '—' }}</td>
                            <td>{{ $r->hours }}</td>
                            <td>{!! status_badge($r->status) !!}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No records yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
