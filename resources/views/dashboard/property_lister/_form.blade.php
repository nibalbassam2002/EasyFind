<div class="row g-3">
    {{-- Title --}}
    <div class="col-md-12">
        <label for="title" class="form-label required">Property Title</label>
        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $property->title ?? '') }}" required>
        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Description --}}
    <div class="col-md-12">
        <label for="description" class="form-label required">Description</label>
        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5" required>{{ old('description', $property->description ?? '') }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Category (مهم للتحكم الديناميكي) --}}
    <div class="col-md-6">
        <label for="property_category_id" class="form-label required">Main Category</label>
        <select class="form-select @error('category_id') is-invalid @enderror" id="property_category_id" name="category_id" required>
            <option value="" disabled {{ !(old('category_id', $property->category_id ?? '')) ? 'selected' : '' }}>Select Type...</option>
            @isset($categories)
                @foreach($categories as $category)
                    {{-- إضافة data-id لمقارنة أسهل في JS --}}
                    <option value="{{ $category->id }}" data-id="{{ $category->id }}" {{ old('category_id', $property->category_id ?? '') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            @endisset
        </select>
        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Sub Category --}}
    <div class="row g-3">
        <div id="sub-category-wrapper" class="col-md-6" style="display: none;"> {{-- إضافة id وإخفاء --}}
            <label for="sub_category_id" class="form-label required">Sub Category (Commercial Type)</label> {{-- تعديل الـ label للتوضيح --}}
            <select class="form-select @error('sub_category_id') is-invalid @enderror" id="sub_category_id" name="sub_category_id"> {{-- إزالة required مؤقتاً أو جعله شرطياً --}}
                 <option value="" disabled selected>Select type...</option>
                 @isset($subCategories)
                     
                     @php $commercialCategoryId = 5;  @endphp
                     @foreach($subCategories->where('parent_id', $commercialCategoryId) as $subCategory)
                        <option value="{{ $subCategory->id }}" data-parent-id="{{ $subCategory->parent_id }}" {{ old('sub_category_id', $property->sub_category_id ?? '') == $subCategory->id ? 'selected' : '' }}>
                            {{ $subCategory->name }}
                        </option>
                     @endforeach
                 @endisset
            </select>
             @error('sub_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    </div>


    {{-- Purpose --}}
    <div class="col-md-4">
         <label for="purpose" class="form-label required">Purpose</label>
        <select class="form-select @error('purpose') is-invalid @enderror" id="purpose" name="purpose" required>
             <option value="" disabled {{ !(old('purpose', $property->purpose ?? '')) ? 'selected' : '' }}>Select...</option>
             @isset($purposes)
                 @foreach($purposes as $purposeValue) {{-- استخدام purposeValue لتجنب التعارض --}}
                 <option value="{{ $purposeValue }}" {{ old('purpose', $property->purpose ?? '') == $purposeValue ? 'selected' : '' }}>{{ ucfirst($purposeValue) }}</option>
                @endforeach
             @endisset
        </select>
         @error('purpose') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Price & Currency --}}
    <div class="col-md-4">
         <label for="price" class="form-label required">Price</label>
         {{-- ***** بداية التعديل ***** --}}
         <input type="number" step="1" {{-- أو 100 أو أي قيمة منطقية لخطوة السعر --}}
                class="form-control @error('price') is-invalid @enderror"
                id="price" name="price"
                value="{{ old('price', $property->price ?? '') }}"
                required
                min="50" {{-- حد أدنى منطقي للسعر (عدّله حسب عملتك) --}}
                placeholder="e.g., 150000">
         {{-- ***** نهاية التعديل ***** --}}
         @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
     <div class="col-md-4">
        <label for="currency" class="form-label required">Currency</label>
        <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency" required>
             @isset($currencies)
                 @foreach($currencies as $currency)
                 <option value="{{ $currency }}" {{ old('currency', $property->currency ?? 'ILS') == $currency ? 'selected' : '' }}>{{ $currency }}</option>
                @endforeach
             @endisset
        </select>
        @error('currency') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Governorate & Area --}}
    <div class="col-md-6">
        <label for="governorate_id" class="form-label required">Governorate</label>
        <select class="form-select @error('governorate_id') is-invalid @enderror" id="governorate_id" name="governorate_id" required>
            <option value="" disabled selected>Select Governorate...</option>
            @isset($governorates)
                @foreach ($governorates as $governorate)
                    <option value="{{ $governorate->id }}"
                        {{ old('governorate_id', $property->area?->governorate_id ?? '') }}
                            data-areas="{{ json_encode($governorate->areas->pluck('name', 'id')) }}">
                        {{ $governorate->name }}
                    </option>
                @endforeach
            @endisset
        </select>
     
    </div>
    <div class="col-md-6">
        <label for="area_id" class="form-label required">Area</label>
        <select class="form-select @error('area_id') is-invalid @enderror" id="area_id" name="area_id" required>
            <option value="" disabled selected>Select Governorate First...</option>
            @if(old('area_id') || isset($property->area_id))
               @php
                   $selectedGovernorateId = old('governorate_id', $property->area?->governorate_id ?? null);
                   $selectedAreaId = old('area_id', $property->area_id ?? null);
                   $areasForSelectedGov = [];
                   if ($selectedGovernorateId && isset($governorates)) {
                       $selectedGov = $governorates->firstWhere('id', $selectedGovernorateId);
                       if($selectedGov) { $areasForSelectedGov = $selectedGov->areas; }
                   }
               @endphp
                @if(!empty($areasForSelectedGov))
                   @foreach($areasForSelectedGov as $area)
                       <option value="{{ $area->id }}" {{ $selectedAreaId == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                   @endforeach
                @else
                     <option value="" disabled selected>Select Governorate First...</option>
                @endif
            @else
                <option value="" disabled selected>Select Governorate First...</option>
            @endif
        </select>
        @error('area_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Address --}}
    <div class="col-md-12">
        <label for="address" class="form-label required">Detailed Address</label>
        <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address', $property->address ?? '') }}" required placeholder="e.g., Building Name, Street Name, Landmark">
         @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label for="area" class="form-label required">Area (sqm)</label>
        <input type="number" class="form-control @error('area') is-invalid @enderror" id="area" name="area" value="{{ old('area', $property->area ?? '') }}" required min="1" placeholder="Total/Built Area">
        @error('area') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
        <label for="land_area" class="form-label">Land Area (sqm)</label>
        <input type="number" class="form-control @error('land_area') is-invalid @enderror" id="land_area" name="land_area" value="{{ old('land_area', $property->land_area ?? '') }}" min="0" placeholder="(If House/Villa/Land)">
        @error('land_area') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
     <div class="col-md-3">
        <label for="property_condition" class="form-label">Condition</label>
        <select class="form-select @error('property_condition') is-invalid @enderror" id="property_condition" name="property_condition">
            <option value="" {{ old('property_condition', $property->property_condition ?? '') == '' ? 'selected' : '' }}>Select...</option>
            <option value="new" {{ old('property_condition', $property->property_condition ?? '') == 'new' ? 'selected' : '' }}>New</option>
            <option value="used" {{ old('property_condition', $property->property_condition ?? '') == 'used' ? 'selected' : '' }}>Used</option>
            <option value="needs_renovation" {{ old('property_condition', $property->property_condition ?? '') == 'needs_renovation' ? 'selected' : '' }}>Needs Renovation</option>
        </select>
        @error('property_condition') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
        <label for="finishing_type" class="form-label">Finishing</label>
        <select class="form-select @error('finishing_type') is-invalid @enderror" id="finishing_type" name="finishing_type">
            <option value="" {{ old('finishing_type', $property->finishing_type ?? '') == '' ? 'selected' : '' }}>Select...</option>
            <option value="full" {{ old('finishing_type', $property->finishing_type ?? '') == 'full' ? 'selected' : '' }}>Full</option>
            <option value="semi" {{ old('finishing_type', $property->finishing_type ?? '') == 'semi' ? 'selected' : '' }}>Semi-Finished</option>
            <option value="none" {{ old('finishing_type', $property->finishing_type ?? '') == 'none' ? 'selected' : '' }}>Unfinished</option>
        </select>
        @error('finishing_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>


    <div class="col-12">
        <div id="specific-fields-container">

    
            <div id="residential-fields" class="row g-3 category-specific-fields" style="display: none;">
                <hr class="my-3"> <h6 class="text-muted">Residential Details</h6>
                <div class="col-md-3">
                    <label for="rooms" class="form-label">Rooms</label>
                    <input type="number" class="form-control @error('rooms') is-invalid @enderror" id="rooms" name="rooms" value="{{ old('rooms', $property->rooms ?? '') }}" min="0">
                    @error('rooms') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label for="bathrooms" class="form-label">Bathrooms</label>
                    <input type="number" class="form-control @error('bathrooms') is-invalid @enderror" id="bathrooms" name="bathrooms" value="{{ old('bathrooms', $property->bathrooms ?? '') }}" min="0">
                    @error('bathrooms') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label for="floors" class="form-label">Floors (Building/House)</label>
                    <input type="number" class="form-control @error('floors') is-invalid @enderror" id="floors" name="floors" value="{{ old('floors', $property->floors ?? '') }}" min="0">
                    @error('floors') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                 <div class="col-md-3" id="apartment-floor-number" style="display: none;"> {{-- خاص بالشقق --}}
                    <label for="apartment_floor_num" class="form-label">Apartment Floor No.</label>
                    {{-- سنحتاج لإضافة هذا العمود لقاعدة البيانات إذا لم يكن floors كافياً --}}
                    <input type="number" class="form-control" id="apartment_floor_num" name="apartment_floor_num" value="{{ old('apartment_floor_num', $property->apartment_floor_num ?? '') }}" min="0">
                </div>
                <div class="col-md-6">
                   <label for="view_type" class="form-label">View</label>
                   <input type="text" class="form-control @error('view_type') is-invalid @enderror" id="view_type_residential" name="view_type" value="{{ old('view_type', $property->view_type ?? '') }}" placeholder="e.g., Sea, Garden, Main Street">
                   @error('view_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
               </div>
            </div>

            {{-- 2. حقل نوع الأرض --}}
            <div id="land-fields" class="row g-3 category-specific-fields" style="display: none;">
                <hr class="my-3"> <h6 class="text-muted">Land Details</h6>
                <div class="col-md-4">
                    <label for="land_type" class="form-label">Land Specific Type</label>
                    <input type="text" class="form-control @error('land_type') is-invalid @enderror" id="land_type" name="land_type" value="{{ old('land_type', $property->land_type ?? '') }}" placeholder="e.g., Agricultural, Residential Plot">
                    @error('land_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            
            </div>

            
            <div id="commercial-fields" class="row g-3 category-specific-fields" style="display: none;">
                 <hr class="my-3"> <h6 class="text-muted">Commercial Details</h6>
                <div class="col-md-4">
                    <label for="commercial_type" class="form-label">Commercial Type</label>
                    <select class="form-select @error('commercial_type') is-invalid @enderror" id="commercial_type" name="commercial_type">
                        <option value="" {{ old('commercial_type', $property->commercial_type ?? '') == '' ? 'selected' : '' }}>Select type...</option>
                        <option value="office" {{ old('commercial_type', $property->commercial_type ?? '') == 'office' ? 'selected' : '' }}>Office</option>
                        <option value="shop" {{ old('commercial_type', $property->commercial_type ?? '') == 'shop' ? 'selected' : '' }}>Shop</option>
                        <option value="warehouse" {{ old('commercial_type', $property->commercial_type ?? '') == 'warehouse' ? 'selected' : '' }}>Warehouse</option>
                    </select>
                    @error('commercial_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                 <div class="col-md-8">
                    <label for="commercial_purpose" class="form-label">Suitable For</label>
                    <input type="text" class="form-control @error('commercial_purpose') is-invalid @enderror" id="commercial_purpose" name="commercial_purpose" value="{{ old('commercial_purpose', $property->commercial_purpose ?? '') }}" placeholder="e.g., Restaurant, Store, Clinic, Storage">
                    @error('commercial_purpose') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div id="tent-fields" class="row g-3 category-specific-fields" style="display: none;">
                <hr class="my-3"> <h6 class="text-muted">Tent Details</h6>
                <div class="col-md-4">
                     <label for="tent_type" class="form-label">Tent Type</label>
                    <input type="text" class="form-control @error('tent_type') is-invalid @enderror" id="tent_type" name="tent_type" value="{{ old('tent_type', $property->tent_type ?? '') }}" placeholder="e.g., Family Tent, Relief Tent">
                    @error('tent_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>


            <div id="caravan-fields" class="row g-3 category-specific-fields" style="display: none;">
                <hr class="my-3"> <h6 class="text-muted">Caravan Details</h6>
                 <div class="col-md-4">
                     <label for="caravan_type" class="form-label">Caravan Type</label>
                    <input type="text" class="form-control @error('caravan_type') is-invalid @enderror" id="caravan_type" name="caravan_type" value="{{ old('caravan_type', $property->caravan_type ?? '') }}" placeholder="e.g., Static, Touring">
                    @error('caravan_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

        </div> 
    </div>

     <div class="col-12">
        <hr class="my-3">
        <label class="form-label mb-2 fw-semibold">Amenities & Features:</label>
        <div class="row">
            @php
               
                $possible_amenities = [
                    'elevator' => 'Elevator',
                    'parking' => 'Parking',
                    'pool' => 'Swimming Pool',
                    'garden' => 'Garden',
                    'security' => 'Security',
                    'ac' => 'Air Conditioning',
                    'main_road_access' => 'Main Road Access', // للأراضي والتجاري
                    'electricity' => 'Electricity Available', // للأراضي
                    'water' => 'Water Available',       // للأراضي
                    'sewage' => 'Sewage Available'       // للأراضي
                ];
                
                $selected_amenities = old('amenities', $property->amenities ?? []);
               
                if (!is_array($selected_amenities)) {
                    $selected_amenities = json_decode($selected_amenities, true) ?? [];
                }
            @endphp
            @foreach($possible_amenities as $key => $label)
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="amenities[]" value="{{ $key }}" id="amenity_{{ $key }}"
                            {{ in_array($key, $selected_amenities) ? 'checked' : '' }}>
                        <label class="form-check-label" for="amenity_{{ $key }}">
                            {{ $label }}
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
         @error('amenities') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        @error('amenities.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    {{-- حقل تفاصيل إضافية --}}
    <div class="col-md-12">
        <label for="additional_details" class="form-label">Additional Details / Land Status</label>
        <textarea class="form-control @error('additional_details') is-invalid @enderror" id="additional_details" name="additional_details" rows="3" placeholder="e.g., Land is flat, contains olive trees, close to main services...">{{ old('additional_details', $property->additional_details ?? '') }}</textarea>
        @error('additional_details') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Location (Coordinates) --}}
    <div class="col-md-12">
         <label for="location" class="form-label">Location (Lat, Lng)</label>
        <input type="text" class="form-control @error('location') is-invalid @enderror" id="location" name="location" value="{{ old('location', $property->location ?? '') }}" placeholder="e.g., 31.9539, 35.9106">
         <div class="form-text">Optional. You can add coordinates for map display.</div>
        @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

     {{-- Images --}}
    <div class="col-md-12">
        <label for="images" class="form-label">Images (Max 5, Max 2MB each)</label>
        <input class="form-control @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror" type="file" id="images" name="images[]" multiple accept="image/*">
         @error('images') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
         @error('images.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

         {{-- عرض الصور الحالية عند التعديل --}}
         @isset($currentImages)
             @if(!empty($currentImages))
                 <div class="mt-3">
                     <p>Current Images:</p>
                     <div class="row row-cols-2 row-cols-md-4 g-2">
                         @foreach($currentImages as $imagePath)
                             <div class="col position-relative">
                                 @if(Storage::disk('public')->exists($imagePath))
                                     <img src="{{ Storage::url($imagePath) }}" class="img-thumbnail" alt="Property Image" style="height: 80px; object-fit: cover;">
                                     <div class="form-check position-absolute top-0 end-0 m-1 bg-light rounded p-1">
                                         <input class="form-check-input delete-image-checkbox" type="checkbox" name="delete_images[]" value="{{ $imagePath }}" id="delete_img_{{ $loop->index }}">
                                         <label class="form-check-label small" for="delete_img_{{ $loop->index }}" title="Mark to delete">
                                             <i class="bi bi-trash text-danger"></i>
                                         </label>
                                     </div>
                                 @else
                                     <span class="badge bg-danger">Image missing</span>
                                     <input type="hidden" name="delete_images[]" value="{{ $imagePath }}">
                                 @endif
                             </div>
                         @endforeach
                     </div>
                 </div>
             @endif
        @endisset
    </div>

    {{-- Video URL --}}
    <div class="col-md-12">
        <label for="video_url" class="form-label">Video URL (YouTube, Vimeo, etc.)</label>
        <input type="url" class="form-control @error('video_url') is-invalid @enderror" id="video_url" name="video_url" value="{{ old('video_url', $property->video_url ?? '') }}" placeholder="https://...">
         @error('video_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

</div>

<p class="text-muted mt-3"><small><span class="text-danger">*</span> Required fields</small></p>
<style> .required::after { content: " *"; color: red; } </style>