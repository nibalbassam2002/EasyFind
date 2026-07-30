@extends('layouts.dashboard')

@section('title', 'Edit User: ' . $user->name) {{-- عرض اسم المستخدم الذي يتم تعديله --}}

@section('breadcrumb-items')
    @parent
    <li class="breadcrumb-item">Admin</li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">User Management</a></li>
    <li class="breadcrumb-item active">Edit User</li>
@endsection

@section('contant')

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Edit User Account: <span class="text-primary">{{ $user->name }}</span></h5>
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
        @if (session('success')) {{-- رسالة نجاح التحديث --}}
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- ▼▼▼ تعديل: action المسار و method ▼▼▼ --}}
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" novalidate>
            @csrf
            @method('PUT') {{-- أو PATCH، كلاهما مناسب للتحديث --}}

            {{-- حقل الاسم --}}
            <div class="row mb-3">
                <label for="name" class="col-sm-3 col-form-label required">Full Name</label>
                <div class="col-sm-9">
                                                                    {{-- ▼▼▼ تعديل: استخدام بيانات $user --}}
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- حقل الإيميل --}}
            <div class="row mb-3">
                <label for="email" class="col-sm-3 col-form-label required">Email</label>
                <div class="col-sm-9">
                                                                    {{-- ▼▼▼ تعديل: استخدام بيانات $user --}}
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>


             {{-- حقل الدور --}}
            <div class="row mb-3">
                <label for="role" class="col-sm-3 col-form-label required">Role</label>
                <div class="col-sm-9">
                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                        <option value="" disabled>Select Role...</option>
                        @isset($roles) {{-- $roles يتم تمريرها من ManagementController@edit --}}
                            @foreach ($roles as $roleValue)
                                <option value="{{ $roleValue }}" {{ old('role', $user->role) == $roleValue ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $roleValue)) }}
                                </option>
                            @endforeach
                        @endisset
                    </select>
                    @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- حقل الحالة --}}
            <div class="row mb-3">
                <label for="status" class="col-sm-3 col-form-label required">Status</label>
                <div class="col-sm-9">
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        @isset($statuses) {{-- $statuses يتم تمريرها من ManagementController@edit --}}
                            @foreach ($statuses as $statusValue)
                                <option value="{{ $statusValue }}" {{ old('status', $user->status) == $statusValue ? 'selected' : '' }}>
                                    {{ ucfirst($statusValue) }}
                                </option>
                            @endforeach
                        @endisset
                    </select>
                     @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- حقل الهاتف --}}
            <div class="row mb-3">
                <label for="phone" class="col-sm-3 col-form-label">Phone</label>
                <div class="col-sm-9">
                                                                    {{-- ▼▼▼ تعديل: استخدام بيانات $user --}}
                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- قائمة المحافظات --}}
            <div class="row mb-3">
                <label for="governorate_id_edit" class="col-sm-3 col-form-label">Governorate</label>
                <div class="col-sm-9">
                                                                                                {{-- ▼▼▼ تعديل: استخدام ID مختلف للـ select --}}
                    <select class="form-select @error('governorate_id') is-invalid @enderror" id="governorate_id_edit" name="governorate_id">
                        <option value="" selected>Select Governorate (Optional)...</option>
                        @isset($governorates) {{-- $governorates يتم تمريرها من ManagementController@edit --}}
                            @foreach ($governorates as $governorate)
                                <option value="{{ $governorate->id }}"
                                        {{-- ▼▼▼ تعديل: استخدام بيانات $user لتحديد القيمة المختارة --}}
                                        {{ old('governorate_id', $user->area?->governorate_id) == $governorate->id ? 'selected' : '' }}
                                        data-areas="{{ json_encode($governorate->areas->pluck('name', 'id')) }}">
                                    {{ $governorate->name }}
                                </option>
                            @endforeach
                        @endisset
                    </select>
                </div>
            </div>

            {{-- قائمة المناطق --}}
            <div class="row mb-3">
                <label for="area_id_edit" class="col-sm-3 col-form-label">Area</label>
                <div class="col-sm-9">
                                                                                {{-- ▼▼▼ تعديل: استخدام ID مختلف للـ select --}}
                    <select class="form-select @error('area_id') is-invalid @enderror" id="area_id_edit" name="area_id">
                        {{-- سيتم ملء هذا بواسطة JS. نعالج حالة الخطأ أو التحميل الأولي هنا --}}
                        @php
                            $currentSelectedGovernorateId = old('governorate_id', $user->area?->governorate_id ?? null);
                            $currentSelectedAreaId = old('area_id', $user->area_id ?? null);
                            $areasForCurrentGov = collect(); // افتراضيًا فارغة
                            if ($currentSelectedGovernorateId && isset($governorates)) {
                                $selectedGovObject = $governorates->firstWhere('id', $currentSelectedGovernorateId);
                                if ($selectedGovObject) {
                                    $areasForCurrentGov = $selectedGovObject->areas;
                                }
                            }
                        @endphp
                        <option value="">Select Area (Optional)...</option>
                        @if($areasForCurrentGov->isNotEmpty())
                            @foreach($areasForCurrentGov as $area)
                                <option value="{{ $area->id }}" {{ $currentSelectedAreaId == $area->id ? 'selected' : '' }}>
                                    {{ $area->name }}
                                </option>
                            @endforeach
                        @elseif(!$currentSelectedGovernorateId) {{-- إذا لم يتم اختيار محافظة بعد --}}
                             <option value="" disabled>Select Governorate First (Optional)...</option>
                        @endif
                    </select>
                    @error('area_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- حقل العنوان --}}
            <div class="row mb-3">
                <label for="address" class="col-sm-3 col-form-label">Address</label>
                <div class="col-sm-9">
                                                                    {{-- ▼▼▼ تعديل: استخدام بيانات $user --}}
                    <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address', $user->address) }}">
                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- حقل الوصف/نبذة --}}
            <div class="row mb-3">
                <label for="description_edit" class="col-sm-3 col-form-label">About / Description</label> {{-- تغيير ID --}}
                <div class="col-sm-9">
                                                                                                            {{-- ▼▼▼ تعديل: استخدام بيانات $user و ID مختلف --}}
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description_edit" name="description" rows="3">{{ old('description', $user->description) }}</textarea>
                     @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-sm-9 offset-sm-3">
                    <button type="submit" class="btn btn-gold me-2">Update User</button> {{-- تغيير النص --}}
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div> {{-- End Card Body --}}
</div> {{-- End Card --}}

