@extends('frontend.Layouts.frontend') 

@section('title', 'Properties Listing - EasyFind')
@section('description', 'Browse all available properties for sale and rent in Gaza. Filter by location, type, price, and more.')

@push('styles')
<style>
    .filter-section {
      background-color: #ffffff;
      padding: 20px;
      border-radius: 10px;
      border: 1px solid #dee2e6; 
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      position: sticky; 
      top: 100px; 
      height: calc(100vh - 120px); 
      overflow-y: auto; 
    }
    .filter-section h5 {
        border-bottom: 1px solid #eee;
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
    }
    .form-label {
        font-weight: 500;
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
    }
    .form-select, .form-control {
        font-size: 0.9rem;
    }
     #propertiesList .property-card { margin-bottom: 1.5rem; }
     .pagination { justify-content: center; margin-top: 2rem; } 
</style>
@endpush

@section('content')
<div class="container-fluid mt-4">
  <div class="row gx-4"> 
    <div class="col-lg-3 col-md-4"> 
      <div class="filter-section">
        <h5 class="mb-3">Filters <i class="bi bi-funnel ms-1"></i></h5>
        <form method="GET" action="{{ route('frontend.properties') }}" id="filter-form">

             <div class="mb-3">
                <label for="searchFilter" class="form-label">Keyword Search</label>
                <input type="search" name="search" id="searchFilter" class="form-control form-control-sm" placeholder="Title, address..." value="{{ request('search') }}">
             </div>

       
             <div class="mb-3">
                <label for="purposeFilter" class="form-label">Purpose</label>
                <select class="form-select form-select-sm" name="purpose" id="purposeFilter">
                    <option value="">All Purposes</option>
                    <option value="sale" {{ request('purpose') == 'sale' ? 'selected' : '' }}>For Sale</option>
                    <option value="rent" {{ request('purpose') == 'rent' ? 'selected' : '' }}>For Rent</option>
                    <option value="lease" {{ request('purpose') == 'lease' ? 'selected' : '' }}>For Lease</option>
                </select>
             </div>

            <div class="mb-3">
              <label for="categoryFilter" class="form-label">Property Type</label>
              <select class="form-select form-select-sm" name="category_id" id="categoryFilter">
                <option value="">All Types</option>
                @isset($categories)
                    @foreach ($categories as $category)
                         <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                @endisset
              </select>
            </div>

            <div class="mb-3">
              <label for="governorateFilter" class="form-label">Governorate</label>
              <select class="form-select form-select-sm" name="governorate_id" id="governorateFilter">
                <option value="">All Governorates</option>
                 @isset($governorates)
                    @foreach ($governorates as $governorate)
                        <option value="{{ $governorate->id }}"
                                {{ request('governorate_id') == $governorate->id ? 'selected' : '' }}
                                data-areas="{{ json_encode($governorate->areas->pluck('name', 'id')) }}">
                            {{ $governorate->name }}
                        </option>
                    @endforeach
                @endisset
              </select>
            </div>
             <div class="mb-3">
              <label for="areaFilter" class="form-label">Area</label>
              <select class="form-select form-select-sm" name="area_id" id="areaFilter" {{ !request('governorate_id') ? 'disabled' : '' }}>
                <option value="">All Areas</option>
           
                 @if(request('governorate_id') && isset($governorates))
                    @php
                        $selectedGov = $governorates->firstWhere('id', request('governorate_id'));
                    @endphp
                    @if($selectedGov)
                        @foreach ($selectedGov->areas as $area)
                            <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                        @endforeach
                    @endif
                 @endif
              </select>
            </div>

            {{-- فلتر السعر --}}
            <div class="mb-3">
                <label class="form-label">Price Range ({{-- Add Currency Symbol --}})</label>
                <div class="input-group input-group-sm mb-1">
                    <span class="input-group-text">Min</span>
                    <input type="number" name="min_price" class="form-control" min="0" placeholder="e.g., 50000" value="{{ request('min_price') }}" aria-label="Minimum Price">
                </div>
                 <div class="input-group input-group-sm">
                     <span class="input-group-text">Max</span>
                    <input type="number" name="max_price" class="form-control" min="0" placeholder="e.g., 500000" value="{{ request('max_price') }}" aria-label="Maximum Price">
                </div>
            </div>

            {{-- فلاتر أخرى (الغرف، الحمامات) --}}
            <div class="mb-3">
              <label for="roomsFilter" class="form-label">Min. Rooms</label>
              <input type="number" name="min_rooms" class="form-control form-control-sm" id="roomsFilter" min="0" value="{{ request('min_rooms', 0) }}">
            </div>
            <div class="mb-3">
              <label for="bathroomsFilter" class="form-label">Min. Bathrooms</label>
              <input type="number" name="min_bathrooms" class="form-control form-control-sm" id="bathroomsFilter" min="0" value="{{ request('min_bathrooms', 0) }}">
            </div>

       
       <button type="submit" class="btn btn-warning w-100 mb-2 fw-bold">Apply Filters</button>
       <a href="{{ route('frontend.properties') }}" class="btn btn-light border w-100" style="background-color: #ebeaea; border-color: #a9a9a9;">Reset Filters</a>
      </form>
      </div>
    </div>

    <div class="col-lg-9 col-md-8">
       
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
             <h5 class="mb-0">Showing {{ $properties->firstItem() }}-{{ $properties->lastItem() }} of {{ $properties->total() }} Properties</h5>
            
        </div>

      <div class="row property-list-row" id="propertiesList">
            @forelse ($properties as $property)
          
            <div class="col-12 col-sm-6 col-md-4 col-lg-4"> {{-- يمكنك تعديل col-lg-4 إلى col-lg-3 إذا أردت 4 كروت في الصف --}}
                <div class="card property-card h-100"> {{-- أضفت h-100 لجعل الكروت بنفس الارتفاع --}}
                    @php
                        $images = json_decode($property->images, true);
                        $firstImage = $images[0] ?? null;
                        // استخدم صورة افتراضية أكثر عمومية إذا لم تتوفر صورة
                        $imageUrl = asset('frontend/assets/home.jpg'); // أو placeholder-property.jpg
                        if ($firstImage && Storage::disk('public')->exists($firstImage)) {
                            $imageUrl = Storage::url($firstImage);
                        }
                    @endphp
                    <a href="{{ route('frontend.property.show', $property->id) }}" title="{{ $property->title }}">
                        <img src="{{ $imageUrl }}" class="property-image card-img-top"
                            alt="{{ Str::limit($property->title, 40) }}">
                    </a>

                    {{-- أيقونة القلب --}}
                    <div class="favorite-icon" data-property-id="{{ $property->id }}">
                        <i class="bi {{ $property->is_favorited ?? false ? 'bi-heart-fill is-favorite' : 'bi-heart' }}"></i>
                    </div>

                    <div class="card-body d-flex flex-column"> {{-- d-flex flex-column لمحاذاة العنوان للأسفل --}}
                        <h5 class="card-title fw-bold text-primary mb-1">{{ $property->currency }}
                            {{ number_format($property->price, 0) }}
                            @if($property->purpose == 'rent' && $property->rent_period)
                                <span class="text-muted small">/ {{ $property->rent_period }}</span>
                            @endif
                        </h5>
                        <p class="fw-semibold mb-2 property-title-clamp" title="{{ $property->title }}">
                            <a href="{{ route('frontend.property.show', $property->id) }}"
                                class="text-dark text-decoration-none">
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
                            @if ($property->area)
                            <div class="me-3"><i class="bi bi-arrows-fullscreen"></i><strong>
                                    {{ number_format($property->area) }}</strong> sqm
                            </div>
                            @endif
                        </div>
                        <div class="property-address text-muted small mt-auto"> {{-- mt-auto لدفع العنوان للأسفل --}}
                            <i class="bi bi-geo-alt-fill me-1"></i>
                            {{ Str::limit($property->address ?? 'Address not available', 25) }}, {{ $property->listarea?->name ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 d-flex justify-content-center align-items-center" style="min-height: 400px;"> 
              <div class="alert alert-light text-center p-4 p-md-5 border rounded-3 shadow-sm" role="alert" style="max-width: 500px;"> 
                   <i class="bi bi-search fs-1 d-block mb-3 text-muted"></i>
                   <p class="mb-0 lead text-secondary">
                       No properties found matching your criteria.<br> Try adjusting your filters!
                   </p>
              </div>
          </div>
            @endforelse
      </div>

       {{-- روابط الترقيم --}}
       <div class="mt-4 d-flex justify-content-center">
            {{ $properties->links() }} {{-- تأكد من أن لديك Bootstrap 5 pagination views --}}
       </div>

    </div>

  </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const governorateSelect = document.getElementById('governorateFilter');
    const areaSelect = document.getElementById('areaFilter');

    function updateAreaOptions() {
        const selectedGovOption = governorateSelect.options[governorateSelect.selectedIndex];
        const requestSelectedArea = "{{ request('area_id', '') }}";

        areaSelect.innerHTML = '<option value="">All Areas</option>';

        if (selectedGovOption && selectedGovOption.value) {
            areaSelect.disabled = false;
            let areas = {};
            try {
                if (selectedGovOption.dataset.areas) {
                    areas = JSON.parse(selectedGovOption.dataset.areas);
                }
            } catch (e) {
                console.error("Error parsing areas data:", e);
                return;
            }

            for (const areaId in areas) {
                if (areas.hasOwnProperty(areaId)) {
                    const option = new Option(areas[areaId], areaId);
                    if (areaId === requestSelectedArea) { 
                        option.selected = true;
                    }
                    areaSelect.add(option);
                }
            }
        } else {
            areaSelect.disabled = true;
        }
    }

    if (governorateSelect && areaSelect) {
        governorateSelect.addEventListener('change', updateAreaOptions);
        if (governorateSelect.value) {
            updateAreaOptions();
        }
    }
});
</script>
@endpush