@extends('frontend.Layouts.frontend')

@section('title', 'My Notifications - EasyFind')

@push('styles')
    <style>
        .notifications-container {
            max-width: 800px;
            margin: auto;
        }

        .notification-card {
            background-color: #fff;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            transition: all 0.2s ease-in-out;
            margin-bottom: 1rem;
        }

        .notification-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.07);
        }

        .notification-card a {
            display: flex;
            align-items: flex-start;
            padding: 1rem 1.25rem;
            text-decoration: none;
            color: #495057;
        }

        .notification-card.unread {
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd;
            /* لمسة زرقاء للإشعارات الجديدة */
        }

        .notification-card .notification-icon {
            font-size: 1.75rem;
            min-width: 40px;
            text-align: center;
            margin-right: 1rem;
        }

        .notification-card .notification-content p {
            margin-bottom: 0.25rem;
            line-height: 1.5;
        }

        .notification-card .notification-content small {
            color: #6c757d;
            font-size: 0.8rem;
        }

        .no-notifications {
            text-align: center;
            padding: 4rem 2rem;
            color: #6c757d;
        }

        .no-notifications i {
            font-size: 3rem;
            display: block;
            margin-bottom: 1rem;
        }

        .notification-card {
            position: relative;
            /* ضروري لتحديد موضع زر الحذف */
        }

        .delete-notification-btn {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            border: none;
            background: none;
            color: #adb5bd;
            /* لون رمادي فاتح */
            font-size: 1.1rem;
            cursor: pointer;
            padding: 5px;
            line-height: 1;
            border-radius: 50%;
            transition: color 0.2s, background-color 0.2s;
        }

        .delete-notification-btn:hover {
            color: #dc3545;
            /* لون أحمر عند التمرير */
            background-color: #f8f9fa;
            /* خلفية خفيفة */
        }
    </style>
@endpush

