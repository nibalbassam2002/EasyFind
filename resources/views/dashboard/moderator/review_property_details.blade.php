@extends('layouts.dashboard')

@section('title', 'Review Property: ' . Str::limit($property->title, 35))

@section('breadcrumb-items')
    @parent
    <li class="breadcrumb-item">Moderation</li>
    <li class="breadcrumb-item"><a href="{{ route('moderator.properties.pending') }}">Pending Properties</a></li>
    <li class="breadcrumb-item active">Review Details</li>
@endsection

@push('styles')
<style>
    .property-detail-card {
        margin-bottom: 1.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
        border: 1px solid #e3e6f0;
        border-radius: 0.375rem;
    }
    .property-detail-card .card-header {
        background-color: #f8f9fc;
        font-weight: 600;
        padding: 0.85rem 1.25rem;
        border-bottom: 1px solid #e3e6f0;
    }
    .main-property-image-display {
        width: 100%;
        max-height: 480px;
        object-fit: cover;
        border-radius: 0.25rem;
        margin-bottom: 1rem;
        border: 1px solid #dee2e6;
    }
    .thumbnails-container img {
        height: 75px;
        width: 110px;
        object-fit: cover;
        border-radius: 0.25rem;
        cursor: pointer;
        border: 2px solid transparent;
        margin: 0.25rem;
        transition: border-color 0.2s ease-in-out;
    }
    .thumbnails-container img.active-thumb,
    .thumbnails-container img:hover {
        border-color: #0d6efd; /* Bootstrap primary color */
    }
    .detail-list-group .list-group-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.8rem 1.25rem;
        font-size: 0.9rem;
        border-bottom: 1px solid #eef0f2;
    }
    .detail-list-group .list-group-item:last-child {
        border-bottom: none;
    }
    .detail-list-group .list-group-item strong {
        color: #5a5c69;
        min-width:140px;
    }
    .property-description-area {
        white-space: pre-wrap;
        line-height: 1.7;
        color: #495057;
        font-size: 0.95rem;
        padding: 1rem 0;
    }
    .amenities-pills span {
        background-color: #e9ecef;
        padding: 0.4rem 0.9rem;
        margin-right: 0.6rem;
        margin-bottom: 0.6rem;
        border-radius: 15px;
        font-size: 0.85rem;
        display: inline-block;
        color: #495057;
    }
    .action-buttons-review {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #dee2e6;
    }
    #rejectionReasonFormReview { /* تم تعديل الـ ID هنا */
        display: none;
        margin-top: 1.5rem;
        padding: 1rem;
        background-color: #fffcf1;
        border: 1px solid #ffc107;
        border-radius: 0.25rem;
    }
    .video-container iframe, .video-container video {
        max-width: 100%;
        border-radius: 0.25rem;
    }
    .map-container-review {
        height: 300px;
        width: 100%;
        border: 1px solid #ccc;
        border-radius: 0.25rem;
    }
</style>
@endpush

@section('contant')
<div class="container-fluid mt-3">

    <div class="row mb-3 align-items-center">
        <div class="col">
            <h4 class="mb-0 fw-bold">{{ $property->title }}</h4>
            <span class="badge bg-gold text-dark fs-6"><i class="bi bi-hourglass-split me-1"></i>Status: {{ ucfirst($property->status) }}</span>
        </div>
        <div class="col-auto">
            <a href="{{ route('moderator.properties.pending') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left-circle"></i> Back to Pending List
            </a>
        </div>
    </div>

    <div class="row g-lg-4 g-md-3 g-2">
        {{-- العمود الأيسر للصور والوصف والمرافق --}}
        <div class="col-lg-7">
            {{-- بطاقة الصور --}}
            <div class="card property-detail-card">
                <div class="card-header"><i class="bi bi-images me-2"></i>Property Gallery</div>
              <div class="card-body p-3">
    @php
        $images = is_string($property->images) ? json_decode($property->images, true) : ($property->images ?? []);
        if (!is_array($images)) $images = [];
        $firstImage = count($images) > 0 ? $images[0] : null;
        $defaultImageUrl = asset('assets/img/placeholder-property.png');
    @endphp

    <div class="row align-items-start">
        {{-- الصورة الرئيسية على اليسار --}}
      <div class="row">
    {{-- الصور المصغّرة على اليسار --}}
    <div class="col-md-3">
        @if(count($images) > 1)
            <div class="d-flex flex-md-column flex-wrap gap-2" id="reviewPropertyImagesContainer">
                @foreach($images as $index => $imagePath)
                    @if(Storage::disk('public')->exists($imagePath))
                        <img src="{{ Storage::url($imagePath) }}" 
                             alt="Thumbnail {{ $index + 1 }}"
                             class="img-thumbnail thumb-image {{ $index == 0 ? 'active-thumb' : '' }}"
                             onclick="document.getElementById('mainPropertyImageReview').src='{{ Storage::url($imagePath) }}'; 
                                      document.querySelectorAll('#reviewPropertyImagesContainer img').forEach(img => img.classList.remove('active-thumb')); 
                                      this.classList.add('active-thumb');">
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    {{-- الصورة الرئيسية على اليمين --}}
    <div class="col-md-9 text-center">
        @if($firstImage && Storage::disk('public')->exists($firstImage))
            <img src="{{ Storage::url($firstImage) }}" 
                 alt="{{ $property->title }}" 
                 class="img-fluid border rounded main-property-image-display" 
                 id="mainPropertyImageReview">
        @else
            <img src="{{ $defaultImageUrl }}" 
                 alt="No Image" 
                 class="img-fluid border rounded main-property-image-display">
        @endif
    </div>
