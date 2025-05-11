@extends('frontend.Layouts.frontend')
@section('title', 'My Account - EasyFind')
@push('styles')
    <style>
        .btn-gold {
            background-color: #FFD700; 
            border-color: #FFD700;
            color: #333; 
            font-weight: 600;
        }
        .btn-gold:hover,
        .btn-gold:focus,
        .btn-gold:active {
            background-color: #FFD700; 
            border-color: #FFD700;
            color: #333; 
            box-shadow: 0 0 0 0.25rem rgba(255, 215, 0, 0.5); 
        }
        .filter-item {
            cursor: pointer;
            padding: 1rem;
            border-radius: 10px;
            transition: background-color 0.3s;
        }

        .filter-item.active,
        .filter-item:hover {
            background-color: #f0f0f0;
        }

        .property-image {
            height: 200px;
            object-fit: cover;
        }

        .favorite-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 1.5rem;
            color: rgb(10, 10, 10);
            cursor: pointer;
        }

        .property-card {
            position: relative;
        }

        .bi-heart-fill {
            color: red;
        }

        #cityFilter {
            border: 1px solid #ccc;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        #cityFilter:focus {
            border-color: #FFD700;
            /* دهبي */
            box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25);
        }

        #typeFilter:focus {
            border-color: #FFD700;
            /* دهبي */
            box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25);
        }

        .filter-section {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            border: 0.5px solid #000000;
            margin-left: 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }


        .property-card {
            transition: transform 0.2s;

        }

        .property-card:hover {
            transform: scale(1.02);
        }

        .card-img-top {
            height: 250px;
            object-fit: cover;
        }

        .favorite-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 1.5rem;
            color: rgb(10, 10, 10);
            cursor: pointer;
        }

        .a-gold {
            color: #000000;
        }

        .a-gold :hover {
            color: #FFD700;
        }

        .a-gold1 {
            color: #FFD700;
        }

        .btn-yellow:hover {
            background-color: #FFD700;
            color: white;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 300px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .btn-yellow {
            background-color: #fadd32;
            color: black;
            font-weight: bold;
        }

        .search-bar {
            border: 1px solid #ccc;
            border-radius: 30px;
            padding: 10px 20px;
        }

        .question-btn {
            border: 1px solid #000;
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 500;
            background-color: #fff;
        }

        .faq-box {
            border: 1px solid #000;
            border-radius: 12px;
            padding: 10px 20px;
            margin-bottom: 15px;
        }

        .btn-support {
            background-color: #fadd32;
            color: black;
            font-weight: bold;
            border-radius: 30px;
            padding: 12px 30px;
        }

        .rounded-box {
            border: 1px solid #ccc;
            border-radius: 12px;
            padding: 10px 15px;
            position: relative;
        }

        .edit-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
        }

        .sidebar {
            border: 1px solid #ccc;
            border-radius: 12px;
            padding: 20px;
        }

        .active-link {
            color: #f5c518;
            font-weight: bold;
        }

        .profile-img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
        }

        .header-nav .nav-profile {
            color: #012970;
        }

        .header-nav .nav-profile img {
            max-height: 36px;
        }

        .header-nav .nav-profile span {
            font-size: 14px;
            font-weight: 600;
        }

        .header-nav .profile {
            min-width: 240px;
            padding-bottom: 0;
            top: 8px !important;
        }

        .header-nav .profile .dropdown-header h6 {
            font-size: 18px;
            margin-bottom: 0;
            font-weight: 600;
            color: #444444;
        }

        .header-nav .profile .dropdown-header span {
            font-size: 14px;
        }

        .header-nav .profile .dropdown-item {
            font-size: 14px;
            padding: 10px 15px;
            transition: 0.3s;
        }

        .header-nav .profile .dropdown-item i {
            margin-right: 10px;
            font-size: 18px;
            line-height: 0;
        }

        .header-nav .profile .dropdown-item:hover {
            background-color: #f6f9ff;
        }

        .profile .profile-card img {
            max-width: 120px;
        }

        .profile .profile-card h2 {
            font-size: 24px;
            font-weight: 700;
            color: #2c384e;
            margin: 10px 0 0 0;
        }

        .profile .profile-card h3 {
            font-size: 18px;
        }

        .profile .profile-card .social-links a {
            font-size: 20px;
            display: inline-block;
            color: rgba(216, 186, 16, 0.5);
            line-height: 0;
            margin-right: 10px;
            transition: 0.3s;
        }

        .profile .profile-card .social-links a:hover {
            color: #012970;
        }

        .profile .profile-overview .row {
            margin-bottom: 20px;
            font-size: 15px;
        }

        .profile .profile-overview .card-title {
            color: #012970;
        }


        .profile .profile-edit img {
            max-width: 120px;
        }

        .account-content .profile .nav-tabs-bordered .nav-link {
            color: #495057;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            transition: color 0.2s ease-in-out, border-color 0.2s ease-in-out;
        }

        .account-content .profile .nav-tabs-bordered .nav-link:hover:not(.active) {
            color: var(--navbar-gold-color, #f0ad4e);
            border-bottom-color: #e9ecef;
        }

        .account-content .profile .nav-tabs-bordered .nav-link.active {
            color: var(--navbar-gold-color, #f0ad4e);
            background-color: #fff;
            border-bottom-color: var(--navbar-gold-color, #f0ad4e);
            font-weight: 600;
        }

        .account-content .profile .nav-tabs-bordered .nav-link,
        .account-content .profile .nav-tabs-bordered .nav-link.active {
            border-left: none;
            border-right: none;
            border-top: none;
        }

        .account-content .profile-details-card .card-body {
            padding-top: 1.5rem !important;
        }

        .profile .profile-overview .card-title {
            color: #343a40;
            font-weight: 600;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }

        .profile .profile-overview .card-title.pt-3 {
            padding-top: 1rem;
        }

        .profile .profile-overview .label ,
        .profile .profile-edit label{
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
            padding-top: 0.15rem;
        }

        .profile .profile-overview .data-value-display {
            font-size: 0.95rem;
            color: #212529;
            word-wrap: break-word;
        }

        .profile .profile-overview .row {
            padding-bottom: 0.8rem;
            margin-bottom: 0.8rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .profile .profile-overview .row:last-child {
            margin-bottom: 0;
            padding-bottom: 0;

        }

        .profile .profile-overview .small.fst-italic {
            color: #6c757d;
            margin-bottom: 1.5rem;
        }
    </style>
@endpush
@section('content')
    <div class="container-fluid mt-4 mb-5">
        <div class="row gx-lg-4">
            <!--  Sidebar -->
            <div class="col-md-3 col-lg-3 mb-4 mb-md-0 account-sidebar">
                      <!-- زر برجر لفتح السايدبار (للشاشات الصغيرة فقط) -->

      <div class="d-md-none p-2">
        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
          <i class="bi bi-list "></i>
        </button>
      </div>
        <!-- Offcanvas للهواتف -->
      <div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
        <div class="offcanvas-header">
          <h5 class="offcanvas-title" id="sidebarOffcanvasLabel">Menu</h5>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
          <div class="offcanvas-body">
       <!-- محتوى السايدبار -->
            <a href="#" class=" nav-link">
                        <p><img src="{{ Auth::user()->profile_image ? asset('storage/images/' . Auth::user()->profile_image) : asset('assets/img/profile.jpg') }}"
                                alt="{{ Auth::user()->name }}" class="rounded-circle" width="30px" height="30px"
                                style="border-radius: 50px;">{{ Auth::user()->name }}</p>
                        
                    </a>
                    <!-- account details -->
                    <div class="mb-3 a-gold1">
                        <a href="{{ route('frontend.account') }}"
                            class="  list-group-item list-group-item-action {{ request()->routeIs('frontend.account') ? 'active' : '' }}">
                            <i class="bi bi-person-fill" style="font-size: 20px;"></i> Account Details</a>

                    </div>
                    <!-- favourites -->
                    <div class="mb-3 a-gold">
                        <a href="{{ route('frontend.favorites') }}"
                            class="list-group-item list-group-item-action {{ request()->routeIs('frontend.favorites') ? 'active' : '' }}"><i
                                class="bi bi-heart" style="font-size: 15px;"></i>
                            Favourites</a>

                    </div>

                    <!-- Chats -->
                    <div class="mb-3 a-gold">
                        <a href="./chat.html" class="nav-link"><i class="bi bi-chat-dots	" style="font-size: 15px;"></i>
                            Chats</a>
                    </div>
                    <!-- notification -->
                    <div class="mb-3 a-gold">
                        <!-- زر الإشعارات كأنه نص -->
                        <button id="notificationBtn2" style="all: unset; cursor: pointer;">
                            <i class="bi bi-bell	" style="font-size: 15px;"></i> Notifications
                        </button>
                    </div>

                    <!-- refer friend -->
                    <div class="mb-3 a-gold">
                        <a href="#" class="nav-link" onclick="copyReferralLink()">
                            <i class="bi bi-person-plus" style="font-size: 15px;"></i> Refer friend
                        </a>
                    </div>
                    <div class="mb-3 " style="height: 40px;">

                    </div>
                    <!--break line -->
                    <div class="mb-3 ">
                        <hr>
                    </div>

                    <!-- Setting -->
                    <div class="mb-3 a-gold">
                        <a href="#" class="nav-link"><i class="bi bi-gear" style="font-size: 20px;"></i> Settings</a>
                    </div>
                    <!-- Help center -->
                    <div class="mb-3 a-gold">
                        <a href="#" class="nav-link"><i class="bi bi-question-circle	" style="font-size: 20px;"></i>
                            Help center</a>
                    </div>
                    <!-- Log out -->
                    <div class="mb-3 a-gold">
                        <a href="#" class="list-group-item list-group-item-action text-danger"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Logout</span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
    
    </div>
          </div>
    
     

              <!--  Sidebar for big screen -->
               <div class="filter-section d-none d-md-block p-3 border">
                    <a href="#" class=" nav-link">
                        <p><img src="{{ Auth::user()->profile_image ? asset('storage/images/' . Auth::user()->profile_image) : asset('assets/img/profile.jpg') }}"
                                alt="{{ Auth::user()->name }}" class="rounded-circle" width="30px" height="30px"
                                style="border-radius: 50px;">{{ Auth::user()->name }}</p>
                        
                    </a>
                    <!-- account details -->
                    <div class="mb-3 a-gold1">
                        <a href="{{ route('frontend.account') }}"
                            class="  list-group-item list-group-item-action {{ request()->routeIs('frontend.account') ? 'active' : '' }}">
                            <i class="bi bi-person-fill" style="font-size: 20px;"></i> Account Details</a>

                    </div>
                    <!-- favourites -->
                    <div class="mb-3 a-gold">
                        <a href="{{ route('frontend.favorites') }}"
                            class="list-group-item list-group-item-action {{ request()->routeIs('frontend.favorites') ? 'active' : '' }}"><i
                                class="bi bi-heart" style="font-size: 15px;"></i>
                            Favourites</a>

                    </div>

                    <!-- Chats -->
                    <div class="mb-3 a-gold">
                        <a href="./chat.html" class="nav-link"><i class="bi bi-chat-dots	" style="font-size: 15px;"></i>
                            Chats</a>
                    </div>
                    <!-- notification -->
                    <div class="mb-3 a-gold">
                        <!-- زر الإشعارات كأنه نص -->
                        <button id="notificationBtn" style="all: unset; cursor: pointer;">
                            <i class="bi bi-bell	" style="font-size: 15px;"></i> Notifications
                        </button>
                    </div>

                    <!-- refer friend -->
                    <div class="mb-3 a-gold">
                        <a href="#" class="nav-link" onclick="copyReferralLink()">
                            <i class="bi bi-person-plus" style="font-size: 15px;"></i> Refer friend
                        </a>
                    </div>
                    <div class="mb-3 " style="height: 40px;">

                    </div>
                    <!--break line -->
                    <div class="mb-3 ">
                        <hr>
                    </div>

                    <!-- Setting -->
                    <div class="mb-3 a-gold">
                        <a href="#" class="nav-link"><i class="bi bi-gear" style="font-size: 20px;"></i> Settings</a>
                    </div>
                    <!-- Help center -->
                    <div class="mb-3 a-gold">
                        <a href="#" class="nav-link"><i class="bi bi-question-circle	" style="font-size: 20px;"></i>
                            Help center</a>
                    </div>
                    <!-- Log out -->
                    <div class="mb-3 a-gold">
                        <a href="#" class="list-group-item list-group-item-action text-danger"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Logout</span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
    </div>
    
            </div>
            <!-- main Section -->
            <div class="col-md-9 col-lg-9 account-content">

                <section class="section profile">
                    <div class="row">
                        <div class="col-xl-4">

                            <div class="card">
                                <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">

                                    <img src="{{ Auth::user()->profile_image ? asset('storage/images/' . Auth::user()->profile_image) : asset('assets/img/profile.jpg') }}"
                                        alt="Profileee" class="rounded-circle">
                                    <h2>{{ Auth::user()->name }}</h2>

                                </div>
                            </div>

                        </div>

                        <div class="col-xl-8">

                            <div class="card">
                                <div class="card-body pt-3">
                                    <!-- Bordered Tabs -->
                                    <ul class="nav nav-tabs nav-tabs-bordered">

                                        <li class="nav-item">
                                            <button class="nav-link active" data-bs-toggle="tab"
                                                data-bs-target="#profile-overview">Overview</button>
                                        </li>

                                        <li class="nav-item">
                                            <button class="nav-link" data-bs-toggle="tab"
                                                data-bs-target="#profile-edit">Edit
                                                Profile</button>
                                        </li>

                                        <li class="nav-item">
                                            <button class="nav-link" data-bs-toggle="tab"
                                                data-bs-target="#profile-settings">Settings</button>
                                        </li>

                                        <li class="nav-item">
                                            <button class="nav-link" data-bs-toggle="tab"
                                                data-bs-target="#profile-change-password">Change Password</button>
                                        </li>

                                    </ul>
                                    <div class="tab-content pt-2">

                                        <div class="tab-pane fade show active profile-overview" id="profile-overview">




                                            {{-- قسم Profile Details --}}
                                            <h5 class="card-title {{ Auth::user()->description ? '' : 'pt-3' }}">Profile
                                                Details</h5> {{-- إضافة pt-3 إذا لم يكن هناك قسم About --}}

                                            {{-- استخدام row و col لعرض التفاصيل بشكل منظم --}}
                                            <div class="row mb-2">
                                                <div class="col-lg-3 col-md-4 label">Full Name</div>
                                                <div class="col-lg-9 col-md-8 data-value-display">{{ Auth::user()->name }}
                                                </div> {{-- إضافة كلاس جديد للعرض --}}
                                            </div>

                                            <div class="row mb-2">
                                                <div class="col-lg-3 col-md-4 label">Location</div>
                                                <div class="col-lg-9 col-md-8 data-value-display">
                                                    {{ Auth::user()->area?->name ?? 'Not Set' }}@if (Auth::user()->area)
                                                        , {{ Auth::user()->area->governorate?->name }}
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="row mb-2">
                                                <div class="col-lg-3 col-md-4 label">Address</div>
                                                <div class="col-lg-9 col-md-8 data-value-display">
                                                    {{ Auth::user()->address ?? 'Not Set' }}</div>
                                            </div>

                                            <div class="row mb-2">
                                                <div class="col-lg-3 col-md-4 label">Phone</div>
                                                <div class="col-lg-9 col-md-8 data-value-display">
                                                    {{ Auth::user()->phone ?? 'N/A' }}</div>
                                            </div>

                                            <div class="row mb-0"> {{-- إزالة mb-2 من آخر صف --}}
                                                <div class="col-lg-3 col-md-4 label">Email</div>
                                                <div class="col-lg-9 col-md-8 data-value-display">
                                                    {{ Auth::user()->email }}</div>
                                            </div>

                                        </div>



                                        <div class="tab-pane fade profile-edit pt-3" id="profile-edit">


                                            @if (session('profile_success'))
                                                <div class="alert alert-success alert-dismissible fade show"
                                                    role="alert">
                                                    {{ session('profile_success') }}
                                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                        aria-label="Close"></button>
                                                </div>
                                            @endif
                                            @if ($errors->any())
                                                <div class="alert alert-danger alert-dismissible fade show"
                                                    role="alert">
                                                    <h6 class="alert-heading fw-bold"><i
                                                            class="bi bi-exclamation-triangle-fill me-2"></i> Please fix
                                                        the errors:</h6>
                                                    <ul class="mb-0">
                                                        @foreach ($errors->all() as $error)
                                                            <li>{{ $error }}</li>
                                                        @endforeach
                                                    </ul>
                                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                        aria-label="Close"></button>
                                                </div>
                                            @endif

                                            {{-- الفورم الكامل لتعديل البروفايل (يرسل إلى دالة update القديمة مؤقتاً أو دالة جديدة) --}}
                                            {{-- ملاحظة: هذا الفورم لا يستخدم AJAX حالياً مثل حقول Overview --}}
                                            <form method="POST" action="{{ route('frontend.account.update') }}"
                                                enctype="multipart/form-data"> {{-- <-- يرسل لدالة الداشبورد حالياً --}}
                                                @csrf
                                                @method('PATCH')

                                                <div class="row mb-3">
                                                    <label for="profileImageUploadEdit"
                                                        class="col-md-4 col-lg-3 col-form-label">Profile Image</label>
                                                    <div class="col-md-8 col-lg-9">
                                                        <img src="{{ Auth::user()->profile_image ? asset('storage/images/' . Auth::user()->profile_image) : asset('assets/img/profile.jpg') }}"
                                                            alt="Profile" class="rounded-circle"
                                                            id="profileImagePreviewEdit"
                                                            style="max-width: 120px; max-height: 120px; object-fit: cover;">
                                                        <div class="pt-2">
                                                            <label for="profileImageUploadEdit"
                                                                class="btn btn-primary btn-sm"
                                                                title="Upload new profile image">
                                                                <i class="bi bi-upload"></i> Upload
                                                            </label>
                                                            <input type="file" name="profile_image"
                                                                id="profileImageUploadEdit" style="display:none;"
                                                                onchange="loadFileEdit(event)">
                                                            {{-- زر الحذف سيستدعي الآن دالة confirmDeleteImage --}}
                                                            <button type="button" class="btn btn-danger btn-sm"
                                                                title="Remove profile image"
                                                                onclick="confirmDeleteImage()">
                                                                <i class="bi bi-trash"></i> Remove
                                                            </button>
                                                        </div>
                                                        @error('profile_image')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                {{-- حقل الاسم الكامل --}}
                                                <div class="row mb-3">
                                                    <label for="fullNameEdit"
                                                        class="col-md-4 col-lg-3 col-form-label required">Full Name</label>
                                                    <div class="col-md-8 col-lg-9">
                                                        <input name="name" type="text"
                                                            class="form-control @error('name') is-invalid @enderror"
                                                            id="fullNameEdit"
                                                            value="{{ old('name', Auth::user()->name) }}" required>
                                                        @error('name')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>


                                                {{-- حقل المحافظة والمنطقة --}}
                                                <div class="row mb-3">
                                                    <label for="governorate_id_edit"
                                                        class="col-md-4 col-lg-3 col-form-label">Governorate</label>
                                                    <div class="col-md-8 col-lg-9">
                                                        <select class="form-select @error('area_id') is-invalid @enderror"
                                                            id="governorate_id_edit" name="governorate_id_display">
                                                            <option value="">Select Governorate (Optional)...
                                                            </option>
                                                            @isset($governorates) {{-- يجب تمرير governorates لهذه الصفحة أيضاً --}}
                                                                @foreach ($governorates as $governorate)
                                                                    <option value="{{ $governorate->id }}"
                                                                        {{ old('governorate_id_display', Auth::user()->area?->governorate_id ?? '') == $governorate->id ? 'selected' : '' }}
                                                                        data-areas="{{ json_encode($governorate->areas->pluck('name', 'id')) }}">
                                                                        {{ $governorate->name }}
                                                                    </option>
                                                                @endforeach
                                                            @endisset
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <label for="area_id_edit"
                                                        class="col-md-4 col-lg-3 col-form-label">Area</label>
                                                    <div class="col-md-8 col-lg-9">
                                                        <select class="form-select @error('area_id') is-invalid @enderror"
                                                            id="area_id_edit" name="area_id">
                                                            <option value="">Select Area (Optional)...</option>
                                                            {{-- إعادة ملء المناطق عند تحميل الصفحة أو عند وجود خطأ --}}
                                                            @if (old('area_id') || Auth::user()->area_id)
                                                                @php
                                                                    /* نفس كود Blade لملء المناطق من الإجابات السابقة */
                                                                @endphp
                                                                @if (!empty($areasForSelectedGov))
                                                                    @foreach ($areasForSelectedGov as $area)
                                                                        <option value="{{ $area->id }}"
                                                                            {{ $selectedAreaId == $area->id ? 'selected' : '' }}>
                                                                            {{ $area->name }}</option>
                                                                    @endforeach
                                                                @else
                                                                    <option value="" disabled>Select Governorate
                                                                        First</option>
                                                                @endif
                                                            @else
                                                                <option value="" disabled>Select Governorate First
                                                                </option>
                                                            @endif
                                                        </select>
                                                        @error('area_id')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                {{-- حقل العنوان --}}
                                                <div class="row mb-3">
                                                    <label for="AddressEdit"
                                                        class="col-md-4 col-lg-3 col-form-label">Address</label>
                                                    <div class="col-md-8 col-lg-9">
                                                        <input name="address" type="text"
                                                            class="form-control @error('address') is-invalid @enderror"
                                                            id="AddressEdit"
                                                            value="{{ old('address', Auth::user()->address) }}">
                                                        @error('address')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                {{-- حقل الهاتف --}}
                                                <div class="row mb-3">
                                                    <label for="PhoneEdit"
                                                        class="col-md-4 col-lg-3 col-form-label">Phone</label>
                                                    <div class="col-md-8 col-lg-9">
                                                        <input name="phone" type="text"
                                                            class="form-control @error('phone') is-invalid @enderror"
                                                            id="PhoneEdit"
                                                            value="{{ old('phone', Auth::user()->phone) }}">
                                                        @error('phone')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                {{-- حقل الإيميل (عادة للقراءة فقط أو يتطلب تحقق) --}}
                                                <div class="row mb-3">
                                                    <label for="EmailEdit"
                                                        class="col-md-4 col-lg-3 col-form-label">Email</label>
                                                    <div class="col-md-8 col-lg-9">
                                                        <input name="email" type="email"
                                                            class="form-control @error('email') is-invalid @enderror"
                                                            id="EmailEdit"
                                                            value="{{ old('email', Auth::user()->email) }}" required>
                                                        @error('email')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>


                                                <div class="text-center">
                                                    <button type="submit" class="btn btn-gold">Save Changes</button>
                                                </div>
                                            </form><!-- End Profile Edit Form -->

                                        </div>

                                        <div class="tab-pane fade pt-3" id="profile-settings">

                                            <!-- Settings Form -->
                                            <form>

                                                <div class="row mb-3">
                                                    <label for="fullName" class="col-md-4 col-lg-3 col-form-label">Email
                                                        Notifications</label>
                                                    <div class="col-md-8 col-lg-9">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="changesMade" checked>
                                                            <label class="form-check-label" for="changesMade">
                                                                Changes made to your account
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="newProducts" checked>
                                                            <label class="form-check-label" for="newProducts">
                                                                Information on new products and services
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="proOffers">
                                                            <label class="form-check-label" for="proOffers">
                                                                Marketing and promo offers
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="securityNotify" checked disabled>
                                                            <label class="form-check-label" for="securityNotify">
                                                                Security alerts
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="text-center">
                                                    <button type="submit" class="btn btn-gold">Save Changes</button>
                                                </div>
                                            </form><!-- End settings Form -->

                                        </div>

                                        <div class="tab-pane fade pt-3" id="profile-change-password">
                                            <!-- Change Password Form -->
                                            <form method="POST" action="{{ route('profile.changePassword') }}">
                                                @csrf
                                                @method('PATCH')

                                                @if (session('success'))
                                                    <div class="alert alert-success alert-dismissible fade show"
                                                        role="alert">
                                                        {{ session('success') }}
                                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                            aria-label="Close"></button>
                                                    </div>
                                                @endif

                                                <div class="row mb-3">
                                                    <label for="currentPassword"
                                                        class="col-md-4 col-lg-3 col-form-label">Current Password</label>
                                                    <div class="col-md-8 col-lg-9">
                                                        <input name="current_password" type="password"
                                                            class="form-control @error('current_password') is-invalid @enderror"
                                                            id="currentPassword">
                                                        @error('current_password')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row mb-3">
                                                    <label for="newPassword" class="col-md-4 col-lg-3 col-form-label">New
                                                        Password</label>
                                                    <div class="col-md-8 col-lg-9">
                                                        <input name="new_password" type="password"
                                                            class="form-control @error('new_password') is-invalid @enderror"
                                                            id="newPassword">
                                                        @error('new_password')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row mb-3">
                                                    <label for="renewPassword"
                                                        class="col-md-4 col-lg-3 col-form-label">Re-enter New
                                                        Password</label>
                                                    <div class="col-md-8 col-lg-9">
                                                        <input name="new_password_confirmation" type="password"
                                                            class="form-control" id="renewPassword">
                                                    </div>
                                                </div>

                                                <div class="text-center">
                                                    <button type="submit" class="btn btn-gold">Change
                                                        Password</button>
                                                </div>
                                            </form>
                                        </div>

                                    </div><!-- End Bordered Tabs -->

                                </div>
                            </div>

                        </div>

                    </div>
            </div>


        @endsection
        @push('scripts')
       <script>
        const modal = document.getElementById('notificationModal');
        const btn1 = document.getElementById('notificationBtn');
        const btn2 = document.getElementById('notificationBtn2');
        const close = document.getElementById('closeModal');

        function openModal() {
        modal.style.display = 'flex';
                     }

        if (btn1) btn1.onclick = openModal;
        if (btn2) btn2.onclick = openModal;

        if (close) {
        close.onclick = () => {
        modal.style.display = 'none';
                     };
                       }

         window.onclick = (e) => {
         if (e.target === modal) {
         modal.style.display = 'none';
                     }
                       };
           </script>

            <script>
                function toggleEdit(icon) {
                    const box = icon.closest('.rounded-box');
                    const input = box.querySelector('input');

                    if (input.readOnly) {
                        input.readOnly = false;
                        input.classList.remove('form-control-plaintext');
                        input.classList.add('form-control');
                        input.focus();
                    } else {
                        input.readOnly = true;
                        input.classList.add('form-control-plaintext');
                        input.classList.remove('form-control');
                    }
                }

                function previewImage(event) {
                    const input = event.target;
                    const reader = new FileReader();
                    reader.onload = function() {
                        const img = document.getElementById('profileImage');
                        img.src = reader.result;
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            </script>
            <script>
  function copyReferralLink() {
    const link = "https://amirkehail1.github.io/EasyFind/";
    navigator.clipboard.writeText(link).then(function () {
      alert("Referral link copied to clipboard!");
    }, function (err) {
      alert("Failed to copy the link: " + err);
    });
  }
</script>
            <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                // Function to preview the uploaded image
                var loadFile = function(event) {
                    var output = document.getElementById('output');
                    if (event.target.files && event.target.files[0]) {
                        output.src = URL.createObjectURL(event.target.files[0]);
                        output.onload = function() {
                            URL.revokeObjectURL(output.src) // free memory
                        }
                    }
                };

                // Function to confirm image deletion
                function confirmDeleteImage(element) {
                    Swal.fire({
                        title: 'هل أنت متأكد؟',
                        text: "لن تتمكن من التراجع عن هذا!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'نعم، احذفها!',
                        cancelButtonText: 'إلغاء'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            performImageDelete();
                        }
                    })
                }

               // Function to perform the image deletion using Axios
function performImageDelete() {
    axios.delete("{{ route('profile.deleteImage') }}")
        .then(function(response) {
            // تحقق من أن الخادم أرسل البيانات المتوقعة
            if (response.data && response.data.message && response.data.default_image) {
                Swal.fire(
                    'تم الحذف!',
                    response.data.message,
                    'success'
                );

                const defaultImagePath = response.data.default_image;

                // 1. تحديث الصورة في تبويب "Edit Profile"
                let editProfileImage = document.getElementById('profileImagePreviewEdit');
                if (editProfileImage) {
                    editProfileImage.src = defaultImagePath;
                } else {
                    console.warn('Element with ID "profileImagePreviewEdit" not found for update.');
                }

                // 2. تحديث الصورة في تبويب "Overview" (البطاقة الجانبية)
                // يفترض أن هذا هو المحدد الصحيح بناءً على هيكل HTML لديك
                let overviewCardImage = document.querySelector('.profile-card .rounded-circle');
                if (overviewCardImage) {
                    overviewCardImage.src = defaultImagePath;
                } else {
                    console.warn('Overview card image not found with selector ".profile-card .rounded-circle".');
                }

                // 4. تحديث صورة السايدبار (القائمة الجانبية للملف الشخصي)
                // يفترض أن هذا هو المحدد الصحيح للصورة الأولى في السايدبار
                let sidebarAccImage = document.querySelector('.account-sidebar .filter-section img.rounded-circle');
                if (sidebarAccImage) {
                    sidebarAccImage.src = defaultImagePath;
                } else {
                    console.warn('Sidebar account image not found with selector ".account-sidebar .filter-section img.rounded-circle".');
                }

                // 5. مسح قيمة حقل إدخال الملف (مهم)
                let fileInput = document.getElementById('profileImageUploadEdit');
                if (fileInput) {
                    fileInput.value = ""; // هذا يمنع إعادة إرسال الملف المحذوف إذا لم يتم اختيار ملف جديد وضغط المستخدم حفظ
                }

            } else {
                // إذا كانت استجابة الخادم ناجحة (2xx) لكنها لا تحتوي على البيانات المتوقعة
                console.error('Server response successful, but data format is incorrect:', response.data);
                Swal.fire(
                    'خطأ في الاستجابة!',
                    'الخادم لم يرجع البيانات بالشكل المتوقع بعد الحذف.',
                    'error'
                );
            }
        })
        .catch(function(error) {
            console.error("AJAX Error during image deletion:", error); // تسجيل الخطأ الكامل في الكونسول

            let errorMessage = 'حدث خطأ ما أثناء الحذف.'; // رسالة عامة

            if (error.response) {
                // الخادم استجاب بحالة خطأ (مثل 4xx أو 5xx)
                console.error("Server Error Response Data:", error.response.data);
                console.error("Server Error Response Status:", error.response.status);
                if (error.response.data && error.response.data.message) {
                    errorMessage = error.response.data.message; // استخدم رسالة الخطأ من الخادم إذا كانت موجودة
                } else if (error.response.status) {
                    errorMessage = `خطأ من الخادم: ${error.response.status}`;
                }
            } else if (error.request) {
                // الطلب أُرسل ولكن لم يتم تلقي أي رد (مشكلة شبكة عادةً)
                console.error("No response received:", error.request);
                errorMessage = 'لم يتم تلقي رد من الخادم. يرجى التحقق من اتصالك بالإنترنت.';
            } else {
                // خطأ حدث أثناء إعداد الطلب
                console.error('Error setting up request:', error.message);
                errorMessage = `خطأ في إعداد الطلب: ${error.message}`;
            }

            Swal.fire(
                'خطأ!',
                errorMessage,
                'error'
            );
        });
}
            </script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const governorateSelectEdit = document.getElementById('governorate_id_edit'); // <--- ID جديد
                    const areaSelectEdit = document.getElementById('area_id_edit'); // <--- ID جديد

                    function updateAreaOptionsEdit() { // <--- اسم دالة جديد
                        if (!governorateSelectEdit || !areaSelectEdit) return;
                        const selectedGovOption = governorateSelectEdit.options[governorateSelectEdit.selectedIndex];
                        const currentSelectedArea = "{{ old('area_id', Auth::user()->area_id ?? '') }}"; // القيمة الحالية
                        areaSelectEdit.innerHTML = ''; // إفراغ

                        let defaultOption = new Option('Select Area (Optional)...', '');
                        areaSelectEdit.add(defaultOption);
                        areaSelectEdit.disabled = true;


                        if (selectedGovOption && selectedGovOption.value && selectedGovOption.dataset.areas) {
                            areaSelectEdit.disabled = false;
                            let areas = {};
                            try {
                                areas = JSON.parse(selectedGovOption.dataset.areas);
                            } catch (e) {
                                console.error(e);
                                return;
                            }

                            for (const areaId in areas) {
                                if (areas.hasOwnProperty(areaId)) {
                                    const option = new Option(areas[areaId], areaId);
                                    if (String(areaId) === String(currentSelectedArea)) {
                                        option.selected = true;
                                    }
                                    areaSelectEdit.add(option);
                                }
                            }
                            if (!areaSelectEdit.value && currentSelectedArea) areaSelectEdit.value = ""; // أبقِ الافتراضي
                        }
                    }

                    if (governorateSelectEdit) {
                        governorateSelectEdit.addEventListener('change', updateAreaOptionsEdit);
                        // شغل عند التحميل
                        updateAreaOptionsEdit();
                    }
                });
            </script>
            <script>
                var loadFileEdit = function(event) {
                    var output = document.getElementById('profileImagePreviewEdit'); // <--- تم التصحيح
                    if (event.target.files && event.target.files[0]) {
                        var reader = new FileReader();
                        reader.onload = function() {
                            output.src = reader.result;
                            output.onload = function() { // اختياري: لتحرير الذاكرة بعد تحميل الصورة في الـ img
                                URL.revokeObjectURL(output.src);
                            }
                        };
                        reader.readAsDataURL(event.target.files[0]);
                    } else {

                        output.src =
                            "{{ Auth::user()->profile_image ? asset('storage/images/' . Auth::user()->profile_image) : asset('assets/img/profile.jpg') }}";
                    }
                };
            </script>
        @endpush

