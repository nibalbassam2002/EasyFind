{{-- resources/views/errors/404.blade.php --}}

@extends('frontend.Layouts.frontend')

@section('title', '404 - Page Not Found')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            
            {{-- يمكنك استخدام صورة من موقع مثل undraw.co للرسوميات المجانية --}}
            <img src="{{ asset('frontend/assets/.svg') }}" alt="Page Not Found" class="img-fluid mb-4" style="max-height: 300px;">

            <h1 class="display-4 fw-bold text-body-emphasis">Oops! Page Not Found</h1>
            <p class="lead text-muted mb-4">
                We can't seem to find the page you're looking for. It might have been moved, deleted, or maybe you just mistyped the address.
            </p>
            
            <div class="d-flex justify-content-center gap-3">
                <a href="{{ route('frontend.home') }}" class="btn btn-gold btn-lg">
                    <i class="bi bi-house-door-fill me-2"></i>Go to Homepage
                </a>
                <a href="{{ route('frontend.properties') }}" class="btn btn-outline-secondary btn-lg">
                    View All Properties
                </a>
            </div>

            <div class="mt-5">
                <p class="text-muted">Or try searching for what you need:</p>
                <div class="col-lg-6 mx-auto">
                    <form class="d-flex" role="search" method="GET" action="{{ route('frontend.public.search') }}">
                        <input class="form-control me-2" type="search" name="search" placeholder="Search for properties..." aria-label="Search">
                        <button class="btn btn-outline-gold" type="submit">Search</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection