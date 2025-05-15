@extends('frontend.Layouts.frontend')
@section('title', 'EasyFind - Home Page')
@section('description', 'Find properties for sale or rent in Gaza. EasyFind helps you locate houses, apartments, tents,
and caravans.')
@section('content')
{{-- Carousel --}}
<div id="carouselExampleCaptions" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="0" class="active"
            aria-current="true" aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="1"
            aria-label="Slide 2"></button>
        <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="2"
            aria-label="Slide 3"></button>
    </div>
    <div class="carousel-inner">
       
        <div class="carousel-item active">
            <img src="{{ asset('frontend/assets/better_cropped_villa.png') }}" class="d-block w-100" alt="Buy properties">
            <div class="container">
                <div class="carousel-caption text-center">
                    <h1><span class="highlighted-word">Buy</span></h1>
                    <p><span class="highlighted-word">All you want in real estate you will find it here</span></p>
                    <form class="d-flex justify-content-center mt-3" role="search" method="GET" action="{{ route('frontend.properties') }}">
                        <input style="width: 300px; max-width: 80%;" class="form-control me-2" type="search" name="search"
                            placeholder="Search by title, address..." aria-label="Search"
                            value="{{ request('search') }}">
                        <button class="btn btn-outline-gold" type="submit">Search</button>
                    </form>
                </div>
            </div>
        </div>
       
        <div class="carousel-item">
            <img src="{{ asset('frontend/assets/cropped_A_high-resolution_digital_photograph_showcases_a_s.png') }}"
            class="d-block w-100" alt="Sell properties">
            <div class="carousel-caption text-center"> 
                <h1>Sell</h1>
                <p>Showcase your property to thousands of potential buyers.</p>
                @can('create', App\Models\Property::class)
                    <p><a class="btn btn-lg btn-outline-light" href="{{ route('lister.properties.create') }}">List Your Property</a></p>
                @else
                    <p><a class="btn btn-lg btn-outline-light"
                            href="{{ route('login') }}?redirect={{ urlencode(route('lister.properties.create')) }}">Login to Sell</a></p>
                @endcan
            </div>
        </div>
        <div class="carousel-item">
            <img src="{{ asset('frontend/assets/better_cropped_villa.png') }}"
                class="d-block w-100" alt="Rent properties">
            <div class="carousel-caption text-center"> 
                <h1><span class="highlighted-word">Rent</span></h1>
                <p class="opacity-75"><span class="highlighted-word">Helping millions find their perfect rental fit.</span></p>
                <p><a class="btn btn-lg btn-outline-gold"
                        href="{{ route('frontend.properties', ['purpose' => 'rent']) }}">View Rentals</a></p>
            </div>
        </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions"
        data-bs-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span
            class="visually-hidden">Previous</span></button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions"
        data-bs-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span
            class="visually-hidden">Next</span></button>
</div>
@auth
    @if(Auth::user()->role == 'property_lister')
        <div class="container mt-3 mb-4" id="lister-quick-actions">
            <div class="card shadow-sm">
                <div class="card-body p-2 p-md-3">
                    <div class="row text-center g-2">
                        <div class="col-6 col-md-3">
                            <a href="{{ route('lister.properties.create', ['purpose' => 'sale']) }}" class="btn btn-outline-gold w-100 py-2">
                                <span class="small">Add for Sale</span>
                                <i class="bi bi-plus-circle-dotted d-block fs-4 mb-1"></i>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('lister.properties.index', ['status' => 'sold']) }}" class="btn btn-outline-secondary w-100 py-2">
                                <span class="small">View Sold</span>
                                <i class="bi bi-eye d-block fs-4 mb-1"></i>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('lister.properties.create', ['purpose' => 'rent']) }}" class="btn btn-outline-gold w-100 py-2">
                                <span class="small">Add for Rent</span>
                                <i class="bi bi-plus-circle-dotted d-block fs-4 mb-1"></i>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('lister.properties.index', ['status' => 'rented']) }}" class="btn btn-outline-secondary w-100 py-2">
                                <span class="small">View Rented</span>
                                   <i class="bi bi-eye d-block fs-4 mb-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endauth

