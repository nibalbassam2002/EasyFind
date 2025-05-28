{{-- resources/views/auth/passwords/email.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password - Easy Find</title>
  {{-- افتراض أن الشعار موجود في public/frontend/assets/ --}}
  <link rel="icon" type="image/x-icon" href="{{ asset('frontend/assets/logo for tab.png') }}">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  {{-- Google Fonts (Inter) --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Inter', sans-serif;
      display: flex;
      align-items: center;
      min-height: 100vh;
    }
    .logo-area-container {
      background-color: #f8f9fa;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      padding: 2rem;
    }
    .logo-area-container img {
        max-width: 100%;
        height: auto;
        max-height: 450px;
    }
    .form-wrapper-outer { /* Wrapper to center content */
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        padding: 2rem;
    }
    .form-wrapper-inner { /* The actual form card */
      width: 100%;
      max-width: 450px;
      padding: 2.5rem;
      border-radius: 0.75rem;
      background-color: #fff;
      box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.075);
    }
    .form-wrapper-inner h3 {
        color: #333;
        font-weight: 600;
        margin-bottom: 0.75rem;
    }
    .form-wrapper-inner .text-muted {
        font-size: 0.9rem;
        margin-bottom: 1.75rem !important;
    }
    .btn-yellow {
      background-color: #FFD700; /* Main gold */
      color: #333; /* Darker text for contrast */
      border: 1px solid #FFD700;
      padding: 0.75rem;
      font-weight: 600;
      width: 100%;
    }
    .btn-yellow:hover {
      background-color: #e6c300; /* Darker gold */
      border-color: #e6c300;
      color: #000;
    }
    .form-label {
        font-weight: 500;
        font-size: 0.9rem;
        color: #495057;
    }
    .form-control {
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
    }
    .form-control:focus {
        border-color: #FFD700;
        box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
    }
    .back-link {
      font-size: 0.9rem;
      color: #495057;
      text-decoration: none;
      display: inline-block;
      margin-bottom: 1.5rem;
      font-weight: 500;
    }
    .back-link i {
        margin-right: 0.25rem;
    }
    .back-link:hover {
        color: #FFD700;
    }
    .invalid-feedback {
        font-size: 0.8rem;
    }
    /* Responsive */
    @media (max-width: 767.98px) {
        .logo-area-container {
            display: none; /* Hide logo column on small screens for this page type */
        }
        .form-wrapper-outer {
             padding: 1rem;
        }
        .form-wrapper-inner {
            padding: 2rem 1.5rem; /* Adjust padding for smaller screens */
        }
    }
  </style>
</head>
<body>

<div class="container-fluid p-0">
  <div class="row g-0 min-vh-100">

    <!-- Logo / Left Side (Hidden on small screens for this page layout) -->
    <div class="col-md-7 d-none d-md-flex logo-area-container">
      {{-- تأكد من أن مسار الشعار صحيح --}}
      <a href="{{ url('/') }}">
        <img src="{{ asset('frontend/assets/شعار مفرغ 2 1 (1).png') }}" alt="Easy Find">
      </a>
    </div>

    <!-- Right Section: Form -->
    <div class="col-md-5 col-12 form-wrapper-outer">
      <div class="form-wrapper-inner">
        {{-- الشعار للشاشات الصغيرة --}}
        <div class="text-center mb-4 d-md-none">
             <a href="{{ url('/') }}">
                <img src="{{ asset('frontend/assets/شعار مفرغ 2 1 (1).png') }}" alt="Easy Find" style="max-width: 180px; margin-bottom: 0.5rem;">
            </a>
        </div>

        <a href="{{ route('login') }}" class="back-link"><i class="bi bi-arrow-left"></i> Back to login</a>
        <h3 class="fw-bold">Forgot Your Password?</h3>
        <p class="text-muted">No problem! Enter your email address below and we'll send you a link to reset your password.</p>

        {{-- Session Status (Success Message) --}}
        @if (session('status'))
            <div class="alert alert-success small py-2" role="alert">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-4"> {{-- Increased margin-bottom --}}
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror"
                       id="email" name="email" value="{{ old('email') }}"
                       placeholder="Enter your email address" required autofocus>
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <button type="submit" class="btn btn-yellow w-100">Send Password Reset Link</button>
        </form>

      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>