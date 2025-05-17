<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Dashboard - Easy Find</title>
    <meta content="" name="description">
    <meta content="" name="keywords">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicons -->
    <link href="{{ asset('assets/img/logo for tab.png') }}" rel="icon">
    <link href="{{ asset('assets/img/apple-touch-icon.png') }}" rel="apple-touch-icon">
    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet">
    <!-- Vendor CSS Files -->
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/quill/quill.snow.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/quill/quill.bubble.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/remixicon/remixicon.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/simple-datatables/style.css') }}" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <style>
        .bg-gold {
            background-color: #FFD700;
        }
    </style>

</head>

<body>

    <!-- ======= Header ======= -->
    <header id="header" class="header fixed-top d-flex align-items-center">
        <div class="d-flex align-items-center justify-content-between">
            <a href="index.html" class="logo d-flex align-items-center">
                <img src="{{ asset('assets/img/شعار مفرغ 2.png') }}" alt="" height="100px">

            </a>
            <i class="bi bi-list toggle-sidebar-btn"></i>
        </div><!-- End Logo -->


        <div class="search-bar">
            <form class="search-form d-flex align-items-center" method="POST" action="#">
                <input type="text" name="query" placeholder="Search" title="Enter search keyword">
                <button type="submit" title="Search"><i class="bi bi-search"></i></button>
            </form>
        </div><!-- End Search Bar -->

        <nav class="header-nav ms-auto">
            <ul class="d-flex align-items-center">

                <li class="nav-item d-block d-lg-none">
                    <a class="nav-link nav-icon search-bar-toggle " href="#">
                        <i class="bi bi-search"></i>
                    </a>
                </li><!-- End Search Icon-->

                <li class="nav-item dropdown">

                    @auth {{-- Ensure user is authenticated --}}
                        @php
                            // Get unread notifications for current user
                            $unreadNotifications = Auth::user()->unreadNotifications;
                            // Get latest 5 notifications (read or unread) for dropdown display
                            $latestNotifications = Auth::user()->notifications()->take(5)->get();
                        @endphp
                    <li class="nav-item dropdown">

                        <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-bell"></i>
                            @if ($unreadNotifications->count() > 0)
                                {{-- Using bg-danger or bg-warning for new notifications would be clearer --}}
                                <span class="badge bg-danger badge-number">{{ $unreadNotifications->count() }}</span>
                            @endif
                        </a><!-- End Notification Icon -->

                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
                            <li class="dropdown-header">
                                @if ($unreadNotifications->count() > 0)
                                    You have {{ $unreadNotifications->count() }}
                                    {{ trans_choice('notification|notifications', $unreadNotifications->count()) }} new
                                @else
                                    No new notifications
                                @endif
                                {{-- TODO: Create this route later --}}
                                <a href="{{route('notifications.index')}}"><span class="badge rounded-pill bg-primary p-2 ms-2">View
                                        All</span></a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>

                            @forelse ($latestNotifications as $notification)
                                <li class="notification-item {{ $notification->unread() ? 'bg-light' : '' }}">
                                    {{-- Highlight unread notifications --}}
                                    <a href="{{ $notification->data['url'] ?? '#' }}"
                                        class="d-flex align-items-start text-decoration-none text-dark w-100"
                                        data-notification-id="{{ $notification->id }}"
                                        onclick="markNotificationAsRead(this)"> {{-- JS function to mark notification as read when clicked --}}

                                        <i
                                            class="{{ $notification->data['icon'] ?? 'bi bi-info-circle text-primary' }} me-3 mt-1 fs-4"></i>
                                        <div>
                                            {{-- You can add a title if it exists in notification data --}}
                                            {{-- <h4 class="mb-0 fs-6">{{ $notification->data['title'] ?? 'New notification' }}</h4> --}}
                                            <p class="mb-1 small">{{ $notification->data['message'] }}</p>
                                            <p class="mb-0 text-muted xsmall">
                                                <small>{{ $notification->created_at->diffForHumans() }}</small></p>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                            @empty
                                <li class="notification-item text-center p-3">
                                    <p class="text-muted mb-0">No notifications to display at this time.</p>
                                </li>
                            @endforelse

                            <li class="dropdown-footer">
                                
                                <a href="{{route('notifications.index')}}">View all notifications</a>
                            </li>

                        </ul><!-- End Notification Dropdown Items -->
                    </li><!-- End Notification Nav -->
                @endauth
                {{-- End of modified notifications section --}}

                </li><!-- End Notification Nav -->
                <li class="nav-item dropdown">
                    @auth
                        <a class="nav-link nav-icon {{ request()->routeIs('chat.*') ? 'active' : '' }}"
                            href="{{ route('chat.index') }}">
                            <i class="bi bi-chat-left-text"></i>
                            <span class="badge bg-success badge-number">3</span>
                        </a>
                    @endauth
                </li>

                <li class="nav-item dropdown pe-3">

                    <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#"
                        data-bs-toggle="dropdown">
                        <img src="{{ Auth::user()->profile_image ? asset('storage/images/' . Auth::user()->profile_image) : asset('assets/img/profile.jpg') }}"
                            alt="Profileee" class="rounded-circle">
                        <span class="d-none d-md-block dropdown-toggle ps-2">{{ Auth::user()->name }}</span>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                        <li class="dropdown-header">
                            <h6>{{ Auth::user()->name }}</h6>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="{{ route('profile.index') }}">
                                <i class="bi bi-person"></i>
                                <span>My Profile</span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
                                <i class="bi bi-gear"></i>
                                <span>Account Settings</span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>

                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item d-flex align-items-center">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span>Sign Out</span>
                                </button>
                            </form>


                        </li>

                    </ul><!-- End Profile Dropdown Items -->
                </li><!-- End Profile Nav -->

            </ul>
        </nav><!-- End Icons Navigation -->

    </header><!-- End Header -->

    <!-- ======= Sidebar ======= -->
    <aside id="sidebar" class="sidebar">
        @include('layouts.partashals.nav')
    </aside><!-- End Sidebar-->
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>@yield('title', ' Dashboard')</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    @yield('breadcrumb-items')
                </ol>
            </nav>
        </div><!-- End Page Title -->
        <section class="section">
            @yield('contant')
        </section>

    </main>



    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->
    <script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/chart.js/chart.umd.js') }}"></script>
    <script src="{{ asset('assets/vendor/echarts/echarts.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/quill/quill.js') }}"></script>
    <script src="{{ asset('assets/vendor/simple-datatables/simple-datatables.js') }}"></script>
    <script src="{{ asset('assets/vendor/tinymce/tinymce.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/php-email-form/validate.js') }}"></script>

    <!-- Template Main JS File -->
    <script src="{{ asset('assets/js/main.js') }}"></script>
    @yield('script')
    <script>
        function markNotificationAsRead(element) {
            let notificationId = element.dataset.notificationId;
            if (notificationId) {
                fetch(`/notifications/${notificationId}/mark-as-read`, { 
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        
                        element.closest('.notification-item').classList.remove('bg-light'); 
                        updateNotificationBadge();
                    }
                })
                .catch(error => console.error('Error marking notification as read:', error));
            }
            
        }

        function updateNotificationBadge() {
            fetch('{{ route("notifications.unread.count") }}') 
                .then(response => response.json())
                .then(data => {
                    const badge = document.querySelector('.header-nav .nav-icon .badge-number');
                    const dropdownHeader = document.querySelector('.notifications .dropdown-header');

                    if (badge) {
                        if (data.count > 0) {
                            badge.textContent = data.count;
                            badge.style.display = 'inline-block';
                        } else {
                            badge.style.display = 'none'; 
                        }
                    }
                    if(dropdownHeader && dropdownHeader.firstChild) {
                        if (data.count > 0) {
                            dropdownHeader.firstChild.textContent = ` You have ${data.count} ${getTransChoice('Notice|Notices|Notices', data.count)} new `;
                        } else {
                            dropdownHeader.firstChild.textContent = ' No new notifications';
                        }
                    }
                })
                .catch(error => console.error('Error fetching unread count:', error));
        }
      
        function getTransChoice(key, number) {
            const parts = key.split('|');
            if (number === 1) return parts[0];
            if (number === 2 && parts.length > 1) return parts[1];
            return parts[parts.length -1];
        }

    
        document.addEventListener('DOMContentLoaded', updateNotificationBadge);
    </script>
</body>

</html>
