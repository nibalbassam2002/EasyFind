<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Firestore;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Illuminate\Support\Str;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Illuminate\Support\Carbon;

class ChatController extends Controller
{
    protected $firestore;
    protected $firebaseAuth;
    protected $messaging;

    public function __construct(Firestore $firestore, FirebaseAuth $firebaseAuth, Messaging $messaging)
    {
        $this->firestore = $firestore;
        $this->firebaseAuth = $firebaseAuth;
        $this->messaging = $messaging;
    }

    public function index()
    {
        $user = Auth::user();
        $conversations = $user->conversations()
            ->with(['users', 'lastMessage.user'])
            ->latest('updated_at')
            ->get();

        $customToken = $this->firebaseAuth->createCustomToken((string) $user->id, ['name' => $user->name]);
        $firebaseToken = $customToken->toString();
        
        return view('frontend.chat.index', compact('conversations', 'user', 'firebaseToken'));
    }

    public function fetchMessages(Conversation $conversation): JsonResponse
    {
        if (!$this->isUserInConversation($conversation)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $conversation->messages()->where('user_id', '!=', Auth::id())->whereNull('read_at')->update(['read_at' => now()]);
        
        $messages = $conversation->messages()->with('user')->latest()->paginate(20);
        
        $messages->getCollection()->transform(function ($message) {
            $message->formatted_created_at = $message->created_at->format('h:i A');
            return $message;
        });

        return response()->json($messages);
    }

// في ChatController.php

public function sendMessage(Request $request, Conversation $conversation): JsonResponse
{
    // --- 1. الجزء الحالي للتحقق وحفظ الرسالة (لا تغيير هنا) ---
    if (!$this->isUserInConversation($conversation)) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $validated = $request->validate(['body' => 'required|string|max:2000']);

    $message = $conversation->messages()->create([
        'user_id' => Auth::id(),
        'body' => $validated['body']
    ]);

    // --- 2. الجزء الحالي لإرسال الإشعار (لا تغيير هنا) ---
    $recipient = $conversation->other_participant;
    if ($recipient && $recipient->fcm_token) {
        try {
            $notification = FirebaseNotification::create(
                'New Message from ' . Auth::user()->name,
                Str::limit($validated['body'], 100)
            );
            $messageToSend = CloudMessage::withTarget('token', $recipient->fcm_token)
                ->withNotification($notification)
                ->withData(['click_action' => route('chat.index', ['activeConversation' => $conversation->id])]);
            $this->messaging->send($messageToSend);
        } catch (\Throwable $e) {
            Log::error('FCM_SEND_ERROR: ' . $e->getMessage());
        }
    }
    
    // --- 3. الجزء الحالي لتحديث المحادثة وتحميل البيانات (لا تغيير هنا) ---
    $conversation->touch(); // هذا السطر مهم جداً لتحديث updated_at
    $message->load('user');

    // --- 4. الجزء الحالي لإرسال الرسالة إلى Firestore (لا تغيير هنا) ---
    try {
        $this->firestore->database()->collection('conversations')->document($conversation->id)
            ->collection('messages')->add([
                'userId'    => (int) Auth::id(),
                'userName'  => Auth::user()->name,
                'message'   => $validated['body'],
                'timestamp' => new \Google\Cloud\Core\Timestamp(new \DateTime()),
            ]);
    } catch (\Exception $e) {
        Log::error('FIREBASE_SEND_FAILED: ' . $e->getMessage());
    }

    // ▼▼▼▼▼ بداية الكود الجديد والمضاف ▼▼▼▼▼

    // --- 5. تحميل بيانات المحادثة المحدثة ---
    // نحن نحتاج `lastMessage` و `users` لعرضها في القائمة الجانبية
    $conversation->load(['users', 'lastMessage.user']);
    
    // --- 6. إنشاء كود HTML باستخدام ملف Blade منفصل ---
    // هذا يجعل الكود أنظف وأسهل للتعديل مستقبلاً
    $sidebarHtml = view('frontend.chat.partials.conversation-item', ['conversation' => $conversation])->render();
    
    // --- 7. تجهيز بيانات الرسالة لإرسالها للواجهة الأمامية ---
    $message->formatted_created_at = $message->created_at->format('h:i A');

    // --- 8. إرسال استجابة JSON تحتوي على كل ما نحتاجه ---
    return response()->json([
        'message' => $message,          // بيانات الرسالة لعرضها في منطقة الشات
        'sidebar_html' => $sidebarHtml, // كود HTML لتحديث القائمة الجانبية
    ]);
    
    // ▲▲▲▲▲ نهاية الكود الجديد والمضاف ▲▲▲▲▲
}
    
    public function initiateChatFromPropertyId($property_id)
    {
        $property = Property::with('user')->findOrFail($property_id);
        $currentUser = Auth::user();
        $lister = $property->user;

        if (!$lister || $currentUser->id === $lister->id) {
            return redirect()->back()->with('error', 'Action not allowed.');
        }

        $conversation = $currentUser->conversations()
            ->whereHas('users', function ($q) use ($lister) {
                $q->where('user_id', $lister->id);
            })->first();

        if (!$conversation) {
            $conversation = Conversation::create();
            $conversation->users()->attach([$currentUser->id, $lister->id]);
        }
        
        $initialMessageBody = "Hello, I'm interested in your property: '{$property->title}'. You can view it here: " . route('frontend.property.show', ['property_id' => $property->id]);
        
        $conversation->messages()->create([
            'user_id' => $currentUser->id,
            'body' => $initialMessageBody,
        ]);
        
        $conversation->touch();

        return redirect()->route('chat.index', ['activeConversation' => $conversation->id]);
    }

    private function isUserInConversation(Conversation $conversation): bool
    {
        return $conversation->users()->where('user_id', Auth::id())->exists();
    }
    public function destroyConversation(Conversation $conversation)
{
    // تحقق من أن المستخدم الحالي هو جزء من هذه المحادثة
    if (!$this->isUserInConversation($conversation)) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    // يمكنك إما حذف المحادثة نهائياً أو إخفاؤها عن المستخدم فقط
    // الخيار الأفضل هو إخفاؤها عن المستخدم الحالي فقط
    // هذا يتطلب إضافة جدول وسيط أو عمود في جدول conversation_user

    // للتبسيط الآن، سنقوم بحذفها نهائياً (احذري، هذا سيحذفها عند كلا الطرفين)
    try {
        DB::transaction(function () use ($conversation) {
            $conversation->messages()->delete(); // حذف كل الرسائل
            $conversation->users()->detach();    // فك ارتباط المستخدمين
            $conversation->delete();             // حذف المحادثة نفسها
        });

        return response()->json(['success' => true, 'message' => 'Conversation deleted successfully.']);

    } catch (\Exception $e) {
        Log::error("Failed to delete conversation ID {$conversation->id}: " . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Could not delete the conversation.'], 500);
    }
}
public function requestViewing(Request $request, Conversation $conversation)
{
    if (!$this->isUserInConversation($conversation)) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $validated = $request->validate([
        'slots' => 'required|array|min:1|max:3',
        // التحقق من أن كل عنصر هو تاريخ ووقت صحيح
        'slots.*' => 'required|date_format:Y-m-d H:i', 
    ]);
    
    // تحويل التواريخ إلى صيغة ISO 8601 للتخزين الموحد
    $suggestedSlots = array_map(function($slot) {
        return Carbon::parse($slot)->toIso8601String();
    }, $validated['slots']);
    
    $otherUser = $conversation->other_participant;
    $body = "A viewing request has been sent to {$otherUser->name}.";

    $message = $conversation->messages()->create([
        'user_id' => Auth::id(),
        'body' => $body,
        'type' => 'viewing_request',
        'metadata' => ['slots' => $suggestedSlots]
    ]);

    $conversation->touch();
    $message->load('user');

    $message->formatted_created_at = $message->created_at->format('h:i A');
    return response()->json($message);
}
public function acceptViewing(Request $request, Message $message)
{
    // التأكد من أن الرسالة هي طلب معاينة
    if ($message->type !== 'viewing_request') {
        return response()->json(['error' => 'Invalid message type.'], 400);
    }
    
    $conversation = $message->conversation;
    // التأكد من أن المستخدم الحالي هو الطرف الآخر (البائع) وليس من أرسل الطلب
    if ($message->user_id == Auth::id() || !$this->isUserInConversation($conversation)) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $validated = $request->validate([
        'slot_index' => 'required|integer'
    ]);

    $slots = $message->metadata['slots'];
    $selectedIndex = $validated['slot_index'];

    if (!isset($slots[$selectedIndex])) {
        return response()->json(['error' => 'Invalid slot selected.'], 422);
    }

    $confirmedSlot = $slots[$selectedIndex];
    
    // **مهم جداً:** قم بتحديث الرسالة الأصلية لتعطيل الأزرار (أو إزالتها)
    // لتجنب قبول الموعد مرتين. يمكننا إضافة حالة 'processed'
    $originalMetadata = $message->metadata;
    $originalMetadata['status'] = 'processed';
    $originalMetadata['confirmed_slot'] = $confirmedSlot;
    $originalMetadata['confirmed_by'] = Auth::id();
    $message->metadata = $originalMetadata;
    $message->save();

    // إنشاء رسالة "تأكيد" جديدة
    $newMessage = $conversation->messages()->create([
        'user_id' => Auth::id(), // يمكن جعله system user ID لاحقاً
        'body' => 'A viewing appointment has been confirmed.',
        'type' => 'viewing_confirmed',
        'metadata' => [
            'confirmed_slot' => $confirmedSlot,
            'original_message_id' => $message->id,
        ]
    ]);

    $conversation->touch();
    $newMessage->load('user');

    // أضف إشعارات Firebase هنا إذا أردت...

    $newMessage->formatted_created_at = $newMessage->created_at->format('h:i A');
    return response()->json($newMessage);
}
public function rejectViewing(Request $request, Message $message)
{
    // التأكد من أن الرسالة هي طلب معاينة وأنها لم تعالج بعد
    if ($message->type !== 'viewing_request' || isset($message->metadata['status'])) {
        return response()->json(['error' => 'Invalid or already processed message.'], 400);
    }
    
    $conversation = $message->conversation;
    // التأكد من أن المستخدم الحالي هو الطرف الآخر (المستقبل) وليس من أرسل الطلب
    if ($message->user_id == Auth::id() || !$this->isUserInConversation($conversation)) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // تحديث الرسالة الأصلية
    $originalMetadata = $message->metadata;
    $originalMetadata['status'] = 'rejected';
    $originalMetadata['processed_by'] = Auth::id();
    $message->metadata = $originalMetadata;
    $message->save();

    // إنشاء رسالة نظام جديدة
    $otherUser = User::find($message->user_id);
    $newMessage = $conversation->messages()->create([
        'user_id' => Auth::id(), // رسالة من النظام
        'body' => 'Your viewing request was rejected by ' . Auth::user()->name,
        'type' => 'viewing_rejected',
        'metadata' => [
            'original_message_id' => $message->id,
        ]
    ]);

    $conversation->touch();
    return response()->json(['success' => true, 'message' => $newMessage]);
}

public function cancelViewing(Request $request, Message $message)
{
    // التأكد من أن الرسالة هي طلب معاينة وأنها لم تعالج بعد
    if ($message->type !== 'viewing_request' || isset($message->metadata['status'])) {
        return response()->json(['error' => 'Invalid or already processed message.'], 400);
    }

    $conversation = $message->conversation;
    // التأكد من أن المستخدم الحالي هو من أرسل الطلب
    if ($message->user_id != Auth::id() || !$this->isUserInConversation($conversation)) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // تحديث الرسالة الأصلية
    $originalMetadata = $message->metadata;
    $originalMetadata['status'] = 'cancelled';
    $message->metadata = $originalMetadata;
    $message->save();

    // إنشاء رسالة نظام جديدة
    $newMessage = $conversation->messages()->create([
        'user_id' => Auth::id(), // رسالة من النظام
        'body' => 'The viewing request was cancelled by the sender.',
        'type' => 'viewing_cancelled',
        'metadata' => [
            'original_message_id' => $message->id,
        ]
    ]);

    $conversation->touch();
    return response()->json(['success' => true, 'message' => $newMessage]);
}

}