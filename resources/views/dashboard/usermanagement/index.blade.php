@extends('layouts.dashboard')

@section('title', 'User Management')

@section('breadcrumb-items')
    @parent

    <li class="breadcrumb-item">Admin</li>
    <li class="breadcrumb-item active">User Management</li>
@endsection
@section('contant')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Manage Users</h5>

            <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-gold ">
                <i class="bi bi-plus-lg"></i> Add New User
            </a>
        </div>
        <div class="card-body">

            {{-- عرض رسائل النجاح أو الخطأ --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive mt-3">
                <table class="table table-hover table-striped table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th> {{-- إضافة الهاتف --}}
                            <th>Role</th>
                            <th>Status</th>
                            <th>Location</th>
                            <th>Joined</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                                <td>
                                    <img src="{{ $user->profile_image ? asset('storage/images/' . $user->profile_image) : asset('assets/img/profile.jpg') }}"
                                        alt="{{ $user->name }}" width="40" height="40"
                                        class="rounded-circle object-fit-cover">



                                </td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone ?? 'N/A' }}</td>
                                <td>

                                    @php
                                        $roleClass = match (strtolower($user->role)) {
                                            'admin' => 'danger',
                                            'content_moderator' => 'warning',
                                            'property_lister' => 'info',
                                            default => 'secondary', // customer
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $roleClass }}">

                                        {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                    </span>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-{{ strtolower($user->status) == 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($user->area && $user->area->governorate)
                                        {{-- ▼▼▼ تم عكس الترتيب هنا ▼▼▼ --}}
                                        {{ $user->area->governorate->name }} - {{ $user->area->name }}
                                    @elseif($user->area)
                                        {{-- إذا كانت المحافظة غير موجودة للمنطقة، اعرض المنطقة فقط --}}
                                        {{ $user->area->name }} (No Governorate Assigned)
                                    @else
                                        N/A
                                    @endif
                                </td>

                                <td>{{ $user->created_at->format('d M Y') }}</td>
                                                            <td class="text-center table-actions-cell">
                                <div class="d-inline-flex align-items-center">
                                    {{-- ▼▼▼ بداية: زر عرض التفاصيل (جعله أولاً) ▼▼▼ --}}
                                    {{-- افترض أن لديك مسارًا لعرض تفاصيل المستخدم، مثلاً 'admin.users.show' --}}
                                    {{-- إذا لم يكن لديك، يمكنك توجيهه لصفحة التعديل مبدئيًا أو إنشاء صفحة عرض --}}
                                    <a href="{{ route('admin.users.show', $user->id) }}" {{-- <--- استبدل برابط العرض الصحيح --}}
                                       class="btn btn-sm btn-outline-info me-1 px-2" title="View User Details">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    {{-- ▲▲▲ نهاية: زر عرض التفاصيل ▲▲▲ --}}

                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-outline-gold me-1 px-2" title="Edit User">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>

                                    @if (Auth::id() !== $user->id)
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline delete-form"> {{-- تم إزالة ms-1 من هنا لأن زر العرض أصبح قبله --}}
                                            @csrf
                                            @method('DELETE')
                                            {{-- ▼▼▼ تعديل: تغيير لون زر الحذف ▼▼▼ --}}
                                            <button type="submit" class="btn btn-sm btn-outline-danger px-2" title="Delete User"> {{-- تغيير btn-outline-dark1 إلى btn-outline-danger --}}
                                                <i class="bi bi-trash-fill"></i> {{-- الأيقونة نفسها ستبقى بلون النص الافتراضي للزر، لكن إطار الزر سيكون أحمر --}}
                                            </button>
                                            {{-- ▲▲▲ نهاية: تعديل لون زر الحذف ▲▲▲ --}}
                                        </form>
                                    @endif
                                </div>
                            </td>
                            </tr>
                        @empty
                            <tr>
                                {{-- تحديث الرسالة --}}
                                <td colspan="10" class="text-center text-muted py-4">No users found. You can add one using
                                    the button above.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- روابط الترقيم --}}
            @if ($users->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{-- إضافة withQueryString للحفاظ على أي فلاتر مستقبلية --}}
                    {{ $users->withQueryString()->links() }}
                </div>
            @endif

        </div> {{-- End Card Body --}}
    </div> {{-- End Card --}}

@endsection

@section('script')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteForms = document.querySelectorAll('.delete-form');
            deleteForms.forEach(form => {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'هل أنت متأكد؟',
                        text: "سيتم حذف هذا المستخدم نهائياً!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'نعم، احذفه!',
                        cancelButtonText: 'إلغاء'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endsection
