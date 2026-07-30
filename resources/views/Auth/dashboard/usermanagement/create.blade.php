@extends('layouts.dashboard')

@section('title', 'Add New User')

@section('breadcrumb-items')
    @parent
    <li class="breadcrumb-item">Admin</li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">User Management</a></li>
    <li class="breadcrumb-item active">Add New User</li>
@endsection

@section('contant')

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Create a New User Account</h5>
    </div>
    <div class="card-body mt-3">

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <h4 class="alert-heading">Please fix the following errors:</h4>
                 <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                 <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif


        <form action="{{ route('admin.users.store') }}" method="POST" novalidate>
            @csrf

            {{-- حقل الاسم --}}
            <div class="row mb-3">
                <label for="name" class="col-sm-3 col-form-label required">Full Name</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- حقل الإيميل --}}
            <div class="row mb-3">
                <label for="email" class="col-sm-3 col-form-label required">Email</label>
                <div class="col-sm-9">
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- حقل كلمة المرور --}}
            <div class="row mb-3">
                <label for="password" class="col-sm-3 col-form-label required">Password</label>
                <div class="col-sm-9">
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- حقل تأكيد كلمة المرور --}}
            <div class="row mb-3">
                <label for="password_confirmation" class="col-sm-3 col-form-label required">Confirm Password</label>
                <div class="col-sm-9">
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                </div>
            </div>

             {{-- حقل الدور --}}
            <div class="row mb-3">
                <label for="role" class="col-sm-3 col-form-label required">Role</label>
                <div class="col-sm-9">
                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                        <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select Role...</option>
                        @isset($roles)
                            @foreach ($roles as $roleValue)
                                <option value="{{ $roleValue }}" {{ old('role') == $roleValue ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $roleValue)) }}
                                </option>
                            @endforeach
                        @endisset
                    </select>
                    @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>


            <div class="row mb-3">
                <label for="status" class="col-sm-3 col-form-label required">Status</label>
                <div class="col-sm-9">
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        @isset($statuses)
                            @foreach ($statuses as $statusValue)
                                <option value="{{ $statusValue }}" {{ old('status', 'active') == $statusValue ? 'selected' : '' }}>
                                    {{ ucfirst($statusValue) }}
                                </option>
                            @endforeach
                        @endisset
                    </select>
                     @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>


            <div class="row mb-3">
                <label for="phone" class="col-sm-3 col-form-label">Phone</label>
                <div class="col-sm-9">
                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}">
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- ***** بداية التعديل ***** --}}
            {{-- قائمة المحافظات --}}
            <div class="row mb-3">
                <label for="governorate_id" class="col-sm-3 col-form-label">Governorate</label> {{-- جعلناها اختيارية؟ بناءً على الكود القديم --}}
                <div class="col-sm-9">
                    <select class="form-select @error('governorate_id') is-invalid @enderror" id="governorate_id" name="governorate_id"> {{-- name اختياري هنا --}}
                        <option value="" selected>Select Governorate (Optional)...</option> {{-- تغيير النص الافتراضي --}}
                        @isset($governorates)
                            @foreach ($governorates as $governorate)
                                <option value="{{ $governorate->id }}"
                                        {{-- التعامل مع القيمة القديمة في حالة خطأ التحقق --}}
                                        {{ old('governorate_id') == $governorate->id ? 'selected' : '' }}
                                        data-areas="{{ json_encode($governorate->areas->pluck('name', 'id')) }}">
                                    {{ $governorate->name }}
                                </option>
                            @endforeach
                        @endisset
                    </select>
                    {{-- @error('governorate_id') <div class="invalid-feedback">{{ $message }}</div> @enderror --}}
                </div>
            </div>

            {{-- قائمة المناطق --}}
            <div class="row mb-3">
                <label for="area_id" class="col-sm-3 col-form-label">Area</label> {{-- اختيارية؟ --}}
                <div class="col-sm-9">
                    <select class="form-select @error('area_id') is-invalid @enderror" id="area_id" name="area_id"> {{-- هذا الحقل هو الذي سيتم إرساله --}}
                         {{-- سيتم ملء هذا بواسطة JS. نعالج حالة الخطأ هنا --}}
                         @if(old('governorate_id') && isset($governorates))
                            @php
                                $selectedGovernorateId = old('governorate_id');
                                $selectedAreaId = old('area_id');
                                $areasForSelectedGov = [];
                                $selectedGov = $governorates->firstWhere('id', $selectedGovernorateId);
                                if($selectedGov) {
                                    $areasForSelectedGov = $selectedGov->areas;
                                }
                            @endphp
                            @if(!empty($areasForSelectedGov))
                                <option value="">Select Area (Optional)...</option> {{-- Default option --}}
                                @foreach($areasForSelectedGov as $area)
                                    <option value="{{ $area->id }}" {{ $selectedAreaId == $area->id ? 'selected' : '' }}>
                                        {{ $area->name }}
                                    </option>
                                @endforeach
                            @else
                                <option value="" disabled selected>Select Governorate First (Optional)...</option>
                            @endif
                        @else
                            <option value="" disabled selected>Select Governorate First (Optional)...</option>
                        @endif
                    </select>
                    @error('area_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            {{-- ***** نهاية التعديل ***** --}}


            <div class="row mb-3">
                <label for="address" class="col-sm-3 col-form-label">Address</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address') }}">
                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>


            <div class="row mb-3">
                <label for="description" class="col-sm-3 col-form-label">About / Description</label>
                <div class="col-sm-9">
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                     @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>


            <div class="row mt-4">
                <div class="col-sm-9 offset-sm-3">
                    <button type="submit" class="btn btn-gold me-2">Create User</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </div>

        </form>
    </div> {{-- End Card Body --}}
</div> {{-- End Card --}}


<style>
    .required::after {
        content: " *";
        color: red;
    }
</style>

@endsection

{{-- أضف هذا القسم في نهاية الملف --}}
@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const governorateSelect = document.getElementById('governorate_id');
    const areaSelect = document.getElementById('area_id');

    function updateAreaOptions() {
        const selectedOption = governorateSelect.options[governorateSelect.selectedIndex];
        areaSelect.innerHTML = ''; // إفراغ قائمة المناطق الحالية

        // إضافة الخيار الافتراضي (الاختياري) أولاً
        areaSelect.add(new Option('Select Area (Optional)...', ''));

        if (selectedOption && selectedOption.value) {
            let areas = {};
            try {
                if (selectedOption.dataset.areas) {
                    areas = JSON.parse(selectedOption.dataset.areas);
                } else {
                    console.warn('No areas data found for selected governorate:', selectedOption.text);
                }
            } catch (e) {
                console.error("Error parsing areas data:", e);
                areaSelect.value = ""; // تأكد من اختيار الخيار الافتراضي
                return; // الخروج إذا حدث خطأ
            }

            // إضافة المناطق الجديدة
            for (const areaId in areas) {
                if (areas.hasOwnProperty(areaId)) {
                    areaSelect.add(new Option(areas[areaId], areaId));
                }
            }

            // إعادة تحديد المنطقة القديمة إذا كانت موجودة (مفيد عند خطأ التحقق)
             const oldAreaId = "{{ old('area_id', '') }}";
            if (oldAreaId && areaSelect.querySelector(`option[value="${oldAreaId}"]`)) {
                areaSelect.value = oldAreaId;
            } else {
                areaSelect.value = ""; // العودة للخيار الافتراضي إذا لم يتم العثور على القيمة القديمة
            }


        } else {
            // إذا لم يتم اختيار محافظة، تأكد من أن الخيار الافتراضي محدد
            areaSelect.value = "";
        }
    }

    // ربط حدث التغيير بقائمة المحافظات
    if (governorateSelect) {
        governorateSelect.addEventListener('change', updateAreaOptions);

        // تحديث القائمة عند تحميل الصفحة إذا كانت هناك قيمة قديمة للمحافظة
        // هذا سيملأ قائمة المناطق إذا حدث خطأ في التحقق وعادت الصفحة
        if (governorateSelect.value) {
            updateAreaOptions();
        }
    } else {
        console.error("Governorate select element not found.");
    }
});
</script>
@endsection