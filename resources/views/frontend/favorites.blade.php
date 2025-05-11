@extends('frontend.Layouts.frontend') 

@section('title', 'My Favourites - EasyFind')
@section('description', 'View and manage your favorite properties on EasyFind.')

@push('styles')
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
      color: var(--bs-primary);
      font-weight: bold;
      font-size: 1.1rem;
    }
    .remove-btn {
      background-color: #ffffff;
      border: 1px solid #dc3545;
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
    .recommended-section .property-card:hover { 
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    }
    .recommended-section .property-card {
         margin-bottom: 1.5rem;
    }
</style>
@endpush

@section('content')

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
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif


  @forelse ($favoriteProperties as $property)
      <div class="favorite-property-card d-flex align-items-center">
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
          <div class="flex-grow-1">
            <div class="text-muted small">{{ ucfirst($property->purpose) }}</div>
            <div class="price-new">{{ $property->currency }} {{ number_format($property->price) }}</div>
            <div class="small text-muted">
                <i class="bi bi-geo-alt-fill me-1"></i>
                {{ $property->area?->name ?? 'N/A' }}, {{ $property->area?->governorate?->name ?? 'N/A' }}
            </div>
             <a href="{{ route('frontend.property.show', $property->id) }}" class="text-dark fw-semibold text-decoration-none d-block mt-1">{{ Str::limit($property->title, 50) }}</a>
          </div>
          <form method="POST" action="{{route('favorites.remove', $property->id)}}" class="ms-3 remove-favorite-form">
              @csrf
              @method('DELETE')
              <button type="submit" class="remove-btn">Remove <i class="bi bi-trash"></i></button>
          </form>
      </div>
  @empty
      <div class="alert alert-secondary text-center p-4">
          <i class="bi bi-heart fs-3 d-block mb-2"></i>
          You haven't added any properties to your favorites yet.
      </div>
  @endforelse

    <div class="mt-4 d-flex justify-content-center">
        @if ($favoriteProperties->hasPages()) 
            {{ $favoriteProperties->links() }}
        @endif
    </div>
</div>

<div class="container mt-5 pt-5 border-top recommended-section">
  <h4 class="mb-4">Recommended For You</h4>
  <div class="row g-4 property-list-row">
    @forelse ($recommendedProperties as $property)
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
                      <img src="{{ $imageUrl }}" class="property-image card-img-top" alt="{{ Str::limit($property->title, 40) }}">
                   </a>

                  <div class="favorite-icon" data-property-id="{{ $property->id }}">
                      <i class="bi {{ $property->is_favorited ?? false ? 'bi-heart-fill is-favorite' : 'bi-heart' }}"></i>
                  </div>

                  <div class="card-body">
                      <h5 class="card-title fw-bold text-primary mb-1">{{ $property->currency }} {{ number_format($property->price, 0) }}</h5>
                       <p class="fw-semibold mb-2 property-title-clamp" title="{{ $property->title }}">
                           <a href="{{ route('frontend.property.show', $property->id) }}" class="text-dark text-decoration-none">
                               {{ Str::limit($property->title, 50) }}
                           </a>
                       </p>
                      <div class="d-flex text-muted property-info mb-2 small">
                          @if($property->rooms) <div class="me-3"><i class="bi bi-door-closed"></i> {{ $property->rooms }} bed</div> @endif
                          @if($property->bathrooms) <div class="me-3"><i class="bi bi-droplet"></i> {{ $property->bathrooms }} bath</div> @endif
                          @if($property->area) <div class="me-3"><i class="bi bi-arrows-fullscreen"></i><strong> {{ number_format($property->area) }}</strong> sqm</div> @endif
                      </div>
                      <div class="property-address text-muted small mt-auto">
                          <i class="bi bi-geo-alt-fill me-1"></i>
                          {{ Str::limit($property->address ?? 'Address N/A', 25) }}, {{ $property->area?->name ?? 'N/A' }}
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

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const removeForms = document.querySelectorAll(".remove-favorite-form");

    removeForms.forEach(form => {
      form.addEventListener("submit", function (event) {
        event.preventDefault(); 
        const currentForm = this; 

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
                currentForm.submit();
            }
        });
      });
    });
  });
</script>


@endpush
