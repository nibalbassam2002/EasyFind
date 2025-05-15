{{-- resources/views/dashboard/moderator/pending_properties.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Pending Properties for Review')

@section('breadcrumb-items')
    @parent
    <li class="breadcrumb-item">Moderation</li>
    <li class="breadcrumb-item active">Pending Properties</li>
@endsection

@section('contant')

    <div class="card-1">
        <div class="card-header">
            <h5 class="card-title mb-0">Properties waiting Approval</h5>
        </div>
        <div class="card-body">

            {{-- Display Session Messages --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    <i class="bi bi-x-octagon-fill me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive mt-3">
                <table class="table table-hover table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Title / Code</th>
                            <th>Category</th>
                            <th>Location</th>
                            <th>Listed By</th>
                            <th>Date Submitted</th>
                            <th class="text-center" style="min-width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendingProperties as $property)
                            <tr>
                                <td>{{ $property->id }}</td>
                                <td>
                                    <span class="fw-bold">{{ Str::limit($property->title, 35) }}</span>
                                    <small class="d-block text-muted">{{ $property->code }}</small>
                                </td>
                                <td>{{ $property->category?->name ?? 'N/A' }}</td>
                                <td>{{ $property->area?->name ?? 'N/A' }}, {{ $property->area?->governorate?->name ?? 'N/A' }}</td>
                                <td>{{ $property->user?->name ?? 'N/A' }}</td>
                                <td>{{ $property->created_at->format('d M Y, H:i') }}</td>
                                <td class="text-center">
                                    {{-- Approve Form --}}
                                    <form action="{{ route('moderator.properties.approve', $property->id) }}" method="POST" class="d-inline approve-form me-1">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                            <i class="bi bi-check-lg"></i><span class="d-none d-md-inline"> Approve</span>
                                        </button>
                                    </form>

                                    {{-- Reject Form --}}
                                    <form action="{{ route('moderator.properties.reject', $property->id) }}" method="POST" class="d-inline reject-form">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Reject">
                                            <i class="bi bi-x-lg"></i><span class="d-none d-md-inline"> Reject</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-check2-circle fs-3 d-block mb-2"></i>
                                    No pending properties found for review.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Links --}}
            @if ($pendingProperties->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $pendingProperties->links() }}
                </div>
            @endif

        </div> {{-- End Card Body --}}
    </div> {{-- End Card --}}

@endsection

@section('scripts')
    {{-- SweetAlert Library --}}
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Confirmation Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Approve Confirmation
            const approveForms = document.querySelectorAll('.approve-form');
            approveForms.forEach(form => {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'Approve Property?',
                        text: "This property will become visible on the site.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#198754',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, approve it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

             // Reject Confirmation
            const rejectForms = document.querySelectorAll('.reject-form');
            rejectForms.forEach(form => {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                     Swal.fire({
                        title: 'Reject Property?',
                        text: "This property will be marked as rejected.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, reject it!',
                        cancelButtonText: 'Cancel',
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