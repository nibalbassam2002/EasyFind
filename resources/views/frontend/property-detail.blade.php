@extends('frontend.Layouts.frontend')
@section('title', 'Properties Listing - EasyFind')
@section('content')
    <div class="card-height" style="background-color: white;"></div>
    <div class="py-4"></div>
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <div class="row">
                    <!-- الفيديو/الصورة الرئيسية -->
                    <div class="col-md-10 order-md-2">
                        <div id="mainMediaDisplay" class="mb-3" style="position: relative;">
                            @php
                                $mediaItems = [];
                                $isMainVideo = false;

                                // 1. معالجة الفيديو أولاً إذا موجود
                                if (!empty($property->video_url)) {
                                    $videoPath = Str::startsWith($property->video_url, ['http://', 'https://'])
                                        ? $property->video_url
                                        : (Storage::exists($property->video_url)
                                            ? Storage::url($property->video_url)
                                            : null);
                                    // تم إصلاح القوس المفقود هنا

                                    if ($videoPath) {
                                        $mediaItems[] = [
                                            'type' => 'video',
                                            'src' => $videoPath,
                                            'thumbnail' => asset('frontend/assets/video_placeholder.png'),
                                        ];
                                    }
                                }

                                // 2. معالجة الصور
                                $images = [];
                                if (!empty($property->images)) {
                                    $images = is_array($property->images)
                                        ? $property->images
                                        : json_decode($property->images, true) ?? [];
                                }

                                foreach ($images as $imagePath) {
                                    // تنظيف مسار الصورة من أي إشارات غير مرغوب فيها
                                    $cleanPath = trim($imagePath, '"\'/\\');

                                    if (filter_var($cleanPath, FILTER_VALIDATE_URL)) {
                                        $mediaItems[] = [
                                            'type' => 'image',
                                            'src' => $cleanPath,
                                            'thumbnail' => $cleanPath,
                                        ];
                                    } elseif (Storage::exists('properties/' . $property->id . '/' . $cleanPath)) {
                                        $mediaItems[] = [
                                            'type' => 'image',
                                            'src' => Storage::url('properties/' . $property->id . '/' . $cleanPath),
                                            'thumbnail' => Storage::url(
                                                'properties/' . $property->id . '/' . $cleanPath,
                                            ),
                                        ];
                                    }
                                }

                                // 3. تحديد الوسائط الافتراضية إذا لم يوجد وسائط
                                if (empty($mediaItems)) {
                                    $mediaItems[] = [
                                        'type' => 'image',
                                        'src' => asset('frontend/assets/no-image-available.jpg'),
                                        'thumbnail' => asset('frontend/assets/no-image-available.jpg'),
                                    ];
                                }
                            @endphp

                            @if ($mediaItems[0]['type'] === 'video')
                                <video src="{{ $mediaItems[0]['src'] }}" class="w-100 rounded" controls></video>
                            @else
                                <img src="{{ $mediaItems[0]['src'] }}" class="w-100 rounded" alt="{{ $property->title }}">
                            @endif
                        </div>

                        <!-- الصور المصغرة -->
                        <div class="col-md-2 order-md-1">
                            @foreach ($mediaItems as $index => $item)
                                <img src="{{ $item['thumbnail'] }}"
                                    class="thumbnail mb-2 {{ $index == 0 ? 'active-thumb' : '' }}"
                                    onclick="changeMainMedia('{{ $item['src'] }}', '{{ $item['type'] }}')"
                                    style="width: 80px; cursor: pointer;">
                            @endforeach
                        </div>
                    </div>
                    <!-- الصور المصغرة -->
                    <div class="col-md-2 order-md-1 d-flex flex-md-column align-items-start overflow-auto"
                        style="max-height: 500px;">
                        @if (!empty($mediaItems))
                            @foreach ($mediaItems as $index => $item)
                                <img src="{{ $item['thumbnail'] }}"
                                    class="thumbnail img-fluid mb-2 rounded shadow-sm @if ($index == 0) border border-primary border-2 @endif"
                                    onclick="switchMainMedia('{{ $item['src'] }}', '{{ $item['type'] }}', this)"
                                    style="width: 100px; height: 75px; cursor: pointer; object-fit: cover;"
                                    alt="Thumbnail {{ $index + 1 }}">
                            @endforeach
                        @else
                            <p class="text-muted small">No media available.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- عمود تفاصيل العقار -->
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h1 class="h3 fw-bold text-primary">{{ $property->currency ?? 'USD' }}
                            {{ number_format($property->price, 0) }}</h1>
                        <h2 class="h5 mb-3">{{ $property->title }}</h2>

                        <p class="text-muted mb-2">
                            <i class="bi bi-tag-fill me-1"></i> For {{ ucfirst($property->purpose) }}
                        </p>
                        <p class="mb-3">
                            <i class="bi bi-geo-alt-fill me-1"></i>
                            {{ $property->address }}, {{ $property->listarea?->name ?? 'N/A' }}
                            @if ($property->listarea?->governorate)
                                , {{ $property->listarea->governorate->name }}
                            @endif
                        </p>

                        <div class="d-flex flex-wrap text-muted property-specs mb-3">
                            @if ($property->rooms)
                                <div class="me-3 mb-2"><i class="bi bi-door-closed me-1"></i> {{ $property->rooms }} Beds
                                </div>
                            @endif
                            @if ($property->bathrooms)
                                <div class="me-3 mb-2"><i class="bi bi-droplet me-1"></i> {{ $property->bathrooms }} Baths
                                </div>
                            @endif
                            @if ($property->area)
                                <div class="me-3 mb-2"><i class="bi bi-arrows-fullscreen me-1"></i>
                                    {{ number_format($property->area) }} sqm</div>
                            @endif
                            @if ($property->floors)
                                <div class="me-3 mb-2"><i class="bi bi-layers me-1"></i> {{ $property->floors }} Floors
                                </div>
                            @endif
                        </div>

                        {{-- زر المفضلة --}}
                        @auth
                            <button
                                class="btn {{ $property->is_favorited ? 'btn-danger' : 'btn-outline-danger' }} w-100 mb-2 favorite-toggle-button"
                                data-property-id="{{ $property->id }}">
                                <i class="bi {{ $property->is_favorited ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                                {{ $property->is_favorited ? 'Remove from Favourites' : 'Add to Favourites' }}
                            </button>
                        @else
                            <a href="{{ route('login', ['redirect' => url()->current()]) }}"
                                class="btn btn-outline-danger w-100 mb-2">
                                <i class="bi bi-heart"></i> Add to Favourites (Login)
                            </a>
                        @endauth

                        {{-- زر الدردشة --}}
                        @if (Auth::check() && Auth::id() !== $property->user_id)
                            <a href="{{ route('chat.conversation.start', ['recipient' => $property->user_id, 'property_id' => $property->id]) }}"
                                class="btn btn-success w-100">
                                <i class="bi bi-chat-dots me-1"></i> Chat with Lister
                            </a>
                        @elseif(!Auth::check())
                            <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="btn btn-success w-100">
                                <i class="bi bi-chat-dots me-1"></i> Chat with Lister (Login)
                            </a>
                        @endif

                        <hr>
                        <div class="text-muted small">
                            <p class="mb-1">Listed by: {{ $property->user?->name ?? 'N/A' }}</p>
                            <p class="mb-0">Posted: {{ $property->created_at->diffForHumans() }}</p>
                            <p class="mb-0">Views: {{ $property->views_count ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-4">
        <div class="row">
            <div class="col-lg-8">
                @if ($property->description)
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Description</h5>
                        </div>
                        <div class="card-body">
                            <p>{!! nl2br(e($property->description)) !!}</p>
                        </div>
                    </div>
                @endif

                @if ($property->category_id == 4 && $property->subCategory)
                    {{-- افتراض أن ID 4 هو "أرض" --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Land Details</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Land Type:</strong> {{ $property->land_type ?? 'N/A' }}</p>
                            {{-- أضف تفاصيل أخرى خاصة بالأرض إذا كانت موجودة في موديل Property --}}
                        </div>
                    </div>
                @elseif ($property->category->name == 'Tent' && $property->subCategory)
                    {{-- مثال آخر --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Tent Details</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Tent Type:</strong> {{ $property->tent_type ?? 'N/A' }}</p>
                        </div>
                    </div>
                @elseif ($property->category->name == 'Caravan' && $property->subCategory)
                    {{-- مثال آخر --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Caravan Details</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Caravan Type:</strong> {{ $property->caravan_type ?? 'N/A' }}</p>
                        </div>
                    </div>
                @endif


                {{-- يمكنك إضافة قسم للمرافق (Amenities) إذا كان لديك --}}
                {{-- @if ($property->amenities && count($property->amenities) > 0)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Amenities</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled row">
                            @foreach ($property->amenities as $amenity)
                                <li class="col-md-6"><i class="bi bi-check-circle-fill text-success me-2"></i>{{ $amenity }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif --}}

                {{-- يمكنك إضافة خريطة هنا إذا كان لديك حقل location بالإحداثيات --}}
                @if ($property->location)
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Location on Map</h5>
                        </div>
                        <div class="card-body p-0">
                            {{-- استبدل هذا بعنصر الخريطة الفعلي (Google Maps, Leaflet, etc.) --}}
                            {{-- للحصول على الإحداثيات: $property->location --}}
                            <iframe
                                src="https://maps.google.com/maps?q={{ urlencode($property->location) }}&t=&z=15&ie=UTF8&iwloc=&output=embed"
                                width="100%" height="350" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                            {{-- ملاحظة: هذا مثال بسيط لخريطة جوجل. قد تحتاج لمفتاح API لطرق أكثر تقدمًا. --}}
                            {{-- قد تحتاج لتحليل $property->location إذا كان يخزن lat,lng بشكل منفصل --}}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>


    {{-- قسم العقارات المشابهة --}}
    @if ($similarProperties && $similarProperties->count() > 0)
        <div class="container py-4">
            <hr class="my-4">
            <h3 class="mb-4">Similar Properties</h3>
            <div class="row g-4">
                @foreach ($similarProperties as $simProperty)
                    <div class="col-md-6 col-lg-3">
                        {{-- هنا يمكنك إعادة استخدام تصميم بطاقة العقار من صفحة القائمة --}}
                        {{-- تأكد من أن بطاقة العقار هذه تأخذ `$simProperty` كمتغير --}}
                        {{-- مثال بسيط: --}}
                        <div class="card property-card h-100 shadow-sm">
                            @php
                                $simImages = $simProperty->images;
                                if (is_string($simProperty->images)) {
                                    $simImages = json_decode($simProperty->images, true);
                                }
                                $simFirstImage = $simImages[0] ?? null;
                                $simImageUrl = asset('frontend/assets/home.jpg');
                                if ($simFirstImage && Storage::disk('public')->exists($simFirstImage)) {
                                    $simImageUrl = Storage::url($simFirstImage);
                                }
                            @endphp
                            <a href="{{ route('frontend.property.show', $simProperty->id) }}">
                                <img src="{{ $simImageUrl }}" class="property-image card-img-top"
                                    alt="{{ Str::limit($simProperty->title, 40) }}">
                            </a>
                            @auth
                                <div class="favorite-icon" data-property-id="{{ $simProperty->id }}">
                                    <i
                                        class="bi {{ $simProperty->is_favorited ? 'bi-heart-fill text-danger' : 'bi-heart' }}"></i>
                                </div>
                            @endauth
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title fw-bold text-primary mb-1">{{ $simProperty->currency ?? 'USD' }}
                                    {{ number_format($simProperty->price, 0) }}</h5>
                                <p class="fw-semibold mb-2 property-title-clamp" title="{{ $simProperty->title }}">
                                    <a href="{{ route('frontend.property.show', $simProperty->id) }}"
                                        class="text-dark text-decoration-none">
                                        {{ Str::limit($simProperty->title, 45) }}
                                    </a>
                                </p>
                                <div class="property-address text-muted small mt-auto">
                                    <i class="bi bi-geo-alt-fill me-1"></i>
                                    {{ Str::limit($simProperty->address, 25) }},
                                    {{ $simProperty->listarea?->name ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

@endsection

@push('styles')
    <style>
        .thumbnail.border-primary {
            outline: 2px solid var(--bs-primary);
            outline-offset: 2px;
        }

        .property-specs i {
            color: var(--bs-primary);
        }
    </style>
@endpush

@push('scripts')
    <script>
        function switchMainMedia(src, type, clickedThumbnail) {
            const mainMediaContainer = document.getElementById('mainMediaDisplay');
            let newMediaElementHTML = '';

            if (type === 'video') {
                newMediaElementHTML =
                    `<video id="mainContentPlayer" src="${src}" class="w-100 rounded shadow-sm" controls autoplay muted style="max-height: 500px; object-fit: cover; aspect-ratio: 16/9;"></video>`;
            } else { // image
                newMediaElementHTML =
                    `<img id="mainContentPlayer" src="${src}" class="w-100 rounded shadow-sm" alt="Main property media" style="max-height: 500px; object-fit: cover; aspect-ratio: 16/9;">`;
            }
            mainMediaContainer.innerHTML = newMediaElementHTML;

            // تحديث الإطار النشط على الصورة المصغرة
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('border', 'border-primary', 'border-2');
            });
            if (clickedThumbnail) {
                clickedThumbnail.classList.add('border', 'border-primary', 'border-2');
            }
        }

        // --- كود التبديل للمفضلة (AJAX) ---
        // تأكد من تضمين jQuery إذا كنت ستستخدمه، أو استخدم Fetch API
        // هذا المثال يستخدم Fetch API وهو لا يحتاج jQuery
        document.addEventListener('DOMContentLoaded', function() {
            const favoriteButtons = document.querySelectorAll('.favorite-toggle-button');
            favoriteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const propertyId = this.dataset.propertyId;
                    const icon = this.querySelector('i');
                    const buttonText = this; // الزر نفسه

                    fetch('{{ route('favorites.toggle') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                property_id: propertyId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'favorited') {
                                icon.classList.remove('bi-heart');
                                icon.classList.add('bi-heart-fill');
                                buttonText.classList.remove('btn-outline-danger');
                                buttonText.classList.add('btn-danger');
                                buttonText.innerHTML =
                                    '<i class="bi bi-heart-fill"></i> Remove from Favourites';
                                // يمكنك عرض رسالة نجاح إذا أردت
                            } else if (data.status === 'unfavorited') {
                                icon.classList.remove('bi-heart-fill');
                                icon.classList.add('bi-heart');
                                buttonText.classList.remove('btn-danger');
                                buttonText.classList.add('btn-outline-danger');
                                buttonText.innerHTML =
                                    '<i class="bi bi-heart"></i> Add to Favourites';
                                // يمكنك عرض رسالة نجاح إذا أردت
                            } else {
                                // التعامل مع الخطأ
                                console.error('Error toggling favorite:', data.message);
                                alert(data.message || 'An error occurred.');
                            }
                        })
                        .catch(error => {
                            console.error('Fetch error:', error);
                            alert('An error occurred while updating favorites.');
                        });
                });
            });
        });
    </script>
@endpush
