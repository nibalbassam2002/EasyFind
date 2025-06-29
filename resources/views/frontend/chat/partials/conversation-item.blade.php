@php
    // جلب المستخدم الآخر في المحادثة
    $otherUser = $conversation->other_participant;
@endphp

{{-- تأكد من وجود مستخدم آخر قبل عرض أي شيء --}}
@if ($otherUser)
    <li class="list-group-item conversation-item {{ request()->query('activeConversation') == $conversation->id ? 'active' : '' }}" 
        data-conversation-id="{{ $conversation->id }}"
        data-is-owner="{{ $conversation->is_current_user_property_owner ? 'true' : 'false' }}">
        
        {{-- 1. الصورة الرمزية للمستخدم الآخر --}}
        <img src="{{ $otherUser->profile_image_url }}" alt="{{ $otherUser->name }}" class="avatar">
        
        {{-- 2. معلومات المحادثة (الاسم وآخر رسالة) --}}
        <div class="chat-info">
            <div class="name">{{ $otherUser->name }}</div>
            <div class="last-message">
                @if ($conversation->lastMessage)
                    @if ($conversation->lastMessage->user_id == Auth::id())
                        <span class="text-muted">You: </span>
                    @endif
                    {{ Illuminate\Support\Str::limit($conversation->lastMessage->body, 25) }}
                @else
                    <span class="text-muted">No messages yet.</span>
                @endif
            </div>
        </div>
        
        {{-- 3. الوقت وعدد الرسائل غير المقروءة على اليسار --}}
        <div class="chat-time-and-badge">
            <div class="chat-time">{{ $conversation->updated_at->shortAbsoluteDiffForHumans() }}</div>
            @if ($conversation->unread_messages_count > 0)
                <span class="badge bg-warning text-dark rounded-pill unread-count mt-1">
                    {{ $conversation->unread_messages_count }}
                </span>
            @endif
        </div>
        
        {{-- 4. زر الحذف الذي يظهر عند المرور بالفأرة --}}
        <button class="delete-conversation-btn" title="Delete Conversation">
            <i class="bi bi-trash3-fill"></i>
        </button>

    </li>
@endif