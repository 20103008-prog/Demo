@extends('layouts.site')
@section('title', 'Products')

@section('content')
<section class="site-hero py-5">
    <div class="container py-3">
        <h1 class="fw-bold mb-2">Products & pricing</h1>
        <p class="mb-0" style="opacity:.9;">Choose modules individually or go all-in with Enterprise Suite. Prices and details come from the <strong>hrpayroll</strong> database.</p>
    </div>
</section>

<section class="site-section">
    <div class="container">
        @if($plans->count())
            <h2 class="h4 fw-bold mb-3">Plans</h2>
            <div class="row g-4 mb-5">
                @foreach($plans as $product)
                    @include('site.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        @endif

        <h2 class="h4 fw-bold mb-3">Modules</h2>
        <div class="row g-4">
            @foreach($modules as $product)
                @include('site.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>
@endsection
