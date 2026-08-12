@extends('layouts.app')
@section('title', 'My Documents')
@section('content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card card-panel">
            <div class="card-body">
                <form method="POST" action="{{ route('employee.documents.store') }}" enctype="multipart/form-data">@csrf
                    <select name="type" class="form-select mb-2">@foreach(['nid','joining_letter','tin','contract','other'] as $t)<option>{{ $t }}</option>@endforeach</select>
                    <input name="title" class="form-control mb-2" required placeholder="Title">
                    <input type="file" name="file" class="form-control mb-2" required>
                    <button class="btn btn-primary btn-sm w-100">Upload</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card card-panel">
            <div class="table-responsive">
                <table class="table mb-0"><thead class="table-light"><tr><th>Type</th><th>Title</th><th>File</th></tr></thead>
                <tbody>@foreach($docs as $d)<tr><td>{{ $d->type }}</td><td>{{ $d->title }}</td><td><a href="{{ asset('storage/'.$d->file_path) }}" target="_blank">Open</a></td></tr>@endforeach</tbody></table>
            </div>
        </div>
    </div>
</div>
@endsection
