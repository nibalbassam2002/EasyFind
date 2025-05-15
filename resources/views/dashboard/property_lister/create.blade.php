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

                @include('dashboard.property_lister._form', [
                    'property' => new \App\Models\Property(), 
                    'categories' => $categories ?? [],
                    'subCategories' => $subCategories ?? [],
                    'governorates' => $governorates ?? [],
                    'purposes' => $purposes ?? ['sale', 'rent', 'lease'],
                    'currencies' => $currencies ?? ['ILS', 'USD', 'JOD'],
                    'currentImages' => [],
                ])

                <div class="text-start mt-4">
                    <button type="submit" class="btn btn-gold">Submit Property for Review</button>
                    <a href="{{ route('lister.properties.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    
                </div>
            </form>
        </div>
    </div>
@endsection


@section('script')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const governorateSelect = document.getElementById('governorate_id');
            const areaSelect = document.getElementById('area_id');
            

            function updateAreaOptions() {
                if (!governorateSelect || !areaSelect) {
                    console.error("Governorate or Area select element not found!");
                    return;
                }

                const selectedGovOption = governorateSelect.options[governorateSelect.selectedIndex];
                const oldAreaId = "{{ old('area_id', '') }}"; 
                areaSelect.innerHTML = ''; 

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
                        areaSelect.value = oldAreaId;
                    } else if (!oldAreaId) {
                        areaSelect.value = ""; 
                    }
                }
            }

            if (governorateSelect) {
                governorateSelect.addEventListener('change', updateAreaOptions);
                if (governorateSelect.value) { 
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
            const governorateSelect = document.getElementById('governorate_id_form');
            const areaSelect = document.getElementById('area_id_form');
            const categorySelect = document.getElementById('property_category_id');
            const subCategoryWrapper = document.getElementById('sub-category-wrapper'); 
            const subCategorySelect = document.getElementById('sub_category_id'); 
            const specificFieldContainers = document.querySelectorAll(
            '.category-specific-fields'); 
            const apartmentFloorField = document.getElementById('apartment-floor-number'); 

            const houseId = 3;
            const apartmentId = 2;
            const landId = 1;
            const commercialId = 5; 
            const tentId = 6;
            const caravanId = 7;
            const villaId = 4;
            const residentialCategoryIds = [houseId, apartmentId, villaId];

            function updateAreaOptions() {
                if (!governorateSelect || !areaSelect) {
                    
                    return;
                }
                
                const selectedGovOption = governorateSelect.options[governorateSelect.selectedIndex];
                const currentSelectedArea = areaSelect.dataset.currentValue ||
                    "{{ old('area_id', $property->area_id ?? '') }}";
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
                        areaSelect.value = ""; 
                    }
                }
            }

            function togglePropertyFields() {
                if (!categorySelect) {
                    console.error("Category select element not found.");
                    return;
                }
                const selectedCategoryId = parseInt(categorySelect.value);

                specificFieldContainers.forEach(container => container.style.display = 'none');
                if (apartmentFloorField) apartmentFloorField.style.display = 'none';
                if (subCategoryWrapper) subCategoryWrapper.style.display = 'none';
                if (subCategorySelect) subCategorySelect.required = false; 
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
                       
                        if (subCategoryWrapper) subCategoryWrapper.style.display = 'block'; 
                        if (subCategorySelect) subCategorySelect.required = true; 
                        break;
                    case tentId:
                        containerToShowId = 'tent-fields';
                        break;
                    case caravanId:
                        containerToShowId = 'caravan-fields';
                        break;
                }

              
                if (containerToShowId) {
                    const container = document.getElementById(containerToShowId);
                    if (container) {
                        container.style.display = 'flex';
                    } else {
                        console.error(`Container with ID '${containerToShowId}' not found.`);
                    }
                }

                
                if (subCategorySelect && selectedCategoryId === commercialId) {
                    subCategorySelect.value = "";
                    const currentSubCategoryId = subCategorySelect.dataset.currentValue ||
                        "{{ old('sub_category_id', $property->sub_category_id ?? '') }}";
                    let subCategoryFound = false;

                    Array.from(subCategorySelect.options).forEach(option => {
                        if (option.value !== "") { 
                         
                            if (parseInt(option.dataset.parentId) === commercialId) {
                                option.style.display = 'block';
                                if (String(option.value) === String(currentSubCategoryId)) {
                                    option.selected = true;
                                    subCategoryFound = true;
                                }
                            } else {
                                option.style.display = 'none'; 
                                option.selected = false;
                            }
                        } else {
                            option.selected = !
                            subCategoryFound; 
                        }
                    });
                  
                    if (!subCategorySelect.value) {
                        subCategorySelect.value = "";
                    }
                } else if (subCategorySelect) {
                   
                    Array.from(subCategorySelect.options).forEach(option => {
                        if (option.value !== "") {
                            option.style.display = 'none';
                            option.selected = false;
                        } else {
                            option.selected = true;
                        }
                    });
                }
            } 


            if (governorateSelect && areaSelect) {
            
                if ("{{ old('area_id', $property->area_id ?? '') }}") {
                    areaSelect.dataset.currentValue = "{{ old('area_id', $property->area_id ?? '') }}";
                }
                governorateSelect.addEventListener('change', updateAreaOptions);
                if (governorateSelect.value) {
                    updateAreaOptions();
                } 
            } else {
                console.error("Governorate or Area select elements not found.");
            }

           
            if (categorySelect) {
                
                if (subCategorySelect && "{{ old('sub_category_id', $property->sub_category_id ?? '') }}") {
                    subCategorySelect.dataset.currentValue =
                        "{{ old('sub_category_id', $property->sub_category_id ?? '') }}";
                }
                categorySelect.addEventListener('change', togglePropertyFields);
                togglePropertyFields(); 
            } else {
                console.error("Category select element not found.");
            }

        }); 
    </script>

@endsection 
