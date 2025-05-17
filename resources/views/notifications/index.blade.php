@extends('layouts.dashboard')
@section('title', 'All of notifications')
@section('breadcrumb-items')
    <li class="breadcrumb-item active">Notification</li>
@endsection

@section('contant') 

<div class="card">
    <div class="card-body">
        <h5 class="card-title">All Notification</h5>

        @if ($notifications->count() > 0)
            <ul class="list-group list-group-flush">
                @foreach ($notifications as $notification)
                    <li class="list-group-item d-flex justify-content-between align-items-start py-3 {{ $notification->unread() ? 'list-group-item-light fw-bold' : '' }}">
                        <a href="{{ $notification->data['url'] ?? '#' }}" class="text-decoration-none text-dark d-flex align-items-start w-100">
                            <i class="{{ $notification->data['icon'] ?? 'bi bi-info-circle' }} me-3 fs-4 {{ $notification->unread() ? 'text-primary' : 'text-muted' }}"></i>
                            <div class="flex-grow-1">
                                <p class="mb-1">{{ $notification->data['message'] }}</p>
                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="mt-4 d-flex justify-content-center">
                {{ $notifications->links() }} 
            </div>

        @else
            <div class="alert alert-info text-center mt-3" role="alert">
               You don't have any notifications at the moment.
            </div>
        @endif
    </div>
</div>

@endsection