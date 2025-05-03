{{-- resources/views/dashboard/property_lister/create.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Add New Property')

@section('breadcrumb-items')
    @parent
    <li class="breadcrumb-item"><a href="{{ route('lister.properties.index') }}">My Properties</a></li>
    <li class="breadcrumb-item active">Add New</li>
@endsection

@section('contant')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">List a New Property</h5>
        </div>
        <div class="card-body mt-3">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <h6 class="alert-heading">Please fix the following errors:</h6>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('lister.properties.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- تضمين النموذج المشترك مع تمرير المتغيرات الصحيحة --}}
                @include('dashboard.property_lister._form', [
                    'property' => new \App\Models\Property(), // كائن فارغ للإضافة
                    'categories' => $categories ?? [],
                    'subCategories' => $subCategories ?? [],
                    'governorates' => $governorates ?? [],
                    'purposes' => $purposes ?? ['sale', 'rent', 'lease'],
                    'currencies' => $currencies ?? ['ILS', 'USD', 'JOD'],
                    'currentImages' => [],
                ])

                <div class="text-end mt-4">
                    <a href="{{ route('lister.properties.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit Property for Review</button>
                </div>
            </form>
        </div>
    </div>
@endsection

{{-- ================================================ --}}
{{--                 قسم السكريبتات                    --}}
{{-- ================================================ --}}
@section('script') {{-- <-- استخدام @section بدلاً من @push --}}

    {{-- 1. كود JS لقوائم المحافظات والمناطق المترابطة --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ***** تأكد من أن هذه الـ IDs تطابق ما في _form.blade.php *****
            const governorateSelect = document.getElementById('governorate_id');
            const areaSelect = document.getElementById('area_id');
            // *********************************************************

            function updateAreaOptions() {
                if (!governorateSelect || !areaSelect) {
                    console.error("Governorate or Area select element not found!");
                    return;
                }

                const selectedGovOption = governorateSelect.options[governorateSelect.selectedIndex];
                const oldAreaId = "{{ old('area_id', '') }}"; // قيمة قديمة (فارغة في create)
                areaSelect.innerHTML = ''; // إفراغ

                let defaultOption = new Option('Select Governorate First...', '');
                defaultOption.disabled = true;
                defaultOption.selected = true;
                areaSelect.add(defaultOption);
                areaSelect.disabled = true;

                if (selectedGovOption && selectedGovOption.value && selectedGovOption.dataset.areas) {
                    areaSelect.disabled = false;
                    areaSelect.innerHTML = '<option value="">Select Area...</option>';

                    let areas = {};
                    try {
                        areas = JSON.parse(selectedGovOption.dataset.areas);
                        // console.log("Parsed areas:", areas); // للتصحيح
                    } catch (e) {
                        console.error("Error parsing areas data:", e, selectedGovOption.dataset.areas);
                        areaSelect.innerHTML = '<option value="">Error loading areas</option>';
                        return;
                    }

                    for (const areaId in areas) {
                        if (areas.hasOwnProperty(areaId)) {
                            const option = new Option(areas[areaId], areaId);
                            if (String(areaId) === String(oldAreaId)) {
                                option.selected = true;
                            }
                            areaSelect.add(option);
                        }
                    }
                    if (oldAreaId && !areaSelect.value) {
                        areaSelect.value = oldAreaId; // محاولة التحديد مرة أخرى
                    } else if (!oldAreaId) {
                        areaSelect.value = ""; // التأكد من اختيار "Select Area"
                    }
                }
            }

            if (governorateSelect) {
                governorateSelect.addEventListener('change', updateAreaOptions);
                if (governorateSelect.value) { // معالجة القيمة القديمة للمحافظة
                    updateAreaOptions();
                }
            } else {
                console.error(
                "Governorate select element not found. Check ID in _form.blade.php: 'governorate_id'");
            }
            if (!areaSelect) {
                console.error("Area select element not found. Check ID in _form.blade.php: 'area_id'");
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

@endsection {{-- <-- إغلاق @section('script') --}}