<div class="px-4 py-5 my-5 text-center">
    <h1 class="display-5 fw-bold text-body-emphasis">What's happening in your area</h1>
    <div class="col-lg-6 mx-auto">
        <p class="lead mb-4">Whether you're in search of a new residence, an investment opportunity, or simply exploring
            the area, we are here to assist you in discovering precisely what meets your needs.</p>
        <h3>Palestine - Gaza Strip</h3>
    </div>
</div>

{{-- قسم الإحصائيات --}}
<div class="container my-5">
    <div class="row g-4">
        <div class="col-md-3 col-sm-6">
            <div class="card-stat card-dark">
                <div class="stat-number">
                    {{ App\Models\Property::where('status', 'approved')->where('purpose', 'rent')->count() }}+</div>
                <div class="stat-text">Homes For Rent</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card-stat card-light">
                <div class="stat-number">
                    {{ App\Models\Property::where('status', 'approved')->where('purpose', 'sale')->count() }}+</div>
                <div class="stat-text">Homes For Sale</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card-stat card-dark">
                <div class="stat-number">
                    {{ App\Models\User::where('role', 'property_lister')->where('status', 'active')->count() }}</div>
                <div class="stat-text">Active Real Estate Agents</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card-stat card-light">
                {{-- قد تحتاج لتعديل هذا الاستعلام ليكون أدق حسب تصنيفاتك --}}
                <div class="stat-number">
                    {{ App\Models\Property::where('status', 'approved')->whereHas('category', fn($q) => $q->where('slug', 'apartment'))->count() }}+
                </div>
                <div class="stat-text">Apartments Available</div>
            </div>
        </div>
    </div>
</div>

{{-- قسم أيقونات الفئات --}}
<div class="container py-4">
    <div class="row text-center justify-content-center g-4">
        <div class="col-6 col-md-3 col-lg-1">
            <a href="{{ route('frontend.properties', ['category_slug' => 'house']) }}"
                class="text-decoration-none filter-item-link">
                <div class="filter-item {{ request('category_slug') == 'house' ? 'active' : '' }}">
                    {{-- جعل active ديناميكي --}}
                    <i class="bi bi-house-door" style="font-size: 2rem;"></i>
                    <div>House</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3 col-lg-1">
            <a href="{{ route('frontend.properties', ['category_slug' => 'apartment']) }}"
                class="text-decoration-none filter-item-link">
                <div class="filter-item {{ request('category_slug') == 'apartment' ? 'active' : '' }}">
                    <i class="bi bi-building" style="font-size: 2rem;"></i>
                    <div>Apartment</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3 col-lg-1">
            <a href="{{ route('frontend.properties', ['category_slug' => 'caravan']) }}"
                class="text-decoration-none filter-item-link">
                <div class="filter-item {{ request('category_slug') == 'caravan' ? 'active' : '' }}">
                    <i class="bi bi-truck" style="font-size: 2rem;"></i>
                    <div>Caravan</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3 col-lg-1">
            <a href="{{ route('frontend.properties', ['category_slug' => 'tent']) }}"
                class="text-decoration-none filter-item-link">
                <div class="filter-item {{ request('category_slug') == 'tent' ? 'active' : '' }}">
                    <i class="fas fa-campground" style="font-size: 2rem;"></i>
                    <div>Tent</div>
                </div>
            </a>
        </div>
    </div>
</div>

