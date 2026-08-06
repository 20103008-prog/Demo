@extends('layouts.site')
@section('title', 'Contact')

@section('content')
<section class="site-hero py-5">
    <div class="container py-3">
        <h1 class="fw-bold mb-2">Contact sales</h1>
        <p class="mb-0" style="opacity:.9;">Tell us about your company — inquiries are saved to the database for the admin team.</p>
    </div>
</section>

<section class="site-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 p-md-5">
                        @if($errors->any())
                            <div class="alert alert-danger small">
                                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                            </div>
                        @endif
                        <form method="POST" action="{{ route('site.inquiry') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Your name</label>
                                    <input name="name" value="{{ old('name') }}" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Email</label>
                                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Company</label>
                                    <input name="company" value="{{ old('company') }}" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Phone</label>
                                    <input name="phone" value="{{ old('phone') }}" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Interested product</label>
                                    <select name="product_id" class="form-select">
                                        <option value="">— General inquiry —</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}" @selected(old('product_id', request('product')) == $p->id)>{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Subject</label>
                                    <input name="subject" value="{{ old('subject', 'Demo request') }}" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Message</label>
                                    <textarea name="message" rows="5" class="form-control" required>{{ old('message') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-primary px-4">Send inquiry</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
