{{-- resources/views/frontend/properties.blade.php --}}
@extends('frontend.Layouts.frontend') {{-- تأكد من المسار الصحيح للـ layout --}}

@section('title', 'Properties Listing - EasyFind') {{-- عنوان وصفي للصفحة --}}
@section('description', 'Browse all available properties for sale and rent in Gaza. Filter by location, type, price, and more.')

{{-- يمكنك إضافة أي CSS خاص بهذه الصفحة هنا --}}
@push('styles')
<style>
    .filter-section {
      background-color: #ffffff;
      padding: 20px;
      border-radius: 10px;
      border: 1px solid #dee2e6; /* استخدام لون Bootstrap الافتراضي للحدود */
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      position: sticky; /* جعل الفلاتر ثابتة عند التمرير */
      top: 100px; /* تعديل المسافة من الأعلى لتناسب النافبار */
      height: calc(100vh - 120px); /* جعل ارتفاعها يأخذ باقي الشاشة مع ترك مسافة */
      overflow-y: auto; /* إضافة تمرير إذا كانت الفلاتر كثيرة */
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
     #propertiesList .property-card { margin-bottom: 1.5rem; } /* إضافة مسافة سفلية للكروت */
     .pagination { justify-content: center; margin-top: 2rem; } /* توسيط الترقيم */
</style>
@endpush

@section('content')
<div class="container-fluid mt-4"> {{-- استخدام container-fluid لعرض أوسع --}}
  <div class="row gx-4"> {{-- gx-4 لإضافة مسافة أفقية بين الأعمدة --}}

    {{-- ================== Filter Sidebar ================== --}}
    <div class="col-lg-3 col-md-4"> {{-- تعديل حجم الأعمدة للشاشات المختلفة --}}
      <div class="filter-section">
        <h5 class="mb-3">Filters <i class="bi bi-funnel ms-1"></i></h5>

        {{-- الفورم يرسل البيانات كـ GET لنفس المسار الحالي --}}
        <form method="GET" action="{{ route('frontend.properties') }}" id="filter-form">

            {{-- فلتر البحث (إذا لم يكن في النافبار) --}}
             <div class="mb-3">
                <label for="searchFilter" class="form-label">Keyword Search</label>
                <input type="search" name="search" id="searchFilter" class="form-control form-control-sm" placeholder="Title, address..." value="{{ request('search') }}">
             </div>

             {{-- فلتر الغرض --}}
             <div class="mb-3">
                <label for="purposeFilter" class="form-label">Purpose</label>
                <select class="form-select form-select-sm" name="purpose" id="purposeFilter">
                    <option value="">All Purposes</option>
                    <option value="sale" {{ request('purpose') == 'sale' ? 'selected' : '' }}>For Sale</option>
                    <option value="rent" {{ request('purpose') == 'rent' ? 'selected' : '' }}>For Rent</option>
                    <option value="lease" {{ request('purpose') == 'lease' ? 'selected' : '' }}>For Lease</option>
                </select>
             </div>

            {{-- فلتر نوع العقار (Category) --}}
            <div class="mb-3">
              <label for="categoryFilter" class="form-label">Property Type</label>
              <select class="form-select form-select-sm" name="category_id" id="categoryFilter">
                <option value="">All Types</option>
                @isset($categories)
                    @foreach ($categories as $category)
                         <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                         {{-- يمكنك إضافة التصنيفات الفرعية هنا إذا أردت --}}
                    @endforeach
                @endisset
              </select>
            </div>

            {{-- فلتر الموقع (Governorate & Area) --}}
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
                 {{-- سيتم ملؤه بواسطة JS --}}
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
    {{-- ================== End Filter Sidebar ================== --}}


    {{-- ================== Properties Section ================== --}}
    <div class="col-lg-9 col-md-8">
        {{-- عرض عدد النتائج (اختياري) --}}
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
             <h5 class="mb-0">Showing {{ $properties->firstItem() }}-{{ $properties->lastItem() }} of {{ $properties->total() }} Properties</h5>
             {{-- يمكنك إضافة قائمة منسدلة للترتيب هنا --}}
        </div>

      <div class="row property-list-row" id="propertiesList">
            @forelse ($properties as $property)
                {{-- استخدام نفس الـ component أو كود الكرت من الصفحة الرئيسية --}}
                 <div class="col-12 col-sm-6 col-lg-4"> {{-- تعديل حجم الأعمدة ليتسع لـ 3 في الصف --}}
                    {{-- @include('frontend.components.property-card', ['property' => $property]) --}}
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
     {{-- ================== End Properties Section ================== --}}

  </div>
</div>
@endsection

{{-- JavaScript الخاص بالقوائم المنسدلة المترابطة --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const governorateSelect = document.getElementById('governorateFilter');
    const areaSelect = document.getElementById('areaFilter');

    function updateAreaOptions() {
        const selectedGovOption = governorateSelect.options[governorateSelect.selectedIndex];
        const currentSelectedArea = "{{ request('area_id') }}"; // القيمة الحالية المختارة من الطلب
        areaSelect.innerHTML = '<option value="">All Areas</option>'; // إفراغ وإضافة الخيار الافتراضي

        if (selectedGovOption && selectedGovOption.value) {
            areaSelect.disabled = false; // تفعيل قائمة المناطق
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
                    // تحديد الخيار الحالي إذا كان مطابقاً
                    if (areaId === currentSelectedArea) {
                        option.selected = true;
                    }
                    areaSelect.add(option);
                }
            }
        } else {
            areaSelect.disabled = true; // تعطيل قائمة المناطق إذا لم يتم اختيار محافظة
        }
    }

    if (governorateSelect && areaSelect) {
        governorateSelect.addEventListener('change', updateAreaOptions);
        // تشغيل التحديث عند تحميل الصفحة لملء المناطق إذا كانت المحافظة محددة مسبقاً
        // (الكود الموجود في Blade يعتني بالتحديد الأولي للخيار)
        // updateAreaOptions(); // لا حاجة للتشغيل الأولي هنا لأن Blade يعالجه
    }

    // (اختياري) يمكنك إضافة event listeners هنا لتطبيق الفلاتر تلقائياً عند التغيير
    // document.querySelectorAll('#filter-form select, #filter-form input').forEach(element => {
    //     element.addEventListener('change', () => {
    //         document.getElementById('filter-form').submit();
    //     });
    // });

});
</script>
@endpush