{{-- resources/views/auth/passwords/reset.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - Easy Find</title>
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
        .form-wrapper-outer {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
        }
        .form-wrapper-inner {
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
            margin-bottom: 1.5rem; /* Adjusted margin */
        }
        .btn-yellow {
            background-color: #FFD700;
            color: #333;
            border: 1px solid #FFD700;
            padding: 0.75rem;
            font-weight: 600;
            width: 100%;
        }
        .btn-yellow:hover {
            background-color: #e6c300;
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
        .input-group .form-control {
            border-right: none;
        }
        .input-group .form-control:focus {
            z-index: 3;
        }
        .toggle-password {
            cursor: pointer;
            border-left: none;
            background-color: #fff;
            color: #6c757d;
        }
        .toggle-password:hover {
            color: #495057;
        }
        .invalid-feedback {
            font-size: 0.8rem;
        }
        @media (max-width: 767.98px) {
            .logo-area-container { display: none; }
            .form-wrapper-outer { padding: 1rem; }
            .form-wrapper-inner { padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0 min-vh-100">
        <div class="col-md-7 d-none d-md-flex logo-area-container">
            <a href="{{ url('/') }}">
                <img src="{{ asset('frontend/assets/شعار مفرغ 2 1 (1).png') }}" alt="Easy Find">
            </a>
        </div>

        <div class="col-md-5 col-12 form-wrapper-outer">
            <div class="form-wrapper-inner">
                <div class="text-center mb-4 d-md-none">
                     <a href="{{ url('/') }}">
                        <img src="{{ asset('frontend/assets/شعار مفرغ 2 1 (1).png') }}" alt="Easy Find" style="max-width: 180px; margin-bottom: 0.5rem;">
                    </a>
                </div>

                <h3 class="fw-bold">Reset Your Password</h3>

                <form method="POST" action="{{ route('password.update') }}"> {{-- أو password.store --}}
                    @csrf

                    {{-- الحقل المخفي للتوكن --}}
                    <input type="hidden" name="token" value="{{ $token }}">

                    {{-- حقل البريد الإلكتروني (عادةً للقراءة فقط أو مخفي ويتم ملؤه تلقائيًا) --}}
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                               name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" readonly>
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    {{-- حقل كلمة المرور الجديدة --}}
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <div class="input-group has-validation">
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                                   name="password" required autocomplete="new-password" placeholder="Enter new password">
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                               <i class="bi bi-eye-slash"></i>
                            </button>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    {{-- حقل تأكيد كلمة المرور الجديدة --}}
                    <div class="mb-4">
                        <label for="password-confirm" class="form-label">Confirm New Password</label>
                        <div class="input-group">
                            <input id="password-confirm" type="password" class="form-control"
                                   name="password_confirmation" required autocomplete="new-password" placeholder="Confirm new password">
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password-confirm">
                               <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-yellow w-100">
                        Reset Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function () {
            const targetId = this.dataset.target;
            const passwordInput = document.getElementById(targetId);
            const icon = this.querySelector('i');

            if (passwordInput && icon) {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                icon.classList.toggle('bi-eye');
                icon.classList.toggle('bi-eye-slash');
            }
        });
    });
</script>
</body>
</html>