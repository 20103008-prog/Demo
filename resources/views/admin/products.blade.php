@extends('layouts.app')
@section('title', 'Website Products')
@section('content')
<p class="text-muted small mb-3">Manage catalog shown on the public website. Prices update live from MySQL <code>hrpayroll</code>.</p>
<div class="card card-panel">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Product</th><th>Category</th><th>Monthly</th><th>Yearly</th><th>Flags</th><th></th></tr>
            </thead>
            <tbody>
            @foreach($products as $p)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $p->name }}</div>
                        <div class="small text-muted">{{ $p->slug }}</div>
                    </td>
                    <td>{{ $p->category }}</td>
                    <td colspan="4">
                        <form method="POST" action="{{ route('admin.products.update', $p) }}" class="row g-2 align-items-center">
                            @csrf
                            <div class="col-auto">
                                <input type="number" step="0.01" name="price_monthly" value="{{ $p->price_monthly }}" class="form-control form-control-sm" style="width:110px;" title="Monthly">
                            </div>
                            <div class="col-auto">
                                <input type="number" step="0.01" name="price_yearly" value="{{ $p->price_yearly }}" class="form-control form-control-sm" style="width:110px;" title="Yearly">
                            </div>
                            <div class="col-auto">
                                <div class="form-check form-check-inline mb-0">
                                    <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="feat{{ $p->id }}" @checked($p->is_featured)>
                                    <label class="form-check-label small" for="feat{{ $p->id }}">Featured</label>
                                </div>
                                <div class="form-check form-check-inline mb-0">
                                    <input type="checkbox" name="is_published" value="1" class="form-check-input" id="pub{{ $p->id }}" @checked($p->is_published)>
                                    <label class="form-check-label small" for="pub{{ $p->id }}">Published</label>
                                </div>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-sm btn-primary">Save</button>
                            </div>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
<p class="small mt-3"><a href="{{ route('site.products') }}" target="_blank">Open public products page →</a></p>
@endsection
