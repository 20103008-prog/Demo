<div class="col-md-6 col-lg-4">
    <div class="card product-card">
        <div class="card-body p-4 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="feature-icon"><i class="bi {{ $product->icon }}"></i></div>
                @if($product->badge)<span class="badge text-bg-secondary">{{ $product->badge }}</span>@endif
            </div>
            <h3 class="h5 fw-bold">{{ $product->name }}</h3>
            <p class="small text-muted flex-grow-1">{{ $product->short_description }}</p>
            <div class="d-flex justify-content-between align-items-end">
                <div>
                    <div class="price-tag" style="font-size:1.35rem;">{{ $product->formatPrice() }}</div>
                    <div class="small text-muted">or {{ $product->formatPrice('yearly') }}</div>
                </div>
                <a href="{{ route('site.product', $product->slug) }}" class="btn btn-primary btn-sm">Details</a>
            </div>
        </div>
    </div>
</div>
