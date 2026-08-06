<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HR Payroll') — Smart HR & Payroll Software</title>
    <meta name="description" content="@yield('meta', 'HR Payroll Management System for modern teams — attendance, leave, payroll, tax, loans and more.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .site-nav { background: #0f1f4b; }
        .site-hero {
            background: linear-gradient(135deg, #1E3A8A 0%, #1e40af 45%, #2563eb 100%);
            color: #fff;
            padding: 5rem 0 4rem;
        }
        .site-section { padding: 4rem 0; }
        .product-card {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            height: 100%;
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.1);
        }
        .price-tag { font-size: 1.75rem; font-weight: 700; color: #1e3a8a; }
        .feature-icon {
            width: 48px; height: 48px; border-radius: .75rem;
            display: inline-flex; align-items: center; justify-content: center;
            background: #dbeafe; color: #1d4ed8; font-size: 1.25rem;
        }
        .site-footer { background: #0f172a; color: #94a3b8; padding: 3rem 0 1.5rem; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark site-nav sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('site.home') }}">
            <i class="bi bi-briefcase"></i> HR Payroll
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#siteNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="siteNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('site.home') ? 'active' : '' }}" href="{{ route('site.home') }}">Home</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('site.products*') ? 'active' : '' }}" href="{{ route('site.products') }}">Products</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('site.contact') ? 'active' : '' }}" href="{{ route('site.contact') }}">Contact</a></li>
                <li class="nav-item"><a class="btn btn-sm btn-outline-light ms-lg-2" href="{{ route('login') }}">Staff Login</a></li>
                <li class="nav-item"><a class="btn btn-sm btn-warning text-dark ms-lg-1" href="{{ route('site.contact') }}">Request Demo</a></li>
            </ul>
        </div>
    </div>
</nav>

@if(session('success'))
    <div class="container mt-3">
        <div class="alert alert-success alert-dismissible fade show py-2">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
@endif

@yield('content')

<footer class="site-footer mt-auto">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-5">
                <h5 class="text-white">HR Payroll</h5>
                <p class="small mb-0">Complete HR & Payroll platform for Bangladesh and South Asian businesses — attendance, payroll, tax, PF, loans, and settlements.</p>
            </div>
            <div class="col-md-3">
                <h6 class="text-white">Explore</h6>
                <ul class="list-unstyled small">
                    <li><a class="link-light link-underline-opacity-0" href="{{ route('site.products') }}">All Products</a></li>
                    <li><a class="link-light link-underline-opacity-0" href="{{ route('site.contact') }}">Contact Sales</a></li>
                    <li><a class="link-light link-underline-opacity-0" href="{{ route('login') }}">Employee Portal</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="text-white">Contact</h6>
                <p class="small mb-1">sales@hrpayroll.local</p>
                <p class="small mb-0">+880 1700-000000</p>
            </div>
        </div>
        <hr class="border-secondary my-4">
        <p class="small mb-0 text-center">&copy; {{ date('Y') }} HR Payroll Management System. All rights reserved.</p>
    </div>
</footer>
</body>
</html>
