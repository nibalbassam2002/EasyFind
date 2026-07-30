@extends('frontend.Layouts.frontend')
@section('title', $property->title . ' - EasyFind')
@section('description', Str::limit(strip_tags($property->description ?? 'View details for ' . $property->title), 155))

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        .main-media-container {
            position: relative;
            background-color: #f0f0f0;
            border-radius: 0.375rem;
            overflow: hidden;
            height: 400px;
            /* يمكنك تعديل هذا الارتفاع حسب احتياجك */
            display: flex;
            justify-content: center;
            align-items: center;
        }

        #mainContentPlayer {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        /* إذا كنت تريد تأثيرًا أكثر سلاسة عند التكبير/التصغير */
        #mainContentPlayer {
            transition: transform 0.3s ease;
        }

        #mainContentPlayer:hover {
            transform: scale(1.02);
        }

        .thumbnails-column {
            max-height: 500px;
            /* نفس ارتفاع الوسائط الرئيسية */
            overflow-y: auto;
            /* تمرير عمودي إذا زادت الصور المصغرة */
        }

        .thumbnail-item {
            width: 100%;
            /* لتأخذ عرض العمود الأب */
            height: 75px;
            /* ارتفاع ثابت للصور المصغرة */
            object-fit: cover;
            cursor: pointer;
            border: 2px solid transparent;
            /* إطار شفاف مبدئي */
            transition: border-color 0.2s ease-in-out;
        }

        .thumbnail-item.active-thumbnail {
            border-color: var(--bs-primary);
            /* أو لونك الذهبي --theme-gold */
            box-shadow: 0 0 5px rgba(var(--bs-primary-rgb, 13, 110, 253), 0.5);
        }

        .property-specs i {
            color: var(--bs-primary);
            /* أو لونك الذهبي */
        }

        .sticky-sidebar {
            position: -webkit-sticky;
            /* Safari */
            position: sticky;
            top: 100px;
            /* مسافة من الأعلى بعد تثبيت الهيدر */
        }

        /* لتحسين مظهر iframe الفيديو إذا كان مباشرًا وليس يوتيوب */
        #mainMediaDisplay video {
            background-color: #000;
            /* خلفية سوداء للفيديو */
        }

        /* تعديل لمواصفات العقار لتكون أكثر تناسقاً */
        .property-specs>div {
            flex-basis: 50%;
            /* كل عنصر يأخذ نصف العرض */
        }

        @media (min-width: 576px) {

            /* للشاشات الصغيرة فأكبر */
            .property-specs>div {
                flex-basis: auto;
                /* يعود للسلوك الافتراضي (بجانب بعض) */
            }
        }

        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            font-size: 1.5rem;
            justify-content: flex-end;
            line-height: 1.5rem;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            color: #ccc;
            cursor: pointer;
            transition: color 0.2s;
        }

        .star-rating :checked~label {
            color: #ffc107;
        }

        .star-rating label:hover,
        .star-rating label:hover~label {
            color: #ffc107;
        }
    </style>
@endpush

