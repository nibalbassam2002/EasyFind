@extends('layouts.dashboard')

@section('title', 'Property: ' . Str::limit($property->title, 35))

@section('breadcrumb-items')
    @parent
    <li class="breadcrumb-item"><a href="{{ route('lister.properties.index') }}">My Properties</a></li>
    <li class="breadcrumb-item active">View Details</li>
@endsection

@push('styles')
<style>
    /* تنسيقات خفيفة جدًا مبدئيًا */
    .property-detail-card {
        margin-bottom: 1.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
        border: 1px solid #e3e6f0;
        border-radius: 0.375rem;
    }
    .property-detail-card .card-header {
        background-color: #f8f9fc;
        font-weight: 600;
    }
    .main-property-image-display {
        width: 100%;
        max-height: 450px; /* حد أقصى لارتفاع الصورة */
        object-fit: cover; /* أو contain */
        border-radius: 0.25rem;
        margin-bottom: 0.5rem;
    }
    .thumbnails-container img {
        height: 70px;
        width: 100px;
        object-fit: cover;
        border-radius: 0.25rem;
        cursor: pointer;
        border: 2px solid transparent;
        margin: 0.25rem;
    }
    .thumbnails-container img.active-thumb,
    .thumbnails-container img:hover {
        border-color: #FFD700;
    }
    .detail-list-group .list-group-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.6rem 1rem; /* تعديل الـ padding */
        font-size: 0.9rem;
    }
    .detail-list-group .list-group-item strong {
        color: #5a5c69;
    }
    .property-description-area {
        white-space: pre-wrap;
        line-height: 1.6;
    }
    .amenities-pills span {
        background-color: #e9ecef;
        padding: 0.3rem 0.8rem;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
        border-radius: 15px;
        font-size: 0.85rem;
        display: inline-block;
    }
</style>
@endpush

