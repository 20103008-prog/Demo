@extends('layouts.app')
@section('title', 'Companies & Branches')
@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card card-panel mb-3">
            <div class="card-header bg-white border-0 fw-semibold">Add Company</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.companies.store') }}">@csrf
                    <input name="name" class="form-control mb-2" placeholder="Name" required>
                    <input name="code" class="form-control mb-2" placeholder="Code" required>
                    <input name="tin" class="form-control mb-2" placeholder="Company TIN">
                    <button class="btn btn-primary btn-sm w-100">Save</button>
                </form>
            </div>
        </div>
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Add Branch</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.branches.store') }}">@csrf
                    <select name="company_id" class="form-select mb-2" required>
                        @foreach($companies as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select>
                    <input name="name" class="form-control mb-2" placeholder="Branch name" required>
                    <input name="code" class="form-control mb-2" placeholder="Branch code" required>
                    <button class="btn btn-primary btn-sm w-100">Save Branch</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Organizations</div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light"><tr><th>Company</th><th>Code</th><th>Branches</th></tr></thead>
                    <tbody>
                    @forelse($companies as $c)
                        <tr>
                            <td>{{ $c->name }}</td>
                            <td>{{ $c->code }}</td>
                            <td>{{ $c->branches->pluck('name')->join(', ') ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted text-center py-4">No companies yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
