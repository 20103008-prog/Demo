<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HR Payroll') — {{ config('app.name', 'HR Payroll') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body data-auth="{{ auth()->check() ? '1' : '0' }}" data-logout-url="{{ route('logout') }}">
@php
    $role = auth()->user()->role;
    $navGroups = match($role) {
        'admin' => [
            'Overview' => [
                ['Dashboard', 'admin.dashboard', 'bi-speedometer2'],
                ['Analytics', 'admin.analytics', 'bi-graph-up-arrow'],
            ],
            'People' => [
                ['Companies', 'admin.companies', 'bi-buildings'],
                ['Employees', 'admin.employees', 'bi-people'],
                ['Roster / Duty', 'admin.roster', 'bi-calendar-range'],
                ['Shift Swaps', 'admin.swaps', 'bi-arrow-left-right'],
                ['Biometrics', 'admin.biometrics', 'bi-fingerprint'],
            ],
            'Payroll' => [
                ['Payroll', 'admin.payroll', 'bi-cash-stack'],
                ['Payroll Approvals', 'admin.payroll.approvals', 'bi-shield-check'],
                ['Bank Advice', 'admin.bank.advice', 'bi-bank2'],
                ['Tax & PF (NBR)', 'admin.taxpf', 'bi-percent'],
                ['Loans', 'admin.loans', 'bi-credit-card'],
                ['Bonus & Increment', 'admin.bonus', 'bi-award'],
                ['Settlement', 'admin.settlement', 'bi-bank'],
            ],
            'Compliance' => [
                ['Investments', 'admin.investments', 'bi-piggy-bank'],
                ['Appraisals', 'admin.appraisals', 'bi-clipboard-data'],
                ['Leave Policies', 'admin.leave.policies', 'bi-journal-text'],
                ['Documents', 'admin.documents', 'bi-folder'],
                ['AI Queries', 'admin.queries', 'bi-robot'],
            ],
            'System' => [
                ['Products', 'admin.products', 'bi-box-seam'],
                ['Inquiries', 'admin.inquiries', 'bi-envelope'],
                ['Reports', 'admin.reports', 'bi-file-earmark-bar-graph'],
                ['Audit Logs', 'admin.audit', 'bi-shield-lock'],
            ],
        ],
        'manager' => [
            'Team' => [
                ['Dashboard', 'manager.dashboard', 'bi-speedometer2'],
                ['My Team', 'manager.team', 'bi-people'],
                ['Team Attendance', 'manager.attendance', 'bi-people-fill'],
                ['Leave Approvals', 'manager.leaves', 'bi-check2-square'],
                ['OT Approvals', 'manager.overtime', 'bi-stopwatch'],
                ['Reports', 'manager.reports', 'bi-bar-chart'],
            ],
            'My self-service' => [
                ['My Attendance', 'employee.attendance', 'bi-clock'],
                ['Leave', 'employee.leave', 'bi-calendar3'],
                ['Overtime Request', 'employee.overtime', 'bi-hourglass'],
                ['Shift Swap', 'employee.swaps', 'bi-arrow-left-right'],
                ['Documents', 'employee.documents', 'bi-folder'],
                ['Investments', 'employee.investments', 'bi-piggy-bank'],
                ['Payroll', 'employee.payroll', 'bi-cash'],
                ['Queries', 'employee.queries', 'bi-chat-dots'],
            ],
        ],
        default => [
            'Daily' => [
                ['Dashboard', 'employee.dashboard', 'bi-speedometer2'],
                ['Attendance', 'employee.attendance', 'bi-clock'],
                ['Leave', 'employee.leave', 'bi-calendar3'],
                ['Overtime', 'employee.overtime', 'bi-hourglass'],
                ['Shift Swap', 'employee.swaps', 'bi-arrow-left-right'],
            ],
            'Account' => [
                ['Documents', 'employee.documents', 'bi-folder'],
                ['Investments', 'employee.investments', 'bi-piggy-bank'],
                ['Appraisals', 'employee.appraisals', 'bi-clipboard-data'],
                ['Payroll', 'employee.payroll', 'bi-cash'],
                ['Queries', 'employee.queries', 'bi-chat-dots'],
                ['2FA Security', 'employee.twofactor', 'bi-shield-lock'],
            ],
        ],
    };
    $roleLabel = match($role) {
        'admin' => 'Admin / HR Portal',
        'manager' => 'Manager Portal',
        default => 'Employee Portal',
    };
    $notifications = auth()->user()->appNotifications()->latest()->limit(8)->get();
    $unread = $notifications->where('is_read', false)->count();