@section('content')
    <div class="container mt-4 mb-5">
        @php
            // --- بداية كود PHP لمعالجة الوسائط (يجب أن يكون هنا مرة واحدة) ---
            $allMediaItems = [];
            $mainDisplayItem = null;
            $defaultImage = asset('frontend/assets/no-image-available.jpg'); // تأكد من وجود هذه الصورة

            // 1. معالجة الفيديو أولاً وإضافته إلى القائمة
            if (!empty($property->video_url)) {
                $videoUrl = trim($property->video_url);
                if (Str::startsWith($videoUrl, ['http://', 'https://'])) {
                    if (
                        preg_match(
                            '/(youtube\.com\/(watch\?v=|embed\/|v\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/',
                            $videoUrl,
                            $youtubeMatches,
                        )
                    ) {
                        $allMediaItems[] = [
                            'type' => 'youtube_video',
                            'src' => 'https://www.youtube.com/embed/' . $youtubeMatches[3],
                            'thumbnail' => 'https://img.youtube.com/vi/' . $youtubeMatches[3] . '/mqdefault.jpg',
                        ];
                    } else {
                        // افترض أنه رابط فيديو مباشر
                        $allMediaItems[] = [
                            'type' => 'video',
                            'src' => $videoUrl,
                            'thumbnail' => asset('frontend/assets/video_placeholder.png'),
                        ];
                    }
                } elseif (Storage::disk('public')->exists($videoUrl)) {
                    $allMediaItems[] = [
                        'type' => 'video',
                        'src' => Storage::url($videoUrl),
                        'thumbnail' => asset('frontend/assets/video_placeholder.png'),
                    ];
                }
            }

            // 2. معالجة الصور وإضافتها إلى القائمة
            $images = [];
            if (!empty($property->images)) {
                $images = is_array($property->images) ? $property->images : json_decode($property->images, true) ?? [];
            }

            foreach ($images as $imagePath) {
                $cleanPath = trim($imagePath, '"\'/\\');
                $imageUrl = null;
                if (filter_var($cleanPath, FILTER_VALIDATE_URL)) {
                    $imageUrl = $cleanPath;
                } elseif (strpos($cleanPath, 'frontend/assets/') === 0 || strpos($cleanPath, 'assets/') === 0) {
                    $imageUrl = asset($cleanPath);
                } elseif (Storage::disk('public')->exists($cleanPath)) {
                    $imageUrl = Storage::url($cleanPath);
                }
                // يمكنك إضافة مسار بديل مثل 'properties/' . $property->id . '/' . $cleanPath إذا كنت تستخدمه
                // elseif (Storage::disk('public')->exists('properties/' . $property->id . '/' . $cleanPath)) {
                //     $imageUrl = Storage::url('properties/' . $property->id . '/' . $cleanPath);
                // }

                if ($imageUrl) {
                    $allMediaItems[] = [
                        'type' => 'image',
                        'src' => $imageUrl,
                        'thumbnail' => $imageUrl, // الصورة المصغرة هي نفسها الصورة
                    ];
                }
            }

            // 3. تحديد العنصر الرئيسي للعرض (الأولوية للصورة)
            if (!empty($allMediaItems)) {
                // ابحث عن أول صورة لعرضها
                foreach ($allMediaItems as $item) {
                    if ($item['type'] === 'image') {
                        $mainDisplayItem = $item;
                        break;
                    }
                }
                // إذا لم يتم العثور على صور، استخدم أول عنصر متاح (قد يكون فيديو)
                if (!$mainDisplayItem && !empty($allMediaItems)) {
                    $mainDisplayItem = $allMediaItems[0];
                }
            }

            // إذا لم يوجد أي وسائط على الإطلاق، استخدم الصورة الافتراضية
            if (!$mainDisplayItem) {
                $mainDisplayItem = ['type' => 'image', 'src' => $defaultImage, 'thumbnail' => $defaultImage];
                // لا تضف الصورة الافتراضية إلى $allMediaItems إلا إذا كانت فارغة تمامًا
                if (empty($allMediaItems)) {
                    $allMediaItems[] = $mainDisplayItem;
                }
            }
            // --- نهاية كود PHP لمعالجة الوسائط ---
        @endphp

        <div class="row gx-lg-4">
            {{-- ======== عمود عرض الوسائط (الصور والفيديو) ======== --}}
            <div class="col-lg-8 mb-4 mb-lg-0">
                <div class="row g-2">
                    <!-- العمود الخاص بالصور المصغرة (على اليسار للشاشات الكبيرة) -->
                    <div class="col-md-2 order-md-1">
                        <div class="thumbnails-column d-flex flex-md-column align-items-stretch">
                            @forelse ($allMediaItems as $index => $item)
                                <img src="{{ $item['thumbnail'] }}"
                                    class="thumbnail-item mb-2 rounded @if ($item['src'] === $mainDisplayItem['src']) active-thumbnail @endif"
                                    onclick="switchMainMedia('{{ $item['src'] }}', '{{ $item['type'] }}', this)"
                                    alt="Thumbnail {{ $index + 1 }}">
                            @empty
                                @if ($mainDisplayItem && $mainDisplayItem['src'] === $defaultImage)
                                    {{-- فقط إذا كانت الصورة الرئيسية هي الافتراضية --}}
                                    <img src="{{ $defaultImage }}" class="thumbnail-item mb-2 rounded active-thumbnail"
                                        alt="Default Thumbnail">
                                @else
                                    <p class="text-muted small w-100 text-center py-2">No thumbnails.</p>
                                @endif
                            @endforelse
                        </div>
                    </div>

                    <!-- العمود الخاص بالفيديو/الصورة الرئيسية (على اليمين للشاشات الكبيرة) -->
                    <div class="col-md-10 order-md-2">
                        <div id="mainMediaDisplay" class="main-media-container">
                            @if ($mainDisplayItem['type'] === 'video')
                                <video id="mainContentPlayer" src="{{ $mainDisplayItem['src'] }}" controls autoplay
                                    muted></video>
                            @elseif ($mainDisplayItem['type'] === 'youtube_video')
                                <div class="embed-responsive embed-responsive-16by9" style="aspect-ratio: 16/9;">
                                    <iframe id="mainContentPlayer" class="embed-responsive-item"
                                        src="{{ $mainDisplayItem['src'] }}?autoplay=1&mute=1&modestbranding=1&rel=0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen title="Property Video"></iframe>
                                </div>
                            @else
                                <img id="mainContentPlayer" src="{{ $mainDisplayItem['src'] }}" alt="{{ $property->title }}"
                                    class="img-fluid main-media-image">
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ======== عمود تفاصيل العقار ======== --}}
            <div class="col-lg-4">
                <div class="card shadow-sm sticky-sidebar">
                    <div class="card-body">
                        <h1 class="h3 fw-bold text-primary mb-2">{{ $property->currency ?? 'USD' }}
                            {{ number_format($property->price, 0) }}</h1>
                        <h2 class="h5 mb-3 fw-semibold">{{ $property->title }}</h2>

                        <p class="text-muted mb-1 small"><i class="bi bi-tag-fill me-1"></i> For
                            {{ ucfirst($property->purpose) }}</p>
                        <p class="mb-3 small">
                            <i class="bi bi-geo-alt-fill me-1"></i>
                            {{ $property->address }}, {{ $property->listarea?->name ?? 'N/A' }}
                            @if ($property->listarea?->governorate)
                                , {{ $property->listarea->governorate->name }}
                            @endif
                        </p>

                        <div class="d-flex flex-wrap text-muted property-specs mb-3 small">
                            @if ($property->rooms)
                                <div class="me-3 mb-1"><i class="bi bi-door-closed me-1"></i> {{ $property->rooms }} Beds
                                </div>
                            @endif
                            @if ($property->bathrooms)
                                <div class="me-3 mb-1"><i class="bi bi-droplet me-1"></i> {{ $property->bathrooms }} Baths
                                </div>
                            @endif
                            @if ($property->area)
                                <div class="me-3 mb-1"><i class="bi bi-arrows-fullscreen me-1"></i>
                                    {{ number_format($property->area) }} sqm</div>
                            @endif
                            @if ($property->floors)
                                <div class="me-3 mb-1"><i class="bi bi-layers me-1"></i> {{ $property->floors }} Floors
                                </div>
                            @endif
                        </div>
                        {{-- هذا هو الكود الجديد والمحسن --}}
                        @if ($reviewsCount > 0)
                            {{-- هذا الجزء يبقى كما هو، ويعمل عندما يكون هناك تقييمات --}}
                            <div class="d-flex align-items-center my-3">
                                <strong
                                    class="me-2">{{ rtrim(rtrim(number_format($avgRating, 1), '0'), '.') }}/5</strong>
                                <div class="rating-stars me-2">
                                    @for ($i = 1; $i <= 5; $i++)
                                        @if ($i <= round($avgRating))
                                        <i class="fas fa-star text-warning"></i>@else<i
                                                class="far fa-star text-warning"></i>
                                        @endif
                                    @endfor
                                </div>
                                <span class="text-muted small">({{ $reviewsCount }}
                                    {{ Str::plural('review', $reviewsCount) }})</span>
                            </div>
                        @else
                            {{-- ▼▼▼ هذا هو الجزء الجديد الذي سيظهر عندما لا تكون هناك تقييمات ▼▼▼ --}}
                            <div class="text-muted small my-3 text-center border-top pt-3">
                                <i class="bi bi-star me-1"></i>
                                No reviews yet.
                            </div>
                        @endif
                        @auth
                            <button
                                class="btn {{ $property->is_favorited ? 'btn-gold' : 'btn-outline-gold' }} w-100 mb-2 favorite-toggle-button"
                                data-property-id="{{ $property->id }}">
                                <i class="bi {{ $property->is_favorited ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                                <span
                                    class="button-text">{{ $property->is_favorited ? 'Remove from Favourites' : 'Add to Favourites' }}</span>
                            </button>
                        @else
                            <a href="{{ route('login', ['redirect' => url()->current()]) }}"
                                class="btn btn-outline-gold w-100 mb-2"><i class="bi bi-heart"></i> Add to Favourites</a>
                        @endauth

                        @if (Auth::check() && Auth::id() !== $property->user_id)
                            <a href="{{ route('chat.initiate', ['property_id' => $property->id]) }}"
                                class="btn btn-gold w-100"><i class="bi bi-chat-dots me-1"></i> Chat with Lister</a>
                        @elseif(!Auth::check())
                            <a href="{{ route('login', ['redirect' => url()->current()]) }}"
                                class="btn btn-success w-100"><i class="bi bi-chat-dots me-1"></i> Chat with Lister</a>
                        @endif
                        <div class="mt-3 text-center">
                            <small class="text-muted d-block mb-2">Share this property</small>
                            <div class="d-flex justify-content-center">
                                <!-- Facebook Share -->
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                                    target="_blank" class="btn btn-outline-secondary btn-sm me-2"
                                    aria-label="Share on Facebook" title="Share on Facebook">
                                    <i class="bi bi-facebook"></i>
                                </a>

                                <!-- Twitter Share -->
                                <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($property->title) }}"
                                    target="_blank" class="btn btn-outline-secondary btn-sm me-2"
                                    aria-label="Share on Twitter" title="Share on Twitter">
                                    <i class="bi bi-twitter"></i>
                                </a>

                                <!-- WhatsApp Share -->
                                <a href="https://api.whatsapp.com/send?text={{ urlencode($property->title . ' - ' . url()->current()) }}"
                                    target="_blank" class="btn btn-outline-secondary btn-sm me-2"
                                    aria-label="Share on WhatsApp" title="Share on WhatsApp">
                                    <i class="bi bi-whatsapp"></i>
                                </a>

                                <!-- Copy Link Button -->
                                <button id="copyLinkBtn" class="btn btn-outline-secondary btn-sm" aria-label="Copy link"
                                    title="Copy property link">
                                    <i class="bi bi-link-45deg"></i>
                                </button>
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="text-muted small">
                            <p class="mb-1"><strong>Listed by:</strong> {{ $property->user?->name ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Posted:</strong> {{ $property->created_at->diffForHumans() }}</p>
                            <p class="mb-0"><strong>Views:</strong> {{ $property->views_count ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ======== قسم الوصف والتفاصيل الإضافية والخريطة (تحت الوسائط) ======== --}}
        <div class="row mt-4">
            <div class="col-lg-8">
                @if ($property->description)
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light py-3">
                            <h5 class="mb-0 fw-semibold">Description</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-break lh-lg">{!! nl2br(e($property->description)) !!}</p>
                        </div>
                    </div>
                @endif

                {{-- تفاصيل خاصة بنوع العقار --}}
                @if ($property->category_id == 4 && $property->subCategory && $property->land_type)
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light py-3">
                            <h5 class="mb-0 fw-semibold">Land Details</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Land Type:</strong> {{ $property->land_type }}</p>
                        </div>
                    </div>
                @elseif ($property->category->name == 'Tent' && $property->subCategory && $property->tent_type)
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light py-3">
                            <h5 class="mb-0 fw-semibold">Tent Details</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Tent Type:</strong> {{ $property->tent_type }}</p>
                        </div>
                    </div>
                @elseif ($property->category->name == 'Caravan' && $property->subCategory && $property->caravan_type)
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light py-3">
                            <h5 class="mb-0 fw-semibold">Caravan Details</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Caravan Type:</strong> {{ $property->caravan_type }}</p>
                        </div>
                    </div>
                @endif

                {{-- الخريطة --}}
                @if ($property->latitude && $property->longitude)
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light py-3">
                            <h5 class="mb-0 fw-semibold">Location on Map</h5>
                        </div>
                        <div class="card-body p-0">
                            <div id="propertyMap" style="height: 350px; width: 100%;"></div>
                        </div>
                    </div>
                @elseif ($property->location)
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light py-3">
                            <h5 class="mb-0 fw-semibold">Location on Map</h5>
                        </div>
                        <div class="card-body p-0">
                            <iframe
                                src="https://maps.google.com/maps?q={{ urlencode($property->location) }}&t=&z=15&ie=UTF8&iwloc=&output=embed"
                                width="100%" height="350" style="border:0;" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                @endif
                <div class="card shadow-sm mt-4" id="reviews-section">
                    <div class="card-header bg-light py-3">
                        <h5 class="mb-0 fw-semibold">Ratings & Reviews</h5>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        @auth
                            @if (Auth::id() != $property->user_id && !$property->reviews->contains('user_id', Auth::id()))
                                <div class="p-3 mb-4">
                                    <h6 class="mb-3">Leave a Review</h6>
                                    <form action="{{ route('reviews.store', $property) }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="rating" class="form-label">Your Rating <span
                                                    class="text-danger">*</span></label>
                                            <div class="star-rating">
                                                <input type="radio" id="5-stars" name="rating" value="5"
                                                    required /><label for="5-stars" class="star">★</label>
                                                <input type="radio" id="4-stars" name="rating" value="4" /><label
                                                    for="4-stars" class="star">★</label>
                                                <input type="radio" id="3-stars" name="rating" value="3" /><label
                                                    for="3-stars" class="star">★</label>
                                                <input type="radio" id="2-stars" name="rating" value="2" /><label
                                                    for="2-stars" class="star">★</label>
                                                <input type="radio" id="1-star" name="rating" value="1" /><label
                                                    for="1-star" class="star">★</label>
                                            </div>
                                            @error('rating')
                                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="comment" class="form-label">Your Comment (optional)</label>
                                            <textarea class="form-control @error('comment') is-invalid @enderror" id="comment" name="comment" rows="3"
                                                placeholder="Share your experience...">{{ old('comment') }}</textarea>
                                            @error('comment')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <button type="submit" class="btn btn-warning">Submit Review</button>
                                    </form>
                                </div>
                            @endif
                        @else
                            <div class="alert alert-info">
                                <a href="{{ route('login') }}?redirect={{ url()->current() }}#reviews-section"
                                    class="alert-link">Log in</a> or <a href="{{ route('register') }}"
                                    class="alert-link">Sign up</a> to leave a review.
                            </div>
                        @endauth

                        @forelse ($property->reviews->sortByDesc('created_at') as $review)
                            <div class="review-item border-top pt-3 mt-3">
                                <div class="d-flex align-items-center mb-2">
                                    <img src="{{ $review->user->profile_image_url }}" alt="{{ $review->user->name }}"
                                        class="rounded-circle me-3 object-fit-cover" width="50" height="50">
                                    <div>
                                        <h6 class="mb-0">{{ $review->user->name }}</h6>
                                        <div class="rating-stars small">
                                            @for ($i = 1; $i <= 5; $i++)
                                                @if ($i <= $review->rating)
                                                <i class="fas fa-star text-warning"></i>@else<i
                                                        class="far fa-star text-warning"></i>
                                                @endif
                                            @endfor
                                        </div>
                                    </div>
                                    <small class="text-muted ms-auto">{{ $review->created_at->diffForHumans() }}</small>
                                </div>
                                @if ($review->comment)
                                    <p class="mb-0 text-muted">{{ nl2br(e($review->comment)) }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-center text-muted mt-3">This property has no reviews yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            {{-- يمكنك إضافة عمود جانبي هنا إذا أردت بجانب الوصف --}}
        </div>

        {{-- قسم العقارات المشابهة --}}
        @if ($similarProperties && $similarProperties->count() > 0)
            <div class="container py-4 px-0">
                <hr class="my-4">
                <h3 class="mb-4 fw-semibold">Similar Properties</h3>
                <div class="row g-3">
                    @foreach ($similarProperties as $simProperty)
                        <div class="col-md-6 col-lg-3 d-flex">
                            <div class="card property-card h-100 shadow-sm w-100">
                                @php
                                    $cardImages = $simProperty->images;
                                    if (is_string($simProperty->images)) {
                                        $cardImages = json_decode($simProperty->images, true) ?? [];
                                    }
                                    $cardFirstImage = $cardImages[0] ?? null;
                                    $cardImageUrl = asset('frontend/assets/home.jpg');

                                    if ($cardFirstImage) {
                                        $cleanCardPath = trim($cardFirstImage, '"\'/\\');
                                         if (filter_var($cleanCardPath, FILTER_VALIDATE_URL)) {
                                             $cardImageUrl = $cleanCardPath;
                                         } elseif (strpos($cleanCardPath, 'frontend/assets/') === 0 || strpos($cleanCardPath, 'assets/') === 0) {
                                             $cardImageUrl = asset($cleanCardPath);
                                         } elseif (Storage::disk('public')->exists($cleanCardPath)) {
                                             $cardImageUrl = Storage::url($cleanCardPath);
                                         }
                                    }
                                @endphp
                                <a href="{{ route('frontend.property.show', $simProperty->id) }}"
                                    class="text-decoration-none">
                                    <img src="{{ $cardImageUrl }}" class="property-image card-img-top"
                                        alt="{{ Str::limit($simProperty->title, 40) }}">
                                </a>

                                @auth
                                    <div class="favorite-icon" data-property-id="{{ $simProperty->id }}">
                                        <i
                                            class="bi {{ $simProperty->is_favorited ?? false ? 'bi-heart-fill text-danger' : 'bi-heart' }}"></i>
                                    </div>
                                @endauth

                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title fw-bold text-primary mb-1">
                                        {{ $simProperty->currency ?? 'USD' }}
                                        {{ number_format($simProperty->price, 0) }}
                                    </h5>
                                    <p class="fw-semibold mb-2 property-title-clamp" title="{{ $simProperty->title }}">
                                        <a href="{{ route('frontend.property.show', $simProperty->id) }}"
                                            class="text-dark text-decoration-none stretched-link">
                                            {{ Str::limit($simProperty->title, 45) }}
                                        </a>
                                    </p>
                                    <div class="d-flex text-muted property-info mb-2 small">
                                        @if ($simProperty->rooms)
                                            <div class="me-3"><i class="bi bi-door-closed"></i>
                                                {{ $simProperty->rooms }} bed</div>
                                        @endif
                                        @if ($simProperty->bathrooms)
                                            <div class="me-3"><i class="bi bi-droplet"></i>
                                                {{ $simProperty->bathrooms }} bath</div>
                                        @endif
                                        @if ($simProperty->area)
                                            <div class="me-3"><i
                                                    class="bi bi-arrows-fullscreen"></i><strong>{{ number_format($simProperty->area) }}</strong>
                                                sqm</div>
                                        @endif
                                    </div>
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
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        function switchMainMedia(src, type, clickedThumbnail) {
            const mainMediaContainer = document.getElementById('mainMediaDisplay');
            const currentPlayer = document.getElementById('mainContentPlayer');

            if (currentPlayer && currentPlayer.tagName === 'VIDEO') {
                currentPlayer.pause();
            }

            let newMediaElementHTML = '';
            if (type === 'youtube_video') {
                newMediaElementHTML = `<div class="embed-responsive embed-responsive-16by9" style="aspect-ratio: 16/9;">
                                          <iframe id="mainContentPlayer" class="embed-responsive-item" src="${src}?autoplay=1&mute=1&modestbranding=1&rel=0"
                                                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                  allowfullscreen title="Property Video" style="width:100%; height:100%;"></iframe>
                                       </div>`;
            } else if (type === 'video') {
                newMediaElementHTML = `<video id="mainContentPlayer" src="${src}" controls autoplay muted></video>`;
            } else { // image
                newMediaElementHTML = `<img id="mainContentPlayer" src="${src}" alt="Main property media" class="img-fluid main-media-image">`;
            }
            mainMediaContainer.innerHTML = newMediaElementHTML;

            document.querySelectorAll('.thumbnail-item').forEach(thumb => {
                thumb.classList.remove('active-thumbnail');
            });
            if (clickedThumbnail) {
                clickedThumbnail.classList.add('active-thumbnail');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // كود المفضلة
            const favoriteButtons = document.querySelectorAll('.favorite-toggle-button');
            favoriteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const propertyId = this.dataset.propertyId;
                    const icon = this.querySelector('i');
                    const buttonTextSpan = this.querySelector(
                        '.button-text'); // استهداف النص داخل الزر

                    fetch('{{ route('favorites.toggle') }}', {
                            method: 'POST', // حدد نوع الطلب
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').getAttribute(
                                    'content') // هذا هو السطر المهم
                            },
                            body: JSON.stringify({ // أرسل البيانات في الـ body
                                property_id: propertyId
                            })
                        })

                        .then(response => response.json())
                        .then(data => {
                            if (data.success) { // تم تعديل هذا ليتناسب مع الاستجابة المتوقعة
                                if (data.status === 'favorited') {
                                    icon.classList.remove('bi-heart');
                                    icon.classList.add('bi-heart-fill');
                                    button.classList.remove('btn-outline-gold'); // استخدم الكلاس الصحيح
                                    button.classList.add('btn-gold');
                                    if (buttonTextSpan) buttonTextSpan.textContent =
                                        'Remove from Favourites';
                                } else { // unfavorited
                                    icon.classList.remove('bi-heart-fill');
                                    icon.classList.add('bi-heart');
                                    button.classList.remove('btn-gold');           // استخدم الكلاس الصحيح
                                    button.classList.add('btn-outline-gold');
                                    if (buttonTextSpan) buttonTextSpan.textContent =
                                        'Add to Favourites';
                                }
                                // يمكنك إضافة رسالة SweetAlert هنا إذا أردت
                                Swal.fire({
                                    icon: 'success',
                                    title: data.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: data.message || 'Something went wrong!'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Fetch error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Could not update favorites. Please try again.'
                            });
                        });
                });
            });
            const copyLinkBtn = document.getElementById('copyLinkBtn');
            if (copyLinkBtn) {
                const originalIcon = copyLinkBtn.innerHTML; // نحفظ الأيقونة الأصلية

                copyLinkBtn.addEventListener('click', function() {
                    const propertyUrl = window.location.href;

                    navigator.clipboard.writeText(propertyUrl).then(() => {
                        // تم النسخ بنجاح
                        copyLinkBtn.innerHTML =
                            '<i class="bi bi-check-lg"></i>'; // تغيير الأيقونة إلى علامة صح
                        copyLinkBtn.classList.add('btn-success');
                        copyLinkBtn.classList.remove('btn-outline-secondary');

                        // إعادة الزر إلى حالته الأصلية بعد ثانيتين
                        setTimeout(() => {
                            copyLinkBtn.innerHTML = originalIcon;
                            copyLinkBtn.classList.remove('btn-success');
                            copyLinkBtn.classList.add('btn-outline-secondary');
                        }, 2000);

                    }).catch(err => {
                        // حدث خطأ أثناء النسخ
                        console.error('Failed to copy link: ', err);
                        alert('Could not copy link to clipboard.');
                    });
                });
            }
            // كود الخريطة (Leaflet)
            @if ($property->latitude && $property->longitude)
                var map = L.map('propertyMap').setView([{{ $property->latitude }}, {{ $property->longitude }}],
                    15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);
                L.marker([{{ $property->latitude }}, {{ $property->longitude }}]).addTo(map)
                    .bindPopup('{{ e($property->title) }}<br>{{ e($property->address) }}').openPopup();
            @endif
        });
    </script>
@endpush
