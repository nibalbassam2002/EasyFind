<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('description', 'EasyFind - Your gateway to properties in Gaza. Buy, sell, or rent houses, apartments, caravans, and tents.')">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'EasyFind - Real Estate')</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/logo for tab.png') }}">
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
                    <li class="nav-item">
                        @if (Auth::check() && Auth::user()->role === 'customer')
                            <a href="#" class="nav-link" data-bs-toggle="modal"
                                data-bs-target="#subscribeModal">Sell</a>
                        @else
                            <a href="{{ route('lister.properties.create') }}" class="nav-link">Sell</a>
                        @endif
                    </li>
                    <li class="nav-item">
                        @guest
                            <a href="{{ route('login') }}?redirect={{ urlencode(route('frontend.properties', ['purpose' => 'sale'])) }}"
                                class="nav-link">Buy</a>
                        @endguest
                        @auth
                            <a href="{{ route('frontend.properties', ['purpose' => 'sale']) }}"
                                class="nav-link {{ request()->routeIs('frontend.properties') && request('purpose') == 'sale' ? 'active' : '' }}">Buy</a>
                        @endauth
                    </li>
                    <li class="nav-item">
                        @guest
                            <a href="{{ route('login') }}?redirect={{ urlencode(route('frontend.properties', ['purpose' => 'rent'])) }}"
                                class="nav-link">Rent</a>
                        @endguest
                        @auth
                            <a href="{{ route('frontend.properties', ['purpose' => 'rent']) }}"
                                class="nav-link {{ request()->routeIs('frontend.properties') && request('purpose') == 'rent' ? 'active' : '' }}">Rent</a>
                        @endauth
                    </li>
                    <li class="nav-item">
                        @auth
                            <a href="{{ route('chat.index') }}"
                                class="nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }}">Chats</a>
                        @endauth
                        @guest <a href="{{ route('login') }}" class="nav-link">Chats</a> @endguest
                    </li>

                    <li class="nav-item"><a href="{{ route('frontend.favorites') }}"
                            class="nav-link {{ request()->routeIs('frontend.favorites') ? 'active' : '' }}">Favourites</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto mb-2 mb-xl-0 d-flex align-items-center">
                    @auth
                        @php
                            $unreadFrontendNotifications = Auth::user()->unreadNotifications;
                            $latestFrontendNotifications = Auth::user()->notifications()->take(5)->get();
                        @endphp
                        <li class="nav-item dropdown me-xl-2"> {{-- مسافة لليمين على الشاشات الكبيرة --}}
                            <a class="nav-link" href="#" id="navbarDropdownNotifications" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                                <i class="bi bi-bell"></i>
                                @if ($unreadFrontendNotifications->count() > 0)
                                    <span
                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-count">
                                        {{ $unreadFrontendNotifications->count() }}
                                        <span class="visually-hidden">unread messages</span>
                                    </span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg"
                                aria-labelledby="navbarDropdownNotifications"
                                style="min-width: 320px; max-height: 400px; overflow-y: auto;">
                                <li class="dropdown-header px-3 py-2">
                                    @if ($unreadFrontendNotifications->count() > 0)
                                        لديك {{ $unreadFrontendNotifications->count() }}
                                        {{ trans_choice('إشعار|إشعاران|إشعارات', $unreadFrontendNotifications->count(), [], 'ar') }}
                                        جديدة
                                    @else
                                        لا توجد إشعارات جديدة
                                    @endif
                                    <a href="{{ route('frontend.notifications.index') }}" class="float-end small">عرض
                                        الكل</a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider my-1">
                                </li>

                                @forelse ($latestFrontendNotifications as $notification)
                                    <li>
                                        <a class="dropdown-item notification-item-frontend {{ $notification->unread() ? 'bg-light-subtle' : '' }}"
                                            href="{{ $notification->data['url'] ?? '#' }}"
                                            data-notification-id-frontend="{{ $notification->id }}"
                                            onclick="markFrontendNotificationAsRead(this, event)">
                                            <div class="d-flex align-items-start">
                                                <i
                                                    class="{{ $notification->data['icon'] ?? 'bi bi-info-circle' }} me-2 mt-1 fs-5 {{ $notification->unread() ? 'text-primary' : 'text-muted' }}"></i>
                                                <div class="notification-content">
                                                    <p class="mb-0 small">{{ $notification->data['message'] }}</p>
                                                    <small
                                                        class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    @if (!$loop->last)
                                        <li>
                                            <hr class="dropdown-divider my-1">
                                        </li>
                                    @endif
                                @empty
                                    <li>
                                        <p class="dropdown-item text-center text-muted small py-3">لا توجد إشعارات لعرضها.
                                        </p>
                                    </li>
                                @endforelse

                                @if ($latestFrontendNotifications->count() > 0)
                                    <li>
                                        <hr class="dropdown-divider my-1">
                                    </li>
                                    <li class="dropdown-footer text-center py-2">
                                        <a href="{{ route('frontend.notifications.index') }}" class="small">عرض جميع
                                            الإشعارات</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endauth

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
                                    id="navbarUserDropdown" role="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <img src="{{ Auth::user()->profile_image ? asset('storage/images/' . Auth::user()->profile_image) : asset('assets/img/profile.jpg') }}"
                                        width="28" height="28" class="rounded-circle me-2 object-fit-cover">
                                    <span class="user-dropdown-name">{{ Str::words(Auth::user()->name, 1, '') }}</span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarUserDropdown">
                                    <li>
                                        @if (Auth::user()->role === 'customer')
                                            <a class="dropdown-item d-flex align-items-center"
                                                href="{{ route('frontend.account') }}">
                                                <i class="bi bi-person-circle me-2"></i><span>My Account</span>
                                            </a>
                                        @else
                                            <a class="dropdown-item d-flex align-items-center"
                                                href="{{ route('profile.index') }}">
                                                <i class="bi bi-person-circle me-2"></i><span>My Profile</span>
                                            </a>
                                        @endif
                                    </li>


                                    <li>
                                        @if (Auth::user()->role === 'customer')
                                            <a class="dropdown-item d-flex align-items-center"
                                                href="{{ route('frontend.account') }}">
                                                <i class="bi bi-gear me-2"></i><span>Settings</span>
                                            </a>
                                        @else
                                            <a class="dropdown-item d-flex align-items-center"
                                                href="{{ route('profile.index') }}#profile-settings">
                                                {{-- توجيه مباشر لتبويب الإعدادات --}}
                                                <i class="bi bi-gear me-2"></i><span>Account Settings</span>
                                            </a>
                                        @endif
                                    </li>
                                    @if (Auth::check() && Auth::user()->role != 'customer')
                                        <li><a class="dropdown-item d-flex align-items-center"
                                                href="{{ route('dashboard') }}"><i
                                                    class="bi bi-speedometer2 me-2"></i><span>Dashboard</span></a></li>
                                    @endif
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}" class="mb-0"> @csrf
                                            <button type="submit"
                                                class="dropdown-item d-flex align-items-center text-danger"><i
                                                    class="bi bi-box-arrow-right me-2"></i><span>Log out</span></button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        @endauth
                    </div>
            </div>
        </div>
    </nav>

    <div style="height: 77px; background-color: white;"></div>
    <main class="flex-shrink-0 py-4">
        @yield('content')
    </main>
    <div class="house-divider my-5"></div>


    <div class="container">
        <footer class="row row-cols-1 row-cols-sm-2 row-cols-md-5 py-5 my-5 border-top">
            <div class="col mb-3"> <!-- Logo & Copyright -->
                <a href="{{ route('frontend.home') }}"
                    class="d-flex align-items-center mb-3 link-body-emphasis text-decoration-none"
                    aria-label="EasyFind">
                    <img src="{{ asset('frontend/assets/شعار مفرغ 2.png') }}" class="bi me-2" width="120"
                        height="120" alt="EasyFind Logo">
                </a>
                <p class="text-muted">© {{ date('Y') }} EasyFind. All rights reserved.</p>
            </div>


            <div class="col mb-3">
                
            </div>
         

            <div class="col mb-3"> <!-- Company -->
                <h5>Company</h5>
                <ul class="nav flex-column">
                    <li class="nav-item  mb-2"><a href="{{ route('frontend.home') }}"
                            class="nav-link p-0  text-muted">Home</a></li>
                    <li class="nav-item mb-2"><a href="#" class="nav-link p-0 text-muted">About Us</a></li>
                    <li class="nav-item mb-2">
                        <a href="{{ route('frontend.help_center') }}#faq-getting-started"
                            class="nav-link p-0 text-muted footer-link {{ request()->routeIs('frontend.help_center') ? 'active' : '' }}">
                            FAQs
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col mb-3"> <!-- Support -->
                <h5>Support</h5>
                <ul class="nav flex-column">
                    <li class="nav-item mb-2"><a href="{{ route('frontend.help_center') }}"
                            class="nav-link p-0 text-muted">Help Center</a></li>
                    <li class="nav-item mb-2"><a href="{{ route('frontend.home') }}#feedback-section"
                            class="nav-link p-0 text-muted">Contact Us</a></li>
                    <li class="nav-item mb-2"><a href="#" class="nav-link p-0 text-muted">Terms &
                            Conditions</a></li>
                    <li class="nav-item mb-2"><a href="#" class="nav-link p-0 text-muted">Privacy Policy</a>
                    </li>
                </ul>
            </div>
            <div class="col mb-3"> <!-- Services -->
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.favorite-icon').forEach(iconElement => {
                iconElement.addEventListener('click', function(event) {
                    event.preventDefault();
                    event.stopPropagation();

                    const propertyId = this.dataset.propertyId;
                    const heartIcon = this.querySelector('i');
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content');

                    if (!propertyId || !heartIcon || !csrfToken) {
                        console.error("Favorite Toggle: Missing data", {
                            propertyId,
                            heartIconExists: !!heartIcon,
                            csrfTokenExists: !!csrfToken
                        });
                        Swal.fire({
                            title: 'Error',
                            text: 'Could not process favorite request. (Missing data)',
                            icon: 'error'
                        });
                        return;
                    }


                    heartIcon.classList.add(
                        'processing-favorite');

                    fetch("{{ route('favorites.toggle') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                property_id: propertyId
                            })
                        })
                        .then(response => {
                            if (response.status === 401) {
                                Swal.fire({
                                    title: 'Authentication Required',
                                    text: 'You need to log in to add properties to your favorites.',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Login',
                                    cancelButtonText: 'Later',
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href =
                                            "{{ route('login') }}?redirect=" +
                                            encodeURIComponent(window.location.href);
                                    }
                                });
                                return Promise.reject({
                                    unauthenticated: true,
                                    message: 'Authentication required.'
                                });
                            }
                            if (!response.ok) {

                                return response.json().then(errData => {
                                    throw new Error(errData.message ||
                                        `Request failed with status ${response.status}`
                                    );
                                }).catch(() => {
                                    // إذا لم يكن هناك JSON، ألقِ خطأ عام
                                    throw new Error(
                                        `Request failed with status ${response.status}`
                                    );
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            heartIcon.classList.remove('processing-favorite');

                            if (data.success) {
                                const isFavorite = data.is_favorited;
                                heartIcon.classList.toggle('bi-heart-fill', isFavorite);
                                heartIcon.classList.toggle('bi-heart', !isFavorite);
                                heartIcon.classList.toggle('is-favorite',
                                    isFavorite);

                                const Toast = Swal.mixin({
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 2000,
                                    timerProgressBar: true,
                                    didOpen: (toast) => {
                                        toast.onmouseenter = Swal.stopTimer;
                                        toast.onmouseleave = Swal.resumeTimer;
                                    }
                                });
                                Toast.fire({
                                    icon: 'success',
                                    title: data.message
                                });

                                if (!isFavorite && window.location.pathname.includes(
                                        '/my-favorites')) {
                                    const cardToRemove = this.closest(
                                        '.favorite-property-card, .property-card'
                                    );
                                    if (cardToRemove) {
                                        cardToRemove.style.transition =
                                            "opacity 0.5s, transform 0.5s";
                                        cardToRemove.style.opacity = "0";
                                        cardToRemove.style.transform = "scale(0.9)";
                                        setTimeout(() => {
                                            cardToRemove.remove();

                                            if (document.querySelectorAll(
                                                    ".favorite-property-card")
                                                .length === 0 &&
                                                !document.querySelector(
                                                    '.alert.alert-secondary')
                                            ) {
                                                const container = document
                                                    .querySelector(
                                                        '.container.mt-5:not(.recommended-section)'
                                                    );
                                                if (container) {
                                                    const noFavMsg = `
                                            <div class="alert alert-secondary text-center p-4">
                                                <i class="bi bi-heart fs-3 d-block mb-2"></i>
                                                You haven't added any properties to your favorites yet.
                                            </div>`;
                                                    const pagination = container
                                                        .querySelector(
                                                            '.mt-4.d-flex.justify-content-center'
                                                        );
                                                    if (pagination) pagination
                                                        .insertAdjacentHTML(
                                                            'beforebegin', noFavMsg);
                                                    else container.insertAdjacentHTML(
                                                        'beforeend', noFavMsg);

                                                }
                                            }
                                        }, 500);
                                    }
                                }

                            } else {
                                Swal.fire({
                                    title: 'Failed',
                                    text: data.message || 'Failed to update favorites.',
                                    icon: 'error'
                                });
                            }
                        })
                        .catch(error => {
                            heartIcon.classList.remove('processing-favorite');
                            if (error.unauthenticated) {
                                // تم التعامل معها بالفعل في .then(response => ...)
                                console.warn("User not authenticated for favorite action.");
                            } else {
                                console.error('Error during favorite toggle request:', error);
                                Swal.fire({
                                    title: 'Error',
                                    text: error.message ||
                                        'An error occurred. Please try again.',
                                    icon: 'error'
                                });
                            }
                        });
                });
            });
        });
    </script>
    @auth
        <script>
           function markFrontendNotificationAsRead(element, event) {
        // يمكنك إلغاء التعليق عن event.preventDefault() إذا كنت تريد التحكم الكامل في التوجيه
        // event.preventDefault();
        let notificationId = element.dataset.notificationIdFrontend;
        // let targetUrl = element.href; // إذا كنت ستتحكم في التوجيه يدويًا

        if (notificationId) {
            fetch(`/notifications/${notificationId}/mark-as-read`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok for markAsRead.');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    element.classList.remove('bg-light-subtle'); // إزالة تمييز الخلفية
                    const iconElement = element.querySelector('i.fs-5'); // استهداف الأيقونة داخل الرابط
                    if (iconElement) {
                        iconElement.classList.remove('text-primary');
                        iconElement.classList.add('text-muted');
                    }
                    updateFrontendNotificationBadge(); // تحديث العداد بعد تمييز الإشعار
                } else {
                    console.error('Failed to mark notification as read:', data.message);
                }
                // إذا استخدمت event.preventDefault()، يمكنك التوجيه هنا:
                // window.location.href = targetUrl;
            })
            .catch(error => {
                console.error('Error in markFrontendNotificationAsRead:', error);
                // يمكنك التوجيه للرابط الأصلي حتى لو فشل طلب AJAX
                // window.location.href = targetUrl;
            });
        }
    }

    // =========================================================================
    // دالة مساعدة لـ trans_choice
    // =========================================================================
    function getTransChoice(key, number) {
        const parts = key.split('|');
        if (number === 1) return parts[0];
        if (number === 2 && parts.length > 1 && parts[1] !== '') return parts[1]; // تأكد أن الجزء الثاني ليس فارغًا
        if (parts.length > 2 && parts[2] !== '') return parts[2]; // إذا كان هناك جزء ثالث
        return parts[parts.length - 1]; // الافتراضي هو الجزء الأخير
    }

    // =========================================================================
    // استدعاء تحديث العداد عند تحميل الصفحة
    // =========================================================================
    document.addEventListener('DOMContentLoaded', function() {
        // تحقق من وجود عناصر الإشعارات قبل محاولة تحديث العداد
        if (document.querySelector('.navbar .notification-count') || document.querySelector('#navbarDropdownNotifications')) {
            updateFrontendNotificationBadge();
        }

        // أي أكواد أخرى تعتمد على DOMContentLoaded وتتطلب مصادقة
    });

        </script>
    @endauth

    @stack('scripts')
    <div class="modal fade" id="subscribeModal" tabindex="-1" aria-labelledby="subscribeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    {{-- <h5 class="modal-title" id="subscribeModalLabel">Subscription Required</h5> --}}
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="bi bi-exclamation-circle-fill text-warning fs-1 mb-3"></i>
                    <p class="lead">You cannot sell or rent before subscribing to EasyFind Plus.</p>
                </div>
                <div class="modal-footer justify-content-center border-0 pb-4">
                    {{-- هذا الزر سيوجه لصفحة الخطط --}}
                    <a href="{{ route('frontend.pricing') }}" class="btn btn-primary btn-lg">Subscribe Now</a>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
