{{-- resources/views/frontend/chat/index.blade.php --}}
@extends('frontend.Layouts.frontend') {{-- أو مسار الـ Layout الصحيح --}}

@section('title', 'My Chats - EasyFind')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
    /* --- Chat Page Specific Styles --- */
    body {
        overflow: hidden; /* منع التمرير للصفحة الرئيسية عند فتح الشات */
    }
    .chat-container-wrapper {
        height: calc(100vh - 77px - 2rem); /* ارتفاع النافبار + margin (77px هو ارتفاع النافبار الافتراضي في layout) */
        /* اضبط 77px إذا كان ارتفاع النافبار لديك مختلفاً */
        /* 2rem هو مجموع my-3 من الحاوية */
        display: flex;
        overflow: hidden;
        background-color: #f8f9fa; /* خلفية رمادية فاتحة للمنطقة كلها */
    }

    /* Conversations List (Sidebar) */
    .chat-sidebar {
        width: 100%;
        max-width: 320px; /* عرض ثابت للشريط الجانبي */
        border-right: 1px solid #dee2e6;
        display: flex;
        flex-direction: column;
        background-color: #fff;
    }
    .chat-sidebar-header {
        padding: 1rem;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .chat-sidebar-header h5 { margin-bottom: 0; font-weight: 600; }
    .chat-sidebar-header .form-control-sm { font-size: 0.875rem; }

    .conversations-list {
        overflow-y: auto;
        flex-grow: 1;
        padding: 0;
        margin: 0;
        list-style: none;
    }
    .conversations-list .list-group-item {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        border: none;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: background-color 0.15s ease-in-out;
    }
    .conversations-list .list-group-item:last-child { border-bottom: none; }
    .conversations-list .list-group-item:hover { background-color: #f8f9fa; }
    .conversations-list .list-group-item.active {
        background-color: var(--navbar-gold-lighter, #fffaf0); /* لون ذهبي فاتح للنشط */
        border-left: 4px solid var(--navbar-gold-color, #f0ad4e);
        padding-left: calc(1rem - 4px);
    }
    .conversations-list img.avatar { width: 40px; height: 40px; object-fit: cover; }
    .conversations-list .chat-info { overflow: hidden; margin-left: 0.75rem; flex-grow: 1;}
    .conversations-list .chat-info .name { font-weight: 600; color: #343a40; margin-bottom: 0.1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;}
    .conversations-list .chat-info .last-message { font-size: 0.85rem; color: #6c757d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .conversations-list .chat-time { font-size: 0.75rem; color: #adb5bd; margin-left: auto; white-space: nowrap; align-self: flex-start; }
    .conversations-list .unread-indicator { /* (اختياري) لدائرة غير مقروءة */
        width: 8px; height: 8px; background-color: var(--bs-primary); border-radius: 50%; margin-left: 5px;
    }


    /* Active Chat Window */
    .chat-window {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        background-color: #fff;
    }
    .chat-window-header {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        align-items: center;
        background-color: #f8f9fa; /* خلفية رمادية فاتحة للهيدر */
    }
    .chat-window-header img.avatar { width: 35px; height: 35px; object-fit: cover; }
    .chat-window-header .user-name { font-weight: 600; margin-left: 0.75rem; }
    .chat-window-header .user-status { font-size: 0.8rem; color: #6c757d; margin-left: 0.5rem; }

    .messages-area {
        flex-grow: 1;
        overflow-y: auto;
        padding: 1rem;
        background-color: #e9ecef; /* خلفية لمنطقة الرسائل */
    }
    .message-item { margin-bottom: 0.75rem; display: flex; }
    .message-item .avatar { width: 30px; height: 30px; object-fit: cover; margin-top: 2px; }
    .message-content {
        max-width: 70%;
        padding: 0.5rem 0.8rem;
        border-radius: 1rem;
        font-size: 0.9rem;
        line-height: 1.4;
    }
    /* الرسائل المرسلة */
    .message-item.sent { flex-direction: row-reverse; }
    .message-item.sent .message-content {
        background-color: var(--navbar-gold-color, #f0ad4e); /* لون ذهبي للرسائل المرسلة */
        color: var(--navbar-gold-text-dark, #343a40);
        margin-right: 0.5rem;
        border-bottom-right-radius: 0.25rem; /* تغيير شكل الزاوية */
    }
    .message-item.sent .avatar { margin-left: 0.5rem; }
    /* الرسائل المستلمة */
    .message-item.received .message-content {
        background-color: #fff;
        border: 1px solid #dee2e6;
        color: #212529;
        margin-left: 0.5rem;
        border-bottom-left-radius: 0.25rem; /* تغيير شكل الزاوية */
    }
    .message-item.received .avatar { margin-right: 0.5rem; }
    .message-time { font-size: 0.7rem; color: #adb5bd; margin-top: 0.2rem; display: block; }
    .message-item.sent .message-time { text-align: right; }


    .chat-input-area {
        padding: 0.75rem 1rem;
        border-top: 1px solid #dee2e6;
        background-color: #f8f9fa;
    }
    .chat-input-area .form-control { border-radius: 20px; padding-right: 40px; /* مساحة لزر الإرسال */ }
    .chat-input-area .btn-send {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        padding: 0.3rem 0.6rem;
        font-size: 1rem;
        background: none;
        border: none;
        color: var(--navbar-gold-color, #f0ad4e);
    }
    .chat-input-area .btn-send:hover { color: var(--navbar-gold-darker, #eca237); }
    #no-conversation-selected {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #6c757d;
    }
    #no-conversation-selected i { font-size: 3rem; margin-bottom: 1rem; }

    /* --- Responsive --- */
    @media (max-width: 767.98px) {
        .chat-sidebar {
            max-width: 100%; /* الشريط الجانبي يأخذ العرض الكامل */
            border-right: none;
            height: auto; /* الارتفاع يتحدد بالمحتوى */
             display: none; /* إخفاء قائمة المحادثات مبدئياً */
        }
         .chat-sidebar.active-mobile {
             display: flex; /* إظهارها عند الحاجة */
             position: absolute;
             top: 0; left: 0; right: 0; bottom: 0;
             z-index: 10;
             height: 100%;
         }

        .chat-window {
            /* نافذة المحادثة تأخذ العرض الكامل */
            display: flex; /* إظهارها دائماً */
        }
         .chat-window-header .back-to-conversations {
             display: inline-block !important; /* إظهار زر الرجوع */
             margin-right: 1rem;
         }
    }
</style>
@endpush

@section('content')
{{-- حاوية رئيسية للمحادثات (لتحديد الارتفاع والعرض) --}}
<div class="container-fluid my-3 chat-container-wrapper">
    <div class="row g-0 h-100 w-100">

        {{-- 1. الشريط الجانبي لقائمة المحادثات --}}
        <div class="col-md-4 col-lg-3 chat-sidebar" id="chatSidebar">
            <div class="chat-sidebar-header">
                <h5>Chats</h5>
                <button class="btn btn-sm btn-outline-secondary d-md-none" id="newChatButtonMobile" title="New Chat (Placeholder)">
                    <i class="bi bi-pencil-square"></i>
                </button>
                {{-- <input type="search" class="form-control form-control-sm" placeholder="Search chats..."> --}}
            </div>
            <ul class="list-group list-group-flush conversations-list" id="conversationsList">
                {{-- سيتم ملء المحادثات هنا بواسطة JavaScript --}}
                @if($conversations->isEmpty())
                    <li class="list-group-item text-center text-muted p-5">
                        <i class="bi bi-chat-dots fs-1 d-block mb-2"></i>
                        No conversations yet.
                    </li>
                @else
                    @foreach($conversations as $conversation)
                        @php
                            // افتراض أن الطرف الآخر هو أول مستخدم في العلاقة (عدل هذا المنطق إذا لزم الأمر)
                            $otherUser = $conversation->users->first(); // يجب أن يكون هذا هو الطرف الآخر فقط
                            $lastMessage = $conversation->lastMessage;
                        @endphp
                        @if($otherUser)
                            <li class="list-group-item conversation-item" data-conversation-id="{{ $conversation->id }}">
                                <img src="{{ $otherUser->profile_image_url ?? asset('frontend/assets/default-avatar.png') }}" alt="{{ $otherUser->name }}" class="rounded-circle avatar">
                                <div class="chat-info">
                                    <div class="name">{{ $otherUser->name }}</div>
                                    <div class="last-message">
                                        @if($lastMessage)
                                            @if($lastMessage->user_id == Auth::id())
                                                <span class="text-muted">You:</span>
                                            @endif
                                            {{ Str::limit($lastMessage->body, 25) }}
                                        @else
                                            No messages yet.
                                        @endif
                                    </div>
                                </div>
                                <div class="chat-time">
                                    {{ $conversation->updated_at->shortAbsoluteDiffForHumans() }}
                                    {{-- يمكنك إضافة مؤشر رسائل غير مقروءة هنا --}}
                                </div>
                            </li>
                        @endif
                    @endforeach
                @endif
            </ul>
            {{-- ترقيم الصفحات لقائمة المحادثات (إذا كان هناك الكثير) --}}
            @if($conversations->hasPages())
            <div class="p-2 border-top">
                {{ $conversations->links('pagination::simple-bootstrap-5') }}
            </div>
            @endif
        </div>

        {{-- 2. نافذة المحادثة النشطة --}}
        <div class="col-md-8 col-lg-9 chat-window" id="chatWindow">
            {{-- سيتم ملء هذا الجزء بواسطة JavaScript --}}
            <div id="no-conversation-selected">
                <i class="bi bi-chat-square-dots"></i>
                <p class="lead">Select a conversation to start chatting.</p>
            </div>

            {{-- هيدر المحادثة النشطة (مخفي مبدئياً) --}}
            <div class="chat-window-header d-none" id="activeChatHeader">
                 <button class="btn btn-sm btn-link d-md-none back-to-conversations" id="backToConversationsBtn" title="Back to list">
                    <i class="bi bi-arrow-left fs-5"></i>
                </button>
                <img src="" alt="Avatar" class="rounded-circle avatar" id="activeChatAvatar">
                <div>
                    <div class="user-name" id="activeChatUserName"></div>
                    <div class="user-status" id="activeChatUserStatus">Offline</div> {{-- (اختياري) يمكن تحديث الحالة لاحقاً --}}
                </div>
                {{-- يمكنك إضافة زر خيارات هنا (مثل حذف المحادثة، حظر المستخدم) --}}
            </div>

            {{-- منطقة عرض الرسائل (مخفية مبدئياً) --}}
            <div class="messages-area d-none" id="messagesArea">
                {{-- الرسائل سيتم إضافتها هنا بواسطة JavaScript --}}
            </div>

            {{-- منطقة إدخال الرسالة (مخفية مبدئياً) --}}
            <div class="chat-input-area d-none" id="chatInputArea">
                <form id="sendMessageForm">
                    <div class="input-group">
                        <input type="text" class="form-control" name="message_body" placeholder="Type a message..." aria-label="Type a message" autocomplete="off" required>
                        <button class="btn btn-send" type="submit" title="Send Message"><i class="bi bi-send-fill"></i></button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const conversationsList = document.getElementById('conversationsList');
    const chatWindow = document.getElementById('chatWindow');
    const noConversationSelectedDiv = document.getElementById('no-conversation-selected');
    const activeChatHeader = document.getElementById('activeChatHeader');
    const activeChatAvatar = document.getElementById('activeChatAvatar');
    const activeChatUserName = document.getElementById('activeChatUserName');
    const activeChatUserStatus = document.getElementById('activeChatUserStatus'); // (اختياري)
    const messagesArea = document.getElementById('messagesArea');
    const chatInputArea = document.getElementById('chatInputArea');
    const sendMessageForm = document.getElementById('sendMessageForm');
    const messageInput = sendMessageForm ? sendMessageForm.querySelector('input[name="message_body"]') : null;
    const chatSidebar = document.getElementById('chatSidebar');
    const backToConversationsBtn = document.getElementById('backToConversationsBtn');

    let currentConversationId = null; // لتتبع المحادثة النشطة حالياً
    const currentUserId = {{ Auth::id() }}; // ID المستخدم الحالي (مهم للتمييز بين مرسل ومستقبل)

    // --- 1. معالجة النقر على محادثة ---
    if (conversationsList) {
        conversationsList.addEventListener('click', function (event) {
            const listItem = event.target.closest('.conversation-item');
            if (listItem) {
                const conversationId = listItem.dataset.conversationId;
                if (conversationId && conversationId !== currentConversationId) {
                    // إزالة كلاس active من المحادثة القديمة
                    const currentlyActive = conversationsList.querySelector('.list-group-item.active');
                    if (currentlyActive) {
                        currentlyActive.classList.remove('active');
                    }
                    // إضافة كلاس active للمحادثة الجديدة
                    listItem.classList.add('active');
                    loadConversation(conversationId);

                    // على الشاشات الصغيرة، أخفِ الشريط الجانبي وأظهر نافذة المحادثة
                    if (window.innerWidth < 768) {
                        if(chatSidebar) chatSidebar.classList.remove('active-mobile');
                        if(chatWindow) chatWindow.style.display = 'flex';
                    }
                }
            }
        });
    }

    // --- 2. دالة لجلب وعرض رسائل محادثة معينة ---
    async function loadConversation(conversationId) {
        currentConversationId = conversationId; // تحديد المحادثة النشطة
        if(noConversationSelectedDiv) noConversationSelectedDiv.style.display = 'none'; // إخفاء الرسالة الافتراضية
        if(messagesArea) messagesArea.innerHTML = '<div class="text-center p-3"><div class="spinner-border spinner-border-sm text-secondary" role="status"><span class="visually-hidden">Loading...</span></div></div>'; // مؤشر تحميل
        if(activeChatHeader) activeChatHeader.classList.remove('d-none');
        if(messagesArea) messagesArea.classList.remove('d-none');
        if(chatInputArea) chatInputArea.classList.remove('d-none');

        try {
            const response = await fetch(`/chat/conversations/${conversationId}/messages`); // استخدام المسار الصحيح
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();

            // تحديث هيدر المحادثة النشطة (نفترض أن المحادثة ثنائية)
            const conversationItem = conversationsList.querySelector(`.conversation-item[data-conversation-id="${conversationId}"]`);
            if (conversationItem) {
                const userName = conversationItem.querySelector('.name').textContent;
                const userAvatar = conversationItem.querySelector('img.avatar').src;
                if(activeChatAvatar) activeChatAvatar.src = userAvatar;
                if(activeChatUserName) activeChatUserName.textContent = userName;
                // يمكنك إضافة منطق لجلب الحالة (online/offline) لاحقاً
                if(activeChatUserStatus) activeChatUserStatus.textContent = 'Offline'; // افتراضي
            }

            // عرض الرسائل
            renderMessages(data.messages || []);

        } catch (error) {
            console.error('Error fetching messages:', error);
            if(messagesArea) messagesArea.innerHTML = '<div class="alert alert-danger text-center">Failed to load messages.</div>';
        }
    }

    // --- 3. دالة لعرض الرسائل في منطقة الشات ---
    function renderMessages(messages) {
        if (!messagesArea) return;
        messagesArea.innerHTML = ''; // مسح الرسائل القديمة
        messages.forEach(message => {
            const messageItemDiv = document.createElement('div');
            messageItemDiv.classList.add('message-item');
            messageItemDiv.classList.add(message.user_id === currentUserId ? 'sent' : 'received');

            const avatarSrc = message.user.profile_image_url || "{{ asset('frontend/assets/default-avatar.png') }}";
            const messageTime = new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            messageItemDiv.innerHTML = `
                <img src="${avatarSrc}" alt="${message.user.name}" class="rounded-circle avatar">
                <div class="message-content">
                    <div>${message.body.replace(/\n/g, '<br>')}</div>
                    <span class="message-time">${messageTime}</span>
                </div>
            `;
            messagesArea.appendChild(messageItemDiv);
        });
        // التمرير لأسفل منطقة الرسائل
        messagesArea.scrollTop = messagesArea.scrollHeight;
    }

    // --- 4. معالجة إرسال رسالة جديدة ---
    if (sendMessageForm && messageInput) {
        sendMessageForm.addEventListener('submit', async function(event) {
            event.preventDefault();
            const messageBody = messageInput.value.trim();
            if (!messageBody || !currentConversationId) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const sendButton = sendMessageForm.querySelector('button[type="submit"]');
            const originalButtonHtml = sendButton.innerHTML;
            sendButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            sendButton.disabled = true;

            try {
                const response = await fetch(`/chat/conversations/${currentConversationId}/messages`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ message_body: messageBody })
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
                }
                const data = await response.json();

                if (data.success && data.message) {
                    // إضافة الرسالة المرسلة لنافذة الشات فوراً
                    appendMessage(data.message);
                    messageInput.value = ''; // مسح حقل الإدخال
                    // TODO: تحديث قائمة المحادثات (نقل هذه المحادثة للأعلى وتحديث آخر رسالة)
                } else {
                    console.error('Failed to send message:', data.message);
                    alert(data.message || 'Failed to send message.');
                }
            } catch (error) {
                console.error('Error sending message:', error);
                alert('An error occurred while sending your message.');
            } finally {
                sendButton.innerHTML = originalButtonHtml;
                sendButton.disabled = false;
                messageInput.focus();
            }
        });
    }

    // دالة لإضافة رسالة واحدة لمنطقة الرسائل (للاستخدام بعد الإرسال)
    function appendMessage(message) {
        if (!messagesArea) return;
        const messageItemDiv = document.createElement('div');
        messageItemDiv.classList.add('message-item');
        messageItemDiv.classList.add(message.user_id === currentUserId ? 'sent' : 'received'); // يفترض أن data.message يحتوي على user

        const avatarSrc = message.user.profile_image_url || "{{ asset('frontend/assets/default-avatar.png') }}";
        const messageTime = new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        messageItemDiv.innerHTML = `
            <img src="${avatarSrc}" alt="${message.user.name}" class="rounded-circle avatar">
            <div class="message-content">
                <div>${message.body.replace(/\n/g, '<br>')}</div>
                <span class="message-time">${messageTime}</span>
            </div>
        `;
        messagesArea.appendChild(messageItemDiv);
        messagesArea.scrollTop = messagesArea.scrollHeight; // التمرير للأسفل
    }


    // --- 5. التعامل مع زر الرجوع على الشاشات الصغيرة ---
    if (backToConversationsBtn && chatSidebar && chatWindow) {
        backToConversationsBtn.addEventListener('click', function() {
            chatWindow.style.display = 'none';    // إخفاء نافذة المحادثة
            activeChatHeader.classList.add('d-none');
            messagesArea.classList.add('d-none');
            chatInputArea.classList.add('d-none');
            noConversationSelectedDiv.style.display = 'flex'; // إظهار الرسالة الافتراضية
            chatSidebar.classList.add('active-mobile'); // إظهار قائمة المحادثات
            currentConversationId = null; // إلغاء تحديد المحادثة النشطة
            // إزالة كلاس active من عناصر القائمة
            const currentlyActive = conversationsList.querySelector('.list-group-item.active');
            if (currentlyActive) {
                currentlyActive.classList.remove('active');
            }
        });
    }

     // --- (اختياري) تحميل أول محادثة عند فتح الصفحة إذا كان هناك ID في الـ URL ---
     const urlParams = new URLSearchParams(window.location.search);
     const initialConversationId = urlParams.get('conversation_id');
     if (initialConversationId) {
         const initialConvItem = conversationsList.querySelector(`.conversation-item[data-conversation-id="${initialConversationId}"]`);
         if (initialConvItem) {
             initialConvItem.click(); // محاكاة النقر لفتح المحادثة
         }
     } else if (window.innerWidth >= 768 && conversationsList && conversationsList.firstElementChild && conversationsList.firstElementChild.classList.contains('conversation-item')) {
         // (اختياري) تحميل أول محادثة تلقائياً على الشاشات الكبيرة إذا لم يكن هناك ID محدد
         // conversationsList.firstElementChild.click();
     }

});
</script>
@endpush