@section('content')
    <div class="container py-5">
        <div class="notifications-container">
            <h2 class="mb-4 text-center">Notifications</h2>

            @forelse ($notifications as $notification)
                @php
                    $shortMessage = $notification->data['message'];
                    $fullMessage = $notification->data['full_message'] ?? $shortMessage;
                @endphp

                <div class="notification-card {{ $notification->unread() ? 'unread' : '' }}"
                    data-notification-id="{{ $notification->id }}">
                    {{-- ▼▼▼ هذا هو التعديل المطلوب في وسم <a> ▼▼▼ --}}
                    <a href="#" class="notification-trigger" data-id="{{ $notification->id }}"
                        data-icon="{{ $notification->data['icon'] ?? 'bi bi-info-circle' }}"
                        data-message="{{ $shortMessage }}" {{-- <-- الرسالة القصيرة هنا --}} data-full-message="{{ $fullMessage }}"
                        {{-- <-- الرسالة الكاملة هنا --}} data-time="{{ $notification->created_at->diffForHumans() }}"
                        data-url="{{ $notification->data['url'] ?? '#' }}"
                        data-is-unread="{{ $notification->unread() ? 'true' : 'false' }}">

                        <div class="notification-icon">
                            <i class="{{ $notification->data['icon'] ?? 'bi bi-info-circle' }}"></i>
                        </div>

                        <div class="notification-content flex-grow-1">
                            {{-- في القائمة، نعرض الرسالة الكاملة --}}
                            <p>{{ $fullMessage }}</p>
                            <small>{{ $notification->created_at->diffForHumans() }}</small>
                        </div>
                    </a>
                    <button class="delete-notification-btn" title="Delete Notification">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            @empty
                <div class="card">
                    <div class="card-body no-notifications">
                        <i class="bi bi-bell-slash"></i>
                        <p class="mb-0 h5">You don't have any notifications yet.</p>
                    </div>
                </div>
            @endforelse

            @if ($notifications->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ▼▼▼ هيكل الـ Modal يبقى كما هو ▼▼▼ --}}
    <div class="modal fade" id="notificationDetailModal" tabindex="-1" aria-labelledby="notificationDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="notificationDetailModalLabel">Notification Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="d-flex align-items-start">
                        <i id="modalNotificationIcon" class="me-3 fs-2 align-self-start"></i>
                        <div class="flex-grow-1">
                            <p id="modalNotificationMessage" class="mb-2"
                                style="white-space: pre-wrap; font-size: 1.05rem;"></p>
                            <small id="modalNotificationTime" class="text-muted"></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="#" id="modalNotificationActionLink" class="btn btn-primary" target="_blank"
                        style="display: none;">Go to Link</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // انتظر حتى يتم تحميل الصفحة بالكامل
    document.addEventListener('DOMContentLoaded', function() {
        
        // =======================================================
        // ▼▼▼ هذا هو الكود القديم الخاص بك (لفتح الـ Modal) ▼▼▼
        // =======================================================
        const notificationLinks = document.querySelectorAll('.notification-trigger');

        notificationLinks.forEach(link => {
            link.addEventListener('click', function(event) {
                event.preventDefault(); 
                showNotificationModal(this);
            });
        });

        // =======================================================
        // ▼▼▼ هذا هو الكود الجديد الذي نضيفه (لزر الحذف) ▼▼▼
        // =======================================================
        const deleteButtons = document.querySelectorAll('.delete-notification-btn');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                event.stopPropagation(); 

                const notificationCard = this.closest('.notification-card');
                const notificationId = notificationCard.dataset.notificationId;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteNotification(notificationId, notificationCard);
                    }
                });
            });
        });
    });

    // =======================================================
    // ▼▼▼ هذه هي دالة الـ Modal القديمة الخاصة بك (تبقى كما هي) ▼▼▼
    // =======================================================
    function showNotificationModal(clickedLink) {
        // ... (كل الكود داخل هذه الدالة يبقى كما هو تماماً)
        const notificationId = clickedLink.dataset.id;
        const iconClass = clickedLink.dataset.icon;
        const message = clickedLink.dataset.fullMessage;
        const time = clickedLink.dataset.time;
        const actionUrl = clickedLink.dataset.url;
        const isUnread = clickedLink.dataset.isUnread === 'true';

        document.getElementById('modalNotificationIcon').className = iconClass + ' me-3 fs-2 align-self-start';
        document.getElementById('modalNotificationMessage').textContent = message;
        document.getElementById('modalNotificationTime').textContent = time;

        const actionLink = document.getElementById('modalNotificationActionLink');
        if (actionUrl && actionUrl !== '#') {
            actionLink.href = actionUrl;
            actionLink.style.display = 'inline-block';
        } else {
            actionLink.style.display = 'none';
        }

        var notificationModal = new bootstrap.Modal(document.getElementById('notificationDetailModal'));
        notificationModal.show();

        if (isUnread) {
            const notificationCard = clickedLink.closest('.notification-card');
            if (notificationId && notificationCard) {
                fetch(`/notifications/${notificationId}/mark-as-read`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            notificationCard.classList.remove('unread');
                            clickedLink.dataset.isUnread = 'false';
                        }
                    })
                    .catch(error => console.error('Error marking notification as read from modal:', error));
            }
        }
    }

    // =======================================================
    // ▼▼▼ هذه هي دالة الحذف الجديدة التي نضيفها ▼▼▼
    // =======================================================
    function deleteNotification(id, cardElement) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch(`/notifications/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Deleted!', 'Your notification has been deleted.', 'success');

                cardElement.style.transition = 'opacity 0.5s, transform 0.5s';
                cardElement.style.opacity = '0';
                cardElement.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    cardElement.remove();
                    if (document.querySelectorAll('.notification-card').length === 0) {
                        const container = document.querySelector('.notifications-container');
                        const noNotificationsMessage = `
                            <div class="card">
                                <div class="card-body no-notifications">
                                    <i class="bi bi-bell-slash"></i>
                                    <p class="mb-0 h5">You don't have any notifications yet.</p>
                                </div>
                            </div>`;
                        container.insertAdjacentHTML('beforeend', noNotificationsMessage);
                    }
                }, 500);

            } else {
                Swal.fire('Failed!', data.message || 'Could not delete the notification.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error!', 'An error occurred while deleting the notification.', 'error');
        });
    }
</script>
@endpush