{{-- قسم عرض العقارات الديناميكية --}}
<div class="container py-4">
    <div class="text-center mb-5">
        <h2 class="display-5 fw-bold text-body-emphasis">Featured Properties</h2>
        <p class="lead text-muted">Explore some of our best listings available now.</p>
    </div>

    <div class="row g-4 property-list-row">
        @forelse ($latestProperties as $property)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex"> {{-- إضافة d-flex هنا --}}
                {{-- اجعل البطاقة بأكملها رابطًا. أضف كلاسات لـ Bootstrap لضمان عدم وجود خط تحت النص إذا لم ترغب به --}}
                <a href="{{ route('frontend.property.show', $property->id) }}" class="text-decoration-none d-block w-100">
                    <div class="card property-card h-100 shadow-sm"> {{-- h-100 لجعل البطاقات متساوية الارتفاع، shadow-sm لتأثير ظل بسيط --}}
                        @php
                            // يفضل استخدام $casts في موديل Property لتحويل 'images' إلى array تلقائياً
                            // إذا لم تكن تستخدم $casts، فالكود الحالي جيد
                            $images = $property->images;
                            if (is_string($property->images)) { // احتياطي إذا لم يتم تحويلها بعد
                                $images = json_decode($property->images, true);
                            }
                            $firstImage = $images[0] ?? null;
                            $imageUrl = asset('frontend/assets/home.jpg'); // صورة افتراضية

                            if ($firstImage && Storage::disk('public')->exists($firstImage)) {
                                $imageUrl = Storage::url($firstImage);
                            } elseif (is_array($images) && count($images) > 0 && Storage::disk('public')->exists($images[0])) {
                                // حالة إضافية إذا كانت images مصفوفة ولكن لم يتم جلبها كـ $firstImage
                                // مفيد إذا لم يكن $casts مستخدماً بشكل صحيح
                                $imageUrl = Storage::url($images[0]);
                            }
                        @endphp
                        <img src="{{ $imageUrl }}" class="property-image card-img-top"
                            alt="{{ Str::limit($property->title, 40) }}">

                        {{-- أيقونة المفضلة --}}
                        {{-- تأكد أن `is_favorited` يتم تعيينها في الكنترولر --}}
                        {{-- الكود الحالي لديك جيد إذا كنت تتعامل مع `is_favorited` بشكل صحيح في الكنترولر --}}
                        @auth {{-- أظهر أيقونة المفضلة فقط للمستخدمين المسجلين --}}
                        <div class="favorite-icon" data-property-id="{{ $property->id }}">
                            {{-- استخدام كلاس text-danger للون الأحمر لأيقونة القلب الممتلئة --}}
                            <i class="bi {{ $property->is_favorited ?? false ? 'bi-heart-fill text-danger' : 'bi-heart' }}"></i>
                        </div>
                        @endauth

                        <div class="card-body d-flex flex-column"> {{-- d-flex و flex-column لمحاذاة العنوان للأسفل --}}
                            <h5 class="card-title fw-bold text-primary mb-1">
                                {{ $property->currency ?? 'USD' }} {{-- افترض أن لديك حقل عملة، أو استخدم قيمة افتراضية --}}
                                {{ number_format($property->price, 0) }}
                            </h5>
                            {{-- لا حاجة لرابط آخر هنا لأن البطاقة كلها رابط --}}
                            <p class="fw-semibold mb-2 property-title-clamp text-dark" title="{{ $property->title }}">
                                {{ Str::limit($property->title, 45) }} {{-- قللت الحد قليلاً لسطرين عادةً --}}
                            </p>
                            <div class="d-flex text-muted property-info mb-2 small">
                                @if ($property->rooms)
                                    <div class="me-3"><i class="bi bi-door-closed"></i> {{ $property->rooms }} bed</div>
                                @endif
                                @if ($property->bathrooms)
                                    <div class="me-3"><i class="bi bi-droplet"></i> {{ $property->bathrooms }} bath</div>
                                @endif
                                @if ($property->area) {{-- تأكد أن اسم الحقل صحيح (مثلاً area أو size) --}}
                                    <div class="me-3"><i class="bi bi-arrows-fullscreen"></i><strong>{{ number_format($property->area) }}</strong> sqm</div>
                                @endif
                            </div>
                            {{-- mt-auto يدفع هذا العنصر لأسفل البطاقة إذا كان هناك مساحة --}}
                            <div class="property-address text-muted small mt-auto">
                                <i class="bi bi-geo-alt-fill me-1"></i>
                                {{ Str::limit($property->address, 25) }}, {{ $property->listarea?->name ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12 d-flex justify-content-center">
                <div class="alert alert-light text-center p-4 p-md-5 border alert-no-properties rounded-3"
                    role="alert" style="max-width: 600px; width: 100%;">
                    <i class="bi bi-buildings fs-1 d-block mb-3 text-muted"></i>
                    <p class="mb-0 lead text-secondary">
                        No properties available to display at the moment.<br> Please check back later!
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- زر عرض الكل --}}
    {{-- يوجد خطأ هنا، إغلاق وسم a غير صحيح --}}
    <div class="text-center mt-5">
        @guest
            <a href="{{ route('login') }}?redirect={{ urlencode(route('frontend.properties')) }}"
                class="btn btn-outline-gold px-5 py-2 rounded-pill shadow-sm">View All Properties</a>
        @endguest
        @auth
            <a href="{{ route('frontend.properties') }}"
                class="btn btn-outline-gold px-5 py-2 rounded-pill shadow-sm">View All Properties</a>
        @endauth
    </div>
