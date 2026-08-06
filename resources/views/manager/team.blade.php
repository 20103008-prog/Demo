@extends('layouts.app')
@section('title', 'My Team')
@section('content')
<div class="card card-panel">
    <div class="card-header bg-white border-0 fw-semibold">{{ $dept }} Directory</div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Code</th><th>Name</th><th>Email</th><th>Title</th><th>Role</th><th>Salary</th><th>Status</th></tr></thead>
            <tbody>
            @foreach($team as $m)
                <tr>
                    <td>{{ $m->employee_code }}</td>
                    <td>{{ $m->name }}</td>
                    <td>{{ $m->email }}</td>
                    <td>{{ $m->job_title }}</td>
                    <td class="text-capitalize">{{ $m->role }}</td>
                    <td>{{ inr($m->salary) }}</td>
                    <td>{!! status_badge($m->status) !!}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
