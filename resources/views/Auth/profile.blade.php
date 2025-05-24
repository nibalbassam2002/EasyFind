@extends('layouts.dashboard')
@section('title', 'Profile')
@section('breadcrumb-items')
    <li class="breadcrumb-item active">Profile</li>
@endsection

@section('contant')

    <section class="section profile">
        <div class="row">
            <div class="col-xl-4">

                <div class="card">
                    <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
                        
                        <img src="{{ Auth::user()->profile_image ? asset('storage/images/' . Auth::user()->profile_image) : asset('assets/img/profile.jpg') }}"
                            alt="Profileee" class="rounded-circle">
                        <h2>{{ Auth::user()->name }}</h2>

                    </div>
                </div>

            </div>

            <div class="col-xl-8">

                <div class="card">
                    <div class="card-body pt-3">
                        <!-- Bordered Tabs -->
                        <ul class="nav nav-tabs nav-tabs-bordered">

                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab"
                                    data-bs-target="#profile-overview">Overview</button>
                            </li>

                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-edit">Edit
                                    Profile</button>
                            </li>

                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab"
                                    data-bs-target="#profile-settings">Settings</button>
                            </li>

                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab"
                                    data-bs-target="#profile-change-password">Change Password</button>
                            </li>

                        </ul>
                        <div class="tab-content pt-2">

                            <div class="tab-pane fade show active profile-overview" id="profile-overview">

                                <h5 class="card-title">About</h5>
                                <p class="small fst-italic">{{ Auth::user()->description }}</p>



                                <h5 class="card-title">Profile Details</h5>

                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label ">Full Name</div>
                                    <div class="col-lg-9 col-md-8">{{ Auth::user()->name }}</div>
                                </div>


                                <div class="row">
                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 label">Location</div> 
                                        <div class="col-lg-9 col-md-8">{{ Auth::user()->area?->name ?? 'Not Set' }}@if(Auth::user()->area), {{ Auth::user()->area->governorate?->name }}@endif</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label">Address</div>
                                    <div class="col-lg-9 col-md-8">{{ Auth::user()->address }}</div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label">Phone</div>
                                    <div class="col-lg-9 col-md-8">{{ Auth::user()->phone ?? 'N/A' }}</div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label">Email</div>
                                    <div class="col-lg-9 col-md-8">{{ Auth::user()->email }}</div>
                                </div>

                            </div>

                            <div class="tab-pane fade profile-edit pt-3" id="profile-edit">


                                @if (session('profile_success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        {{ session('profile_success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                @endif
                                @error('profile_image')
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        {{ $message }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                @enderror

                                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                                    @csrf
                                    @method('PATCH')
                                    <div class="row mb-3">
                                        <label for="profileImage"
                                            class="col-md-4 col-lg-3 col-form-label">ProfileImage</label>
                                        <div class="col-md-8 col-lg-9">
                                            <img id="output"
                                                src="{{ Auth::user()->profile_image ? asset('storage/images/' . Auth::user()->profile_image) : asset('assets/img/profile.jpg') }}">
                                            <div class="pt-2">
                                                <label for="upload" class="btn btn-primary btn-sm"
                                                    title="Upload new profile image">
                                                    <i class="bi bi-upload"></i>
                                                </label>
                                                <input type="file" id="upload" name="profile_image"
                                                    style="display:none;" onchange="loadFile(event)">



                                                <button type="button" onclick="confirmDeleteImage(this)"
                                                    class="btn btn-danger btn-sm" title="Remove my profile image"><i
                                                        class="bi bi-trash"></i></button>






                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="fullName" class="col-md-4 col-lg-3 col-form-label">Full Name</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="name" type="text"
                                                class="form-control @error('name') is-invalid @enderror" id="fullName"
                                                value="{{ old('name', Auth::user()->name) }}">
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="about" class="col-md-4 col-lg-3 col-form-label">About</label>
                                        <div class="col-md-8 col-lg-9">
                                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" id="about"
                                                style="height: 100px">{{ old('description', Auth::user()->description) }}</textarea>
                                            @error('description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="governorate_id" class="col-md-4 col-lg-3 col-form-label">Governorate</label> {{-- اختيارية؟ --}}
                                        <div class="col-md-8 col-lg-9">
                                            <select class="form-select @error('governorate_id') is-invalid @enderror" id="governorate_id" name="governorate_id"> {{-- name اختياري هنا --}}
                                                <option value="">Select Governorate (Optional)...</option>
                                                @isset($governorates)
                                                    @foreach ($governorates as $governorate)
                                                        <option value="{{ $governorate->id }}"
                                                                {{-- تحديد المحافظة الحالية للمستخدم --}}
                                                                {{ old('governorate_id', Auth::user()->area?->governorate_id ?? '') == $governorate->id ? 'selected' : '' }}
                                                                data-areas="{{ json_encode($governorate->areas->pluck('name', 'id')) }}">
                                                            {{ $governorate->name }}
                                                        </option>
                                                    @endforeach
                                                @endisset
                                            </select>
                                            {{-- لا يوجد @error هنا عادةً --}}
                                        </div>
                                    </div>
                                    
                                    {{-- قائمة المناطق --}}
                                    <div class="row mb-3">
                                        <label for="area_id" class="col-md-4 col-lg-3 col-form-label">Area</label> {{-- اختيارية؟ --}}
                                        <div class="col-md-8 col-lg-9">
                                            <select class="form-select @error('area_id') is-invalid @enderror" id="area_id" name="area_id"> {{-- هذا الحقل الذي يتم إرساله --}}
                                                <option value="" disabled selected>Select Governorate First (Optional)...</option>
                                                {{-- التعامل مع القيم القديمة/الحالية في حالة التعديل أو الخطأ --}}
                                                 @if(old('area_id') || Auth::user()->area_id)
                                                   @php
                                                       $selectedGovernorateId = old('governorate_id', Auth::user()->area?->governorate_id ?? null);
                                                       $selectedAreaId = old('area_id', Auth::user()->area_id ?? null);
                                                       $areasForSelectedGov = [];
                                                       // تأكد من وجود $governorates
                                                       if ($selectedGovernorateId && isset($governorates)) {
                                                           $selectedGov = $governorates->firstWhere('id', $selectedGovernorateId);
                                                           if($selectedGov) {
                                                               $areasForSelectedGov = $selectedGov->areas; // يفترض أنه تم تحميلها
                                                           }
                                                       }
                                                   @endphp
                                                    @if(!empty($areasForSelectedGov))
                                                       <option value="">Select Area (Optional)...</option> {{-- Default option --}}
                                                       @foreach($areasForSelectedGov as $area)
                                                           <option value="{{ $area->id }}" {{ $selectedAreaId == $area->id ? 'selected' : '' }}>
                                                               {{ $area->name }}
                                                           </option>
                                                       @endforeach
                                                    {{-- إذا لم تكن هناك قيمة قديمة أو حالية، اعرض الخيار الافتراضي --}}
                                                    @elseif (!old('governorate_id') && !Auth::user()->area_id)
                                                         <option value="" disabled selected>Select Governorate First (Optional)...</option>
                                                    @endif
                                                @else
                                                    <option value="" disabled selected>Select Governorate First (Optional)...</option>
                                                 @endif
                                            </select>
                                            @error('area_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="Address" class="col-md-4 col-lg-3 col-form-label">Address</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="address" type="text"
                                                class="form-control @error('address') is-invalid @enderror" id="Address"
                                                value="{{ old('address', Auth::user()->address) }}"> {{-- تأكد من وجود حقل address في جدول users --}}
                                            @error('address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="Phone" class="col-md-4 col-lg-3 col-form-label">Phone</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="phone" type="text"
                                                class="form-control @error('phone') is-invalid @enderror" id="Phone"
                                                value="{{ old('phone', Auth::user()->phone) }}"> {{-- تأكد من وجود حقل phone في جدول users --}}
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="Email" class="col-md-4 col-lg-3 col-form-label">Email</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="email" type="email"
                                                class="form-control @error('email') is-invalid @enderror" id="Email"
                                                value="{{ old('email', Auth::user()->email) }}">
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>


                                    <div class="text-center">
                                        <button type="submit" class="btn btn-gold">Save Changes</button>
                                    </div>
                                </form><!-- End Profile Edit Form -->

                            </div>

                            <div class="tab-pane fade pt-3" id="profile-settings">

                                <!-- Settings Form -->
                                <form>

                                    <div class="row mb-3">
                                        <label for="fullName" class="col-md-4 col-lg-3 col-form-label">Email
                                            Notifications</label>
                                        <div class="col-md-8 col-lg-9">
                                            <div class="form-check ">
                                                <input class="form-check-input" type="checkbox" id="changesMade" checked>
                                                <label class="form-check-label" for="changesMade">
                                                    Changes made to your account
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="newProducts" checked>
                                                <label class="form-check-label" for="newProducts">
                                                    Information on new products and services
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="proOffers">
                                                <label class="form-check-label" for="proOffers">
                                                    Marketing and promo offers
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="securityNotify"
                                                    checked disabled>
                                                <label class="form-check-label" for="securityNotify">
                                                    Security alerts
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" class="btn btn-gold">Save Changes</button>
                                    </div>
                                </form><!-- End settings Form -->

                            </div>

                            <div class="tab-pane fade pt-3" id="profile-change-password">
                                <!-- Change Password Form -->
                                <form method="POST" action="{{ route('profile.changePassword') }}">
                                    @csrf
                                    @method('PATCH')
                            
                                    @if (session('success'))
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            {{ session('success') }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    @endif
                            
                                    <div class="row mb-3">
                                        <label for="currentPassword" class="col-md-4 col-lg-3 col-form-label">Current Password</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="current_password" type="password" class="form-control @error('current_password') is-invalid @enderror" id="currentPassword">
                                            @error('current_password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                            
                                    <div class="row mb-3">
                                        <label for="newPassword" class="col-md-4 col-lg-3 col-form-label">New Password</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="new_password" type="password" class="form-control @error('new_password') is-invalid @enderror" id="newPassword">
                                            @error('new_password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                            
                                    <div class="row mb-3">
                                        <label for="renewPassword" class="col-md-4 col-lg-3 col-form-label">Re-enter New Password</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="new_password_confirmation" type="password" class="form-control" id="renewPassword">
                                        </div>
                                    </div>
                            
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-gold">Change Password</button>
                                    </div>
                                </form>
                            </div>

                        </div><!-- End Bordered Tabs -->

                    </div>
                </div>

            </div>

        </div>



    @endsection
    @section('script')
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            // Function to preview the uploaded image
            var loadFile = function(event) {
                var output = document.getElementById('output');
                if (event.target.files && event.target.files[0]) {
                    output.src = URL.createObjectURL(event.target.files[0]);
                    output.onload = function() {
                        URL.revokeObjectURL(output.src) // free memory
                    }
                }
            };

            // Function to confirm image deletion
            function confirmDeleteImage(element) {
                Swal.fire({
                    title: 'هل أنت متأكد؟',
                    text: "لن تتمكن من التراجع عن هذا!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'نعم، احذفها!',
                    cancelButtonText: 'إلغاء'
                }).then((result) => {
                    if (result.isConfirmed) {
                        performImageDelete();
                    }
                })
            }

            // Function to perform the image deletion using Axios
            function performImageDelete() {
                axios.delete("{{ route('profile.deleteImage') }}")
                    .then(function(response) {
                        Swal.fire(
                            'تم الحذف!',
                            response.data.message,
                            'success'
                        );

                        // تحديث جميع الصور في الصفحة
                        document.getElementById('output').src = response.data.default_image;

                        // تحديث صورة الهيدر
                        let headerImage = document.querySelector('.nav-profile img.rounded-circle');
                        if (headerImage) {
                            headerImage.src = response.data.default_image;
                        }

                        // تحديث صورة Overview إذا كانت موجودة
                        let overviewImage = document.querySelector('.profile-card img.rounded-circle');
                        if (overviewImage) {
                            overviewImage.src = response.data.default_image;
                        }
                    })
                    .catch(function(error) {
                        console.log(error);
                        let errorMessage = 'حدث خطأ ما أثناء الحذف.';
                        if (error.response && error.response.data && error.response.data.message) {
                            errorMessage = error.response.data.message;
                        }
                        Swal.fire(
                            'خطأ!',
                            errorMessage,
                            'error'
                        );
                    });
            }
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const governorateSelect = document.getElementById('governorate_id');
                const areaSelect = document.getElementById('area_id');
            
                function updateAreaOptions() {
                    const selectedOption = governorateSelect.options[governorateSelect.selectedIndex];
                    areaSelect.innerHTML = ''; // إفراغ قائمة المناطق الحالية
            
                     // إضافة الخيار الافتراضي (الاختياري)
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
                             areaSelect.value = "";
                            return;
                        }
            
                        // إضافة المناطق الجديدة
                        for (const areaId in areas) {
                            if (areas.hasOwnProperty(areaId)) {
                                areaSelect.add(new Option(areas[areaId], areaId));
                            }
                        }
            
                        // إعادة تحديد المنطقة القديمة إذا كانت موجودة (مفيد عند خطأ التحقق)
                         const oldAreaId = "{{ old('area_id', Auth::user()->area_id ?? '') }}"; // استخدم القيمة الحالية كـ fallback
                        if (oldAreaId && areaSelect.querySelector(`option[value="${oldAreaId}"]`)) {
                            areaSelect.value = oldAreaId;
                        } else {
                            areaSelect.value = ""; // العودة للخيار الافتراضي
                        }
            
                    } else {
                        // إذا لم يتم اختيار محافظة
                         areaSelect.value = "";
                    }
                }
            
                // ربط حدث التغيير بقائمة المحافظات
                if (governorateSelect) {
                    governorateSelect.addEventListener('change', updateAreaOptions);
            
                    // تحديث القائمة عند تحميل الصفحة إذا كانت هناك قيمة للمحافظة
                    if (governorateSelect.value) {
                        updateAreaOptions();
                    }
                } else {
                    console.error("Governorate select element not found.");
                }
            });
            </script>
    @endsection