</div>

{{-- قسم تقديم الملاحظات --}}
@auth {{-- أظهر هذا القسم فقط للمستخدمين المسجلين --}}
<div class="container my-5" id="feedback-section">
    {{-- تم تغيير الكلاسات هنا لتصبح مشابهة للتصميم الداكن --}}
    <div class="bg-dark text-white rounded-4 p-4 p-md-5">
        <div class="text-center mb-4">
            <h4 class="fw-bold">Share your feedback with us</h4>
            {{-- استخدام text-white-50 للنص الثانوي كما في تصميمك الأصلي --}}
            <p class="text-white-50">
                We value your feedback. Whether it's a complaint, improvement suggestion, or any other idea, please share it with us.
            </p>
        </div>

        @if (session('success'))
            {{-- يمكن ترك الـ alert كما هو أو تخصيص ألوانه ليتناسب أكثر --}}
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('feedback.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="feedback_type" class="form-label fw-semibold">Feedback Type<span class="text-danger">*</span></label>
                    <select class="form-select @error('feedback_type') is-invalid @enderror" id="feedback_type" name="feedback_type" required>
                        <option value="" disabled {{ old('feedback_type') ? '' : 'selected' }}>Choose the note type...</option>
                        <option value="complaint" {{ old('feedback_type') == 'complaint' ? 'selected' : '' }}>Complaint</option>
                        <option value="suggestion" {{ old('feedback_type') == 'suggestion' ? 'selected' : '' }}>Suggestion</option>
                        <option value="improvement" {{ old('feedback_type') == 'improvement' ? 'selected' : '' }}>Improvement Request</option>
                        <option value="other" {{ old('feedback_type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('feedback_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="feedback_subject" class="form-label fw-semibold">Subject(optional)</label>
                    <input type="text" class="form-control @error('feedback_subject') is-invalid @enderror" id="feedback_subject" name="feedback_subject" value="{{ old('feedback_subject') }}" placeholder="Example: Property display issue">
                    @error('feedback_subject')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="feedback_message" class="form-label fw-semibold">Your Message<span class="text-danger">*</span></label>
                    <textarea class="form-control @error('feedback_message') is-invalid @enderror" id="feedback_message" name="feedback_message" rows="5" placeholder="Please write your feedback in detail..." required>{{ old('feedback_message') }}</textarea>
                    @error('feedback_message')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 text-center mt-4">
                    {{-- تغيير لون الزر ليصبح أصفر/ذهبي مثل زر "Subscribe" الأصلي --}}
                    <button type="submit" class="btn btn-warning px-5 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-send me-2"></i>Submit Feedback
                    </button>
                </div>
            </div>
        </form>
        {{-- إذا كان هناك رابط لعرض الملاحظات السابقة، تأكد من أن لونه مناسب للخلفية الداكنة --}}
        {{-- <div class="text-center mt-3">
            <a href="{{ route('feedback.my') }}" class="text-decoration-none text-warning">عرض ملاحظاتي السابقة</a>
        </div> --}}
    </div>
</div>
@else
{{-- قسم الزوار غير المسجلين، تم تعديل ألوانه أيضًا --}}
<div class="container my-5" id="feedback-section-guest">
    <div class="bg-dark text-white rounded-4 p-4 p-md-5 text-center">
        <h4 class="fw-bold">Share your feedback with us</h4>
        <p class="text-white-50">
           Your feedback matters to us! Let us know your thoughts <a href="{{ route('login') }}?redirect={{ url()->current() }}#feedback-section" class="text-warning fw-bold">log in </a> or  <a href="{{ route('register') }}" class="text-warning fw-bold">Sign up</a>.
        </p>
    </div>
</div>
@endauth


@endsection

