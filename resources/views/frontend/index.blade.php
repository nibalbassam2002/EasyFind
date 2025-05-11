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
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card property-card"> 
                    @php
                        $images = json_decode($property->images, true);
                        $firstImage = $images[0] ?? null;
                        $imageUrl = asset('frontend/assets/home.jpg');
                        if ($firstImage && Storage::disk('public')->exists($firstImage)) {
                            $imageUrl = Storage::url($firstImage);
                        }
                    @endphp
                    <a href="{{ route('frontend.property.show', $property->id) }}" title="{{ $property->title }}">
                        <img src="{{ $imageUrl }}" class="property-image card-img-top"
                            alt="{{ Str::limit($property->title, 40) }}">
                    </a>

                    <div class="favorite-icon" data-property-id="{{ $property->id }}">
                        <i class="bi {{ $property->is_favorited ?? false ? 'bi-heart-fill is-favorite' : 'bi-heart' }}"></i>
                    </div>

                    <div class="card-body">
                        <h5 class="card-title fw-bold text-primary mb-1">{{ $property->currency }}
                            {{ number_format($property->price, 0) }}</h5>
                        <p class="fw-semibold mb-2 property-title-clamp" title="{{ $property->title }}">
                            <a href="{{ route('frontend.property.show', $property->id) }}"
                                class="text-dark text-decoration-none"> {{-- تفعيل الرابط لاحقاً --}}
                                {{ Str::limit($property->title, 50) }}
                            </a>
                        </p>
                        <div class="d-flex text-muted property-info mb-2 small">
                            @if ($property->rooms)
                                <div class="me-3"><i class="bi bi-door-closed"></i> {{ $property->rooms }} bed
                                </div>
                            @endif
                            @if ($property->bathrooms)
                                <div class="me-3"><i class="bi bi-droplet"></i> {{ $property->bathrooms }} bath
                                </div>
                            @endif
                            <div class="me-3"><i class="bi bi-arrows-fullscreen"></i><strong>
                                    {{ number_format($property->area) }}</strong> sqm</div>
                        </div>
                        <div class="property-address text-muted small mt-auto">
                            <i class="bi bi-geo-alt-fill me-1"></i>
                            {{ Str::limit($property->address, 25) }}, {{ $property->area?->name ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 d-flex justify-content-center">
                {{-- ***** تعديل هنا: إضافة border وكلاس مخصص ***** --}}
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
    <div class="text-center mt-5">
        @guest
            <a href="{{ route('login') }}?redirect={{ urlencode(route('frontend.properties')) }}"
                class="btn btn-outline-gold px-5 py-2 rounded-pill shadow-sm">View All Properties</a>
        @endguest
        @auth
            <a href="{{ route('frontend.properties') }}"
                class="btn btn-outline-dark px-5 py-2 rounded-pill shadow-sm">View All Properties</a>
        @endauth
    </div>>
</div>

{{-- قسم الاشتراك --}}
<div class="container my-5">
    <div class="bg-dark text-white rounded-4 p-5 text-center">
        <h4 class="fw-bold">Stay Updated</h4> {{-- تعديل النص --}}
        <p class="mt-3 text-white-50">
            Subscribe to our newsletter to get the latest updates on new listings and market news.
        </p>
        <form class="d-flex justify-content-center mt-4" style="max-width: 500px; margin: auto;" method="POST"
            action="#"> {{-- إضافة action لاحقاً --}}
            @csrf
            <input type="email" name="email" class="form-control rounded-start-pill"
                placeholder="Enter email address" required>
            <button type="submit" class="btn btn-warning rounded-end-pill px-4">Subscribe</button>
        </form>
    </div>
</div>


@endsection

