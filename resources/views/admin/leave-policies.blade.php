@extends('layouts.app')
@section('title', 'Leave Policies')
@section('content')
<div class="card card-panel">
    <div class="card-header bg-white border-0 fw-semibold">Leave Policy Engine</div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>Type</th><th>Quota</th><th>Carry Fwd</th><th>Max CF</th><th>Half-day</th><th>Sandwich</th><th>Active</th><th></th></tr></thead>
            <tbody>
            @foreach($policies as $p)
                <tr>
                    <form method="POST" action="{{ route('admin.leave.policies.update', $p) }}">@csrf @method('PUT')
                        <td>{{ $p->type }}</td>
                        <td><input type="number" name="annual_quota" value="{{ $p->annual_quota }}" class="form-control form-control-sm" style="width:80px"></td>
                        <td><input type="checkbox" name="carry_forward" value="1" @checked($p->carry_forward)></td>
                        <td><input type="number" name="max_carry_forward" value="{{ $p->max_carry_forward }}" class="form-control form-control-sm" style="width:80px"></td>
                        <td><input type="checkbox" name="allow_half_day" value="1" @checked($p->allow_half_day)></td>
                        <td><input type="checkbox" name="sandwich_rule" value="1" @checked($p->sandwich_rule)></td>
                        <td><input type="checkbox" name="is_active" value="1" @checked($p->is_active)></td>
                        <td><button class="btn btn-sm btn-outline-primary">Save</button></td>
                    </form>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
