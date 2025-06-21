
@extends('frontend.Layouts.frontend')
@section('title', '500 - Server Error')
@section('content')
<div class="container my-5 text-center">
    
    <img src="{{ asset('frontend/assets/11.svg') }}" alt="Server Error" class="img-fluid mb-4" style="max-height: 300px;">
    <h1 class="display-4 fw-bold text-danger">Oops! Something Went Wrong</h1>
    <p class="lead text-muted mb-4">
        We're sorry, but our server is currently experiencing some technical difficulties. Our team has been automatically notified and we are working to get it fixed.
    </p>
    <a href="{{ route('frontend.home') }}" class="btn btn-gold btn-lg">Return to Homepage</a>
</div>
@endsection