@extends('layouts.app')
@section('title', 'Add Employee')
@section('content')
<div class="container-fluid max-width-lg px-0 px-md-2" style="max-width: 960px;">
    <!-- Top Header -->
    <div class="mb-4">
        <a href="{{ route('admin.employees') }}" class="text-decoration-none text-secondary small fw-medium d-inline-flex align-items-center gap-1 mb-2">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <h3 class="fw-bold mb-1 d-flex align-items-center gap-2 text-dark">
            <i class="bi bi-person-plus text-primary fs-3"></i> Add Employee
        </h3>
        <p class="text-secondary small mb-0">Link a device code to a real person.</p>
    </div>

    <!-- Form Card -->
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-4 bg-white mb-5">
        <form method="POST" action="{{ route('admin.employees.store') }}">
            @csrf

            <!-- Row 1: Full name & Status -->
            <div class="row g-3 mb-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold text-dark small mb-1">Full name</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                        <input type="text" name="name" class="form-control bg-light border-start-0 ps-0" placeholder="e.g. John Doe" value="{{ old('name') }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-dark small mb-1">Status</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-toggle-on"></i></span>
                        <select name="status" class="form-select bg-light border-start-0 ps-0">
                            <option value="Active" {{ old('status', 'Active') === 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ old('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Row 2: Employee code, Department, Designation -->
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-dark small mb-1">Employee code (device PIN)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-qr-code-scan"></i></span>
                        <input type="text" name="employee_code" class="form-control bg-light border-start-0 ps-0" placeholder="e.g. 1001" value="{{ old('employee_code') }}">
                    </div>
                    <div class="form-text text-muted" style="font-size: 0.8rem; margin-top: 4px;">
                        Must exactly match the PIN / user ID on the ZKTeco device.
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-dark small mb-1">Department <span class="text-muted fw-normal">(optional)</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-building"></i></span>
                        <select name="department" class="form-select bg-light border-start-0 ps-0">
                            <option value="">— none —</option>
                            @if(isset($departments) && $departments->count())
                                @foreach($departments as $d)
                                    <option value="{{ $d }}" {{ old('department') == $d ? 'selected' : '' }}>{{ $d }}</option>
                                @endforeach
                            @else
                                @foreach(['Engineering', 'Finance', 'HR', 'Marketing', 'Operations', 'Sales'] as $d)
                                    <option value="{{ $d }}" {{ old('department') == $d ? 'selected' : '' }}>{{ $d }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-dark small mb-1">Designation <span class="text-muted fw-normal">(optional)</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-briefcase"></i></span>
                        <select name="job_title" class="form-select bg-light border-start-0 ps-0">
                            <option value="">— none —</option>
                            @if(isset($designations) && $designations->count())
                                @foreach($designations as $t)
                                    <option value="{{ $t }}" {{ old('job_title') == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            @else
                                @foreach(['Software Engineer', 'Senior Developer', 'HR Executive', 'Accountant', 'Manager', 'Staff'] as $t)
                                    <option value="{{ $t }}" {{ old('job_title') == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            </div>

            <!-- Row 3: Email, Phone, Joining date -->
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-dark small mb-1">Email <span class="text-muted fw-normal">(optional)</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control bg-light border-start-0 ps-0" placeholder="name@example.com" value="{{ old('email') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-dark small mb-1">Phone <span class="text-muted fw-normal">(optional)</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-telephone"></i></span>
                        <input type="text" name="phone" class="form-control bg-light border-start-0 ps-0" placeholder="e.g. 01700000000" value="{{ old('phone') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-dark small mb-1">Joining date <span class="text-muted fw-normal">(optional)</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-calendar3"></i></span>
                        <input type="date" name="join_date" class="form-control bg-light border-start-0 ps-0" value="{{ old('join_date') }}">
                    </div>
                </div>
            </div>

            <!-- Row 4: Weekly off (weekend) -->
            <div class="mb-4">
                <label class="form-label fw-semibold text-dark small mb-1">Weekly off <span class="text-muted fw-normal">(weekend)</span></label>
                <div class="card border border-light-subtle bg-light-subtle p-3 rounded-3">
                    <div class="d-flex flex-wrap gap-4 align-items-center">
                        @php
                            $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                            $defaultOff = old('weekly_off', ['Friday', 'Saturday']);
                        @endphp
                        @foreach($days as $day)
                            <div class="form-check me-2">
                                <input class="form-check-input" type="checkbox" name="weekly_off[]" value="{{ $day }}" id="day_{{ $day }}"
                                    {{ in_array($day, (array)$defaultOff) ? 'checked' : '' }}>
                                <label class="form-check-label text-dark fw-medium" for="day_{{ $day }}">
                                    {{ $day }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="form-text text-muted mt-2" style="font-size: 0.8rem;">
                    Weekend days are excluded from absences, leave day counts, and paid leave in payroll. Leave all unchecked if the employee works every day.
                </div>
            </div>

            <!-- Row 5: Address -->
            <div class="mb-4">
                <label class="form-label fw-semibold text-dark small mb-1">Address <span class="text-muted fw-normal">(optional)</span></label>
                <textarea name="address" class="form-control bg-light" rows="2" placeholder="Street, city...">{{ old('address') }}</textarea>
            </div>

            <hr class="my-4 text-muted opacity-25">

            <!-- Row 6: Portal login card -->
            <div class="card border-0 bg-light p-3 p-md-4 rounded-4 mb-4">
                <div class="d-flex align-items-start gap-3">
                    <div class="fs-4 text-dark mt-1"><i class="bi bi-phone"></i></div>
                    <div class="w-100">
                        <h6 class="fw-bold mb-1 text-dark">Portal login</h6>
                        <p class="text-muted small mb-3">
                            Lets this employee sign in at the same login page to view attendance, request leave, and download payslips.
                        </p>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" name="portal_login" value="1" id="portalLoginSwitch" {{ old('portal_login', true) ? 'checked' : '' }} onchange="togglePortalLoginFields(this.checked)">
                            <label class="form-check-label fw-medium text-dark ms-2" for="portalLoginSwitch">
                                Enable employee portal login
                            </label>
                        </div>

                        <!-- Expanded fields when portal login is enabled -->
                        <div id="portalLoginFields" class="{{ old('portal_login', true) ? '' : 'd-none' }}">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small mb-1">Login email</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                        <input type="email" name="login_email" class="form-control bg-white border-start-0 ps-0" placeholder="employee@example.com" value="{{ old('login_email') }}">
                                    </div>
                                    <div class="form-text text-muted" style="font-size: 0.8rem; margin-top: 4px;">
                                        Used to sign in. Defaults to the employee email when set.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small mb-1">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                        <input type="password" name="password" class="form-control bg-white border-start-0 ps-0" placeholder="••••••••">
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small mb-1">Confirm password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                        <input type="password" name="password_confirmation" class="form-control bg-white border-start-0 ps-0" placeholder="••••••••">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold rounded-3">
                    <i class="bi bi-check-lg me-1"></i> Create
                </button>
                <a href="{{ route('admin.employees') }}" class="btn btn-outline-secondary px-4 py-2 fw-semibold rounded-3">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    function togglePortalLoginFields(checked) {
        const fields = document.getElementById('portalLoginFields');
        if (fields) {
            if (checked) {
                fields.classList.remove('d-none');
            } else {
                fields.classList.add('d-none');
            }
        }
    }
</script>
@endsection
