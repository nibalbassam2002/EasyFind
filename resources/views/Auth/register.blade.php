<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Easy Find - Sign Up</title>
    {{-- ↓↓↓ تم التعديل باستخدام asset() ↓↓↓ --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('frontend/assets/شعار مفرغ 2.png') }}" sizes="100x100">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- ↓↓↓ إضافة بعض التنسيقات للـ invalid-feedback من Bootstrap ↓↓↓ --}}
    <style>
        body {
            background-color: #f8f9fa;
        }

        .logo {
            font-size: 2.5rem;
            font-weight: bold;
            color: #f1c40f;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .form-container {
            max-width: 400px;
            margin: auto;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .btn-yellow {
            background-color: #f1c40f;
            color: black;
        }

        .form-control:focus {
            border-color: #f1c40f;
            box-shadow: 0 0 0 0.2rem rgba(241, 196, 15, 0.25);
        }

        /* إضافة تنسيق لإظهار حقل الشروط كـ invalid */
        .form-check-input.is-invalid~.form-check-label {
            color: #dc3545;
        }

        .form-check-input.is-invalid {
            border-color: #dc3545;
        }
    </style>
</head>

<body>

    <div class="container-fluid">
        <div class="row min-vh-100">

            <!-- Left: Logo -->
            <div class="col-md-6 d-none d-md-flex logo flex-column">
                <div>
                    {{-- ↓↓↓ تم التعديل باستخدام asset() ↓↓↓ --}}
                    <img src="{{ asset('frontend/assets/شعار مفرغ 2 1 (1).png') }}" alt="Easy Find"
                        style="max-width: 600px;">
                </div>
            </div>

            <!-- Right: Form -->
            <div class="col-md-5 d-flex align-items-center justify-content-center">
                <div class="form-container bg-white">
                    <h2 class="text-center mb-4">Create Account</h2>

                    {{-- ===== ملاحظة: أزرار Google/Facebook هنا للعرض فقط، لا تعمل بعد ===== --}}
                    <div class="d-flex justify-content-between mb-3">
                        <a href="{{ route('socialite.redirect', 'google') }}" class="btn btn-light border">
                            {{-- border لإضافة حد بسيط --}}
                            <img src="https://img.icons8.com/color/20/google-logo.png" alt="Google logo"
                                class="me-2" /> Sign up with Google
                        </a>
                        <button type="button" class="btn btn-light w-100 ms-2" disabled title="Coming soon!">
                            {{-- Added disabled & title --}}
                            <img src="https://img.icons8.com/color/16/facebook-new.png" /> Sign up with Facebook
                        </button>
                    </div>
                    <div class="text-center mb-3">— OR —</div>
                    {{-- ===== نهاية الملاحظة ===== --}}


                    {{-- ↓↓↓ عرض أخطاء التحقق العامة ورسائل الخطأ من السيرفر ↓↓↓ --}}
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif
                    {{-- عرض رسالة خطأ عامة إذا فشل التحقق لأي حقل (ماعدا الشروط التي تظهر أدناه) --}}
                    @if ($errors->any() && !$errors->has('terms'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            Please check the fields below for errors.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif
                    {{-- ↑↑↑ نهاية عرض الأخطاء العامة ↑↑↑ --}}


                    {{-- =================================== --}}
                    {{-- ↓↓↓ بـــــدء تـــعـــديـــل الفورم ↓↓↓ --}}
                    {{-- =================================== --}}
                    <form method="POST" action="{{ route('register.post') }}" novalidate>
                        @csrf {{-- ← مهم جداً: إضافة توكن الحماية CSRF --}}

                        {{-- حقل الاسم الكامل --}}
                        <div class="mb-2">
                            {{-- ↓↓↓ إضافة name, old(), @error, is-invalid ↓↓↓ --}}
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                placeholder="Full Name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- حقل رقم الهاتف (اختياري) --}}
                        <div class="mb-2">
                            {{-- ↓↓↓ إضافة name, old(), @error, is-invalid ↓↓↓ --}}
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                placeholder="Phone number (Optional)" name="phone" value="{{ old('phone') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- حقل الإيميل --}}
                        <div class="mb-2">
                            {{-- ↓↓↓ إضافة name, old(), @error, is-invalid ↓↓↓ --}}
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                placeholder="Email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-2">
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                placeholder="Password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        <div class="mb-3">
                            <input type="password" class="form-control" placeholder="Confirm Password"
                                name="password_confirmation" required>
                        </div>

                        <div class="form-check mb-3">

                            <input type="checkbox" class="form-check-input @error('terms') is-invalid @enderror"
                                id="terms" name="terms" value="1" {{ old('terms') ? 'checked' : '' }}>
                            <label class="form-check-label" for="terms">
                                I agree to all the <a href="#">Terms</a> and <a href="#">Privacy
                                    Policies</a>
                            </label>
                            @error('terms')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-yellow w-100">Create Account</button>

                    </form>



                    <div class="text-center mt-3">
                        Already have an account? <a href="{{ route('login') }}" class="text-warning">Log in</a>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>

</html>
