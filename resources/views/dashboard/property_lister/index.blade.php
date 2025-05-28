@extends('layouts.dashboard')

@section('title', 'My Properties')

@section('breadcrumb-items')
    @parent
    <li class="breadcrumb-item active">My Properties</li>
@endsection

@section('contant')
    <div class="card shadow mb-4">
        <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold"><i class="bi bi-building me-2"></i>My Listed Properties</h5>
            <a href="{{ route('lister.properties.create') }}" class="btn btn-gold btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add New Property
            </a>
        </div>
        <div class="card-body pt-3">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- جدول العقارات --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle table-nowrap mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Code</th>
                            <th scope="col">Image</th>
                            <th scope="col">Title</th>
                            <th scope="col">Area</th>
                            <th scope="col" class="text-end">Price</th>
                            <th scope="col">Purpose</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($properties as $property)
                            <tr>
                                <td class="fw-medium">{{ $property->code }}</td>
                                <td>
                                    @php
                                        $images = json_decode($property->images, true);
                                        $firstImage = $images[0] ?? null;
                                        $imageUrl = asset('assets/img/placeholder.jpg'); // تأكد أن هذا المسار صحيح لصورتك الافتراضية
                                        if ($firstImage && Storage::disk('public')->exists($firstImage)) {
                                            $imageUrl = Storage::url($firstImage);
                                        }
                                    @endphp
                                    <img src="{{ $imageUrl }}" alt="{{ Str::limit($property->title, 20) }}"
                                        width="80" height="55" class="object-fit-cover rounded border">
                                </td>
                                <td>
                                    {{-- ▼▼▼ بداية التعديل: جعل العنوان رابطًا ▼▼▼ --}}
                                    <a href="{{ route('lister.properties.show', $property->id) }}"
                                        class="fw-semibold text-dark text-decoration-none property-title-link">
                                        {{ $property->title }}
                                    </a>
                                    {{-- ▲▲▲ نهاية التعديل ▲▲▲ --}}
                                    <div class="text-muted small">
                                        {{ $property->category?->name ?? 'N/A' }}
                                        @if ($property->subCategory)
                                            / {{ $property->subCategory?->name }}
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $property->listarea?->name ?? 'N/A' }}</td>
                                <td class="text-end">{{ number_format($property->price, 0) }} {{ $property->currency }}
                                </td>
                                <td>
                                    {{-- ... (كود عرض الغرض) ... --}}
                                </td>
                                <td>
                                    {{-- ... (كود عرض الحالة) ... --}}
                                </td>
                                <td class="text-center">
                                    {{-- ▼▼▼ إضافة زر عرض التفاصيل (اختياري، لأن العنوان أصبح رابطًا) ▼▼▼ --}}
                                    <a href="{{ route('lister.properties.show', $property->id) }}"
                                        class="btn btn-sm btn-outline-info me-1" title="View Details">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    {{-- ▲▲▲ نهاية زر عرض التفاصيل ▲▲▲ --}}
                                    <a href="{{ route('lister.properties.edit', $property->id) }}"
                                        class="btn btn-sm btn-outline-primary me-1" title="Edit"><i
                                            class="bi bi-pencil-square"></i></a>
                                    <form action="{{ route('lister.properties.destroy', $property->id) }}" method="POST"
                                        class="d-inline delete-property-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i
                                                class="bi bi-trash3"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty

                            <tr>
                                <td colspan="8">
                                    <div class="text-center p-5 my-4 border rounded bg-light">
                                        <i class="bi bi-journal-plus display-4 text-secondary mb-3 d-block"></i>

                                        <h4 class="fw-bold">No Properties Found Yet!</h4>
                                        <p class="text-muted mb-4">
                                            It looks like you haven't added any properties.<br>
                                            Click the button below to list your first property and reach potential clients.
                                        </p>
                                        <a href="{{ route('lister.properties.create') }}" class="btn btn-gold btn-lg">
                                            <i class="bi bi-plus-circle me-1"></i> Add Your First Property
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- روابط الترقيم --}}
            @if ($properties->hasPages())
                <div class="d-flex justify-content-center pt-3 mt-3 border-top">
                    {{ $properties->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@section('script')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteForms = document.querySelectorAll('.delete-property-form');
            deleteForms.forEach(form => {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33', // لون زر التأكيد (أحمر)
                        cancelButtonColor: '#3085d6', // لون زر الإلغاء (أزرق)
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit(); // إرسال الفورم الأصلي عند التأكيد
                        }
                    });
                });
            });
        });
    </script>
@endsection