</div>

@if(empty($images))
    <p class="text-muted mt-3 text-center">No images provided for this property.</p>
@endif

  </div>
        </div>
            </div>

            {{-- بطاقة الوصف --}}
            <div class="card property-detail-card">
                <div class="card-header"><i class="bi bi-text-paragraph me-2"></i>Description</div>
                <div class="card-body p-3">
                    <p class="property-description-area">{{ $property->description ?? 'No description provided.' }}</p>
                </div>
            </div>

             {{-- بطاقة المرافق --}}
            @php $amenities = is_string($property->amenities) ? json_decode($property->amenities, true) : ($property->amenities ?? []); if (!is_array($amenities)) $amenities = []; @endphp
            @if(!empty($amenities))
            <div class="card property-detail-card">
                <div class="card-header"><i class="bi bi-check2-square me-2"></i>Amenities & Features</div>
                <div class="card-body amenities-pills p-3">
                    @foreach($amenities as $amenityKey => $amenityValue)
                        @if(is_string($amenityValue))
                            <span><i class="bi bi-check-lg text-success me-1"></i>{{ ucfirst(str_replace('_', ' ', $amenityValue)) }}</span>
                        @elseif(is_string($amenityKey))
                             <span><i class="bi bi-check-lg text-success me-1"></i>{{ ucfirst(str_replace('_', ' ', $amenityKey)) }}</span>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- العمود الأيمن للتفاصيل الرئيسية، الموقع، الفيديو، وإجراءات المراجعة --}}
        <div class="col-lg-5">
            <div class="card property-detail-card">
                <div class="card-header"><i class="bi bi-list-ul me-2"></i>Key Details</div>
                <ul class="list-group list-group-flush detail-list-group">
                    <li class="list-group-item"><strong>Price:</strong> <span class="fw-bold text-primary fs-6">{{ $property->currency }} {{ number_format($property->price, 0) }}</span></li>
                    <li class="list-group-item"><strong>Purpose:</strong> <span class="badge bg-{{ $property->purpose == 'sale' ? 'primary' : ($property->purpose == 'rent' ? 'success' : 'info') }}">{{ ucfirst($property->purpose) }}</span></li>
                    <li class="list-group-item"><strong>Category:</strong> {{ $property->category?->name ?? 'N/A' }}@if($property->subCategory) / {{ $property->subCategory?->name }}@endif</li>
                    <li class="list-group-item"><strong>Code:</strong> {{ $property->code }}</li>
                    <li class="list-group-item"><strong>Listed By:</strong> {{ $property->user?->name ?? 'N/A' }}
                        @if($property->user)
                        (<a href="{{ route('admin.users.show', $property->user_id) }}" target="_blank">View User Profile</a>)
                        @endif
                    </li>
                    <li class="list-group-item"><strong>Area (Built):</strong> {{ number_format($property->area) }} sqm</li>
                    @if($property->land_area)<li class="list-group-item"><strong>Land Area:</strong> {{ number_format($property->land_area) }} sqm</li>@endif
                    <li class="list-group-item"><strong>Added:</strong> {{ $property->created_at->format('d M, Y') }} ({{ $property->created_at->diffForHumans() }})</li>
                    <li class="list-group-item"><strong>Views:</strong> {{ $property->views_count ?? 0 }}</li>
                </ul>
            </div>

            <div class="card property-detail-card">
                <div class="card-header"><i class="bi bi-gear-wide-connected me-2"></i>Additional Specifics</div>
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
                    <div class="card-body border-top p-3">
                        <p class="mb-0"><small class="text-muted">{{ $property->additional_details }}</small></p>
                    </div>
                @endif
            </div>

             <div class="card property-detail-card">
                <div class="card-header"><i class="bi bi-geo-alt-fill me-2"></i>Location Details</div>
                <ul class="list-group list-group-flush detail-list-group">
                    <li class="list-group-item"><strong>Address:</strong> {{ $property->address }}</li>
                    <li class="list-group-item"><strong>City/Area:</strong> {{ $property->listarea?->name ?? 'N/A' }}</li>
                    <li class="list-group-item"><strong>Governorate:</strong> {{ $property->listarea?->governorate?->name ?? 'N/A' }}</li>
                </ul>
                {{-- عرض الخريطة إذا كانت الإحداثيات موجودة --}}
                @if($property->latitude && $property->longitude)
                    <div class="card-body border-top p-3">
                        <div id="propertyReviewMap" class="map-container-review"></div>
                    </div>
                @else
                    <div class="card-body border-top text-center text-muted py-3">
                        <small>No precise map location coordinates provided.</small>
                    </div>
                @endif
            </div>

            @if($property->video_url)
            <div class="card property-detail-card">
                <div class="card-header"><i class="bi bi-play-btn-fill me-2"></i>Property Video</div>
                <div class="card-body video-container p-3">
                    {{-- يمكنك تحسين عرض الفيديو هنا باستخدام iframe إذا كان رابط يوتيوب مثلاً --}}
                    <a href="{{ $property->video_url }}" target="_blank">{{ $property->video_url }}</a>
                </div>
            </div>
            @endif

            {{-- قسم أزرار المراجعة وسبب الرفض --}}
            <div class="card property-detail-card">
                <div class="card-header"><i class="bi bi-check2-circle me-2"></i>Moderation Actions</div>
                <div class="card-body action-buttons-review text-center p-3">
                    <form action="{{ route('moderator.properties.approve', $property->id) }}" method="POST" class="d-inline-block me-2 mb-2">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-outline-gold btn-lg">
                            <i class="bi bi-check-lg"></i> Approve Property
                        </button>
                    </form>

                    <button type="button" class="btn btn-outline-danger btn-lg " id="rejectButtonReview">
                        <i class="bi bi-x-lg"></i> Reject Property
                    </button>

                    <form action="{{ route('moderator.properties.reject', $property->id) }}" method="POST" id="rejectionReasonFormReview" class="mt-3">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3 text-start">
                            <label for="rejection_reason_review" class="form-label fw-bold">Reason for Rejection (Required):</label>
                            <textarea class="form-control @error('rejection_reason') is-invalid @enderror" id="rejection_reason_review" name="rejection_reason" rows="3" required>{{ old('rejection_reason') }}</textarea>
                            @error('rejection_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                        <button type="button" class="btn btn-secondary" id="cancelRejectionReview">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{-- سكريبت Leaflet إذا كانت الإحداثيات موجودة --}}
    @if($property->latitude && $property->longitude)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof L !== 'undefined' && document.getElementById('propertyReviewMap')) {
                    const lat = parseFloat("{{ $property->latitude }}");
                    const lng = parseFloat("{{ $property->longitude }}");
                    if (!isNaN(lat) && !isNaN(lng)) {
                        try {
                            const mapReview = L.map('propertyReviewMap').setView([lat, lng], 15);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                                maxZoom: 18, // تحديد أقصى تكبير
                            }).addTo(mapReview);
                            L.marker([lat, lng]).addTo(mapReview).bindPopup("{{ Str::limit(e($property->title), 30) }}");
                        } catch (e) {
                            console.error("Error initializing Leaflet map for review: ", e);
                            document.getElementById('propertyReviewMap').innerHTML = '<p class="text-danger text-center p-3">Map could not be loaded.</p>';
                        }
                    } else {
                         document.getElementById('propertyReviewMap').innerHTML = '<p class="text-warning text-center p-3">Invalid coordinates for map.</p>';
                    }
                } else if (document.getElementById('propertyReviewMap')) {
                     document.getElementById('propertyReviewMap').innerHTML = '<p class="text-muted text-center p-3">Map library (Leaflet) not loaded or map container missing.</p>';
                }
            });
        </script>
    @endif

    {{-- سكريبت لإظهار/إخفاء نموذج سبب الرفض --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rejectButton = document.getElementById('rejectButtonReview');
            const rejectionForm = document.getElementById('rejectionReasonFormReview');
            const cancelRejectionButton = document.getElementById('cancelRejectionReview');
            const rejectionTextarea = document.getElementById('rejection_reason_review');

            if (rejectButton && rejectionForm) {
                rejectButton.addEventListener('click', function() {
                    rejectionForm.style.display = 'block';
                    rejectButton.style.display = 'none';
                    if(rejectionTextarea) rejectionTextarea.focus();
                });
            }
            if (cancelRejectionButton && rejectionForm && rejectButton) {
                cancelRejectionButton.addEventListener('click', function() {
                    rejectionForm.style.display = 'none';
                    if(rejectionTextarea) rejectionTextarea.value = '';
                    rejectButton.style.display = 'inline-block';
                });
            }
            // إذا كان هناك خطأ تحقق من سبب الرفض، أبقِ النموذج ظاهرًا
             @if ($errors->has('rejection_reason'))
                if (rejectButton && rejectionForm) {
                    rejectionForm.style.display = 'block';
                    rejectButton.style.display = 'none';
                }
            @endif

            // JavaScript لتبديل الصور المصغرة
            const mainImageReview = document.getElementById('mainPropertyImageReview');
            const thumbnailsReview = document.querySelectorAll('#reviewPropertyImagesContainer img'); // تم تعديل الـ ID

            thumbnailsReview.forEach(thumb => {
                thumb.addEventListener('click', function() {
                    if(mainImageReview) mainImageReview.src = this.src;
                    thumbnailsReview.forEach(img => img.classList.remove('active-thumb'));
                    this.classList.add('active-thumb');
                });
            });
        });
    </script>
@endpush