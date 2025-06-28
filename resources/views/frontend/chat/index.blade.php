@extends('frontend.Layouts.frontend')
@section('title', 'My Chats - EasyFind')

@push('styles')
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
            /* عرض ثابت للبطاقة لتبدو مرتبة */
            max-width: 100%;
            /* للتأكد من أنها لا تتجاوز الشاشة على الأجهزة الصغيرة */
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            /* ظل خفيف لتحسين المظهر */
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
            <div class="col-lg-8 col-xl-9 chat-window" id="chatWindow">
                <div id="no-conversation-selected"
                    class="d-flex align-items-center justify-content-center h-100 flex-column text-center text-muted">
                    <i class="bi bi-chat-square-dots fs-1"></i>
                    <p class="lead mt-3">Select a conversation to start chatting</p>
                </div>
                <div class="d-flex flex-column h-100 d-none" id="active-chat-container">
                    <div class="chat-window-header" id="activeChatHeader">
                        <button class="btn me-2 p-1" id="backToConversationsBtn" type="button" title="Back to chats"><i
                                class="bi bi-arrow-left fs-5"></i></button>
                        <img src="" alt="Avatar" class="avatar" id="activeChatAvatar">
                        <div>
                            <div class="user-name" id="activeChatUserName"></div>
                        </div>
                    </div>
                    <div class="messages-area" id="messagesArea"></div>
                    <div class="chat-input-area p-2 bg-white border-top">
                        <form id="sendMessageForm">
                            <div class="input-group">
                                <button class="btn" type="button" id="requestViewingBtn" title="Request a Viewing"
                                    data-bs-toggle="modal" data-bs-target="#requestViewingModal">
                                    <i class="bi bi-calendar-check-fill fs-5 text-secondary"></i>
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

    <!-- Request Viewing Modal -->
    <div class="modal fade" id="requestViewingModal" tabindex="-1" aria-labelledby="requestViewingModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="requestViewingModalLabel">Suggest Viewing Times</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Please suggest up to 3 preferred time slots for the property viewing.</p>
                    <form id="requestViewingForm">
                        <div class="mb-3">
                            <label class="form-label">Suggestion 1</label>
                            <input type="text" class="form-control datetime-picker" placeholder="Select Date and Time"
                                name="slots[]" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Suggestion 2 (Optional)</label>
                            <input type="text" class="form-control datetime-picker" placeholder="Select Date and Time"
                                name="slots[]">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Suggestion 3 (Optional)</label>
                            <input type="text" class="form-control datetime-picker" placeholder="Select Date and Time"
                                name="slots[]">
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
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // --- 1. تفعيل المكتبات ---
            flatpickr(".datetime-picker", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                minDate: "today",
                time_24hr: false
            });

            // --- 2. تعريف المتغيرات ---
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

            let currentConversationId = null;
            let isLoading = false;
            const currentUserId = {{ Auth::id() }};


            // --- 3. الدوال الجديدة للـ REAL-TIME ---

            // المستمع الرئيسي: يستمع للتحديثات على كل المحادثات لتحديث القائمة الجانبية
            function listenForOverallUpdates() {
                db.collection('conversations').where('participants', 'array-contains', currentUserId)
                    .onSnapshot((snapshot) => {
                        snapshot.docChanges().forEach((change) => {
                            if (change.type === "modified") {
                                const convData = change.doc.data();
                                const convId = change.doc.id;
                                if (convId === currentConversationId) return; // تجاهل المحادثة النشطة
                                
                                console.log(`Update received for inactive conversation: ${convId}`);
                                updateSidebarItem(convId, convData.lastMessage);
                            }
                        });
                    }, (error) => {
                        console.error("Error listening for conversation updates: ", error);
                    });
            }

            // دالة مساعدة لتحديث عنصر في القائمة الجانبية
            function updateSidebarItem(conversationId, lastMessage) {
                const conversationItem = conversationsList.querySelector(`li[data-conversation-id="${conversationId}"]`);
                if (!conversationItem || !lastMessage) return;

                const lastMessageElement = conversationItem.querySelector('.last-message');
                if (lastMessageElement && lastMessage.text) {
                     const prefix = (lastMessage.senderId == currentUserId) ? '<span class="text-muted">You: </span>' : '';
                    const shortMessage = lastMessage.text.length > 25 ? lastMessage.text.substring(0, 25) + '...' : lastMessage.text;
                    lastMessageElement.innerHTML = prefix + shortMessage;
                }

                const timeElement = conversationItem.querySelector('.chat-time');
                if (timeElement) timeElement.textContent = 'Just now';
                
                if(lastMessage.senderId != currentUserId) {
                    let unreadBadge = conversationItem.querySelector('.unread-count');
                    const badgeContainer = conversationItem.querySelector('.chat-time-and-badge');
                    if (badgeContainer && !unreadBadge) {
                        unreadBadge = document.createElement('span');
                        unreadBadge.className = 'badge bg-danger rounded-pill unread-count ms-auto';
                        badgeContainer.appendChild(unreadBadge);
                    }
                    if(unreadBadge){
                        unreadBadge.textContent = (parseInt(unreadBadge.textContent) || 0) + 1;
                        unreadBadge.style.display = 'inline-block';
                    }
                }
                conversationsList.prepend(conversationItem);
            }

            // --- 4. الدوال المساعدة الأصلية ---

            function linkify(text) {
                if (!text) return '';
                const urlRegex = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
                return text.replace(urlRegex, url =>
                    `<span dir="ltr"><a href="${url}" target="_blank" rel="noopener noreferrer">${url}</a></span>`
                );
            }

            function scrollToBottom() {
                setTimeout(() => { messagesArea.scrollTop = messagesArea.scrollHeight; }, 50);
            }
            
            // --- 5. الدوال الرئيسية (معظمها من الكود الأصلي) ---

            // دالة إنشاء HTML للرسائل (لم تتغير)
            function createMessageHtml(message, isFirstInGroup) {
                if (typeof message.metadata === 'string') {
                    try { message.metadata = JSON.parse(message.metadata); } catch (e) { message.metadata = null; }
                }
                const isSent = message.user_id == currentUserId;
                const otherUserAvatar = document.getElementById('activeChatAvatar').src;
                const avatarUrl = isSent ? '{{ Auth::user()->profile_image_url }}' : otherUserAvatar;
                const groupClass = isFirstInGroup ? 'is-first-in-group' : '';
                const avatarHtml = `<img src="${avatarUrl}" alt="Avatar" class="avatar">`;

                if (message.type === 'viewing_request' && message.metadata?.slots) {
                    const hasBeenProcessed = message.metadata.status && message.metadata.status !== 'pending';
                    const slotsHtml = message.metadata.slots.map((slot, index) => {
                        const date = new Date(slot);
                        const formattedDate = date.toLocaleDateString('en-GB', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                        const formattedTime = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
                        let actionButton = '';
                        if (!isSent && !hasBeenProcessed) {
                            actionButton = `<button class="btn btn-sm btn-success accept-viewing-btn" data-slot-index="${index}">Accept</button>`;
                        } else if (message.metadata.status === 'processed' && message.metadata.confirmed_slot === slot) {
                            actionButton = `<span class="badge bg-success">Accepted</span>`;
                        }
                        return `<li class="list-group-item d-flex justify-content-between align-items-center"><div><i class="bi bi-calendar-event me-2"></i> ${formattedDate}<br><i class="bi bi-clock me-2"></i> ${formattedTime}</div><div>${actionButton}</div></li>`;
                    }).join('');
                    const requestorName = isSent ? 'You' : (message.user ? message.user.name : 'User');
                    let cardFooter = '';
                    if (!hasBeenProcessed) {
                        if (isSent) { cardFooter = `<div class="card-footer bg-transparent border-0 text-end py-2 px-1"><button class="btn btn-outline-secondary btn-sm cancel-request-btn">Cancel Request</button></div>`;
                        } else { cardFooter = `<div class="card-footer bg-transparent border-0 text-end py-2 px-1"><button class="btn btn-outline-danger btn-sm reject-request-btn">Reject All</button></div>`; }
                    }
                    const cardContent = `<div class="card message-card"><div class="card-header bg-light"><i class="bi bi-calendar-check-fill me-2 text-primary"></i><strong>Viewing Request</strong></div><div class="card-body p-0"><p class="card-text p-3 pb-2">${requestorName} requested a viewing:</p><ul class="list-group list-group-flush">${slotsHtml}</ul>${cardFooter}</div></div>`;
                    return `<div class="message-item ${isSent ? 'sent' : 'received'} ${groupClass}" data-message-id="${message.id}" data-user-id="${message.user_id}">${!isSent ? avatarHtml : ''}${cardContent}${isSent ? avatarHtml : ''}</div>`;
                }
                else if (['viewing_confirmed', 'viewing_rejected', 'viewing_cancelled'].includes(message.type)) {
                    // ... (كود رسائل النظام لم يتغير)
                    let alertHtml = '';
                    if (message.type === 'viewing_confirmed' && message.metadata?.confirmed_slot) {
                        const confirmedDate = new Date(message.metadata.confirmed_slot);
                        const formattedDate = confirmedDate.toLocaleDateString('en-GB', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                        const formattedTime = confirmedDate.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
                        alertHtml = `<div class="alert alert-success text-center w-100 my-2"><h5 class="alert-heading"><i class="bi bi-check-circle-fill me-2"></i> Viewing Confirmed!</h5><p class="mb-1">Your appointment is set for:</p><p class="fw-bold fs-5">${formattedDate} at ${formattedTime}</p></div>`;
                    } else if (message.type === 'viewing_rejected' || message.type === 'viewing_cancelled') {
                        const alertClass = message.type === 'viewing_rejected' ? 'alert-danger' : 'alert-warning';
                        const alertIcon = message.type === 'viewing_rejected' ? 'bi-x-circle-fill' : 'bi-slash-circle-fill';
                        const alertTitle = message.type === 'viewing_rejected' ? 'Request Rejected' : 'Request Cancelled';
                        alertHtml = `<div class="alert ${alertClass} text-center w-100 my-2 small py-2"><i class="bi ${alertIcon} me-2"></i> <strong>${alertTitle}:</strong> ${message.body}</div>`;
                    }
                    return `<div class="message-item system-message" data-message-id="${message.id}">${alertHtml}</div>`;
                }
                else {
                    const timeHtml = `<span class="message-time">${message.formatted_created_at || 'Just now'}</span>`;
                    const messageBody = linkify(message.body).replace(/\n/g, '<br>');
                    return `<div class="message-item ${isSent ? 'sent' : 'received'} ${groupClass}" data-message-id="${message.id}" data-user-id="${message.user_id}">${!isSent ? avatarHtml : ''}<div class="message-content"><div>${messageBody}</div>${timeHtml}</div>${isSent ? avatarHtml : ''}</div>`;
                }
            }
            
            // دالة تحميل المحادثة (لم تتغير)
            async function loadConversation(convId, convElement) {
                if (isLoading && currentConversationId == convId) return;
                isLoading = true;
                if (window.firestoreListener) window.firestoreListener(); // إيقاف المستمع القديم

                currentConversationId = convId;
                document.querySelectorAll('.conversation-item.active').forEach(el => el.classList.remove('active'));
                if (convElement) {
                    convElement.classList.add('active');
                    const unreadBadge = convElement.querySelector('.unread-count');
                    if (unreadBadge) unreadBadge.style.display = 'none';
                }

                noConversationDiv.classList.add('d-none');
                activeChatContainer.classList.remove('d-none');
                activeChatContainer.classList.add('d-flex');
                if (window.innerWidth < 992) document.getElementById('chatContainer').classList.add('mobile-chat-view');
                if (convElement) {
                    document.getElementById('activeChatUserName').textContent = convElement.querySelector('.name').textContent;
                    document.getElementById('activeChatAvatar').src = convElement.querySelector('img.avatar').src;
                }
                messagesArea.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-secondary"></div></div>';

                try {
                    const response = await fetch(`/chat/conversations/${convId}/messages`);
                    if (!response.ok) throw new Error('Network response was not ok');
                    const result = await response.json();
                    messagesArea.innerHTML = '';
                    let lastMessageUserId = null;
                    result.data.slice().reverse().forEach(msg => {
                        const isFirstInGroup = (msg.user_id != lastMessageUserId);
                        messagesArea.insertAdjacentHTML('beforeend', createMessageHtml(msg, isFirstInGroup));
                        lastMessageUserId = msg.user_id;
                    });
                    scrollToBottom();
                    const messagesRef = db.collection('conversations').doc(convId).collection('messages');
                    const lastMessageTimestamp = result.data.length > 0 ? firebase.firestore.Timestamp.fromDate(new Date(result.data[0].created_at)) : firebase.firestore.Timestamp.now();
                    window.firestoreListener = messagesRef.where('timestamp', '>', lastMessageTimestamp)
                        .onSnapshot((snapshot) => {
                            snapshot.docChanges().forEach((change) => {
                                if (change.type === "added") {
                                    const firestoreMessage = change.doc.data();
                                    const formattedMessage = { id: change.doc.id, user_id: firestoreMessage.userId, user: { name: firestoreMessage.userName }, body: firestoreMessage.message, formatted_created_at: new Date(firestoreMessage.timestamp.seconds * 1000).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) };
                                    if (!messagesArea.querySelector(`[data-message-id="${formattedMessage.id}"]`)) {
                                        let lastMsgEl = messagesArea.querySelector('.message-item:last-child');
                                        let lastUserId = lastMsgEl ? lastMsgEl.dataset.userId : null;
                                        const isFirst = String(formattedMessage.user_id) !== lastUserId;
                                        messagesArea.insertAdjacentHTML('beforeend', createMessageHtml(formattedMessage, isFirst));
                                        scrollToBottom();
                                        updateSidebarItem(convId, firestoreMessage);
                                    }
                                }
                            });
                        });
                } catch (error) {
                    console.error('Error loading messages:', error);
                    messagesArea.innerHTML = '<div class="alert alert-danger m-2">Could not load messages.</div>';
                } finally {
                    isLoading = false;
                }
            }

            function resetToDefaultView() {
                // ... (الكود لم يتغير)
                if (window.firestoreListener) window.firestoreListener();
                document.getElementById('chatContainer').classList.remove('mobile-chat-view');
                currentConversationId = null;
                document.querySelectorAll('.conversation-item.active').forEach(el => el.classList.remove('active'));
                activeChatContainer.classList.add('d-none');
                activeChatContainer.classList.remove('d-flex');
                noConversationDiv.classList.remove('d-none');
            }

            async function deleteConversation(convId, convItemElement) {
                 // ... (الكود لم يتغير)
                try {
                    const response = await fetch(`/chat/conversations/${convId}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } });
                    const data = await response.json();
                    if (data.success) {
                        if (currentConversationId == convId) resetToDefaultView();
                        convItemElement.remove();
                    } else { Swal.fire('Failed!', data.message || 'Could not delete.', 'error'); }
                } catch (error) { Swal.fire('Error!', 'An error occurred.', 'error'); }
            }

            // --- 6. مستمعات الأحداث (مع تعديل دالة الإرسال) ---

            conversationsList.addEventListener('click', e => {
                const deleteBtn = e.target.closest('.delete-conversation-btn');
                if (deleteBtn) {
                    e.stopPropagation();
                    //... (منطق الحذف لم يتغير)
                    const convItem = deleteBtn.closest('.conversation-item');
                    const convId = convItem.dataset.conversationId;
                    Swal.fire({ title: 'Are you sure?', text: "This action cannot be undone!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete it!'
                    }).then((result) => { if (result.isConfirmed) { deleteConversation(convId, convItem); } });
                    return;
                }
                const convElement = e.target.closest('.conversation-item');
                if (convElement) loadConversation(convElement.dataset.conversationId, convElement);
            });

            backToConversationsBtn.addEventListener('click', resetToDefaultView);

            // ▼▼▼▼▼ دالة إرسال الرسالة المعدلة ▼▼▼▼▼
            sendMessageForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const messageInput = e.target.querySelector('input[name="body"]');
                const body = messageInput.value.trim();
                if (!body || !currentConversationId) return;
                
                const originalText = messageInput.value;
                messageInput.value = '';
                messageInput.focus();

                const tempMessage = { id: 'temp-' + Date.now(), user_id: currentUserId, body: originalText, formatted_created_at: 'Sending...' };
                const lastMsgEl = messagesArea.querySelector('.message-item:last-child');
                const lastUserId = lastMsgEl ? lastMsgEl.dataset.userId : null;
                const isFirst = String(tempMessage.user_id) !== lastUserId;

                messagesArea.insertAdjacentHTML('beforeend', createMessageHtml(tempMessage, isFirst));
                const tempElement = messagesArea.querySelector(`[data-message-id="${tempMessage.id}"]`);
                scrollToBottom();
                
                try {
                    const response = await fetch(`/chat/conversations/${currentConversationId}/messages`, {
                            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            body: JSON.stringify({ body: originalText })
                        });
                    const result = await response.json();
                    if (!response.ok || !result.success) throw new Error(result.message || 'Failed to send message.');
                    
                    if (tempElement) {
                        const finalMessageHtml = createMessageHtml(result.data, isFirst);
                        tempElement.outerHTML = finalMessageHtml;
                    }
                    
                    updateSidebarItem(currentConversationId, { text: result.data.body, senderId: result.data.user_id });
                } catch (error) {
                    console.error('Failed to send message:', error);
                    if (tempElement) { tempElement.querySelector('.message-time').textContent = 'Failed'; tempElement.style.opacity = '0.7'; }
                }
            });

            // باقي مستمعات الأحداث (لم تتغير)
            if (sendViewingRequestBtn) {
                sendViewingRequestBtn.addEventListener('click', async function() { /* ... كود إرسال طلب المعاينة لم يتغير ... */ });
            }
            messagesArea.addEventListener('click', async function(e) { /* ... كود قبول ورفض وإلغاء طلبات المعاينة لم يتغير ... */ });
            async function processRequest(btn, url, body = null) { /* ... كود دالة processRequest لم يتغير ... */ }


            // --- 7. التحميل المبدئي ---
            function initialLoad() {
                const urlParams = new URLSearchParams(window.location.search);
                const initialConvId = urlParams.get('activeConversation');
                if (initialConvId) {
                    const conversationElement = conversationsList.querySelector(`li[data-conversation-id="${initialConvId}"]`);
                    if (conversationElement) {
                        setTimeout(() => conversationElement.click(), 200);
                    }
                }
                listenForOverallUpdates(); // تشغيل المستمع الرئيسي
            }
            
            initialLoad();
        });
    </script>
@endpush
