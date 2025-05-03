{{-- resources/views/frontend/favorites.blade.php --}}
@extends('frontend.Layouts.frontend') {{-- تأكد من المسار الصحيح للـ layout --}}

@section('title', 'My Favourites - EasyFind')
@section('description', 'View and manage your favorite properties on EasyFind.')

@push('styles')
{{-- يمكن نقل هذه الـ CSS إلى ملف style.css العام إذا أردت --}}
<style>
    .favorite-property-card {
      border: 1px solid #e9ecef;
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 1.5rem;
      background-color: #fff;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .favorite-property-card img {
      width: 100px;
      height: 80px;
      object-fit: cover;
      border-radius: 4px;
    }
    .price-new {
      color: var(--bs-primary); /* استخدام لون Bootstrap الأساسي */
      font-weight: bold;
      font-size: 1.1rem;
    }
    .price-old {
      text-decoration: line-through;
      color: #6c757d; /* Muted color */
      font-size: 0.9rem;
      margin-left: 0.5rem;
    }
    .remove-btn {
      background-color: #ffffff;
      border: 1px solid #dc3545; /* لون خطر للإزالة */
      color: #dc3545;
      border-radius: 20px;
      padding: 5px 15px;
      font-size: 0.85rem;
      font-weight: 500;
      transition: background-color 0.2s ease, color 0.2s ease;
    }
    .remove-btn:hover {
      background-color: #dc3545;
      color: white;
    }
    .remove-btn i {
        vertical-align: middle;
        margin-left: 3px;
    }

    /* إعادة استخدام تنسيقات property-card من style.css */
    .recommended-section .property-card {
         /* أي تنسيقات إضافية للكروت الموصى بها إذا لزم الأمر */
         margin-bottom: 1.5rem; /* إضافة مسافة سفلية */
    }
    .recommended-section .property-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    }

</style>
@endpush

@section('content')

{{-- قسم المفضلة --}}
<div class="container mt-5">
  <h4 class="mb-4">My Favourites</h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
     @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

  @forelse ($favoriteProperties as $property)
      <div class="favorite-property-card d-flex align-items-center">
          {{-- صورة وعنوان العقار --}}
          <a href="{{ route('frontend.property.show', $property->id) }}">
              @php
                  $images = json_decode($property->images, true);
                  $firstImage = !empty($images) ? $images[0] : null;
                  $imageUrl = asset('frontend/assets/placeholder-property.jpg');
                  if ($firstImage && Storage::disk('public')->exists($firstImage)) {
                      $imageUrl = Storage::url($firstImage);
                  }
              @endphp
              <img src="{{ $imageUrl }}" class="me-3" alt="{{ $property->title }}">
          </a>

          {{-- تفاصيل العقار --}}
          <div class="flex-grow-1">
            <div class="text-muted small">{{ ucfirst($property->purpose) }}</div>
            <div class="price-new">{{ $property->currency }} {{ number_format($property->price) }}
                {{-- يمكنك إضافة السعر القديم إذا كان لديك حقل له --}}
                {{-- <span class="price-old">$650,000</span> --}}
            </div>
            <div class="small text-muted">
                <i class="bi bi-geo-alt-fill me-1"></i>
                {{ $property->area?->name ?? 'N/A' }}, {{ $property->area?->governorate?->name ?? 'N/A' }}
            </div>
             <a href="{{ route('frontend.property.show', $property->id) }}" class="text-dark fw-semibold text-decoration-none d-block mt-1">{{ Str::limit($property->title, 50) }}</a>
          </div>

          {{-- زر الإزالة (داخل فورم) --}}
          {{-- سنحتاج لتحديد مسار الإزالة لاحقاً وليكن favorites.remove --}}
          <form method="POST" action="{{-- route('favorites.remove', $property->id) --}}" class="ms-3 remove-favorite-form">
              @csrf
              @method('DELETE')
              <button type="submit" class="remove-btn">Remove <i class="bi bi-trash"></i></button>
          </form>
      </div>
  @empty
      {{-- رسالة عند عدم وجود مفضلة --}}
      <div class="alert alert-secondary text-center p-4">
          <i class="bi bi-heart fs-3 d-block mb-2"></i>
          You haven't added any properties to your favorites yet.
      </div>
  @endforelse

    {{-- روابط الترقيم للمفضلة --}}
    <div class="mt-4 d-flex justify-content-center">
        {{ $favoriteProperties->links() }}
    </div>

</div>

