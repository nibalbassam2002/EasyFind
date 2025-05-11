@extends('frontend.Layouts.frontend')
@section('title', 'Page Not Found')
@section('content')
<div class="container text-center py-5">
    <h1 class="display-1">404</h1>
    <h2>Page Not Found</h2>
    <p class="lead">Sorry, the page you are looking for could not be found.</p>
    <a href="{{ route('frontend.home') }}" class="btn btn-primary">Go to Homepage</a>
</div>
@endsection