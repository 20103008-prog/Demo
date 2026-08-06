@extends('layouts.app')
@section('title', 'Roster / Duty')
@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="mb-4">
        <h4 class="fw-bold mb-1 d-flex align-items-center gap-2 text-dark">
            <i class="bi bi-calendar-range text-primary fs-3"></i> Roster / Duty
        </h4>
        <p class="text-secondary small mb-0">Define shifts and assign them to employees by date range. Late detection uses the assigned shift when available.</p>
    </div>

    <!-- Top Cards Row -->
    <div class="row g-4 mb-4">
        <!-- Shifts Card -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-clock text-dark fs-5"></i>
                        <h5 class="fw-bold mb-0 text-dark">Shifts</h5>
                    </div>
                    <button class="btn btn-primary btn-sm px-3 rounded-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#addShiftModal">
                        <i class="bi bi-plus-lg me-1"></i> Add shift
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <thead>
                            <tr class="text-muted small border-bottom">
                                <th style="width: 40%;" class="ps-2">NAME</th>
                                <th style="width: 35%;">HOURS</th>
                                <th style="width: 15%;">GRACE</th>
                                <th style="width: 10%;" class="text-end pe-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shifts as $shift)
                                <tr class="border-bottom-0">
                                    <td class="ps-2 py-3">
                                        <div class="fw-bold text-dark fs-6">{{ $shift->name }}</div>
                                        <div class="text-muted small" style="font-size: 0.8rem;">{{ $shift->assignments_count }} active assignments</div>
                                    </td>
                                    <td class="py-3 text-dark fw-medium">{{ $shift->formatted_hours }}</td>
                                    <td class="py-3 text-dark fw-medium">{{ $shift->grace_minutes }}m</td>
                                    <td class="text-end pe-2 py-3">
                                        <div class="d-inline-flex gap-1">
                                            <button class="btn btn-outline-secondary btn-sm rounded-3 px-2 py-1" data-bs-toggle="modal" data-bs-target="#editShiftModal_{{ $shift->id }}" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" action="{{ route('admin.shifts.destroy', $shift) }}" onsubmit="return confirm('Delete shift?')" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-3 px-2 py-1" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4 small">No shifts defined yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Assign Shift Card -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white h-100">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-person-check text-dark fs-5"></i>
                    <h5 class="fw-bold mb-0 text-dark">Assign shift</h5>
                </div>

                <form method="POST" action="{{ route('admin.shift.assignments.store') }}">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Employee</label>
                            <select name="user_id" class="form-select bg-light border-0 py-2 rounded-3" required>
                                <option value="">Select...</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} (Code: {{ $emp->employee_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Shift</label>
                            <select name="shift_id" class="form-select bg-light border-0 py-2 rounded-3" required>
                                <option value="">Select...</option>
                                @foreach($shifts as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->formatted_hours }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold text-dark small mb-1">From</label>
                            <input type="date" name="from_date" class="form-control bg-light border-0 py-2 rounded-3" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold text-dark small mb-1">To <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="date" name="to_date" class="form-control bg-light border-0 py-2 rounded-3">
                            <div class="form-text text-muted" style="font-size: 0.78rem;">Leave blank for ongoing.</div>
                        </div>
                        <div class="col-md-2 text-end">
                            <button type="submit" class="btn btn-primary w-100 py-2 rounded-3" title="Save Assignment">
                                <i class="bi bi-check-lg fs-5"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Assignments Bottom Card -->
    <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white">
        <div class="d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-list-ul text-dark fs-5"></i>
            <h5 class="fw-bold mb-0 text-dark">Assignments</h5>
        </div>

        <div class="table-responsive">
            <table class="table table-borderless align-middle mb-0">
                <thead>
                    <tr class="text-muted small border-bottom">
                        <th style="width: 35%;" class="ps-2">EMPLOYEE</th>
                        <th style="width: 30%;">SHIFT</th>
                        <th style="width: 25%;">PERIOD</th>
                        <th style="width: 10%;" class="text-end pe-2">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assignment)
                        <tr class="border-bottom-0">
                            <td class="ps-2 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="avatar-circle rounded-circle text-primary bg-primary-subtle fw-bold d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px;font-size:14px;">
                                        {{ substr($assignment->user->name ?? 'E', 0, 1) }}
                                    </span>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $assignment->user->name ?? 'Unknown' }}</div>
                                        <div class="text-muted small" style="font-size: 0.8rem;">Code: {{ $assignment->user->employee_code ?? '—' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3">
                                <div class="fw-bold text-dark">{{ $assignment->shift->name ?? '—' }}</div>
                                <div class="text-muted small" style="font-size: 0.8rem;">{{ $assignment->shift?->formatted_hours }}</div>
                            </td>
                            <td class="py-3 text-dark fw-medium">
                                {{ $assignment->formatted_period }}
                            </td>
                            <td class="text-end pe-2 py-3">
                                <form method="POST" action="{{ route('admin.shift.assignments.destroy', $assignment) }}" onsubmit="return confirm('Remove this assignment?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-3 px-2 py-1" title="Delete assignment">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4 small">No shift assignments created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal 1: Add Shift Modal -->
<div class="modal fade" id="addShiftModal" tabindex="-1" aria-labelledby="addShiftModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content border-0 shadow-lg rounded-4 p-2">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="addShiftModalLabel">Add shift</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.shifts.store') }}">
                @csrf
                <div class="modal-body py-3">
                    <!-- Name -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small mb-1">Name</label>
                        <input type="text" name="name" class="form-control bg-light border-0 py-2 px-3 rounded-3" placeholder="e.g. Morning" required>
                    </div>

                    <!-- Start & End -->
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Start</label>
                            <div class="input-group">
                                <input type="time" name="start_time" class="form-control bg-light border-0 py-2 ps-3 rounded-start-3" value="09:00" required>
                                <span class="input-group-text bg-light border-0 text-muted rounded-end-3"><i class="bi bi-clock"></i></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">End</label>
                            <div class="input-group">
                                <input type="time" name="end_time" class="form-control bg-light border-0 py-2 ps-3 rounded-start-3" value="17:00" required>
                                <span class="input-group-text bg-light border-0 text-muted rounded-end-3"><i class="bi bi-clock"></i></span>
                            </div>
                        </div>
                    </div>

                    <!-- Grace & Break -->
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Grace (minutes)</label>
                            <input type="number" name="grace_minutes" class="form-control bg-light border-0 py-2 px-3 rounded-3" value="0" min="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Break (minutes)</label>
                            <input type="number" name="break_minutes" class="form-control bg-light border-0 py-2 px-3 rounded-3" value="0" min="0">
                        </div>
                    </div>

                    <!-- OT Starts After -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small mb-1">OT starts after (minutes past shift start)</label>
                        <input type="number" name="ot_starts_after" class="form-control bg-light border-0 py-2 px-3 rounded-3" value="0" min="0">
                        <div class="form-text text-muted" style="font-size: 0.8rem;">0 = use expected shift length for overtime elsewhere.</div>
                    </div>

                    <!-- Overnight shift -->
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="is_overnight" value="1" id="add_is_overnight">
                        <label class="form-check-label text-dark fw-medium" for="add_is_overnight">
                            Overnight shift (ends next day)
                        </label>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary px-4 py-2 rounded-3 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-semibold">Save shift</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Edit Shift Modals for each shift -->
@foreach($shifts as $shift)
<div class="modal fade" id="editShiftModal_{{ $shift->id }}" tabindex="-1" aria-labelledby="editShiftModalLabel_{{ $shift->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content border-0 shadow-lg rounded-4 p-2">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="editShiftModalLabel_{{ $shift->id }}">Edit shift</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.shifts.update', $shift) }}">
                @csrf
                @method('PUT')
                <div class="modal-body py-3">
                    <!-- Name -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small mb-1">Name</label>
                        <input type="text" name="name" class="form-control bg-light border-0 py-2 px-3 rounded-3" value="{{ $shift->name }}" required>
                    </div>

                    <!-- Start & End -->
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Start</label>
                            <div class="input-group">
                                <input type="time" name="start_time" class="form-control bg-light border-0 py-2 ps-3 rounded-start-3" value="{{ $shift->start_time }}" required>
                                <span class="input-group-text bg-light border-0 text-muted rounded-end-3"><i class="bi bi-clock"></i></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">End</label>
                            <div class="input-group">
                                <input type="time" name="end_time" class="form-control bg-light border-0 py-2 ps-3 rounded-start-3" value="{{ $shift->end_time }}" required>
                                <span class="input-group-text bg-light border-0 text-muted rounded-end-3"><i class="bi bi-clock"></i></span>
                            </div>
                        </div>
                    </div>

                    <!-- Grace & Break -->
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Grace (minutes)</label>
                            <input type="number" name="grace_minutes" class="form-control bg-light border-0 py-2 px-3 rounded-3" value="{{ $shift->grace_minutes }}" min="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Break (minutes)</label>
                            <input type="number" name="break_minutes" class="form-control bg-light border-0 py-2 px-3 rounded-3" value="{{ $shift->break_minutes }}" min="0">
                        </div>
                    </div>

                    <!-- OT Starts After -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small mb-1">OT starts after (minutes past shift start)</label>
                        <input type="number" name="ot_starts_after" class="form-control bg-light border-0 py-2 px-3 rounded-3" value="{{ $shift->ot_starts_after }}" min="0">
                        <div class="form-text text-muted" style="font-size: 0.8rem;">0 = use expected shift length for overtime elsewhere.</div>
                    </div>

                    <!-- Overnight shift -->
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="is_overnight" value="1" id="edit_is_overnight_{{ $shift->id }}" {{ $shift->is_overnight ? 'checked' : '' }}>
                        <label class="form-check-label text-dark fw-medium" for="edit_is_overnight_{{ $shift->id }}">
                            Overnight shift (ends next day)
                        </label>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary px-4 py-2 rounded-3 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-semibold">Save shift</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