@section('contant')
<div class="container-fluid mt-3"> {{-- استخدام container-fluid ومسافة علوية --}}

    {{-- صف العنوان وأزرار الإجراءات --}}
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h4 class="mb-0 fw-bold">{{ $property->title }}</h4>
        </div>
        <div class="col-auto">
            @if($property->status === 'pending' || (Auth::check() && Auth::user()->hasRole('admin')))
                <a href="{{ route('lister.properties.edit', $property->id) }}" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i> Edit</a>
            @else
                <button class="btn btn-sm btn-outline-secondary me-1" disabled><i class="bi bi-pencil"></i> Edit</button>
            @endif
            <form action="{{ route('lister.properties.destroy', $property->id) }}" method="POST" class="d-inline delete-property-form-show">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Delete</button>
            </form>
        </div>
    </div>

    {{-- الصف الرئيسي لمحتوى العقار (صور على اليسار، تفاصيل على اليمين) --}}
    <div class="row g-4">
        {{-- العمود الأيسر للصور والوصف والمرافق --}}
        <div class="col-lg-7">
            {{-- بطاقة الصور --}}
            <div class="card property-detail-card">
                <div class="card-header">
                    <i class="bi bi-images me-2"></i>Property Gallery
                </div>
                <div class="card-body text-center">
                    @php
                        $images = is_string($property->images) ? json_decode($property->images, true) : ($property->images ?? []);
                        if (!is_array($images)) $images = [];
                        $firstImage = count($images) > 0 ? $images[0] : null;
                        $defaultImageUrl = asset('assets/img/placeholder-property.png');
                    @endphp

                    @if($firstImage)
                        <img src="{{ Storage::disk('public')->exists($firstImage) ? Storage::url($firstImage) : $defaultImageUrl }}"
                             alt="{{ $property->title }}" class="main-property-image-display" id="mainPropertyImage">
                    @else
                        <img src="{{ $defaultImageUrl }}" alt="No Image" class="main-property-image-display">
                    @endif

                    @if(count($images) > 1)
                        <div class="thumbnails-container d-flex flex-wrap justify-content-center mt-2">
                            @foreach($images as $index => $imagePath)
                                <img src="{{ Storage::disk('public')->exists($imagePath) ? Storage::url($imagePath) : $defaultImageUrl }}"
                                     alt="Thumb {{ $index + 1 }}"
                                     class="{{ $index == 0 ? 'active-thumb' : '' }}"
                                     onclick="document.getElementById('mainPropertyImage').src='{{ Storage::disk('public')->exists($imagePath) ? Storage::url($imagePath) : $defaultImageUrl }}'; document.querySelectorAll('.thumbnails-container img').forEach(img => img.classList.remove('active-thumb')); this.classList.add('active-thumb');">
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- بطاقة الوصف --}}
            <div class="card property-detail-card">
                <div class="card-header"><i class="bi bi-text-paragraph me-2"></i>Description</div>
                <div class="card-body">
                    <p class="property-description-area">{{ $property->description ?? 'No description provided.' }}</p>
                </div>
            </div>

             {{-- بطاقة المرافق --}}
            @php
                $amenities = is_string($property->amenities) ? json_decode($property->amenities, true) : ($property->amenities ?? []);
                if (!is_array($amenities)) $amenities = [];
            @endphp
            @if(!empty($amenities))
            <div class="card property-detail-card">
                <div class="card-header"><i class="bi bi-check2-square me-2"></i>Amenities</div>
                <div class="card-body amenities-pills">
                    @foreach($amenities as $amenity)
                        <span><i class="bi bi-check-lg text-success"></i> {{ ucfirst(str_replace('_', ' ', $amenity)) }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- العمود الأيمن للتفاصيل الرئيسية، الموقع، الفيديو، الاشتراك --}}
        <div class="col-lg-5">
            <div class="card property-detail-card">
                <div class="card-header"><i class="bi bi-list-ul me-2"></i>Key Details</div>
                <ul class="list-group list-group-flush detail-list-group">
                    <li class="list-group-item"><strong>Price:</strong> <span class="fw-bold text-success fs-6">{{ $property->currency }} {{ number_format($property->price, 0) }}</span></li>
                    <li class="list-group-item"><strong>Status:</strong> @php /* ... كود الحالة ... */ @endphp <span class="badge bg-{{ $sConfig['color'] ?? 'light' }}"><i class="{{ $sConfig['icon'] ?? 'bi-question-circle' }} me-1"></i>{{ ucfirst($property->status) }}</span></li>
                    <li class="list-group-item"><strong>Purpose:</strong> <span class="badge bg-{{ $property->purpose == 'sale' ? 'primary' : 'success' }}">{{ ucfirst($property->purpose) }}</span></li>
                    <li class="list-group-item"><strong>Category:</strong> {{ $property->category?->name ?? 'N/A' }}@if($property->subCategory) / {{ $property->subCategory?->name }}@endif</li>
                    <li class="list-group-item"><strong>Code:</strong> {{ $property->code }}</li>
                    <li class="list-group-item"><strong>Area (Built):</strong> {{ number_format($property->area) }} sqm</li>
                    @if($property->land_area)<li class="list-group-item"><strong>Land Area:</strong> {{ number_format($property->land_area) }} sqm</li>@endif
                    <li class="list-group-item"><strong>Added:</strong> {{ $property->created_at->format('d M, Y') }}</li>
                     <li class="list-group-item"><strong>Views:</strong> {{ $property->views_count ?? 0 }}</li>
                </ul>
            </div>

            <div class="card property-detail-card">
                <div class="card-header"><i class="bi bi-gear-wide me-2"></i>Additional Specifics</div>
                <ul class="list-group list-group-flush detail-list-group">
                    @if($property->rooms) <li class="list-group-item"><strong>Rooms:</strong> {{ $property->rooms }}</li> @endif
                    @if($property->bathrooms) <li class="list-group-item"><strong>Bathrooms:</strong> {{ $property->bathrooms }}</li> @endif
                    @if($property->floors) <li class="list-group-item"><strong>Floors:</strong> {{ $property->floors }}</li> @endif
                    @if($property->apartment_floor_num) <li class="list-group-item"><strong>Apt. Floor:</strong> {{ $property->apartment_floor_num }}</li> @endif
                    @if($property->property_condition) <li class="list-group-item"><strong>Condition:</strong> {{ ucfirst(str_replace('_', ' ', $property->property_condition)) }}</li> @endif
                    @if($property->finishing_type) <li class="list-group-item"><strong>Finishing:</strong> {{ ucfirst($property->finishing_type) }}</li> @endif
                    @if($property->view_type) <li class="list-group-item"><strong>View:</strong> {{ $property->view_type }}</li> @endif
                    @if($property->land_type) <li class="list-group-item"><strong>Land Type:</strong> {{ $property->land_type }}</li> @endif
                    @if($property->commercial_type) <li class="list-group-item"><strong>Comm. Type:</strong> {{ $property->commercial_type }}</li> @endif
                    @if($property->commercial_purpose) <li class="list-group-item"><strong>Suitable For:</strong> {{ $property->commercial_purpose }}</li> @endif
                    @if($property->tent_type) <li class="list-group-item"><strong>Tent Type:</strong> {{ $property->tent_type }}</li> @endif
                    @if($property->caravan_type) <li class="list-group-item"><strong>Caravan Type:</strong> {{ $property->caravan_type }}</li> @endif
                </ul>
                @if($property->additional_details)
                    <div class="card-body border-top">
                        <p class="mb-0"><small>{{ $property->additional_details }}</small></p>
                    </div>
                @endif
            </div>

             <div class="card property-detail-card">
                <div class="card-header"><i class="bi bi-geo-alt-fill me-2"></i>Location</div>
                <ul class="list-group list-group-flush detail-list-group">
                    <li class="list-group-item"><strong>Address:</strong> {{ $property->address }}</li>
                    <li class="list-group-item"><strong>City/Area:</strong> {{ $property->listarea?->name ?? 'N/A' }}</li>
                    <li class="list-group-item"><strong>Governorate:</strong> {{ $property->listarea?->governorate?->name ?? 'N/A' }}</li>
                </ul>
                @if($property->location)
                    <div class="card-body border-top">
                        <p class="mb-1"><strong>Coordinates:</strong> {{ $property->location }}</p>
                        <div class="map-placeholder mt-2"><p>Map Placeholder</p></div>
                    </div>
                @else
                    <div class="card-body border-top text-center text-muted py-2">
                        <small>No precise location coordinates provided.</small>
                    </div>
                @endif
            </div>

            @if($property->video_url)
            <div class="card property-detail-card">
                <div class="card-header"><i class="bi bi-play-btn-fill me-2"></i>Video</div>
                <div class="card-body video-container">
                    {{-- كود تضمين الفيديو هنا --}}
                </div>
            </div>
            @endif

            @if(isset($activeSubscription) && $activeSubscription && $activeSubscription->plan)
            <div class="card property-detail-card">
                <div class="card-header"><i class="bi bi-award me-2"></i>Subscription Info</div>
                <ul class="list-group list-group-flush detail-list-group">
                    <li class="list-group-item"><strong>Listed Under:</strong> {{ $activeSubscription->plan->name }}</li>
                    <li class="list-group-item"><strong>Plan Active Until:</strong> {{ $activeSubscription->ends_at ? $activeSubscription->ends_at->format('d M, Y') : 'No Expiry' }}</li>
                </ul>
                <div class="card-body border-top">
                    <a href="{{ route('frontend.pricing') }}" class="btn btn-sm btn-outline-gold w-100">View/Upgrade Plan</a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function changeMainImage(newSrc, thumbElement) {
        document.getElementById('mainPropertyImage').src = newSrc;
        document.querySelectorAll('.property-gallery-thumbnails img').forEach(img => {
            img.classList.remove('active-thumb');
        });
        if(thumbElement) { // تأكد أن العنصر موجود
            thumbElement.classList.add('active-thumb');
        }
    }

    document.querySelectorAll('.delete-property-form-show').forEach(form => {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            Swal.fire({ /* ... (كود SweetAlert للحذف) ... */ }).then((result) => {
                if (result.isConfirmed) { form.submit(); }
            });
        });
    });
</script>
@endpush