@extends('layouts.dashboard')

@section('title', 'Viewing Requests')

@section('breadcrumb-items')
    @parent
    <li class="breadcrumb-item active">Viewing Requests</li>
@endsection

@section('contant')
    <div class="card shadow mb-4">
        <div class="card-header bg-light py-3">
            <h5 class="card-title mb-0 fw-bold"><i class="bi bi-calendar2-check me-2"></i>My Viewing Requests & Appointments
            </h5>
        </div>
        <div class="card-body pt-3">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover align-middle table-nowrap mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Property</th>
                            <th>Requester / Client</th>
                            <th>Status</th>
                            <th>Details</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($viewingRequests as $request)
                            <tr>
                                <td>
                                    @if ($request->property)
                                        {{-- تحقق مباشرة من الخاصية التي أضفناها في الكنترولر --}}
                                        <a href="{{ route('lister.properties.show', $request->property->id) }}">
                                            {{ $request->property->title }}
                                        </a>
                                    @else
                                        <span class="text-muted">Property data missing</span>
                                    @endif
                                </td>
                                <td>{{ $request->user->name ?? 'N/A' }}</td>
                                <td>
                                    @if ($request->type === 'viewing_confirmed')
                                        <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>
                                            Confirmed</span>
                                    @elseif (data_get($request, 'metadata.status') === 'pending')
                                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>
                                            Pending Response</span>
                                    @else
                                        <span
                                            class="badge bg-secondary text-capitalize">{{ str_replace('_', ' ', data_get($request, 'metadata.status', 'Archived')) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($request->type === 'viewing_confirmed')
                                        Appt. on:
                                        <br><strong>{{ \Carbon\Carbon::parse($request->metadata['confirmed_slot'])->format('D, M j, Y, g:i A') }}</strong>
                                    @else
                                        Requested on: {{ $request->created_at->format('M j, Y') }}
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('chat.index', ['activeConversation' => $request->conversation_id]) }}"
                                        class="btn btn-sm btn-info me-1" title="Open Chat to Respond">
                                        <i class="bi bi-chat-dots-fill"></i> Respond
                                    </a>

                                    @if (
                                        ($request->type === 'viewing_confirmed' &&
                                            \Carbon\Carbon::parse(data_get($request, 'metadata.confirmed_slot'))->isFuture()) ||
                                            data_get($request, 'metadata.status') === 'pending')
                                        <form id="cancel-form-{{ $request->id }}"
                                            action="{{ route('dashboard.viewingRequests.cancel', $request->id) }}"
                                            method="POST" class="d-inline">
                                            @csrf
                                            {{-- غير نوع الزر من submit إلى button واستدعي دالة JavaScript --}}
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="confirmCancel('{{ $request->id }}')"
                                                title="Cancel Request/Appointment">
                                                <i class="bi bi-x-circle"></i> Cancel
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="bi bi-calendar-x fs-1 text-muted"></i>
                                    <h5 class="mt-2">No viewing requests or appointments found.</h5>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($viewingRequests->hasPages())
                <div class="d-flex justify-content-center pt-3 mt-3 border-top">
                    {{ $viewingRequests->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
@section('script')
    {{-- أولاً: نقوم بتضمين مكتبة SweetAlert --}}
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- ثانياً: نُعرّف الدالة الخاصة بنا --}}
    <script>
        function confirmCancel(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, cancel it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('cancel-form-' + id).submit();
                }
            });
        }
    </script>
@endsection