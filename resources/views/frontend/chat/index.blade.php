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
                        @php $otherUser = $conversation->other_participant; @endphp
                        @if ($otherUser)
                            <li class="conversation-item" data-conversation-id="{{ $conversation->id }}">
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
                                <button class="delete-conversation-btn" title="Delete Conversation"><i
                                        class="bi bi-trash3-fill"></i></button>
                            </li>
                        @endif
                    @empty
                        <li class="text-center text-muted p-5 list-group-item"><i
                                class="bi bi-chat-dots fs-1 d-block mb-2"></i>No conversations yet.</li>
                    @endforelse
                </ul>
            </div>
            <div class="col-lg-8 col-xl-9 chat-window" id="chatWindow">
                <div id="no-conversation-selected"
                    class="d-flex align-items-center justify-content-center h-100 flex-column text-center text-muted">
                    <i class="bi bi-chat-square-dots fs-1"></i>
                    <p class="lead mt-3">Select a conversation to start chatting</p>
                </div>
                <!-- ## تم إصلاح الـ ID هنا ## -->
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
                    <div class="chat-input-area" id="chatInputArea">
                        <form id="sendMessageForm">
                            <div class="input-group">
                                <input type="text" class="form-control" name="body" placeholder="Type a message..."
                                    autocomplete="off" required>
                                <button class="btn" type="submit" title="Send Message"><i
                                        class="bi bi-send-fill fs-5 text-warning"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ## تم إصلاح اسم المتغير هنا ##
            const activeChatContainer = document.getElementById('active-chat-container');
            const conversationsList = document.getElementById('conversationsList');
            const noConversationDiv = document.getElementById('no-conversation-selected');
            const messagesArea = document.getElementById('messagesArea');
            const sendMessageForm = document.getElementById('sendMessageForm');
            const backToConversationsBtn = document.getElementById('backToConversationsBtn');
            let currentConversationId = null;
            let isLoading = false;
            let pollingInterval = null;

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

            // ## تم إصلاح الدالة هنا ##
            function createMessageHtml(message, isFirstInGroup) {
                const currentUserId = {{ Auth::id() }};
                const isSent = message.user_id == currentUserId;
                // الحصول على الصورة من القائمة الجانبية في حال كانت رسالة مستلمة
                const otherUserAvatar = document.getElementById('activeChatAvatar').src;
                const avatarUrl = isSent ? '{{ Auth::user()->profile_image_url }}' : otherUserAvatar;

                const avatarHtml = `<img src="${avatarUrl}" alt="Avatar" class="avatar">`;
                const timeHtml = `<span class="message-time">${message.formatted_created_at || 'Just now'}</span>`;
                const messageBody = linkify(message.body).replace(/\n/g, '<br>');
                const groupClass = isFirstInGroup ? 'is-first-in-group' : '';
                const itemHtml = `
            <div class="message-item ${isSent ? 'sent' : 'received'} ${groupClass}" data-message-id="${message.id}" data-user-id="${message.user_id}">
                ${!isSent ? avatarHtml : ''}
                <div class="message-content">
                    <div>${messageBody}</div>
                    ${timeHtml}
                </div>
                ${isSent ? avatarHtml : ''}
            </div>
        `;
                return itemHtml;
            }

            async function fetchNewMessages() {
                if (!currentConversationId || document.hidden) return;
                try {
                    const lastMessageElement = messagesArea.querySelector('.message-item:last-child');
                    const lastMessageId = lastMessageElement ? lastMessageElement.dataset.messageId : 0;
                    if (String(lastMessageId).startsWith('temp-')) return;

                    const response = await fetch(
                        `/chat/conversations/${currentConversationId}/messages?since=${lastMessageId}`);
                    if (!response.ok) return;

                    const result = await response.json();
                    const newMessages = result.data.slice().reverse();
                    if (newMessages.length > 0) {
                        let lastMessageUserId = messagesArea.querySelector('.message-item:last-child')?.dataset
                            .userId;
                        newMessages.forEach(msg => {
                            if (!messagesArea.querySelector(`[data-message-id="${msg.id}"]`)) {
                                const isFirst = msg.user_id != lastMessageUserId;
                                messagesArea.insertAdjacentHTML('beforeend', createMessageHtml(msg,
                                    isFirst));
                                lastMessageUserId = msg.user_id;
                            }
                        });
                        scrollToBottom();
                    }
                } catch (error) {
                    console.error('Polling error:', error);
                }
            }

            async function loadConversation(convId, convElement) {
                if (isLoading && currentConversationId == convId) return;
                isLoading = true;
                currentConversationId = convId;
                if (pollingInterval) clearInterval(pollingInterval);

                document.querySelectorAll('.conversation-item.active').forEach(el => el.classList.remove(
                    'active'));
                if (convElement) convElement.classList.add('active');

                noConversationDiv.classList.add('d-none');
                activeChatContainer.classList.remove('d-none');
                activeChatContainer.classList.add('d-flex');
                if (window.innerWidth < 992) {
                    document.getElementById('chatContainer').classList.add('mobile-chat-view');
                }
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
                    result.data.slice().reverse().forEach(msg => {
                        const isFirstInGroup = (msg.user_id != lastMessageUserId);
                        messagesArea.insertAdjacentHTML('beforeend', createMessageHtml(msg,
                            isFirstInGroup));
                        lastMessageUserId = msg.user_id;
                    });
                    scrollToBottom();
                    pollingInterval = setInterval(fetchNewMessages, 7000); // 7 seconds
                } catch (error) {
                    console.error('Error loading messages:', error);
                    messagesArea.innerHTML =
                        '<div class="alert alert-danger m-2">Could not load messages.</div>';
                } finally {
                    isLoading = false;
                }
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
                        if (currentConversationId == convId) {
                            resetToDefaultView();
                        }
                        convItemElement.style.transition = 'opacity 0.3s';
                        convItemElement.style.opacity = '0';
                        setTimeout(() => {
                            convItemElement.remove();
                        }, 300);
                    } else {
                        Swal.fire('Failed!', data.message || 'Could not delete.', 'error');
                    }
                } catch (error) {
                    Swal.fire('Error!', 'An error occurred.', 'error');
                }
            }

            function resetToDefaultView() {
                if (pollingInterval) clearInterval(pollingInterval);
                pollingInterval = null;
                document.getElementById('chatContainer').classList.remove('mobile-chat-view');
                currentConversationId = null;
                document.querySelectorAll('.conversation-item.active').forEach(el => el.classList.remove('active'));
                activeChatContainer.classList.add('d-none');
                activeChatContainer.classList.remove('d-flex');
                noConversationDiv.classList.remove('d-none');
                const url = new URL(window.location);
                url.searchParams.delete('activeConversation');
                window.history.replaceState({}, '', url.toString());
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
                        cancelButtonColor: '#6c757d',
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
                messageInput.focus();

                const tempMessage = {
                    id: 'temp-' + Date.now(),
                    user_id: {{ Auth::id() }},
                    body: originalText,
                    formatted_created_at: 'Sending...'
                };
                const lastMsgEl = messagesArea.querySelector('.message-item:last-child');
                const lastUserId = lastMsgEl ? lastMsgEl.dataset.userId : null;
                const isFirst = String(tempMessage.user_id) !== lastUserId;
                messagesArea.insertAdjacentHTML('beforeend', createMessageHtml(tempMessage, isFirst));
                scrollToBottom();

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
                    const sentMessage = await response.json();
                    const tempElement = messagesArea.querySelector(
                        `[data-message-id="${tempMessage.id}"]`);
                    if (tempElement) {
                        // استبدال العنصر المؤقت بالعنصر الحقيقي
                        tempElement.outerHTML = createMessageHtml(sentMessage, isFirst);
                    }
                } catch (error) {
                    console.error('Failed to send message:', error);
                    const tempElement = messagesArea.querySelector(
                        `[data-message-id="${tempMessage.id}"]`);
                    if (tempElement) {
                        tempElement.querySelector('.message-time').textContent = 'Failed';
                        tempElement.style.opacity = '0.5';
                    }
                }
            });

            function initialLoad() {
                const urlParams = new URLSearchParams(window.location.search);
                const initialConvId = urlParams.get('activeConversation');
                if (initialConvId) {
                    const conversationElement = conversationsList.querySelector(
                        `li[data-conversation-id="${initialConvId}"]`);
                    if (conversationElement) {
                        setTimeout(() => {
                            conversationElement.click();
                        }, 200); // زيادة طفيفة في التأخير لضمان تحميل كل شيء
                    }
                }
            }
            initialLoad();
        });
    </script>
@endpush
