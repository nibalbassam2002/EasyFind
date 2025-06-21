{{-- resources/views/errors/403.blade.php --}}
@extends('frontend.Layouts.frontend')
@section('title', '403 - Access Denied')
@section('content')
<div class="container my-5 text-center">
    <img src="{{ asset('frontend/assets/403 Forbidden Error.jpg') }}" alt="Access Denied" class="img-fluid mb-4" style="max-height: 300px;">
    <h1 class="display-4 fw-bold text-warning">Access Denied</h1>
    <p class="lead text-muted mb-4">
        You do not have the necessary permissions to view this page.
    </p>
    <div class="d-flex justify-content-center gap-3">
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-lg">Go Back</a>
        <a href="{{ route('frontend.home') }}" class="btn btn-gold btn-lg">Go to Homepage</a>
    </div>
</div>
@endsection