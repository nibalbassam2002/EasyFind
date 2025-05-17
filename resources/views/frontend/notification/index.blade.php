@extends('frontend.Layouts.frontend')

@section('title', 'My notification - EasyFind')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <h2 class="mb-4 text-center">إشعاراتي</h2>
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    @if ($notifications->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach ($notifications as $notification)
                                <li class="list-group-item py-3 px-3 px-md-4 {{ $notification->unread() ? 'bg-light-subtle fw-medium' : '' }}">
                                    {{-- تعديل الرابط هنا ليستدعي دالة JS --}}
                                    <a href="#"
                                       onclick="showNotificationModal(event, '{{ $notification->id }}', '{{ $notification->data['icon'] ?? 'bi bi-info-circle' }}', '{{ addslashes(htmlspecialchars($notification->data['message'])) }}', '{{ $notification->created_at->diffForHumans() }}', '{{ $notification->unread() }}', '{{ $notification->data['url'] ?? '#' }}')"
                                       class="text-decoration-none d-flex align-items-start w-100 {{ $notification->unread() ? 'text-dark' : 'text-secondary' }}">
                                        <i class="{{ $notification->data['icon'] ?? 'bi bi-info-circle' }} me-3 fs-4 align-self-center"></i>
                                        <div class="flex-grow-1">
                                            <p class="mb-1 small">{{ $notification->data['message'] }}</p>
                                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-bell-slash fs-1 mb-3"></i>
                            <p class="mb-0">لا توجد لديك أي إشعارات حالياً.</p>
                        </div>
                    @endif
                </div>
            </div>

            @if ($notifications->count() > 0 && $notifications->hasPages())
            <div class="mt-4 d-flex justify-content-center">
                {{ $notifications->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ▼▼▼ إضافة هيكل الـ Modal هنا (سيكون مخفيًا بشكل افتراضي) ▼▼▼ --}}
<div class="modal fade" id="notificationDetailModal" tabindex="-1" aria-labelledby="notificationDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"> {{-- modal-lg لجعله أعرض قليلاً --}}
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationDetailModalLabel">تفاصيل الإشعار</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-start">
                    <i id="modalNotificationIcon" class="me-3 fs-2 align-self-start"></i> {{-- تم تكبير الأيقونة قليلاً --}}
                    <div class="flex-grow-1">
                        <p id="modalNotificationMessage" class="mb-2" style="white-space: pre-wrap;"></p>
                        <small id="modalNotificationTime" class="text-muted"></small>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                {{-- زر اختياري للانتقال إلى الرابط الأصلي للإشعار --}}
                <a href="#" id="modalNotificationActionLink" class="btn btn-primary" target="_blank" style="display: none;">الانتقال للرابط</a>
            </div>
        </div>
    </div>
</div>
{{-- ▲▲▲ نهاية هيكل الـ Modal ▲▲▲ --}}

@endsection

@push('scripts') {{-- أو @section('scripts') إذا كنت تستخدمه في الـ layout --}}
<script>
    // احتفظ بدوال markFrontendNotificationAsRead و updateFrontendNotificationBadge و getTransChoice
    // التي كانت لديك في الـ layout الرئيسي للـ frontend، فهي لا تزال مفيدة للقائمة المنسدلة.

    // دالة جديدة لعرض الـ Modal
    function showNotificationModal(event, notificationId, iconClass, message, time, isUnread, actionUrl) {
        event.preventDefault(); // منع سلوك الرابط الافتراضي

        // ملء محتوى الـ Modal
        document.getElementById('modalNotificationIcon').className = iconClass + ' me-3 fs-2 align-self-start'; // إعادة تعيين الكلاس بالكامل
        document.getElementById('modalNotificationMessage').innerHTML = message.replace(/\\n/g, '<br>'); // استبدال \n بـ <br> لعرض الأسطر الجديدة
        document.getElementById('modalNotificationTime').textContent = time;

        const actionLink = document.getElementById('modalNotificationActionLink');
        if (actionUrl && actionUrl !== '#') {
            actionLink.href = actionUrl;
            actionLink.style.display = 'inline-block';
        } else {
            actionLink.style.display = 'none';
        }

        // فتح الـ Modal
        var notificationModal = new bootstrap.Modal(document.getElementById('notificationDetailModal'));
        notificationModal.show();

        // تمييز الإشعار كمقروء إذا لم يكن مقروءًا بالفعل (باستخدام دالة AJAX التي لديك)
        // ونقوم بتحديث الـ UI للعنصر في القائمة
        if (isUnread === true || isUnread === 'true' || isUnread === 1 || isUnread === '1') { // تحقق مرن من القيمة
            const listItem = event.currentTarget.closest('.list-group-item');
            if (notificationId && listItem) {
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
                        listItem.classList.remove('bg-light-subtle', 'fw-medium');
                        listItem.classList.remove('text-dark'); // إذا كنت تضيفه
                        listItem.classList.add('text-secondary'); // أو أي كلاس للإشعار المقروء
                        const iconInList = listItem.querySelector('i');
                        if (iconInList) {
                            iconInList.classList.remove('text-primary');
                            iconInList.classList.add('text-muted');
                        }
                        // قد تحتاج أيضًا لتحديث عداد الإشعارات في الهيدر
                        if (typeof updateFrontendNotificationBadge === 'function') {
                            updateFrontendNotificationBadge();
                        }
                    }
                })
                .catch(error => console.error('Error marking notification as read from modal:', error));
            }
        }
    }
</script>
@endpush