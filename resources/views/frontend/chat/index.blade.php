@extends('frontend.Layouts.frontend')
@section('title', 'My Chats - EasyFind')

@push('styles')
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
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .conversation-item:hover,
        .conversation-item.active {
            background-color: #fff8e1;
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
        }

        /* أنماط الرسائل تبقى كما هي */
        .message-item {
            display: flex;
            max-width: 75%;
            margin-bottom: 1rem;
            align-items: flex-end;
        }

        .message-item.sent {
            margin-left: auto;
            flex-direction: row-reverse;
        }

        .message-item .avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 8px;
        }

        .avatar-placeholder {
            width: 38px;
        }

        .message-content {
            padding: 8px 12px;
            border-radius: 18px;
            position: relative;
            word-break: break-word;
        }

        .message-item.sent .message-content {
            background-color: #FFD699;
            color: #333;
            border-bottom-right-radius: 4px;
        }

        .message-item.received .message-content {
            background-color: #fff;
            border: 1px solid #e0e0e0;
            border-bottom-left-radius: 4px;
        }

        .message-sender-name {
            font-weight: 600;
            font-size: 0.8rem;
            margin-bottom: 4px;
            color: #555;
        }

        .message-time {
            display: block;
            font-size: 0.7rem;
            color: #777;
            margin-top: 4px;
            text-align: right;
        }

        .message-item.received .message-time {
            text-align: left;
        }

        /* ▼▼▼ تعديل تصميم منطقة الإدخال ▼▼▼ */
        .chat-input-area {
            padding: 1rem;
            /* زيادة الحشوة لرفعها عن الحافة */
            border-top: 1px solid #e0e0e0;
            background-color: #f8f9fa;
            /* تغيير الخلفية للتمييز */
            flex-shrink: 0;
            /* منعها من التقلص */
        }

        .chat-input-area .input-group {
            align-items: center;
            /* لمحاذاة الزر مع حقل الإدخال عمودياً */
        }

        .chat-input-area .form-control {
            border-radius: 20px;
            border-color: #ced4da;
        }

        .chat-input-area .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(255, 202, 40, 0.25);
            border-color: #ffca28;
        }

        .chat-input-area .btn {
            background-color: transparent;
            border: none;
        }

        .chat-input-area .btn:hover i {
            color: #ffb400 !important;
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

        /* ▼▼▼ CSS جديد ومبسط للمعالجة على الشاشات الصغيرة (Responsive) ▼▼▼ */
        @media (max-width: 991.98px) {
            /* نقطة التحول لشاشات lg في بوتستراب */

            /* في الوضع الافتراضي للموبايل، نخفي نافذة الدردشة */
            .chat-window {
                display: none;
            }

            /* عندما يتم اختيار محادثة، نطبق هذا الكلاس */
            .mobile-chat-view .chat-sidebar {
                display: none;
                /* إخفاء قائمة المحادثات */
            }

            .mobile-chat-view .chat-window {
                display: flex;
                /* إظهار نافذة الدردشة */
                width: 100%;
                /* جعلها تملأ الشاشة */
            }
        }
    </style>
@endpush

@section('content')
    {{-- الحاوية الرئيسية الآن تستخدم الكلاس للتحكم في العرض على الموبايل --}}
    <div class="container-fluid chat-container-wrapper" id="chatContainer">

        <div class="row g-0 h-100 w-100">

            {{-- 1. الشريط الجانبي للمحادثات --}}
            <div class="col-lg-4 col-xl-3 chat-sidebar" id="chatSidebar">
                <div class="chat-sidebar-header">
                    <h5>Chats</h5>
                </div>
                <ul class="list-group list-group-flush conversations-list" id="conversationsList">
                    @forelse($conversations as $conversation)
                        @php $otherUser = $conversation->other_participant; @endphp
                        @if ($otherUser)
                            <li class="conversation-item" data-conversation-id="{{ $conversation->id }}" data-user-id="{{ $otherUser->id }}">
                                <img src="{{ $otherUser->profile_image_url }}" alt="{{ $otherUser->name }}" class="avatar">
                                <div class="chat-info">
                                    <div class="name">{{ $otherUser->name }}</div>
                                    <div class="last-message">
                                        @if ($conversation->lastMessage)
                                            @if ($conversation->lastMessage->user_id == Auth::id())
                                                <span class="text-muted">You: </span>
                                            @endif
                                            {{ Str::limit($conversation->lastMessage->body, 25) }}
                                        @else
                                            No messages yet.
                                        @endif
                                    </div>
                                </div>
                                <div class="chat-time">{{ $conversation->updated_at->shortAbsoluteDiffForHumans() }}</div>
                            </li>
                        @endif
                    @empty
                        <li class="text-center text-muted p-5 list-group-item"><i
                                class="bi bi-chat-dots fs-1 d-block mb-2"></i>No conversations yet.</li>
                    @endforelse
                </ul>
            </div>

            {{-- 2. نافذة الدردشة النشطة --}}
            <div class="col-lg-8 col-xl-9 chat-window" id="chatWindow">
                <div id="no-conversation-selected"><i class="bi bi-chat-square-dots"></i>
                    <p class="lead mt-3">Select a conversation to start chatting</p>
                </div>

                <div class="chat-window-header d-none" id="activeChatHeader">
                    {{-- ▼▼ قمنا بتعديل هذا الزر وإزالة "d-lg-none" ليظهر دائماً ▼▼ --}}
                    <button class="btn me-2 p-1" id="backToConversationsBtn" type="button" title="Back to chats">
                        <i class="bi bi-arrow-left fs-5"></i>
                    </button>
                    <img src="" alt="Avatar" class="avatar" id="activeChatAvatar">
                    <div>
                        <div class="user-name" id="activeChatUserName"></div>
                        <div class="user-status text-muted small">Offline</div>
                    </div>
                    {{-- قمنا بحذف زر الإغلاق (X) بالكامل --}}
                </div>

                <div class="messages-area d-none" id="messagesArea"></div>
                <div class="chat-input-area d-none" id="chatInputArea">
                    <form id="sendMessageForm">
                        <div class="input-group">
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
@endsection

@push('scripts')
    {{-- Firebase SDKs --}}
    <script src="https://www.gstatic.com/firebasejs/9.15.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.15.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.15.0/firebase-firestore-compat.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Define all constants for DOM elements
            const chatContainer = document.getElementById('chatContainer');
            const conversationsList = document.getElementById('conversationsList');
            const messagesArea = document.getElementById('messagesArea');
            const activeChatHeader = document.getElementById('activeChatHeader');
            const activeChatAvatar = document.getElementById('activeChatAvatar');
            const activeChatUserName = document.getElementById('activeChatUserName');
            const noConversationDiv = document.getElementById('no-conversation-selected');
            const chatInputArea = document.getElementById('chatInputArea');
            const sendMessageForm = document.getElementById('sendMessageForm');
            const messageInput = sendMessageForm.querySelector('input[name="body"]');
            const backToConversationsBtn = document.getElementById('backToConversationsBtn');

            // State variables
            let currentConversationId = null;
            const currentUserId = {{ Auth::id() }};
            let nextPageUrl = null;
            let isLoading = false;
            let db = null;
            let unsubscribeFromMessages = null;

            // Firebase Config
            const firebaseConfig = {
                apiKey: "{{ config('services.firebase.api_key') }}",
                authDomain: "{{ config('services.firebase.auth_domain') }}",
                projectId: "{{ config('services.firebase.project_id') }}",
                storageBucket: "{{ config('services.firebase.storage_bucket') }}",
                messagingSenderId: "{{ config('services.firebase.messaging_sender_id') }}",
                appId: "{{ config('services.firebase.app_id') }}"
            };

            // Initialize Firebase App
            firebase.initializeApp(firebaseConfig);
            const auth = firebase.auth();
            const firestoreToken = '{{ $firebaseToken ?? '' }}';

            // Main function to authenticate and start the application
            async function initializeFirebase() {
                try {
                    if (!firestoreToken) throw new Error("Firebase token is missing!");
                    await auth.signInWithCustomToken(firestoreToken);
                    console.log("Firebase authentication successful!");
                    db = firebase.firestore();
                    setupEventListeners();
                    initialLoad();
                } catch (error) {
                    console.error("Firebase authentication failed:", error);
                    noConversationDiv.innerHTML = '<div class="alert alert-danger m-2">Could not connect to chat service. Please refresh the page.</div>';
                }
            }

            // Helper Functions
            function resetToDefaultView() {
                const url = new URL(window.location);
                url.searchParams.delete('activeConversation');
                window.history.replaceState({}, '', url);
                chatContainer.classList.remove('mobile-chat-view');
                document.querySelectorAll('.conversation-item.active').forEach(el => el.classList.remove('active'));
                activeChatHeader.classList.add('d-none');
                messagesArea.classList.add('d-none');
                chatInputArea.classList.add('d-none');
                noConversationDiv.style.display = 'flex';
                if (unsubscribeFromMessages) {
                    unsubscribeFromMessages();
                    unsubscribeFromMessages = null;
                }
                currentConversationId = null;
            }

            async function loadConversation(convId, convElement) {
                if (isLoading) return;
                const url = new URL(window.location);
                url.searchParams.set('activeConversation', convId);
                window.history.replaceState({}, '', url);
                currentConversationId = convId;
                document.querySelectorAll('.conversation-item.active').forEach(el => el.classList.remove('active'));
                convElement.classList.add('active');
                chatContainer.classList.add('mobile-chat-view');
                noConversationDiv.style.display = 'none';
                activeChatHeader.classList.remove('d-none');
                messagesArea.classList.remove('d-none');
                chatInputArea.classList.remove('d-none');
                activeChatUserName.textContent = convElement.querySelector('.name').textContent;
                activeChatAvatar.src = convElement.querySelector('img.avatar').src;
                messagesArea.innerHTML = '<div class="text-center p-3"><div class="spinner-border text-secondary"></div></div>';
                await fetchAndRenderMessagesFromLaravel(`/chat/conversations/${convId}/messages`);
                messageInput.focus();
                listenForNewMessages(convId);
            }

            async function fetchAndRenderMessagesFromLaravel(url, prepend = false) {
                if (isLoading) return;
                isLoading = true;
                if (prepend) messagesArea.insertAdjacentHTML('afterbegin', '<div id="loading-more" class="text-center p-2"><div class="spinner-border spinner-border-sm"></div></div>');
                try {
                    const response = await fetch(url);
                    if (!response.ok) throw new Error('Request failed');
                    const result = await response.json();
                    const messages = result.data.reverse();
                    nextPageUrl = result.next_page_url;
                    if (!prepend && messagesArea.querySelector('.spinner-border')) {
                        messagesArea.innerHTML = '';
                    }
                    let lastUserId = prepend ? (messagesArea.firstChild?.dataset.userId || null) : (messages.length > 0 ? messages[0].user_id : null);
                    messages.forEach((msg, index) => {
                        const showAvatar = index === 0 ? true : msg.user_id != messages[index - 1].user_id;
                        messagesArea.insertAdjacentHTML(prepend ? 'afterbegin' : 'beforeend', createMessageHtmlFromLaravel(msg, showAvatar));
                    });
                    if (!prepend) scrollToBottom();
                } catch (error) {
                    console.error("Fetch/Render Error:", error);
                    if (!prepend) messagesArea.innerHTML = '<div class="alert alert-danger m-2">Could not load messages.</div>';
                } finally {
                    isLoading = false;
                    document.getElementById('loading-more')?.remove();
                }
            }

            function scrollToBottom() {
                setTimeout(() => { messagesArea.scrollTop = messagesArea.scrollHeight; }, 50);
            }

            function createMessageHtmlFromLaravel(message, showAvatar) {
                const isSent = message.user_id === currentUserId;
                const avatarHtml = showAvatar ? `<img src="${message.user.profile_image_url}" class="avatar">` : '<div class="avatar-placeholder"></div>';
                const nameHtml = (showAvatar && !isSent) ? `<div class="message-sender-name">${message.user.name}</div>` : '';
                return `<div class="message-item ${isSent ? 'sent' : 'received'}" data-user-id="${message.user_id}">${avatarHtml}<div class="message-content">${nameHtml}<div>${message.body.replace(/\n/g, '<br>')}</div><span class="message-time">${message.formatted_created_at}</span></div></div>`;
            }

            function createMessageHtmlFromFirestore(messageData, showAvatar) {
                const isSent = messageData.userId === currentUserId;
                const avatarUrl = isSent ? '{{ Auth::user()->profile_image_url }}' : activeChatAvatar.src;
                const avatarHtml = showAvatar ? `<img src="${avatarUrl}" class="avatar">` : '<div class="avatar-placeholder"></div>';
                const nameHtml = (showAvatar && !isSent) ? `<div class="message-sender-name">${messageData.userName}</div>` : '';
                return `<div class="message-item ${isSent ? 'sent' : 'received'}" data-user-id="${messageData.userId}">${avatarHtml}<div class="message-content">${nameHtml}<div>${messageData.message.replace(/\n/g, '<br>')}</div><span class="message-time">Just now</span></div></div>`;
            }

            function listenForNewMessages(conversationId) {
                if (unsubscribeFromMessages) unsubscribeFromMessages();
                unsubscribeFromMessages = db.collection('conversations').doc(conversationId).collection('messages')
                    .orderBy('timestamp').where('timestamp', '>', new Date())
                    .onSnapshot(querySnapshot => {
                        
                    }, error => console.error("Firestore listener error: ", error));
            }

            // Event Listeners Setup
            function setupEventListeners() {
                backToConversationsBtn.addEventListener('click', resetToDefaultView);
                conversationsList.addEventListener('click', e => {
                    const convElement = e.target.closest('.conversation-item');
                    if (convElement && convElement.dataset.conversationId !== currentConversationId) loadConversation(convElement.dataset.conversationId, convElement);
                });
                sendMessageForm.addEventListener('submit', async e => {
    e.preventDefault();
    const body = messageInput.value.trim();
    if (!body || !currentConversationId) return;

    // === ▼▼▼ هذا هو الجزء الجديد والمهم ▼▼▼ ===
    
    // 1. أنشئ نسخة وهمية من الرسالة لعرضها فوراً
    const optimisticMessage = {
        userId: currentUserId,
        userName: '{{ Auth::user()->name }}',
        message: body,
    };
    
    // 2. تحقق من آخر رسالة لتحديد هل يجب عرض الصورة الرمزية
    const lastMsgEl = messagesArea.querySelector('.message-item:last-child');
    const showAvatar = !lastMsgEl || lastMsgEl.dataset.userId != currentUserId;

    // 3. أضف الرسالة إلى الواجهة فوراً
    messagesArea.insertAdjacentHTML('beforeend', createMessageHtmlFromFirestore(optimisticMessage, showAvatar));
    scrollToBottom();
    
    // 4. قم بتحديث قائمة المحادثات الجانبية
    const convInList = conversationsList.querySelector(`[data-conversation-id="${currentConversationId}"]`);
    if (convInList) {
        convInList.querySelector('.last-message').innerHTML = `<span class="text-muted">You:</span> ${body.substring(0, 25)}...`;
        convInList.querySelector('.chat-time').textContent = 'Just now';
        conversationsList.prepend(convInList);
    }
    // === ▲▲▲ نهاية الجزء الجديد ▲▲▲ ===

    const originalText = messageInput.value;
    messageInput.value = ''; // امسح حقل الإدخال فوراً
    
    // الآن، أرسل الرسالة الحقيقية إلى السيرفر في الخلفية
    try {
        const response = await fetch(`/chat/conversations/${currentConversationId}/messages`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ body })
        });
        if (!response.ok) {
            // في حالة الفشل، يمكنك إظهار علامة خطأ بجانب الرسالة
            console.error("Failed to send message to server");
            messageInput.value = originalText; // أعد النص للمستخدم
        }
        // لا نحتاج لعمل أي شيء آخر عند النجاح، لأن الواجهة تم تحديثها بالفعل
    } catch (error) {
        console.error('Send Error:', error);
        messageInput.value = originalText;
    }
});
                messagesArea.addEventListener('scroll', () => { if (messagesArea.scrollTop === 0 && nextPageUrl && !isLoading) fetchAndRenderMessagesFromLaravel(nextPageUrl, true); });
            }

            // Initial Load Logic
            function initialLoad() {
                const initialConvId = new URLSearchParams(window.location.search).get('activeConversation');
                if (initialConvId) {
                    const el = conversationsList.querySelector(`[data-conversation-id="${initialConvId}"]`);
                    if (el) setTimeout(() => el.click(), 250); // Small delay to ensure everything is ready
                }
            }

            // Start the entire process
            initializeFirebase();
        });
    </script>
@endpush

