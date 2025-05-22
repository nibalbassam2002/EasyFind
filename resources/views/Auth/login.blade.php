<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Easy Find - Log In</title>
  <link rel="icon" type="image/x-icon" href="{{ asset('frontend/assets/logo for tab.png') }}" >
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    .logo-area { background-color: #f8f9fa; color: #FFD700; font-size: 2.5rem; font-weight: bold; display: flex; justify-content: center; align-items: center; height: 100vh; flex-direction: column; }
    .form-container { max-width: 400px; margin: auto; padding: 2rem; border-radius: 1rem; box-shadow: 0 0 10px rgba(0,0,0,0.1); background-color: #fff; }
    .btn-yellow { background-color: #FFD700; color: #ffffff; }
    .btn-yellow:hover { background-color: #FFD700; color: black;}
    .btn-outline-yellow { border-color: #FFD700; color: #FFD700; }
    .btn-outline-yellow:hover { background-color: #FFD700; color: #ffffff;}
    .form-control:focus { border-color: #f1c40f; box-shadow: 0 0 0 0.2rem rgba(241, 196, 15, 0.25); }
    .invalid-feedback { display: block; }
    .toggle-password { cursor: pointer; }
  </style>
</head>
<body>

<div class="container-fluid">
  <div class="row min-vh-100">

    <!-- Left: Logo -->
    <div class="col-md-6 d-none d-md-flex logo-area">
      <a href="{{ url('/') }}">
        <img src="{{ asset('frontend/assets/شعار مفرغ 2 1 (1).png') }}" alt="Easy Find" style="max-width: 600px; margin-bottom: 1rem;">
      </a>
    </div>

    <!-- Right: Login Form -->
    <div class="col-md-5 d-flex align-items-center justify-content-center">
      <div class="form-container">
        <h2 class="text-center mb-4">Login</h2>
        <div class="d-flex justify-content-between">
            <button class="btn btn-yellow ms-2">log in by <i class="bi bi-google"></i></button>
            <button class="btn btn-outline-yellow me-2">log in by <i class="bi bi-facebook"></i></button>
          </div> <br>

        
        {{-- النص التوضيحي المضاف --}}
        <p class="text-center text-muted small mb-3">Enter your email & password to login</p>

        {{-- Display General/Login Errors --}}
        @if ($errors->has('email') && !$errors->has('password') && count($errors->all()) == 1)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                 {{ $errors->first('email') }}
                 <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @elseif ($errors->any())
             <div class="alert alert-warning alert-dismissible fade show" role="alert">
                 Please check the fields marked below.
                 <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
             </div>
        @endif

        {{-- Laravel Integrated Form --}}
        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            {{-- Email Input --}}
            <div class="mb-3">
                 <label for="email" class="form-label visually-hidden">Email</label>
                 <input type="email"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="Email"
                       name="email"
                       id="email"
                       value="{{ old('email') }}"
                       required>
            </div>

            {{-- Password Input with Forgot Password Link --}}
            <div class="mb-3">
                 <div class="d-flex justify-content-between align-items-center mb-1">
                     <label for="password" class="form-label mb-0">Password</label>
                     <a href="#" class="text-decoration-none text-secondary small" title="Feature coming soon">
                         Forgot Password?
                     </a>
                 </div>
                 <div class="input-group">
                     <input type="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Enter your password"
                            name="password"
                            id="password"
                            required>
                      <button class="btn btn-outline-secondary toggle-password" type="button" id="togglePasswordButton">
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
                <input class="form-check-input" type="checkbox" name="remember" id="rememberMe" {{ old('remember') ? 'checked' : '' }}>
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
                     <a href="{{ route('register') }}" class="text-warning text-decoration-none">Create an account</a>
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
        togglePasswordButton.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            toggleIcon.classList.toggle('bi-eye');
            toggleIcon.classList.toggle('bi-eye-slash');
        });
    }
</script>

</body>
</html>