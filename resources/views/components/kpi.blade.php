@props(['title', 'value', 'sub' => null, 'icon' => 'bi-graph-up', 'color' => 'primary'])

<div class="card kpi-card h-100">
    <div class="card-body d-flex align-items-start gap-3">
        <div class="kpi-icon bg-{{ $color }}-subtle text-{{ $color }}">
            <i class="bi {{ $icon }}"></i>
        </div>
        <div>
            <div class="kpi-label">{{ $title }}</div>
            <div class="fs-5 fw-bold">{{ $value }}</div>
            @if($sub)<div class="text-muted" style="font-size:12px;">{{ $sub }}</div>@endif
        </div>
    </div>
</div>
