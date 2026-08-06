@extends('layouts.site')
@section('title', $product->name)
@section('meta', $product->short_description)

@section('content')
<section class="site-hero py-5">
    <div class="container py-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a class="link-light" href="{{ route('site.products') }}">Products</a></li>
                <li class="breadcrumb-item active text-white-50" aria-current="page">{{ $product->name }}</li>
            </ol>
        </nav>
        <div class="d-flex align-items-center gap-3 mb-2">
            <div class="feature-icon bg-white"><i class="bi {{ $product->icon }}"></i></div>
            @if($product->badge)<span class="badge text-bg-warning text-dark">{{ $product->badge }}</span>@endif
            <span class="badge text-bg-light text-dark">{{ $product->category }}</span>
        </div>
        <h1 class="fw-bold mb-2">{{ $product->name }}</h1>
        <p class="lead mb-0" style="opacity:.9;">{{ $product->tagline }}</p>
    </div>
</section>

<section class="site-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="h5 fw-bold">Overview</h2>
                        <p class="text-muted">{{ $product->short_description }}</p>
                        <p>{{ $product->description }}</p>

                        <h3 class="h6 fw-bold mt-4">What's included</h3>
                        <ul class="list-group list-group-flush">
                            @foreach($product->features ?? [] as $feature)
                                <li class="list-group-item px-0 d-flex align-items-center gap-2">
                                    <i class="bi bi-check2-circle text-success"></i> {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow sticky-top rounded-4" style="top:88px;">
                    <div class="card-body p-4">
                        <div class="small text-muted">Starting at</div>
                        <div class="price-tag">{{ $product->formatPrice() }}</div>
                        <div class="small text-muted mb-3">Yearly: {{ $product->formatPrice('yearly') }}</div>
                        <a href="{{ route('site.contact', ['product' => $product->id]) }}" class="btn btn-primary w-100 mb-2">Request quote</a>
                        <a href="{{ route('site.products') }}" class="btn btn-outline-secondary w-100">All products</a>
                    </div>
                </div>
            </div>
        </div>

        @if($related->count())
            <h2 class="h5 fw-bold mt-5 mb-3">Related products</h2>
            <div class="row g-4">
                @foreach($related as $item)
                    @include('site.partials.product-card', ['product' => $item])
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
