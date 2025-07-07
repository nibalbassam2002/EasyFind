<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Easy Find - Log In</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('frontend/assets/logo for tab.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
            /* Applying Inter font */
            display: flex;
            align-items: center;
            /* Vertically center content if page is short */
            min-height: 100vh;
        }

        .logo-area-container {
            /* Renamed for clarity */
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            /* Full height for this column */
            padding: 2rem;
        }

        .logo-area-container img {
            max-width: 100%;
            height: auto;
            max-height: 450px;
            /* Adjusted max height */
        }

        .form-wrapper {
            /* New wrapper for centering form vertically */
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            /* Full height for this column's content area */
            padding: 2rem;
            /* Padding around the form container */
        }

        .form-container {
            width: 100%;
            /* Responsive width */
            max-width: 420px;
            /* Max width for the form itself */
            padding: 2.5rem;
            /* More padding inside form */
            border-radius: 0.75rem;
            /* Slightly softer radius */
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.075);
            /* Softer shadow */
            background-color: #fff;
        }

        .form-container h2 {
            color: #333;
            font-weight: 600;
        }

        /* Social Login Buttons */
        .social-login-buttons .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.65rem 0.75rem;
            font-size: 0.9rem;
            font-weight: 500;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }

        .social-login-buttons .btn img {
            margin-right: 0.6rem;
        }

        .btn-google {
            background-color: #fff;
            border: 1px solid #ddd;
            color: #444;
        }

        .btn-google:hover {
            background-color: #f8f8f8;
            border-color: #ccc;
        }

        .btn-facebook {
            /* Style for consistency, even if disabled */
            background-color: #fff;
            border: 1px solid #ddd;
            color: #444;
        }

        .btn-facebook:hover:not(:disabled) {
            background-color: #f8f8f8;
            border-color: #ccc;
        }


        /* OR Divider */
        .or-divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: #adb5bd;
            /* Lighter grey */
            margin: 1.75rem 0;
            /* More vertical space */
            font-size: 0.85rem;
            font-weight: 500;
        }

        .or-divider::before,
        .or-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e9ecef;
            /* Lighter line */
        }

        .or-divider:not(:empty)::before {
            margin-right: .75em;
        }

        .or-divider:not(:empty)::after {
            margin-left: .75em;
        }

        /* Form Elements */
        .form-label {
            font-weight: 500;
            font-size: 0.9rem;
            color: #495057;
        }

        .form-control {
            padding: 0.75rem 1rem;
            /* More padding in inputs */
            font-size: 0.95rem;
            border-radius: 0.375rem;
            /* Bootstrap default */
        }

        .form-control:focus {
            border-color: #FFD700;
            /* Gold focus */
            box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
            /* Gold shadow */
        }

        .input-group .form-control {
            /* Ensure focus on input-group is correct */
            border-right: none;
            /* If toggle button is on right */
        }

        .input-group .form-control:focus {
            z-index: 3;
            /* Bring focused input in group to front */
        }

        .btn-yellow {
            background-color: #FFD700;
            /* Gold */
            color: #333;
            /* Darker text for contrast */
            border: 1px solid #FFD700;
            padding: 0.75rem;
            font-weight: 600;
            width: 100%;
            /* Full width login button */
        }

        .btn-yellow:hover {
            background-color: #e6c300;
            /* Darker gold */
            border-color: #e6c300;
            color: #000;
        }

        .invalid-feedback {
            display: block;
            /* Ensure it's always block */
            font-size: 0.8rem;
        }

        .toggle-password {
            cursor: pointer;
            border-left: none;
            /* Remove default border if input-group has one */
            background-color: #fff;
            /* Match input background */
            color: #6c757d;
        }

        .toggle-password:hover {
            color: #495057;
        }

        .small-text-muted {
            /* Utility class for small muted text */
            font-size: 0.85rem;
            color: #6c757d;
        }

        .text-app-gold {
            /* Custom class for your app's gold color */
            color: #DAA520 !important;
            /* Darker, more readable gold for text */
        }

        /* Responsive adjustments for logo and form layout */
        @media (max-width: 767.98px) {

            /* md breakpoint */
            .logo-area-container {
                height: auto;
                /* Auto height on small screens */
                padding: 2rem 1rem 1rem 1rem;
                /* Adjust padding */
            }

            .logo-area-container img {
                max-height: 150px;
                /* Smaller logo on small screens */
                margin-bottom: 1rem;
            }

            .form-wrapper {
                min-height: auto;
                /* Auto height for form wrapper */
                padding: 1rem;
                /* Less padding on small screens */
            }

            .form-container {
                padding: 1.5rem;
                /* Less padding inside form */
                box-shadow: none;
                /* Optional: remove shadow on small screens for edge-to-edge feel */
            }

            .or-divider {
                margin: 1.25rem 0;
            }
        }
    </style>
