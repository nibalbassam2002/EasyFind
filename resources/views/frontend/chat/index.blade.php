@extends('frontend.Layouts.frontend')
@section('title', 'My Chats - EasyFind')

@push('styles')
    {{-- CSS لا تغيير هنا --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .chat-container-wrapper {
            height: calc(100vh - 77px);
            display: flex;
            overflow: hidden;
        }

        .chat-container-wrapper .row {
            height: 100%;
            width: 100%;
        }

        .chat-sidebar {
            width: 100%;
            max-width: 320px;
            border-right: 1px solid #e0e0e0;
            display: flex;
            flex-direction: column;
            background-color: #fff;
        }

        .chat-sidebar-header {
            padding: 1rem;
            border-bottom: 1px solid #e0e0e0;
        }

        .chat-sidebar-header h5 {
            margin-bottom: 0;
            font-weight: 600;
        }

        .conversations-list {
            list-style: none;
            margin: 0;
            padding: 0;
            overflow-y: auto;
            flex-grow: 1;
        }

        .conversation-item {
            position: relative;
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .conversation-item:hover,
        .conversation-item.active {
            background-color: #f1f3f5;
        }

        .conversation-item.active {
            border-left: 4px solid #ffca28;
        }

        .conversation-item .avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 12px;
        }

        .conversation-item .chat-info {
            flex-grow: 1;
            overflow: hidden;
            margin-right: 10px;
        }

        .conversation-item .chat-time-and-badge {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            flex-shrink: 0;
        }

        .conversation-item .name {
            font-weight: 600;
            margin-bottom: 2px;
        }

        .conversation-item .last-message {
            font-size: 0.85rem;
            color: #6c757d;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .conversation-item .chat-time {
            font-size: 0.75rem;
            color: #9e9e9e;
            margin-left: 10px;
            flex-shrink: 0;
            transition: opacity 0.2s;
        }

        .chat-window {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .chat-window-header {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e0e0e0;
            background: #fff;
        }

        .chat-window-header .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 12px;
        }

        .chat-window-header .user-name {
            font-weight: 600;
        }

        .messages-area {
            flex-grow: 1;
            overflow-y: auto;
            padding: 1rem;
            background-color: #f5f5f5;
            display: flex;
            flex-direction: column;
        }

        .message-item {
            display: flex;
            max-width: 75%;
            align-items: flex-end;
            margin-bottom: 2px;
        }

        .message-item.sent {
            align-self: flex-end;
        }

        .message-item.received {
            align-self: flex-start;
        }

        .message-item .avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
            transition: visibility 0.1s;
            visibility: hidden;
        }

        .message-item.is-first-in-group {
            margin-top: 1rem;
        }

        .message-item.is-first-in-group .avatar {
            visibility: visible;
        }

        .message-item.sent .avatar {
            margin-left: 8px;
            margin-right: 0;
        }

        .message-item.received .avatar {
            margin-right: 8px;
            margin-left: 0;
        }

        .message-content {
            padding: 8px 12px;
            border-radius: 18px;
            position: relative;
            word-break: break-word;
        }

        .message-content a {
            color: #0d6efd;
            text-decoration: underline;
        }

        .message-item.sent .message-content {
            background-color: #ffca28;
            color: #333;
            border-bottom-right-radius: 4px;
        }

        .message-item.received .message-content {
            background-color: #fff;
            border: 1px solid #e0e0e0;
            border-bottom-left-radius: 4px;
        }

        .message-time {
            display: block;
            font-size: 0.7rem;
            color: #777;
            margin-top: 4px;
            text-align: right;
        }

        .message-content {
            direction: rtl;
            text-align: right;
        }

        .message-content a,
        .message-content span[dir="ltr"] {
            direction: ltr;
            display: inline-block;
            text-align: left;
        }

        .delete-conversation-btn {
            position: absolute;
            top: 50%;
            right: 0.5rem;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #adb5bd;
            cursor: pointer;
            padding: 0.4rem;
            line-height: 1;
            border-radius: 50%;
            display: block;
            z-index: 10;
            transition: all 0.2s ease;
            opacity: 0;
        }

        .conversation-item:hover .delete-conversation-btn {
            opacity: 1;
        }

        .conversation-item:hover .chat-time {
            opacity: 0;
        }

        .delete-conversation-btn:hover {
            color: #fff;
            background-color: #dc3545;
        }

        #no-conversation-selected {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            flex-direction: column;
            color: #9e9e9e;
            text-align: center;
        }

        #no-conversation-selected i {
            font-size: 4rem;
        }

        .system-message {
            width: 100%;
            max-width: 100%;
            justify-content: center;
        }

        .system-message .card,
        .system-message .alert {
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .flatpickr-months .flatpickr-month {
            height: 34px !important;
            line-height: 34px !important;
            background: transparent !important;
            color: inherit !important;
        }

        .flatpickr-months .flatpickr-prev-month,
        .flatpickr-months .flatpickr-next-month {
            height: 34px !important;
            width: 14% !important;
            top: 0 !important;
            padding: 0 !important;
        }

        .message-card {
            width: 450px;
            max-width: 100%;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
        }

        @media (max-width: 991.98px) {
            .chat-window {
                display: none;
            }

            .mobile-chat-view .chat-sidebar {
                display: none;
            }

            .mobile-chat-view .chat-window {
                display: flex;
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid chat-container-wrapper" id="chatContainer">
        <div class="row g-0 h-100 w-100">
            {{-- Sidebar --}}
            <div class="col-lg-4 col-xl-3 chat-sidebar" id="chatSidebar">
                <div class="chat-sidebar-header">
                    <h5>Chats</h5>
                </div>
                <ul class="list-group list-group-flush conversations-list" id="conversationsList">
                    @forelse($conversations as $conversation)
                        @include('frontend.chat.partials.conversation-item', [
                            'conversation' => $conversation,
                        ])
                    @empty
                        <li class="text-center text-muted p-5 list-group-item">
                            <i class="bi bi-chat-dots fs-1 d-block mb-2"></i>No conversations yet.
                        </li>
                    @endforelse
                </ul>
            </div>

            {{-- Chat Window --}}
            <div class="col-lg-8 col-xl-9 chat-window" id="chatWindow">
                {{-- Placeholder --}}
                <div id="no-conversation-selected"
                    class="d-flex align-items-center justify-content-center h-100 flex-column text-center text-muted">
                    <i class="bi bi-chat-square-dots fs-1"></i>
                    <p class="lead mt-3">Select a conversation to start chatting</p>
                </div>

                {{-- Active Chat Container --}}
                <div class="d-flex flex-column h-100 d-none" id="active-chat-container">
                    {{-- Header --}}
                    <div class="chat-window-header" id="activeChatHeader">
                        <button class="btn me-2 p-1" id="backToConversationsBtn" type="button" title="Back to chats"><i
                                class="bi bi-arrow-left fs-5"></i></button>
                        <img src="" alt="Avatar" class="avatar" id="activeChatAvatar">
                        <div>
                            <div class="user-name" id="activeChatUserName"></div>
                        </div>
                    </div>
                    {{-- Messages Area --}}
                    <div class="messages-area" id="messagesArea"></div>
                    {{-- Input Area --}}
                    <div class="chat-input-area p-2 bg-white border-top">
                        <form id="sendMessageForm">
                            <div class="input-group">
                                {{-- زر طلب المعاينة --}}
                                <button class="btn d-none" type="button" id="requestViewingBtn" title="Request a Viewing"
                                    data-bs-toggle="modal" data-bs-target="#requestViewingModal">
                                    <i class="bi bi-calendar-check-fill fs-5 text-secondary"></i>
                                </button>

                                {{-- الزر الجديد: تقديم عرض (مخفي افتراضياً) --}}
                                <button class="btn d-none" type="button" id="makeOfferBtn" title="Make an Offer"
                                    data-bs-toggle="modal" data-bs-target="#makeOfferModal">
                                    <i class="bi bi-cash-coin fs-5 text-success"></i>
                                </button>

                                <input type="text" class="form-control" name="body" placeholder="Type a message..."
                                    autocomplete="off" required>
                                <button class="btn" type="submit" title="Send Message">
                                    <i class="bi bi-send-fill fs-5 text-warning"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- نافذة طلب المعاينة - لا تغيير هنا --}}
    <div class="modal fade" id="requestViewingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Suggest Viewing Times</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Please suggest up to 3 preferred time slots for the property viewing.</p>
                    <form id="requestViewingForm">
                        <div class="mb-3"><label class="form-label">Suggestion 1</label><input type="text"
                                class="form-control datetime-picker" placeholder="Select Date and Time" name="slots[]"
                                required></div>
                        <div class="mb-3"><label class="form-label">Suggestion 2 (Optional)</label><input type="text"
                                class="form-control datetime-picker" placeholder="Select Date and Time" name="slots[]">
                        </div>
                        <div class="mb-3"><label class="form-label">Suggestion 3 (Optional)</label><input type="text"
                                class="form-control datetime-picker" placeholder="Select Date and Time" name="slots[]">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="sendViewingRequestBtn">Send Request</button>
                </div>
            </div>
        </div>
    </div>

    {{-- النافذة المنبثقة الجديدة: تقديم عرض (Make Offer Modal) --}}
    <div class="modal fade" id="makeOfferModal" tabindex="-1" aria-labelledby="makeOfferModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="makeOfferModalLabel">Make an Offer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="makeOfferForm">
                        <input type="hidden" name="property_id" id="offer_property_id">
                        <input type="hidden" name="viewing_request_message_id" id="offer_viewing_request_id">

                        <div class="mb-3">
                            <label for="offer_amount" class="form-label">Offer Amount ($)</label>
                            <input type="number" class="form-control" id="offer_amount" name="amount"
                                placeholder="e.g., 50000" required min="1" step="any">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="payment_online"
                                    value="online" checked>
                                <label class="form-check-label" for="payment_online">
                                    Secure Online Deposit (Simulated)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method"
                                    id="payment_offline" value="offline">
                                <label class="form-check-label" for="payment_offline">
                                    Arrange Offline Payment
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="offer_notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="offer_notes" name="notes" rows="3"
                                placeholder="Any additional terms or conditions..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="sendOfferBtn">Send Offer</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Libraries --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // تهيئة Flatpickr
            flatpickr(".datetime-picker", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                time_24hr: false,
                monthSelectorType: 'dropdown',
                appendTo: document.body,
                minDate: new Date()
            });

            // --- تعريف المتغيرات ---
            const activeChatContainer = document.getElementById('active-chat-container');
            const conversationsList = document.getElementById('conversationsList');
            const noConversationDiv = document.getElementById('no-conversation-selected');
            const messagesArea = document.getElementById('messagesArea');
            const sendMessageForm = document.getElementById('sendMessageForm');
            const backToConversationsBtn = document.getElementById('backToConversationsBtn');
            const requestViewingModalEl = document.getElementById('requestViewingModal');
            const requestViewingModal = requestViewingModalEl ? new bootstrap.Modal(requestViewingModalEl) : null;
            const sendViewingRequestBtn = document.getElementById('sendViewingRequestBtn');
            const requestViewingForm = document.getElementById('requestViewingForm');
            const makeOfferModalEl = document.getElementById('makeOfferModal');
            const makeOfferModal = makeOfferModalEl ? new bootstrap.Modal(makeOfferModalEl) : null;
            const sendOfferBtn = document.getElementById('sendOfferBtn');
            const makeOfferForm = document.getElementById('makeOfferForm');

            let currentConversationId = null;
            let isLoading = false;
            const currentUserId = {{ Auth::id() }};


            // --- الدوال المساعدة ---
            function linkify(text) {
                if (!text) return '';
                const urlRegex = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
                return text.replace(urlRegex, url =>
                    `<span dir="ltr"><a href="${url}" target="_blank" rel="noopener noreferrer">${url}</a></span>`
                );
            }

            function scrollToBottom() {
                setTimeout(() => {
                    messagesArea.scrollTop = messagesArea.scrollHeight;
                }, 50);
            }

            // function updateActionButtons(messages, isOwner) {
            //     const requestBtn = document.getElementById('requestViewingBtn');
            //     const offerBtn = document.getElementById('makeOfferBtn');
            //     const offerPropertyIdInput = document.getElementById('offer_property_id');
            //     const now = new Date();

            //     // إخفاء الأزرار بشكل افتراضي
            //     requestBtn.classList.add('d-none');
            //     offerBtn.classList.add('d-none');

            //     // البائع لا يقوم بهذه الإجراءات
            //     if (isOwner) {
            //         return;
            //     }

            //     // --- المنطق الجديد والذكي ---

            //     // 1. ابحث عن آخر عرض تم تقديمه
            //     const lastOfferMade = messages
            //         .filter(msg => msg.type === 'offer_made')
            //         .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))[0];

            //     // 2. إذا كان هناك عرض، تحقق من حالته
            //     if (lastOfferMade) {
            //         const offerMetadata = lastOfferMade.metadata;
            //         // إذا كان العرض لا يزال نشطاً (في انتظار الرد أو في انتظار الدفع)، لا تفعل شيئاً
            //         if (offerMetadata.status === 'pending' || (offerMetadata.status === 'accepted' && !offerMetadata
            //                 .payment_simulated)) {
            //             return; // نتوقف هنا، الأزرار على البطاقة هي المسؤولة
            //         }
            //         // إذا كان العرض مغلقاً (مرفوض أو تم الدفع)، فسنسمح ببدء دورة جديدة (نكمل للأسفل)
            //     }

            //     // 3. ابحث عن آخر موعد معاينة مؤكد
            //     const lastConfirmedViewing = messages
            //         .filter(msg => msg.type === 'viewing_confirmed' && msg.metadata?.confirmed_slot)
            //         .sort((a, b) => new Date(b.metadata.confirmed_slot) - new Date(a.metadata.confirmed_slot))[0];

            //     // 4. إذا كان هناك موعد مؤكد، تحقق مما إذا كان يجب إظهار زر "Make an Offer"
            //     if (lastConfirmedViewing) {
            //         // هل هذا الموعد أدى إلى عرض مغلق بالفعل؟
            //         const hasBeenDealtWith = lastOfferMade && lastOfferMade.metadata.original_message_id ===
            //             lastConfirmedViewing.metadata.original_message_id;

            //         const appointmentDate = new Date(lastConfirmedViewing.metadata.confirmed_slot);

            //         // أظهر زر العرض فقط إذا: تاريخ الموعد قد مضى، وهذا الموعد لم يتم التعامل معه بعد
            //         if (appointmentDate < now && !hasBeenDealtWith) {
            //             const propertyId = lastConfirmedViewing.metadata.property_id;
            //             offerBtn.classList.remove('d-none');
            //             offerBtn.dataset.propertyId = propertyId;
            //             offerPropertyIdInput.value = propertyId;
            //             return; // أظهرنا الزر، نتوقف هنا
            //         }
            //     }

            //     // 5. الحالة الافتراضية: تحقق مما إذا كان يمكن طلب موعد معاينة جديد
            //     const isPendingRequest = messages.some(msg => msg.type === 'viewing_request' && (!msg.metadata
            //         .status || msg.metadata.status === 'pending'));
            //     const isFutureAppointment = messages.some(msg => {
            //         if (msg.type !== 'viewing_confirmed' || !msg.metadata?.confirmed_slot || new Date(msg
            //                 .metadata.confirmed_slot) < now) {
            //             return false; // ليس موعداً مستقبلياً
            //         }

            //         // الآن، تحقق مما إذا كان هذا الموعد قد أدى إلى صفقة بالفعل
            //         const relatedOffer = messages.find(offer =>
            //             offer.type === 'offer_made' &&
            //             offer.metadata?.original_message_id === msg.metadata?.original_message_id
            //         );

            //         // إذا لم نجد عرضاً مرتبطاً بهذا الموعد، فهو لا يزال "نشطاً"
            //         // إذا وجدنا عرضاً، فهو ليس نشطاً (لأنه تمت معالجته)
            //         return !relatedOffer;
            //     });

            //     if (!isPendingRequest && !isFutureAppointment) {
            //         // لا يوجد أي شيء نشط، أظهر زر "Request Viewing"
            //         requestBtn.classList.remove('d-none');

            //         // تحديث propertyId لزر طلب المعاينة
            //         let lastDiscussedPropertyId = null;
            //         const propertyUrlRegex = /properties\/show\/(\d+)/;
            //         const propertyMessage = [...messages].reverse().find(msg => msg.body.match(propertyUrlRegex));
            //         if (propertyMessage) {
            //             lastDiscussedPropertyId = propertyMessage.body.match(propertyUrlRegex)[1];
            //         }
            //         requestBtn.dataset.propertyId = lastDiscussedPropertyId;
            //     }
            // }
            function updateActionButtons(messages, isOwner) {
                const requestBtn = document.getElementById('requestViewingBtn');
                const offerBtn = document.getElementById('makeOfferBtn');
                const offerPropertyIdInput = document.getElementById('offer_property_id');
                const offerViewingRequestIdInput = document.getElementById('offer_viewing_request_id');
                const now = new Date();

                // إخفاء الأزرار بشكل افتراضي
                requestBtn.classList.add('d-none');
                offerBtn.classList.add('d-none');

                // البائع لا يقوم بأي إجراء
                if (isOwner) {
                    return;
                }

                // --- المنطق الجديد والمبسّط ---

                // 1. هل هناك أي طلب معاينة معلق؟
                const hasPendingRequest = messages.some(m => m.type === 'viewing_request' && m.metadata.status ===
                    'pending');
                if (hasPendingRequest) {
                    return;
                } // إذا نعم، لا تفعل شيئاً

                // 2. هل هناك أي عرض معلق أو مقبول وينتظر الدفع؟
                const hasActiveOffer = messages.some(m =>
                    m.type === 'offer_made' &&
                    (m.metadata.status === 'pending' || (m.metadata.status === 'accepted' && !m.metadata
                        .payment_simulated && !m.metadata.deal_completed))
                );
                if (hasActiveOffer) {
                    return;
                } // إذا نعم، لا تفعل شيئاً

                // 3. هل هناك أي موعد معاينة مستقبلي لم تتم معالجته بعد؟
                const hasFutureAppointment = messages.some(m => {
                    if (m.type !== 'viewing_confirmed' || !m.metadata?.confirmed_slot || new Date(m.metadata
                            .confirmed_slot) < now) {
                        return false;
                    }
                    const originalRequestId = m.metadata.original_request_message_id;
                    // هل تم تقديم عرض بناءً على هذا الموعد؟
                    const hasOffer = messages.some(offer => offer.type === 'offer_made' && offer.metadata
                        .original_request_message_id == originalRequestId);
                    return !hasOffer;
                });
                if (hasFutureAppointment) {
                    return;
                } // إذا نعم، لا تفعل شيئاً

                // 4. إذا لم نجد أي إجراءات نشطة، تحقق مما إذا كان يجب إظهار زر "تقديم عرض"
                // ابحث عن آخر موعد مكتمل لم يتم تقديم عرض له
                const lastCompletedAppointment = messages
                    .filter(m => m.type === 'viewing_confirmed' && m.metadata.confirmed_slot && new Date(m.metadata
                        .confirmed_slot) < now)
                    .find(confirmation => {
                        const originalRequestId = confirmation.metadata.original_request_message_id;
                        const hasOffer = messages.some(offer => offer.type === 'offer_made' && offer.metadata
                            .original_request_message_id == originalRequestId);
                        return !hasOffer;
                    });
                const lastMessage = messages.length > 0 ? messages[0] : null; // آخر رسالة (لأنها مرتبة تنازلياً)
                const propertyUrlRegex = /properties\/show\/(\d+)/;

                // هل آخر رسالة هي رسالة عقار جديد؟ إذا نعم، يجب أن نسمح بطلب معاينة جديد
                const isNewPropertyInitiated = lastMessage && propertyUrlRegex.test(lastMessage.body) && lastMessage
                    .user_id === currentUserId;
                if (lastCompletedAppointment && !isNewPropertyInitiated) { // <-- أضفنا !isNewPropertyInitiated
                    offerBtn.classList.remove('d-none');
                    offerPropertyIdInput.value = lastCompletedAppointment.metadata.property_id;
                    offerViewingRequestIdInput.value = lastCompletedAppointment.metadata
                        .original_request_message_id;
                    return;
                }

                // 5. إذا لم يتحقق أي من الشروط السابقة، فالمحادثة خاملة.
                requestBtn.classList.remove('d-none');
            }

            function createMessageHtml(message, isFirstInGroup) {
                if (typeof message.metadata === 'string') {
                    try {
                        message.metadata = JSON.parse(message.metadata);
                    } catch (e) {
                        message.metadata = null;
                    }
                }
                const isSent = message.user_id == currentUserId;
                const otherUserAvatar = document.getElementById('activeChatAvatar').src;
                const avatarUrl = isSent ? '{{ Auth::user()->profile_image_url }}' : otherUserAvatar;
                const groupClass = isFirstInGroup ? 'is-first-in-group' : '';
                const avatarHtml = `<img src="${avatarUrl}" alt="Avatar" class="avatar">`;

                // 1. التعامل مع طلبات المعاينة
                if (message.type === 'viewing_request' && message.metadata?.slots) {
                    const hasBeenProcessed = message.metadata.status && message.metadata.status !== 'pending';
                    const slotsHtml = message.metadata.slots.map((slot, index) => {
                        const date = new Date(slot);
                        const formattedDate = date.toLocaleDateString('en-GB', {
                            weekday: 'long',
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });
                        const formattedTime = date.toLocaleTimeString('en-US', {
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: true
                        });
                        let actionButton = '';
                        if (!isSent && !hasBeenProcessed) {
                            actionButton =
                                `<button class="btn btn-sm btn-success accept-viewing-btn" data-slot-index="${index}">Accept</button>`;
                        } else if (message.metadata.status === 'processed' && message.metadata
                            .confirmed_slot === slot) {
                            actionButton = `<span class="badge bg-success">Accepted</span>`;
                        }
                        return `<li class="list-group-item d-flex justify-content-between align-items-center"><div><i class="bi bi-calendar-event me-2"></i> ${formattedDate}<br><i class="bi bi-clock me-2"></i> ${formattedTime}</div><div>${actionButton}</div></li>`;
                    }).join('');
                    const requestorName = isSent ? 'You' : (message.user ? message.user.name : 'User');
                    let cardFooter = '';
                    if (!hasBeenProcessed) {
                        if (isSent) {
                            cardFooter =
                                `<div class="card-footer bg-transparent border-0 text-end py-2 px-1"><button class="btn btn-outline-secondary btn-sm cancel-request-btn">Cancel</button></div>`;
                        } else {
                            cardFooter =
                                `<div class="card-footer bg-transparent border-0 text-end py-2 px-1"><button class="btn btn-outline-danger btn-sm reject-request-btn">Reject</button></div>`;
                        }
                    }
                    const cardContent =
                        `<div class="card message-card"><div class="card-header bg-light"><i class="bi bi-calendar-check-fill me-2 text-primary"></i><strong>Viewing Request</strong></div><div class="card-body p-0"><p class="card-text p-3 pb-2">${message.body||(requestorName+' requested a viewing:')}</p><ul class="list-group list-group-flush">${slotsHtml}</ul>${cardFooter}</div></div>`;
                    return `<div class="message-item ${isSent?'sent':'received'} ${groupClass}" data-message-id="${message.id}" data-user-id="${message.user_id}">${!isSent?avatarHtml:''}${cardContent}${isSent?avatarHtml:'' }</div>`;
                }

                // 2. التعامل مع عروض الشراء/الإيجار
                else if (message.type === 'offer_made' && message.metadata) {
                    const metadata = message.metadata;
                    const offerAmount = new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'USD'
                    }).format(metadata.amount);
                    const paymentMethodText = metadata.payment_method === 'online' ? 'Online Deposit (Simulated)' :
                        'Offline Payment';
                    const offerorName = isSent ? 'You have' : (message.user ? message.user.name + ' has' :
                        'A user has');
                    let cardFooter = '';

                    if (metadata.status === 'pending') {
                        if (!isSent) {
                            cardFooter =
                                `<div class="card-footer bg-transparent border-0 text-end py-2 px-1"><button class="btn btn-outline-danger btn-sm reject-offer-btn">Reject</button><button class="btn btn-success btn-sm ms-2 accept-offer-btn">Accept</button></div>`;
                        } else {
                            cardFooter =
                                `<div class="card-footer bg-light text-center small py-2"><i class="bi bi-hourglass-split me-1"></i> Waiting for seller's response...</div>`;
                        }

                    } else if (metadata.status === 'accepted') {
                        // ▼▼▼ هذا هو التعديل المهم ▼▼▼
                        let footerContent = `<i class="bi bi-check-circle-fill me-1"></i> Offer Accepted!`;
                        // إذا كان الدفع لم يتم محاكاته بعد، والمستخدم هو المشتري، وطريقة الدفع online
                        if (!metadata.payment_simulated && isSent && metadata.payment_method === 'online') {
                            footerContent +=
                                `<br><button class="btn btn-primary btn-sm mt-2 simulate-payment-btn"><i class="bi bi-credit-card-2-front-fill me-1"></i> Pay Deposit Now (Simulated)</button>`;
                        }
                        cardFooter =
                            `<div class="card-footer bg-success-subtle text-success-lighten text-center small p-2">${footerContent}</div>`;
                        // ▲▲▲ نهاية التعديل المهم ▲▲▲

                    } else if (metadata.status === 'rejected') {
                        cardFooter =
                            `<div class="card-footer bg-danger-subtle text-danger-lighten text-center small py-2"><i class="bi bi-x-circle-fill me-1"></i> Offer Rejected</div>`;
                    }

                    const notesHtml = metadata.notes ?
                        `<hr class="my-0"><div class="p-3 pt-2"><p class="card-text small text-muted mb-0"><strong>Notes:</strong> ${linkify(metadata.notes)}</p></div>` :
                        '';
                    const cardContent =
                        `<div class="card message-card"><div class="card-header bg-light"><i class="bi bi-tag-fill me-2 text-success"></i><strong>Offer Made</strong></div><div class="card-body p-0"><div class="p-3 pb-2"><p class="card-text">${offerorName} made an offer of <strong>${offerAmount}</strong>.</p><p class="card-text small text-muted"><i class="bi bi-credit-card me-2"></i>Proposed Payment: <strong>${paymentMethodText}</strong></p></div>${notesHtml}</div>${cardFooter}</div>`;
                    return `<div class="message-item system-message" data-message-id="${message.id}" data-user-id="${message.user_id}">${cardContent}</div>`;
                }

                // 3. التعامل مع رسائل النظام الأخرى
                else if (['viewing_confirmed', 'viewing_rejected', 'viewing_cancelled', 'system'].includes(message
                        .type)) {
                    let alertHtml = '';
                    if (message.type === 'viewing_confirmed' && message.metadata?.confirmed_slot) {
                        const confirmedDate = new Date(message.metadata.confirmed_slot);
                        const formattedDate = confirmedDate.toLocaleDateString('en-GB', {
                            weekday: 'long',
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });
                        const formattedTime = confirmedDate.toLocaleTimeString('en-US', {
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: true
                        });
                        alertHtml =
                            `<div class="alert alert-success text-center w-100 my-2"><h5 class="alert-heading"><i class="bi bi-check-circle-fill me-2"></i> Confirmed!</h5><p class="mb-1">Appointment set for:</p><p class="fw-bold fs-5">${formattedDate} at ${formattedTime}</p></div>`;
                    } else if (message.type === 'viewing_rejected' || message.type === 'viewing_cancelled') {
                        const alertClass = message.type === 'viewing_rejected' ? 'alert-danger' : 'alert-warning';
                        const alertIcon = message.type === 'viewing_rejected' ? 'bi-x-circle-fill' :
                            'bi-slash-circle-fill';
                        const alertTitle = message.type === 'viewing_rejected' ? 'Request Rejected' :
                            'Request Cancelled';
                        alertHtml =
                            `<div class="alert ${alertClass} text-center w-100 my-2 small py-2"><i class="bi ${alertIcon} me-2"></i> <strong>${alertTitle}:</strong> ${message.body}</div>`;
                    } else if (message.type === 'system') {
                        alertHtml =
                            `<div class="alert alert-info text-center w-100 my-2 small py-2"><i class="bi bi-info-circle-fill me-2"></i> ${message.body}</div>`;
                    }
                    return `<div class="message-item system-message" data-message-id="${message.id}">${alertHtml}</div>`;

                    // 4. التعامل مع رسالة تأكيد الدفع (هذا هو التعديل!)
                } else if (message.type === 'payment_confirmation') {
                    const isSent = message.user_id == currentUserId;
                    const offerorName = isSent ? 'You' : (message.user ? message.user.name + ' has' :
                        'A user has');
                    const cardContent =
                        `<div class="alert alert-success text-center w-100 my-2"><h5 class="alert-heading"><i class="bi bi-check-circle-fill me-2"></i> Payment Confirmed!</h5><p class="mb-1">${offerorName} have completed the simulated payment.</p><p class="fw-bold fs-5">${message.body}</p></div>`; // استخدم body الرسالة لعرض أي معلومات إضافية
                    return `<div class="message-item system-message" data-message-id="${message.id}">${cardContent}</div>`;
                }

                // 5. التعامل مع الرسائل النصية العادية
                else {
                    const timeHtml =
                        `<span class="message-time">${message.formatted_created_at||'Just now'}</span>`;
                    const messageBody = linkify(message.body).replace(/\n/g, '<br>');
                    return `<div class="message-item ${isSent?'sent':'received'} ${groupClass}" data-message-id="${message.id}" data-user-id="${message.user_id}">${!isSent?avatarHtml:''}<div class="message-content"><div>${messageBody}</div>${timeHtml}</div>${isSent?avatarHtml:'' }</div>`;
                }
            }

            async function loadConversation(convId, convElement) {
                if (isLoading && currentConversationId == convId) return;
                isLoading = true;
                if (window.firestoreListener) window.firestoreListener();
                currentConversationId = convId;
                document.querySelectorAll('.conversation-item.active').forEach(el => el.classList.remove(
                    'active'));
                if (convElement) {
                    convElement.classList.add('active');
                    const unreadBadge = convElement.querySelector('.unread-count');
                    if (unreadBadge) unreadBadge.style.display = 'none';
                }
                noConversationDiv.classList.add('d-none');
                activeChatContainer.classList.remove('d-none');
                activeChatContainer.classList.add('d-flex');
                if (window.innerWidth < 992) document.getElementById('chatContainer').classList.add(
                    'mobile-chat-view');
                const isOwner = convElement ? convElement.dataset.isOwner === 'true' : false;
                if (convElement) {
                    document.getElementById('activeChatUserName').textContent = convElement.querySelector(
                        '.name').textContent;
                    document.getElementById('activeChatAvatar').src = convElement.querySelector('img.avatar')
                        .src;
                }
                messagesArea.innerHTML =
                    '<div class="text-center p-5"><div class="spinner-border text-secondary"></div></div>';
                try {
                    const response = await fetch(`/chat/conversations/${convId}/messages`);
                    if (!response.ok) throw new Error('Network response was not ok');
                    const result = await response.json();
                    messagesArea.innerHTML = '';
                    let lastMessageUserId = null;
                    const reversedMessages = result.data.slice().reverse();
                    reversedMessages.forEach(msg => {
                        const isFirstInGroup = (msg.user_id != lastMessageUserId);
                        messagesArea.insertAdjacentHTML('beforeend', createMessageHtml(msg,
                            isFirstInGroup));
                        lastMessageUserId = msg.user_id;
                    });

                    // ▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼
                    //     هذا هو المنطق الجديد والمهم
                    // ▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼
                    // يتم تنفيذه بعد تحميل كل الرسائل
                    updateActionButtons(result.data, isOwner);

                    // تحديث propertyId لزر طلب المعاينة
                    const requestBtn = document.getElementById('requestViewingBtn');
                    let lastDiscussedPropertyId = null;
                    const propertyUrlRegex = /properties\/show\/(\d+)/;
                    // نبحث في كل الرسائل عن آخر رابط عقار
                    const propertyMessage = [...result.data].reverse().find(msg => msg.body.match(
                        propertyUrlRegex));
                    if (propertyMessage) {
                        lastDiscussedPropertyId = propertyMessage.body.match(propertyUrlRegex)[1];
                    }
                    // نضع الـ ID على الزر
                    if (requestBtn) {
                        requestBtn.dataset.propertyId = lastDiscussedPropertyId;
                    }
                    // ▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲

                    scrollToBottom();
                } catch (error) {
                    console.error('Error loading messages:', error);
                    messagesArea.innerHTML =
                        '<div class="alert alert-danger m-2">Could not load messages.</div>';
                } finally {
                    isLoading = false;
                }
            }

            // --- مستمعات الأحداث (Event Listeners) ---

            function resetToDefaultView() {
                if (window.firestoreListener) window.firestoreListener();
                document.getElementById('chatContainer').classList.remove('mobile-chat-view');
                currentConversationId = null;
                document.querySelectorAll('.conversation-item.active').forEach(el => el.classList.remove('active'));
                activeChatContainer.classList.add('d-none');
                activeChatContainer.classList.remove('d-flex');
                noConversationDiv.classList.remove('d-none');
            }
            async function deleteConversation(convId, convItemElement) {
                try {
                    const response = await fetch(`/chat/conversations/${convId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    if (data.success) {
                        if (currentConversationId == convId) resetToDefaultView();
                        convItemElement.remove();
                    } else {
                        Swal.fire('Failed!', data.message || 'Could not delete.', 'error');
                    }
                } catch (error) {
                    Swal.fire('Error!', 'An error occurred.', 'error');
                }
            }
            async function processRequest(btn, url, body = null) {
                document.getElementById('requestViewingBtn').classList.add('d-none');
                document.getElementById('makeOfferBtn').classList.add('d-none');
                try {
                    const options = {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    };
                    if (body) {
                        options.body = JSON.stringify(body);
                    }
                    const response = await fetch(url, options);
                    const data = await response.json();
                    if (!response.ok) {
                        let errorMessage = data.message || 'An error occurred.';
                        if (data.errors) {
                            errorMessage = Object.values(data.errors).flat().join(' ');
                        }
                        throw new Error(errorMessage);
                    }
                    if (data.success) {
                        const activeConvElement = conversationsList.querySelector('.conversation-item.active');
                        if (activeConvElement) loadConversation(currentConversationId, activeConvElement);
                    } else {
                        Swal.fire('Error', data.message || 'An unknown error occurred.', 'error');
                    }
                } catch (error) {
                    Swal.fire('Error', error.message, 'error');
                }
            }

            conversationsList.addEventListener('click', e => {
                const deleteBtn = e.target.closest('.delete-conversation-btn');
                if (deleteBtn) {
                    e.stopPropagation();
                    const convItem = deleteBtn.closest('.conversation-item');
                    const convId = convItem.dataset.conversationId;
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This action cannot be undone!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            deleteConversation(convId, convItem);
                        }
                    });
                    return;
                }
                const convElement = e.target.closest('.conversation-item');
                if (convElement) {
                    loadConversation(convElement.dataset.conversationId, convElement);
                }
            });
            backToConversationsBtn.addEventListener('click', resetToDefaultView);
            sendMessageForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const messageInput = e.target.querySelector('input[name="body"]');
                const body = messageInput.value.trim();
                if (!body || !currentConversationId) return;

                const originalText = messageInput.value;
                messageInput.value = '';
                messageInput.disabled = true; // تعطيل مؤقت لمنع الإرسال المزدوج

                try {
                    const response = await fetch(
                        `/chat/conversations/${currentConversationId}/messages`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                body: originalText
                            })
                        });

                    const result = await response.json();
                    if (!response.ok || !result.success) {
                        throw new Error(result.message || 'Failed to send message.');
                    }

                    // الآن نعتمد بشكل كامل على loadConversation للتحديث
                    const activeConvItem = conversationsList.querySelector(
                        `li[data-conversation-id="${currentConversationId}"]`);
                    if (activeConvItem) {
                        // لا حاجة لتحديث الشريط الجانبي يدوياً، loadConversation ستقوم بذلك
                        loadConversation(currentConversationId, activeConvItem);
                    }

                } catch (error) {
                    console.error('Failed to send message:', error);
                    // في حالة الخطأ، نعيد النص الأصلي إلى حقل الإدخال
                    messageInput.value = originalText;
                    Swal.fire('Error', 'Could not send the message.', 'error');
                } finally {
                    messageInput.disabled = false; // إعادة تفعيل حقل الإدخال
                    messageInput.focus();
                }
            });
            if (sendViewingRequestBtn) {
                sendViewingRequestBtn.addEventListener('click', async function() {
                    if (!currentConversationId) return Swal.fire('Error', 'No active conversation.',
                        'error');
                    const propertyId = document.getElementById('requestViewingBtn').dataset.propertyId;
                    if (!propertyId) {
                        return Swal.fire('Error',
                            'Could not determine which property this request is for. Please make sure a property link is in the chat.',
                            'error');
                    }
                    const formData = new FormData(requestViewingForm);
                    const slots = formData.getAll('slots[]').filter(slot => slot.trim() !== '');
                    if (slots.length === 0) return Swal.fire('Error',
                        'Please provide at least one slot.', 'error');
                    this.disabled = true;
                    this.innerHTML =
                        '<span class="spinner-border spinner-border-sm"></span> Sending...';
                    try {
                        const response = await fetch(
                            `/chat/conversations/${currentConversationId}/request-viewing`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    slots: slots,
                                    property_id: propertyId
                                })
                            });
                        if (!response.ok) {
                            const errorData = await response.json();
                            throw new Error(errorData.message || 'Failed to send request.');
                        }
                        requestViewingModal.hide();
                        requestViewingForm.reset();
                        document.querySelectorAll('.datetime-picker').forEach(picker => picker
                            ._flatpickr.clear());
                        const activeConvElement = conversationsList.querySelector(
                            '.conversation-item.active');
                        if (activeConvElement) loadConversation(currentConversationId,
                            activeConvElement);
                    } catch (error) {
                        Swal.fire('Failed!', error.message, 'error');
                    } finally {
                        this.disabled = false;
                        this.innerHTML = 'Send Request';
                    }
                });
            }

            messagesArea.addEventListener('click', async function(e) {
                // تحديد كل الأزرار الممكنة
                const acceptViewingBtn = e.target.closest('.accept-viewing-btn');
                const rejectViewingBtn = e.target.closest('.reject-request-btn');
                const cancelViewingBtn = e.target.closest('.cancel-request-btn');
                const acceptOfferBtn = e.target.closest('.accept-offer-btn');
                const rejectOfferBtn = e.target.closest('.reject-offer-btn');
                const simulatePaymentBtn = e.target.closest('.simulate-payment-btn'); // <-- الزر الجديد

                // 1. التعامل مع قبول طلب المعاينة
                if (acceptViewingBtn) {
                    e.preventDefault();
                    const messageId = acceptViewingBtn.closest('.message-item').dataset.messageId;
                    const slotIndex = acceptViewingBtn.dataset.slotIndex;
                    processRequest(acceptViewingBtn, `/chat/messages/${messageId}/accept-viewing`, {
                        slot_index: slotIndex
                    });
                    return;
                }

                // 2. التعامل مع رفض طلب المعاينة
                if (rejectViewingBtn) {
                    e.preventDefault();
                    const messageId = rejectViewingBtn.closest('.message-item').dataset.messageId;
                    processRequest(rejectViewingBtn, `/chat/messages/${messageId}/reject-viewing`);
                    return;
                }

                // 3. التعامل مع إلغاء طلب المعاينة
                if (cancelViewingBtn) {
                    e.preventDefault();
                    const messageId = cancelViewingBtn.closest('.message-item').dataset.messageId;
                    Swal.fire({
                            title: 'Are you sure?',
                            text: "You are about to cancel this request.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, cancel it!',
                            confirmButtonColor: '#d33'
                        })
                        .then((result) => {
                            if (result.isConfirmed) {
                                processRequest(cancelViewingBtn,
                                    `/chat/messages/${messageId}/cancel-viewing`);
                            }
                        });
                    return;
                }

                // 4. التعامل مع قبول العرض
                if (acceptOfferBtn) {
                    e.preventDefault();
                    const messageId = acceptOfferBtn.closest('.message-item').dataset.messageId;
                    processRequest(acceptOfferBtn, `/chat/messages/${messageId}/accept-offer`);
                    return;
                }

                // 5. التعامل مع رفض العرض
                if (rejectOfferBtn) {
                    e.preventDefault();
                    const messageId = rejectOfferBtn.closest('.message-item').dataset.messageId;
                    processRequest(rejectOfferBtn, `/chat/messages/${messageId}/reject-offer`);
                    return;
                }

                // 6. التعامل مع زر محاكاة الدفع
                if (simulatePaymentBtn) {
                    e.preventDefault();
                    const messageId = simulatePaymentBtn.closest('.message-item').dataset.messageId;
                    Swal.fire({
                        title: 'Payment Simulation',
                        text: "This is a demo. Clicking 'Confirm' will complete the transaction as if a real payment was made.",
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: 'Confirm Payment',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            processRequest(simulatePaymentBtn,
                                `/chat/messages/${messageId}/simulate-payment`);
                        }
                    });
                    return;
                }
            });

            if (sendOfferBtn) {
                sendOfferBtn.addEventListener('click', async function() { // <--- أضفنا async
                    // جمع البيانات من الفورم
                    const formData = new FormData(makeOfferForm);
                    const offerData = {
                        property_id: formData.get('property_id'),
                        amount: formData.get('amount'),
                        payment_method: formData.get('payment_method'),
                        notes: formData.get('notes'),
                        viewing_request_message_id: formData.get('viewing_request_message_id')
                    };

                    // التحقق من صحة البيانات
                    if (!offerData.property_id || !offerData.amount) {
                        Swal.fire('Error', 'Please fill in the offer amount.', 'error');
                        return;
                    }

                    // تعطيل الزر لمنع النقرات المتكررة
                    this.disabled = true;
                    this.innerHTML =
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';

                    try {
                        // إرسال البيانات إلى الخادم
                        const response = await fetch('/chat/conversations/' + currentConversationId +
                            '/make-offer', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify(offerData)
                            });

                        const result = await response.json();

                        if (!response.ok) {
                            // عرض رسالة الخطأ من الخادم
                            throw new Error(result.message || 'Failed to send the offer.');
                        }

                        // إذا نجح الطلب
                        makeOfferModal.hide(); // إخفاء النافذة
                        makeOfferForm.reset(); // تفريغ الفورم
                        Swal.fire('Offer Sent!', 'Your offer has been sent to the seller.', 'success');

                        // أهم خطوة: إعادة تحميل رسائل المحادثة لإظهار الرسالة الجديدة
                        const activeConvElement = conversationsList.querySelector(
                            '.conversation-item.active');
                        if (activeConvElement) {
                            loadConversation(currentConversationId, activeConvElement);
                        }

                    } catch (error) {
                        Swal.fire('Error', error.message, 'error');
                    } finally {
                        // إعادة تفعيل الزر في كل الحالات (نجاح أو فشل)
                        this.disabled = false;
                        this.innerHTML = 'Send Offer';
                    }
                });
            }

            // --- التحميل المبدئي ---
            function initialLoad() {
                const urlParams = new URLSearchParams(window.location.search);
                const initialConvId = urlParams.get('activeConversation');
                if (initialConvId) {
                    const conversationElement = conversationsList.querySelector(
                        `li[data-conversation-id="${initialConvId}"]`);
                    if (conversationElement) {
                        setTimeout(() => conversationElement.click(), 200);
                    }
                }
            }
            initialLoad();
        });
    </script>
@endpush
