{{-- resources/views/dashboard/property_lister/edit.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Edit Property: ' . $property->title)

@section('breadcrumb-items')
    @parent
    <li class="breadcrumb-item"><a href="{{ route('lister.properties.index') }}">My Properties</a></li>
    {{-- تعديل رابط العقار ليشير لصفحة العرض في لوحة التحكم إن وجدت، أو يبقى كما هو --}}
    <li class="breadcrumb-item"><a href="{{ route('lister.properties.show', $property->id) }}">{{ Str::limit($property->title, 20) }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('contant')
    <div class="card">
        <div class="card-header">
             <h5 class="card-title mb-0">Edit Property: <span class="text-primary">{{ $property->title }}</span></h5>
        </div>
        <div class="card-body mt-3">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h6 class="alert-heading fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Please fix the following errors:</h6>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                     <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('lister.properties.update', $property->id) }}" method="POST" enctype="multipart/form-data" id="edit-property-form"> {{-- إضافة ID للفورم --}}
                @csrf
                @method('PUT') {{-- استخدام PUT للتعديل --}}

                {{-- تضمين النموذج المشترك مع تمرير المتغيرات الصحيحة --}}
                @include('dashboard.property_lister._form', [
                    'property' => $property,                      // تمرير العقار الحالي للتعديل
                    'categories' => $categories ?? collect(),
                    'subCategories' => $subCategories ?? collect(),
                    'governorates' => $governorates ?? collect(),
                    'purposes' => $purposes ?? ['sale', 'rent', 'lease'],
                    'currencies' => $currencies ?? ['ILS', 'USD', 'JOD'],
                    'currentImages' => $currentImages ?? [],     // الصور الحالية للعقار
                ])

                <div class="text-end mt-4">
                     <a href="{{ route('lister.properties.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Property</button>
                </div>
            </form>
        </div>
    </div>
@endsection

{{-- ================================================ --}}
{{-- قسم السكريبتات (مهم جداً لصفحة التعديل)        --}}
{{-- ================================================ --}}
@section('script') {{-- أو @push('scripts') حسب الـ Layout --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const governorateSelect = document.getElementById('governorate_id');
        const areaSelect = document.getElementById('area_id');

        function updateAreaOptions() {
            if (!governorateSelect || !areaSelect) return;
            const selectedGovOption = governorateSelect.options[governorateSelect.selectedIndex];
            const currentSelectedArea = "{{ old('area_id', $property->area_id ?? '') }}"; // <-- استخدام قيمة العقار
            areaSelect.innerHTML = '';

            let defaultOptionGov = new Option('Select Governorate First...', '');
            defaultOptionGov.disabled = true; defaultOptionGov.selected = true;
            let defaultOptionArea = new Option('Select Area...', '');

            if (!selectedGovOption || !selectedGovOption.value) {
                areaSelect.add(defaultOptionGov);
                areaSelect.disabled = true;
            } else {
                areaSelect.disabled = false;
                areaSelect.add(defaultOptionArea);

                let areas = {};
                try {
                    if (selectedGovOption.dataset.areas) areas = JSON.parse(selectedGovOption.dataset.areas);
                } catch (e) { console.error("Error parsing areas data:", e); return; }

                let areaFound = false;
                for (const areaId in areas) {
                    if (areas.hasOwnProperty(areaId)) {
                        const option = new Option(areas[areaId], areaId);
                        if (String(areaId) === String(currentSelectedArea)) {
                            option.selected = true;
                            areaFound = true;
                        }
                        areaSelect.add(option);
                    }
                }
                if (!areaFound) {
                     areaSelect.value = "";
                }
            }
        }

        if (governorateSelect && areaSelect) {
            governorateSelect.addEventListener('change', updateAreaOptions);
            updateAreaOptions(); // التشغيل الأولي مهم للتعديل
        } else {
            console.error("Governorate or Area select elements not found in edit form.");
        }
    });
    </script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // --- العناصر الأساسية ---
        const governorateSelect = document.getElementById('governorate_id');
        const areaSelect = document.getElementById('area_id');
        const categorySelect = document.getElementById('property_category_id');
        const subCategoryWrapper = document.getElementById('sub-category-wrapper'); // حاوية التصنيف الفرعي
        const subCategorySelect = document.getElementById('sub_category_id'); // قائمة التصنيف الفرعي
        const specificFieldContainers = document.querySelectorAll(
        '.category-specific-fields'); // كل حاويات الحقول الخاصة
        const apartmentFloorField = document.getElementById('apartment-floor-number'); // حقل طابق الشقة

        // --- IDs التصنيفات (مهم جداً: تأكد من مطابقتها لقاعدة بياناتك!) ---
        const houseId = 3;
        const apartmentId = 2;
        const landId = 1;
        const commercialId = 5; // ID التجاري الرئيسي
        const tentId = 6;
        const caravanId = 7;
        const villaId = 4;
        // مصفوفة IDs السكنية (للحقول المشتركة)
        const residentialCategoryIds = [houseId, apartmentId, villaId];
        // ---------------------------------------------------------------

        // --- دالة تحديث المناطق ---
        function updateAreaOptions() {
            if (!governorateSelect || !areaSelect) {
                // console.error("Governorate or Area select element not found!");
                return;
            }
            // ... (نفس كود updateAreaOptions من الإجابات السابقة) ...
            const selectedGovOption = governorateSelect.options[governorateSelect.selectedIndex];
            const currentSelectedArea = areaSelect.dataset.currentValue ||
                "{{ old('area_id', $property->area_id ?? '') }}"; // استخدام dataset
            areaSelect.innerHTML = '';

            let defaultOptionGov = new Option('Select Governorate First...', '');
            defaultOptionGov.disabled = true;
            defaultOptionGov.selected = true;
            let defaultOptionArea = new Option('Select Area...', '');

            if (!selectedGovOption || !selectedGovOption.value) {
                areaSelect.add(defaultOptionGov);
                areaSelect.disabled = true;
            } else {
                areaSelect.disabled = false;
                areaSelect.add(defaultOptionArea);

                let areas = {};
                try {
                    if (selectedGovOption.dataset.areas) areas = JSON.parse(selectedGovOption.dataset.areas);
                } catch (e) {
                    console.error("Error parsing areas data:", e);
                    return;
                }

                let areaFound = false;
                for (const areaId in areas) {
                    if (areas.hasOwnProperty(areaId)) {
                        const option = new Option(areas[areaId], areaId);
                        if (String(areaId) === String(currentSelectedArea)) {
                            option.selected = true;
                            areaFound = true;
                        }
                        areaSelect.add(option);
                    }
                }
                if (!areaFound) {
                    areaSelect.value = ""; // تحديد الخيار الافتراضي إذا لم يتم العثور على القيمة القديمة
                }
            }
        }

        // --- دالة التحكم بالحقول الديناميكية ---
        function togglePropertyFields() {
            if (!categorySelect) {
                console.error("Category select element not found.");
                return;
            }
            const selectedCategoryId = parseInt(categorySelect.value);

            // 1. إخفاء كل الحقول الخاصة + حاوية التصنيف الفرعي
            specificFieldContainers.forEach(container => container.style.display = 'none');
            if (apartmentFloorField) apartmentFloorField.style.display = 'none';
            if (subCategoryWrapper) subCategoryWrapper.style.display = 'none';
            if (subCategorySelect) subCategorySelect.required = false; // إلغاء تطلبه مبدئياً

            // 2. تحديد وإظهار الحاوية المناسبة
            let containerToShowId = null;
            switch (selectedCategoryId) {
                case houseId:
                case villaId:
                    containerToShowId = 'residential-fields';
                    break;
                case apartmentId:
                    containerToShowId = 'residential-fields';
                    if (apartmentFloorField) apartmentFloorField.style.display = 'block';
                    break;
                case landId:
                    containerToShowId = 'land-fields';
                    break;
                case commercialId:
                    containerToShowId = 'commercial-fields';
                    // **إظهار حاوية التصنيف الفرعي هنا**
                    if (subCategoryWrapper) subCategoryWrapper.style.display = 'block'; // أو 'flex'
                    if (subCategorySelect) subCategorySelect.required = true; // جعله مطلوباً
                    break;
                case tentId:
                    containerToShowId = 'tent-fields';
                    break;
                case caravanId:
                    containerToShowId = 'caravan-fields';
                    break;
            }

            // إظهار الحاوية المحددة
            if (containerToShowId) {
                const container = document.getElementById(containerToShowId);
                if (container) {
                    container.style.display = 'flex'; // استخدام flex للعرض
                } else {
                    console.error(`Container with ID '${containerToShowId}' not found.`);
                }
            }

            // 3. (اختياري) فلترة خيارات التصنيف الفرعي (فقط إذا كانت حاويته ظاهرة)
            if (subCategorySelect && selectedCategoryId === commercialId) {
                subCategorySelect.value = ""; // إعادة تعيين القيمة
                const currentSubCategoryId = subCategorySelect.dataset.currentValue ||
                    "{{ old('sub_category_id', $property->sub_category_id ?? '') }}";
                let subCategoryFound = false;

                Array.from(subCategorySelect.options).forEach(option => {
                    if (option.value !== "") { // تجاهل الخيار الافتراضي
                        // إظهار الخيار فقط إذا كان parent_id يطابق ID التجاري
                        if (parseInt(option.dataset.parentId) === commercialId) {
                            option.style.display = 'block';
                            if (String(option.value) === String(currentSubCategoryId)) {
                                option.selected = true;
                                subCategoryFound = true;
                            }
                        } else {
                            option.style.display = 'none'; // إخفاء الخيارات غير التجارية
                            option.selected = false;
                        }
                    } else {
                        option.selected = !
                        subCategoryFound; // تحديد الخيار الافتراضي إذا لم يتم تحديد شيء آخر
                    }
                });
                // إذا لم يتم تحديد قيمة بعد الفلترة، تأكد من اختيار القيمة الافتراضية
                if (!subCategorySelect.value) {
                    subCategorySelect.value = "";
                }
            } else if (subCategorySelect) {
                // إذا لم يكن التصنيف تجارياً، أخفِ كل الخيارات ما عدا الافتراضي
                Array.from(subCategorySelect.options).forEach(option => {
                    if (option.value !== "") {
                        option.style.display = 'none';
                        option.selected = false;
                    } else {
                        option.selected = true;
                    }
                });
            }
        } // نهاية togglePropertyFields

        // --- ربط الأحداث والتشغيل الأولي ---

        // المحافظات والمناطق
        if (governorateSelect && areaSelect) {
            // إضافة قيمة قديمة/حالية كـ dataset للمساعدة في الاسترجاع
            if ("{{ old('area_id', $property->area_id ?? '') }}") {
                areaSelect.dataset.currentValue = "{{ old('area_id', $property->area_id ?? '') }}";
            }
            governorateSelect.addEventListener('change', updateAreaOptions);
            if (governorateSelect.value) {
                updateAreaOptions();
            } // التشغيل الأولي
        } else {
            console.error("Governorate or Area select elements not found.");
        }

        // التصنيفات والحقول الخاصة
        if (categorySelect) {
            // إضافة قيمة قديمة/حالية كـ dataset للمساعدة في الاسترجاع
            if (subCategorySelect && "{{ old('sub_category_id', $property->sub_category_id ?? '') }}") {
                subCategorySelect.dataset.currentValue =
                    "{{ old('sub_category_id', $property->sub_category_id ?? '') }}";
            }
            categorySelect.addEventListener('change', togglePropertyFields);
            togglePropertyFields(); // التشغيل الأولي
        } else {
            console.error("Category select element not found.");
        }

    }); // نهاية DOMContentLoaded
</script>

@endsection