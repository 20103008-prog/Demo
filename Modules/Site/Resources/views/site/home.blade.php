@extends('layouts.site')
@section('title', 'Home')

@section('content')
<section class="site-hero">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <p class="text-uppercase small mb-2" style="letter-spacing:.08em;opacity:.85;">HR & Payroll Software</p>
                <h1 class="display-5 fw-bold mb-3">Run payroll & HR without spreadsheets</h1>
                <p class="lead mb-4" style="opacity:.9;">Attendance, leave, salary processing, tax & PF, loans, bonuses, and settlements — one Laravel + Bootstrap platform your team can trust.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('site.products') }}" class="btn btn-warning btn-lg text-dark fw-semibold">View Products</a>
                    <a href="{{ route('site.contact') }}" class="btn btn-outline-light btn-lg">Talk to Sales</a>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="bg-white text-dark rounded-4 p-4 shadow">
                    <div class="row text-center g-3">
                        <div class="col-4">
                            <div class="fs-3 fw-bold text-primary">{{ $stats['modules'] }}</div>
                            <div class="small text-muted">Products</div>
                        </div>
                        <div class="col-4">
                            <div class="fs-3 fw-bold text-primary">{{ $stats['employees'] }}+</div>
                            <div class="small text-muted">Demo Staff</div>
                        </div>
                        <div class="col-4">
                            <div class="fs-3 fw-bold text-primary">3</div>
                            <div class="small text-muted">Role Portals</div>
                        </div>
                    </div>
                    <hr>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Employee · Manager · Admin portals</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Live MySQL database (<code>hrpayroll</code>)</li>
                        <li class="mb-0"><i class="bi bi-check-circle-fill text-success me-2"></i>Payslips, PF, TDS & loan EMI</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="site-section bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Featured products</h2>
            <p class="text-muted">Everything your clients need — loaded live from the database.</p>
        </div>
        <div class="row g-4">
            @foreach($featured as $product)
                <div class="col-md-6 col-lg-4">
                    <div class="card product-card">
                        <div class="card-body p-4">
                            @if($product->badge)
                                <span class="badge text-bg-primary mb-2">{{ $product->badge }}</span>
                            @endif
                            <div class="feature-icon mb-3"><i class="bi {{ $product->icon }}"></i></div>
                            <h3 class="h5 fw-bold">{{ $product->name }}</h3>
                            <p class="text-muted small">{{ $product->short_description }}</p>
                            <div class="price-tag mb-3">{{ $product->formatPrice() }}</div>
                            <a href="{{ route('site.product', $product->slug) }}" class="btn btn-outline-primary btn-sm">View details</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="text-center mt-4">
            <a href="{{ route('site.products') }}" class="btn btn-primary">Browse all products</a>
        </div>
    </div>
</section>

<section class="site-section" style="background:#f8fafc;">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <h2 class="fw-bold mb-3">Built for real HR operations</h2>
                <p class="text-muted">From punch-in to final settlement, every module stores data in MySQL so dashboards, reports, and payslips stay dynamic.</p>
                <div class="row g-3 mt-2">
                    @foreach([
                        ['bi-person-badge', 'Role-based access'],
                        ['bi-receipt', 'Payslip engine'],
                        ['bi-graph-up', 'Live reports'],
                        ['bi-shield-check', 'Audit logs'],
                    ] as [$icon, $label])
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi {{ $icon }} text-primary fs-5"></i>
                                <span class="fw-semibold small">{{ $label }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-6">
                <div class="bg-white rounded-4 p-4 shadow-sm">
                    <h3 class="h6 fw-bold mb-3">Ready to see it live?</h3>
                    <p class="small text-muted">Request a demo or sign in with the seeded staff accounts.</p>
                    <a href="{{ route('site.contact') }}" class="btn btn-primary me-2">Contact sales</a>
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary">Staff login</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
