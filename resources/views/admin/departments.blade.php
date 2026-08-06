@extends('layouts.app')
@section('title', 'Departments & Designations')
@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 d-flex align-items-center gap-2 text-dark">
                <i class="bi bi-diagram-3 text-primary fs-3"></i> Departments & Designations
            </h4>
            <p class="text-secondary small mb-0">Organize your workforce structure. Used in employee profiles.</p>
        </div>
        <a href="{{ route('admin.employees') }}" class="btn btn-outline-secondary btn-sm px-3 rounded-3">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <!-- Content Columns -->
    <div class="row g-4">
        <!-- Departments Column -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-building text-dark fs-5"></i>
                    <h5 class="fw-bold mb-0 text-dark">Departments</h5>
                </div>

                <!-- Add Department Input Bar -->
                <form method="POST" action="{{ route('admin.departments.store') }}" class="mb-4">
                    @csrf
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-plus-lg"></i></span>
                        <input type="text" name="name" class="form-control bg-light border-start-0 ps-0" placeholder="New department name" required>
                        <button class="btn btn-primary px-4 fw-semibold" type="submit">Add</button>
                    </div>
                </form>

                <!-- Departments Table -->
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <thead>
                            <tr class="text-muted small border-bottom">
                                <th style="width: 60%;" class="ps-2">NAME</th>
                                <th class="text-center" style="width: 20%;">STAFF</th>
                                <th class="text-end pe-2" style="width: 20%;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($departments as $dept)
                                <tr>
                                    <td class="ps-0 py-2">
                                        <form id="update-dept-{{ $dept->id }}" method="POST" action="{{ route('admin.departments.update', $dept) }}">
                                            @csrf
                                            @method('PUT')
                                            <input type="text" name="name" value="{{ $dept->name }}" class="form-control form-control-sm bg-light border-0 px-3 py-2 rounded-3 text-dark fw-medium" required>
                                        </form>
                                    </td>
                                    <td class="text-center py-2">
                                        <span class="badge bg-light text-dark border px-3 py-2 rounded-3 fw-bold">{{ $dept->staff_count }}</span>
                                    </td>
                                    <td class="text-end pe-0 py-2">
                                        <div class="d-inline-flex gap-1">
                                            <button type="submit" form="update-dept-{{ $dept->id }}" class="btn btn-primary btn-sm rounded-3 px-2 py-1" title="Save">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            <form method="POST" action="{{ route('admin.departments.destroy', $dept) }}" onsubmit="return confirm('Delete this department?')" class="d-inline">
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
                                    <td colspan="3" class="text-center text-muted py-4 small">No departments added yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Designations Column -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-briefcase text-dark fs-5"></i>
                    <h5 class="fw-bold mb-0 text-dark">Designations</h5>
                </div>

                <!-- Add Designation Input Bar -->
                <form method="POST" action="{{ route('admin.designations.store') }}" class="mb-4">
                    @csrf
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-plus-lg"></i></span>
                        <input type="text" name="name" class="form-control bg-light border-start-0 ps-0" placeholder="New designation name" required>
                        <button class="btn btn-primary px-4 fw-semibold" type="submit">Add</button>
                    </div>
                </form>

                <!-- Designations Table -->
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <thead>
                            <tr class="text-muted small border-bottom">
                                <th style="width: 60%;" class="ps-2">NAME</th>
                                <th class="text-center" style="width: 20%;">STAFF</th>
                                <th class="text-end pe-2" style="width: 20%;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($designations as $desig)
                                <tr>
                                    <td class="ps-0 py-2">
                                        <form id="update-desig-{{ $desig->id }}" method="POST" action="{{ route('admin.designations.update', $desig) }}">
                                            @csrf
                                            @method('PUT')
                                            <input type="text" name="name" value="{{ $desig->name }}" class="form-control form-control-sm bg-light border-0 px-3 py-2 rounded-3 text-dark fw-medium" required>
                                        </form>
                                    </td>
                                    <td class="text-center py-2">
                                        <span class="badge bg-light text-dark border px-3 py-2 rounded-3 fw-bold">{{ $desig->staff_count }}</span>
                                    </td>
                                    <td class="text-end pe-0 py-2">
                                        <div class="d-inline-flex gap-1">
                                            <button type="submit" form="update-desig-{{ $desig->id }}" class="btn btn-primary btn-sm rounded-3 px-2 py-1" title="Save">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            <form method="POST" action="{{ route('admin.designations.destroy', $desig) }}" onsubmit="return confirm('Delete this designation?')" class="d-inline">
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
                                    <td colspan="3" class="text-center text-muted py-4 small">No designations added yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