</head>

<body>

    <div class="container-fluid">
        <div class="row min-vh-100">

            <!-- Left: Logo -->
            <div class="col-md-6 d-none d-md-flex logo-area">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('frontend/assets/logo1.png') }}" alt="Easy Find"
                        style="max-width: 600px; margin-bottom: 1rem;">
                </a>
            </div>

            <!-- Right: Login Form -->
            <div class="col-md-5 d-flex align-items-center justify-content-center">
                <div class="form-container">
                    <h2 class="text-center mb-4">Login to Your Account</h2>
                    <div class="social-login-buttons mb-4">
                        <div class="row g-3"> 
                            <div class="col-md-6">
                                <a href="{{ route('socialite.redirect', 'google') }}" class="btn btn-google w-100">
                                    <img src="https://img.icons8.com/color/24/000000/google-logo.png" alt="Google logo">
                                    Continue with Google
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('socialite.redirect', 'facebook') }}" class="btn btn-facebook w-100">
                                    <img src="https://img.icons8.com/color/24/000000/facebook-new.png"
                                        alt="Facebook logo">
                                    Continue with Facebook
                                </a>

                            </div>
                        </div>
                    </div>
                    <div class="or-divider">OR</div>
                    {{-- النص التوضيحي المضاف --}}
                    <p class="text-center text-muted small mb-3">Enter your email & password to login</p>

                    {{-- Display General/Login Errors --}}
                    @if ($errors->has('email') && !$errors->has('password') && count($errors->all()) == 1)
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ $errors->first('email') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @elseif ($errors->any())
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            Please check the fields marked below.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- Laravel Integrated Form --}}
                    <form method="POST" action="{{ route('login.post') }}">
                        @csrf

                        {{-- Email Input --}}
                        <div class="mb-3">
                            <label for="email" class="form-label visually-hidden">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                placeholder="Email" name="email" id="email" value="{{ old('email') }}" required>
                        </div>

                        {{-- Password Input with Forgot Password Link --}}
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="password" class="form-label mb-0">Password</label>
                                <a href="{{route('password.request')}}" class="text-decoration-none text-secondary small"
                                    title="Feature coming soon">
                                    Forgot Password?
                                </a>
                            </div>
                            <div class="input-group">
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Enter your password" name="password" id="password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                    id="togglePasswordButton">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Remember Me Checkbox --}}
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="remember" id="rememberMe"
                                {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="rememberMe">
                                Remember Me
                            </label>
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" class="btn btn-yellow w-100 mb-3">Log in</button>

                    </form>

                    {{-- Create Account Link --}}
                    <div class="text-center mt-3">
                        @if (Route::has('register'))
                            <p class="small mb-0">Don't have account?
                                <a href="{{ route('register') }}" class="text-warning text-decoration-none">Create an
                                    account</a>
                            </p>
                        @endif
                    </div>

                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const togglePasswordButton = document.getElementById('togglePasswordButton');
        const passwordInput = document.getElementById('password');
        const toggleIcon = togglePasswordButton.querySelector('i');

        if (togglePasswordButton && passwordInput && toggleIcon) {
            togglePasswordButton.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                toggleIcon.classList.toggle('bi-eye');
                toggleIcon.classList.toggle('bi-eye-slash');
            });
        }
    </script>

</body>

</html>