<style> /* نفس الـ CSS المطلوب */
    .required::after { content: " *"; color: red; }
</style>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ▼▼▼ تعديل: استخدام IDs فريدة لصفحة التعديل ▼▼▼
    const governorateSelectEdit = document.getElementById('governorate_id_edit');
    const areaSelectEdit = document.getElementById('area_id_edit');

    function updateAreaOptionsEdit() {
        if (!governorateSelectEdit || !areaSelectEdit) return;

        const selectedOption = governorateSelectEdit.options[governorateSelectEdit.selectedIndex];
        areaSelectEdit.innerHTML = ''; // إفراغ قائمة المناطق الحالية

        areaSelectEdit.add(new Option('Select Area (Optional)...', ''));

        if (selectedOption && selectedOption.value && selectedOption.dataset.areas) {
            let areas = JSON.parse(selectedOption.dataset.areas);
            for (const areaId in areas) {
                if (areas.hasOwnProperty(areaId)) {
                    areaSelectEdit.add(new Option(areas[areaId], areaId));
                }
            }
            // استرجاع القيمة القديمة أو الحالية للمنطقة
            const currentAreaId = "{{ old('area_id', $user->area_id ?? '') }}";
            if (currentAreaId && areaSelectEdit.querySelector(`option[value="${currentAreaId}"]`)) {
                areaSelectEdit.value = currentAreaId;
            } else {
                 areaSelectEdit.value = ""; // إذا لم تكن القيمة الحالية ضمن الخيارات الجديدة
            }
        } else {
             areaSelectEdit.value = ""; // إذا لم يتم اختيار محافظة
        }
    }

    if (governorateSelectEdit) {
        governorateSelectEdit.addEventListener('change', updateAreaOptionsEdit);
        // تحديث عند تحميل الصفحة لملء المناطق إذا كانت هناك محافظة محددة مسبقًا (مهم للتعديل)
        updateAreaOptionsEdit();
    }
});
</script>
@endsection