@endphp

<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<div class="app-shell">
    <aside class="sidebar" id="appSidebar">
        <div class="sidebar-brand">
            <div class="d-flex align-items-center gap-3 min-w-0">
                <div class="logo-box"><i class="bi bi-briefcase text-white"></i></div>
                <div class="min-w-0 overflow-hidden">
                    <div class="fw-bold small text-white lh-1 text-truncate">HR Payroll</div>
                    <div class="text-uppercase text-truncate" style="font-size:10px;color:#93c5fd;letter-spacing:.06em;">{{ $roleLabel }}</div>
                </div>
            </div>
        </div>
        <nav class="sidebar-nav">
            @foreach($navGroups as $group => $items)
                <div class="sidebar-section-label">{{ $group }}</div>
                @foreach($items as [$label, $route, $icon])
                    @if(\Illuminate\Support\Facades\Route::has($route))
                    <a href="{{ route($route) }}" class="nav-link {{ request()->routeIs($route) ? 'active' : '' }}">
                        <i class="bi {{ $icon }}"></i>
                        <span>{{ $label }}</span>
                    </a>
                    @endif
                @endforeach
            @endforeach
        </nav>
        <div class="sidebar-user">
            <div class="d-flex align-items-center gap-2 min-w-0">
                <span class="avatar-circle" style="background:{{ auth()->user()->avatarColor() }}">{{ auth()->user()->initials() }}</span>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="text-white small fw-semibold text-truncate">{{ auth()->user()->name }}</div>
                    <div class="text-truncate" style="font-size:10px;color:#93c5fd;">{{ auth()->user()->department }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-link text-decoration-none p-0" style="color:#93c5fd;" title="Logout"><i class="bi bi-box-arrow-right"></i></button>
                </form>
            </div>
        </div>
    </aside>

    <div class="main-content">
        <header class="topbar">
            
            
            <button class="btn btn-sm btn-outline-secondary d-lg-none me-2" id="sidebarToggle" type="button">
                <i class="bi bi-list"></i>
            </button>
            <h1 class="h6 mb-0 fw-semibold text-secondary flex-grow-1">@yield('title', 'Dashboard')</h1>

            <form method="POST" action="{{ route('locale.set') }}" class="me-2">
                @csrf
                <select name="locale" class="form-select form-select-sm" onchange="this.form.submit()" style="width:auto">
                    <option value="en" @selected(app()->getLocale()==='en')>EN</option>
                    <option value="bn" @selected(app()->getLocale()==='bn')>বাংলা</option>
                </select>
            </form>

            @if($role === 'admin')
            <div class="dropdown me-2">
                <button class="btn btn-sm btn-light d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-people me-1"></i>
                    <span class="d-none d-md-inline">People</span>
                </button>
                <ul class="dropdown-menu shadow" style="min-width:220px;">
                    <li class="dropdown-header text-muted small">WORKFORCE</li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="{{ route('admin.employees') }}">
                            <i class="bi bi-people me-2"></i>
                            Employees
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="{{ route('admin.departments') }}">
                            <i class="bi bi-diagram-2 me-2"></i>
                            Departments
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="{{ route('admin.roster') }}">
                            <i class="bi bi-calendar-check me-2"></i>
                            Roster / Duty
                        </a>
                    </li>
                </ul>
            </div>
            @endif

            <div class="dropdown">
                <button class="btn btn-sm btn-light position-relative" data-bs-toggle="dropdown">
                    <i class="bi bi-bell"></i>
                    @if($unread)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $unread }}</span>
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow" style="width:320px;max-height:360px;overflow:auto;">
                    <li class="dropdown-header fw-semibold">Notifications</li>
                    @forelse($notifications as $n)
                        <li>
                            <div class="dropdown-item-text px-3 py-2 {{ $n->is_read ? '' : 'bg-light' }}">
                                <div class="fw-semibold small">{{ $n->title }}</div>
                                <div class="text-muted" style="font-size:12px;">{{ $n->body }}</div>
                            </div>
                        </li>
                    @empty
                        <li><span class="dropdown-item-text text-muted small">No notifications</span></li>
                    @endforelse
                </ul>
            </div>
        </header>

        <main class="page-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show py-2">{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show py-2">{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger py-2">
                    <ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>

<div class="modal fade" id="sessionWarnModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Session expiring</h5>
            </div>
            <div class="modal-body small">You will be logged out in about a minute due to inactivity.</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal" onclick="location.reload()">Stay signed in</button>
            </div>
        </div>
    </div>
</div>
@stack('scripts')
</body>
</html>
