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
use Kreait\Firebase\Contract\Messaging;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class ChatController extends Controller
{
    // استخدام protected مع تحديد النوع (Type Hinting) هو أفضل ممارسة
    protected Firestore $firestore;
    protected FirebaseAuth $firebaseAuth;
    protected Messaging $messaging;

    /**
     * هذا هو الـ Constructor النظيف الذي يستخدم الحقن التلقائي من Laravel.
     * هذا هو الكود القياسي الذي سيعمل على الاستضافة.
     */
    public function __construct(Firestore $firestore, FirebaseAuth $firebaseAuth, Messaging $messaging)
    {
        $this->firestore = $firestore;
        $this->firebaseAuth = $firebaseAuth;
        $this->messaging = $messaging;
    }

    /**
     * عرض صفحة الشات الرئيسية.
     */
    public function index()
    {
        $user = Auth::user();
        $conversations = $user->conversations()
            ->with(['users', 'lastMessage.user'])
            ->latest('updated_at')
            ->get();
            
        $firebaseToken = null;
        try {
            // استخدام $this->firebaseAuth التي تم حقنها في الـ constructor
            $customToken = $this->firebaseAuth->createCustomToken((string) $user->id, ['name' => $user->name]);
            $firebaseToken = $customToken->toString();
        } catch (\Exception $e) {
            // تسجيل الخطأ وعرض رسالة للمستخدم في حال فشل الاتصال
            Log::error('FIREBASE_AUTH_ERROR_IN_INDEX: ' . $e->getMessage());
            session()->flash('error', 'Could not connect to the chat service. Please try again later.');
        }

        return view('frontend.chat.index', compact('conversations', 'user', 'firebaseToken'));
    }

    /**
     * إرسال رسالة جديدة.
     */
    public function sendMessage(Request $request, Conversation $conversation): JsonResponse
    {
        if (!$this->isUserInConversation($conversation)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $validated = $request->validate(['body' => 'required|string|max:2000']);
        
        $message = $conversation->messages()->create([
            'user_id' => Auth::id(),
            'body' => $validated['body']
        ]);
        $conversation->touch();

        // إرسال إشعار للمستقبل (Push Notification)
        $recipient = $conversation->other_participant;
        if ($recipient && $recipient->fcm_token) {
            try {
                $notification = \Kreait\Firebase\Messaging\Notification::create('New Message from ' . Auth::user()->name, Str::limit($validated['body'], 100));
                $messageToSend = \Kreait\Firebase\Messaging\CloudMessage::withTarget('token', $recipient->fcm_token)
                    ->withNotification($notification)
                    ->withData(['click_action' => route('chat.index', ['activeConversation' => $conversation->id])]);
                
                // استخدام $this->messaging التي تم حقنها
                $this->messaging->send($messageToSend);
            } catch (\Throwable $e) {
                Log::error('FCM_SEND_ERROR: ' . $e->getMessage());
            }
        }

        // الكتابة في Firestore لتفعيل الـ Real-time
        try {
            // استخدام $this->firestore التي تم حقنها
            $db = $this->firestore->database();
            $timestamp = new \Google\Cloud\Core\Timestamp(new \DateTime());
            
            // 1. إضافة الرسالة إلى المجموعة الفرعية
            $db->collection('conversations')->document($conversation->id)
               ->collection('messages')->add([
                    'userId' => (int) Auth::id(), 
                    'userName' => Auth::user()->name,
                    'message' => $validated['body'], 
                    'timestamp' => $timestamp,
                ]);

            // 2. تحديث المستند الرئيسي للمحادثة (هذا هو مفتاح الـ Real-time)
            $db->collection('conversations')->document($conversation->id)
               ->set([
                    'lastMessage' => ['text' => $validated['body'], 'senderId' => (int) Auth::id(), 'senderName' => Auth::user()->name],
                    'updatedAt' => $timestamp,
                    'participants' => $conversation->users()->pluck('id')->toArray() 
                ], ['merge' => true]);

        } catch (\Exception $e) {
            Log::error('FIREBASE_SEND_FAILED: ' . $e->getMessage());
            // لا نرجع خطأ هنا حتى لا يفشل إرسال الرسالة للمرسل في واجهته
        }

        // إرجاع بيانات الرسالة للـ JavaScript ليقوم بتحديث الواجهة فوراً للمرسل
        $message->load('user');
        $message->formatted_created_at = $message->created_at->format('h:i A');
        return response()->json(['success' => true, 'data' => $message]);
    }
    
    // --- الدوال الأخرى (لا حاجة لتغييرها) ---

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
    
    public function initiateChatFromPropertyId($property_id)
    {
        $property = Property::with('user')->findOrFail($property_id);
        $currentUser = Auth::user();
        $lister = $property->user;
        if (!$lister || $currentUser->id === $lister->id) {
            return redirect()->back()->with('error', 'Action not allowed.');
        }
        $conversation = $currentUser->conversations()
            ->whereHas('users', function ($q) use ($lister) { $q->where('user_id', $lister->id); })
            ->first();
        if (!$conversation) {
            $conversation = Conversation::create();
            $conversation->users()->attach([$currentUser->id, $lister->id]);
        }
        $initialMessageBody = "Hello, I'm interested in your property: '{$property->title}'. You can view it here: " . route('frontend.property.show', ['property_id' => $property->id]);
        $conversation->messages()->create(['user_id' => $currentUser->id, 'body' => $initialMessageBody]);
        $conversation->touch();
        return redirect()->route('chat.index', ['activeConversation' => $conversation->id]);
    }

    private function isUserInConversation(Conversation $conversation): bool
    {
        return $conversation->users()->where('user_id', Auth::id())->exists();
    }

    public function destroyConversation(Conversation $conversation)
    {
        if (!$this->isUserInConversation($conversation)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        try {
            DB::transaction(function () use ($conversation) {
                // يمكنك هنا إضافة منطق لحذف البيانات من Firestore أيضاً إذا أردت
                $conversation->messages()->delete();
                $conversation->users()->detach();
                $conversation->delete();
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