{{-- قسم العقارات الموصى بها --}}
<div class="container mt-5 pt-5 border-top recommended-section">
  <h4 class="mb-4">Recommended For You</h4>
  <div class="row g-4 property-list-row">
    @forelse ($recommendedProperties as $property)
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            {{-- استخدام نفس كرت العقار --}}
            <div class="col-12 col-sm-6 col-md-4 col-lg-3"> {{-- تم إزالة d-flex align-items-stretch --}}
              <div class="card property-card"> {{-- تم إزالة w-100 --}}
                  @php
                      $images = json_decode($property->images, true);
                      $firstImage = $images[0] ?? null;
                      $imageUrl = asset('frontend/assets/placeholder-property.jpg'); // مسار الصورة الافتراضية
                      if ($firstImage && Storage::disk('public')->exists($firstImage)) {
                          $imageUrl = Storage::url($firstImage);
                      }
                  @endphp
                   <a href="{{ route('frontend.property.show', $property->id) }}" title="{{ $property->title }}"> {{-- تفعيل الرابط لاحقاً --}}
                      <img src="{{ $imageUrl }}" class="property-image card-img-top" alt="{{ Str::limit($property->title, 40) }}">
                   </a>

                  <div class="favorite-icon" data-property-id="{{ $property->id }}"> {{-- إضافة data attribute للمفضلة --}}
                      {{-- يجب جعل حالة القلب ديناميكية بناءً على مفضلة المستخدم --}}
                      <i class="bi bi-heart"></i>
                  </div>

                  <div class="card-body">
                      <h5 class="card-title fw-bold text-primary mb-1">{{ $property->currency }} {{ number_format($property->price, 0) }}</h5>
                       <p class="fw-semibold mb-2 property-title-clamp" title="{{ $property->title }}">
                           <a href="{{ route('frontend.property.show', $property->id) }}" class="text-dark text-decoration-none"> {{-- تفعيل الرابط لاحقاً --}}
                               {{ Str::limit($property->title, 50) }}
                           </a>
                       </p>
                      <div class="d-flex text-muted property-info mb-2 small">
                          @if($property->rooms) <div class="me-3"><i class="bi bi-door-closed"></i> {{ $property->rooms }} bed</div> @endif
                          @if($property->bathrooms) <div class="me-3"><i class="bi bi-droplet"></i> {{ $property->bathrooms }} bath</div> @endif
                          <div class="me-3"><i class="bi bi-arrows-fullscreen"></i><strong> {{ number_format($property->area) }}</strong> sqm</div>
                      </div>
                      <div class="property-address text-muted small mt-auto">
                          <i class="bi bi-geo-alt-fill me-1"></i>
                          {{ Str::limit($property->address, 25) }}, {{ $property->area?->name ?? 'N/A' }}
                      </div>
                  </div>
              </div>
          </div>
        </div>
    @empty
         <div class="col-12">
            <p class="text-center text-muted">No recommendations available at the moment.</p>
        </div>
    @endforelse
  </div>
</div>

@endsection

@push('scripts')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script> {{-- إضافة SweetAlert --}}
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const removeForms = document.querySelectorAll(".remove-favorite-form");

    removeForms.forEach(form => {
      form.addEventListener("submit", function (event) {
        event.preventDefault(); // منع الإرسال الافتراضي للفورم

        Swal.fire({
            title: 'Are you sure?',
            text: "This property will be removed from your favorites.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, remove it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // !! هنا يجب إرسال طلب AJAX لحذف المفضلة من قاعدة البيانات !!
                // ثم عند النجاح، قم بإخفاء أو إزالة الكرت من الصفحة
                console.log("Form submitted (AJAX call needed here)");

                // كمثال، إخفاء الكرت بصرياً بعد التأكيد (يجب ربطه بنجاح AJAX)
                 const card = form.closest(".favorite-property-card");
                 if (card) {
                    card.style.transition = "opacity 0.5s, transform 0.5s";
                    card.style.opacity = "0";
                    card.style.transform = "scale(0.9)";
                    setTimeout(() => { card.remove(); }, 500);
                 }
                 // يمكنك أيضاً إرسال الفورم نفسه إذا كان مسار الـ action صحيحاً
                 // form.submit();
            }
        });
      });
    });

    // إعادة استخدام كود تبديل أيقونة القلب (من index) إذا احتجت لعرضها في الكروت الموصى بها
    document.querySelectorAll('.favorite-icon').forEach(icon => {
        icon.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            const propertyId = this.dataset.propertyId;
            const heart = this.querySelector('i');
            // في صفحة المفضلة، الضغط على القلب يعني إزالة
            // لذا قد تحتاج لمنطق مختلف هنا أو تعطيل الضغط على القلب في هذه الصفحة
            // الكود الحالي سيجعل القلب فارغاً عند الضغط
            const isFavorite = heart.classList.toggle('bi-heart-fill');
            heart.classList.toggle('bi-heart');
            heart.classList.toggle('is-favorite', isFavorite);
            console.log(`Toggled favorite visually for property ${propertyId}. Is favorite: ${isFavorite}`);
            
        });
    });

  });
</script>
@endpush