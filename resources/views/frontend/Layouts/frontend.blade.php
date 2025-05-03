{{-- resources/views/layouts/frontend.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('description', 'EasyFind - Your gateway to properties in Gaza. Buy, sell, or rent houses, apartments, caravans, and tents.')">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'EasyFind - Real Estate')</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('frontend/assets/الشعار مفرغ 2.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('frontend/style.css') }}">
    @stack('styles')

</head>

<body>
    <nav class="navbar navbar-expand-xl bg-white border-bottom navbar1">
        <div class="container">
            <a href="{{ route('frontend.home') }}" class="navbar-brand">
                <img src="{{ asset('frontend/assets/شعار مفرغ 2.png') }}" width="80px" height="80px" alt="logo">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarLinks">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarLinks">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    @can('create', App\Models\Property::class)
                        <li class="nav-item"><a href="{{ route('lister.properties.create') }}" class="nav-link">Sell</a>
                        </li>
                    @else
                        <li class="nav-item"><a
                                href="{{ route('login') }}?redirect={{ urlencode(route('lister.properties.create')) }}"
                                class="nav-link">Sell</a></li>
                    @endcan
                    <li class="nav-item">
                        @guest
                            <a href="{{ route('login') }}?redirect={{ urlencode(route('frontend.properties', ['purpose' => 'sale'])) }}" class="nav-link">Buy</a>
                        @endguest
                        @auth
                            <a href="{{ route('frontend.properties', ['purpose' => 'sale']) }}" class="nav-link {{ request()->routeIs('frontend.properties') && request('purpose') == 'sale' ? 'active' : '' }}">Buy</a>
                        @endauth
                    </li>
                            <li class="nav-item">
                                 @guest
                                    <a href="{{ route('login') }}?redirect={{ urlencode(route('frontend.properties', ['purpose' => 'rent'])) }}" class="nav-link">Rent</a>
                                @endguest
                                @auth
                                    <a href="{{ route('frontend.properties', ['purpose' => 'rent']) }}" class="nav-link {{ request()->routeIs('frontend.properties') && request('purpose') == 'rent' ? 'active' : '' }}">Rent</a>
                                @endauth
                            </li>
                    <li class="nav-item"><a href="#" class="nav-link">Chats</a></li>

                    <li class="nav-item"><a href="{{ route('frontend.favorites') }}"
                            class="nav-link {{ request()->routeIs('frontend.favorites') ? 'active' : '' }}">Favourites</a>
                    </li>


                </ul>

                <!-- أزرار المصادقة الديناميكية -->
                <div class="d-flex align-items-center">
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-light me-2">Log in</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-outline-gold">Sign Up</a>
                        @endif
                    @endguest

                    @auth
                        <div class="nav-item dropdown">
                            <a class="btn btn-light dropdown-toggle d-flex align-items-center" href="#"
                                id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="{{ Auth::user()->profile_image_url }}" alt="{{ Auth::user()->name }}"
                                    width="28" height="28" class="rounded-circle me-2 object-fit-cover">
                                <span class="user-dropdown-name">{{ Str::words(Auth::user()->name, 1, '') }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarUserDropdown">
                                <li><a class="dropdown-item d-flex align-items-center"
                                        href="{{ route('profile.index') }}"><i class="bi bi-person fs-5 me-2"></i><span>My
                                            Profile</span></a></li>
                                @if (Auth::check() && in_array(Auth::user()->role, ['admin', 'content_moderator', 'property_lister']))
                                    <li><a class="dropdown-item d-flex align-items-center"
                                            href="{{ route('dashboard') }}"><i
                                                class="bi bi-speedometer2 fs-5 me-2"></i><span>Dashboard</span></a></li>
                                @endif
                                <li><a class="dropdown-item d-flex align-items-center"
                                        href="{{ route('profile.index') }}"><i
                                            class="bi bi-gear fs-5 me-2"></i><span>Settings</span></a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" class="mb-0">
                                        @csrf
                                        <button type="submit"
                                            class="dropdown-item d-flex align-items-center text-danger"><i
                                                class="bi bi-box-arrow-right fs-5 me-2"></i><span>Log out</span></button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
    {{-- مسافة تحت النافبار --}}
    <div style="height: 77px; background-color: white;"></div>
    <main class="flex-shrink-0 py-4">
        @yield('content')
    </main>
    <div class="house-divider my-5"></div>

    {{-- ================== Footer ================== --}}
    <div class="container">
        <footer class="row row-cols-1 row-cols-sm-2 row-cols-md-5 py-5 my-5 border-top">
            <div class="col mb-3">
                <a href="{{ route('frontend.home') }}"
                    class="d-flex align-items-center mb-3 link-body-emphasis text-decoration-none"
                    aria-label="EasyFind">
                    <img src="{{ asset('frontend/assets/شعار مفرغ 2.png') }}" class="bi me-2" width="120"
                        height="120" alt="EasyFind Logo">
                </a>
                <p class="text-muted">© {{ date('Y') }} EasyFind. All rights reserved.</p>
            </div>
            <div class="col mb-3"></div> {{-- عمود فارغ --}}
            <div class="col mb-3">
                <h5>Company</h5>
                <ul class="nav flex-column">
                    <li class="nav-item mb-2"><a href="{{ route('frontend.home') }}"
                            class="nav-link p-0 text-muted">Home</a></li>
                    <li class="nav-item mb-2"><a href="#" class="nav-link p-0 text-muted">About Us</a></li>
                    <li class="nav-item mb-2"><a href="#" class="nav-link p-0 text-muted">FAQs</a></li>
                </ul>
            </div>
            <div class="col mb-3">
                <h5>Support</h5>
                <ul class="nav flex-column">
                    <li class="nav-item mb-2"><a href="#" class="nav-link p-0 text-muted">Help Center</a></li>
                    <li class="nav-item mb-2"><a href="#" class="nav-link p-0 text-muted">Contact Us</a></li>
                    <li class="nav-item mb-2"><a href="#" class="nav-link p-0 text-muted">Terms &
                            Conditions</a></li>
                    <li class="nav-item mb-2"><a href="#" class="nav-link p-0 text-muted">Privacy Policy</a>
                    </li>
                </ul>
            </div>
            <div class="col mb-3">
                <h5>Services</h5>
                <ul class="nav flex-column">
                    <li class="nav-item mb-2"><a href="{{ route('frontend.properties') }}"
                            class="nav-link p-0 text-muted">Explore Properties</a></li>
                    @can('create', App\Models\Property::class)
                        <li class="nav-item mb-2"><a href="{{ route('lister.properties.create') }}"
                                class="nav-link p-0 text-muted">List Property</a></li>
                    @else
                        <li class="nav-item mb-2"><a
                                href="{{ route('login') }}?redirect={{ urlencode(route('lister.properties.create')) }}"
                                class="nav-link p-0 text-muted">List Property</a></li>
                    @endcan
                </ul>
            </div>
        </footer>
    </div>
    {{-- ================== End Footer ================== --}}

    {{-- Bootstrap JS Bundle --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

    {{-- مكان للسكريبتات الإضافية الخاصة بكل صفحة --}}
    @stack('scripts')

</body>

</html>
