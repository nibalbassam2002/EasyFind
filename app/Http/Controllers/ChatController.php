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

// في app/Http/Controllers/ChatController.php

    public function index()
    {
        $user = Auth::user();
        // ▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼
        //     هنا هو السطر الذي كان ناقصاً
        // ▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼
        $userId = $user->id;

        $conversations = $user->conversations()
            ->with(['users', 'lastMessage.user'])
            ->latest('updated_at')
            ->get();

        // جلب كل العقارات التي يملكها المستخدم الحالي مرة واحدة فقط
        $userOwnedPropertyIds = Property::where('user_id', $userId)->pluck('id')->toArray();

        $conversations->each(function ($conversation) use ($userOwnedPropertyIds) { // أزلنا $userId من هنا لأنه لم يعد ضرورياً داخل الـ closure
            // البحث عن آخر رسالة تحتوي على رابط عقار
            $lastPropertyMessage = $conversation->messages()
                ->where('body', 'like', '%/properties/show/%')
                ->latest()
                ->first();
            
            $propertyId = null;
            if ($lastPropertyMessage) {
                preg_match('/\/properties\/show\/(\d+)/', $lastPropertyMessage->body, $matches);
                if (isset($matches[1])) {
                    $propertyId = (int) $matches[1];
                }
            }
            
            // نضيف خاصيتين جديدتين لكل محادثة
            $conversation->last_discussed_property_id = $propertyId;
            // هل المستخدم الحالي هو مالك العقار الذي تتم مناقشته؟
            $conversation->is_current_user_property_owner = $propertyId ? in_array($propertyId, $userOwnedPropertyIds) : false;
        });

        $firebaseToken = null;
        try {
            $customToken = $this->firebaseAuth->createCustomToken((string) $user->id, ['name' => $user->name]);
            $firebaseToken = $customToken->toString();
        } catch (\Exception $e) {
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
    
// في ChatController.php

public function initiateChatFromPropertyId($property_id)
{
    $property = Property::with('user')->findOrFail($property_id);
    $currentUser = Auth::user();
    $lister = $property->user;

    if (!$lister || $currentUser->id === $lister->id) {
        return redirect()->back()->with('error', 'Action not allowed.');
    }

    $conversation = Conversation::whereHas('users', function ($query) use ($currentUser) {
            $query->where('user_id', $currentUser->id);
        })
        ->whereHas('users', function ($query) use ($lister) {
            $query->where('user_id', $lister->id);
        })
        ->withCount('users')
        ->having('users_count', 2)
        ->first();

    if (!$conversation) {
        $conversation = DB::transaction(function () use ($currentUser, $lister) {
            $conv = Conversation::create();
            $conv->users()->attach([$currentUser->id, $lister->id]);
            return $conv;
        });
    }

    $initialMessageExists = $conversation->messages()
        ->where('user_id', $currentUser->id)
        ->where('body', 'like', '%' . route('frontend.property.show', ['property_id' => $property->id]) . '%')
        ->exists();

    if (!$initialMessageExists) {
        $initialMessageBody = "Hello, I'm interested in your property: '{$property->title}'. You can view it here: " . route('frontend.property.show', ['property_id' => $property->id]);
        
        $conversation->messages()->create([
            'user_id' => $currentUser->id,
            'body' => $initialMessageBody,
        ]);
        $conversation->touch();
    }

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
    // 1. التحقق من الصلاحيات: هل المستخدم الحالي جزء من هذه المحادثة؟
    if (!$this->isUserInConversation($conversation)) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // 2. التحقق من وجود طلبات سابقة: هل هناك طلب معلق أو موعد مؤكد في المستقبل؟
    $isPendingRequest = $conversation->messages()
        ->where('type', 'viewing_request')
        ->whereJsonContains('metadata->status', 'pending')
        ->exists();
    
    $isScheduledAppointment = $conversation->messages()
        ->where('type', 'viewing_confirmed')
        ->where('metadata->confirmed_slot', '>=', now())
        ->exists();

    if ($isPendingRequest || $isScheduledAppointment) {
        return response()->json([
            'message' => 'You cannot send a new request while another viewing is pending or scheduled.'
        ], 409); // 409 Conflict: يوجد تعارض
    }

    // 3. التحقق من صحة البيانات المدخلة من المستخدم
    $validated = $request->validate([
        'slots' => 'required|array|min:1|max:3',
        'slots.*' => 'required|date_format:Y-m-d H:i', 
        'property_id' => 'required|integer|exists:properties,id' // التأكد من أن العقار موجود
    ]);

    // 4. التحقق من منطق العمل: هل الطرف الآخر هو فعلاً مالك العقار؟
    $property = Property::find($validated['property_id']);
    
    // تأكد من أن دالة other_participant موجودة في موديل Conversation
    $otherUser = $conversation->other_participant;

    // إذا لم نجد الطرف الآخر، أو إذا كان ID مالك العقار لا يتطابق مع ID الطرف الآخر
    if (!$otherUser || $property->user_id !== $otherUser->id) {
        return response()->json(['message' => 'The other user is not the owner of this property.'], 422); // 422: لا يمكن معالجة الطلب
    }

    // 5. تجهيز البيانات للحفظ
    $suggestedSlots = array_map(fn($slot) => Carbon::parse($slot)->toIso8601String(), $validated['slots']);
    
    // 6. إنشاء وحفظ رسالة طلب المعاينة
    $message = new Message();
    $message->conversation_id = $conversation->id;
    $message->user_id = Auth::id(); // المرسل هو المستخدم الحالي
    $message->body = "A viewing request has been sent for property: '{$property->title}'"; // رسالة توضيحية
    $message->type = 'viewing_request';
    $message->metadata = [
        'slots' => $suggestedSlots, 
        'status' => 'pending',
        'property_id' => (int) $validated['property_id'] // أهم جزء: تخزين ID العقار
    ];
    $message->save(); 

    // 7. تحديث المحادثة وإرجاع الرد
    $conversation->touch(); // لتحديث updated_at وجعل المحادثة في الأعلى
    $message->load('user'); // تحميل بيانات المستخدم مع الرسالة للـ JavaScript
    $message->formatted_created_at = $message->created_at->format('h:i A');

    return response()->json(['success' => true, 'data' => $message]);
}
public function acceptViewing(Request $request, Message $message)
{
    // 1. التحقق من الصلاحيات والنوع
    if ($message->type !== 'viewing_request') {
        return response()->json(['error' => 'Invalid message type.'], 400);
    }
    if ($message->user_id == Auth::id() || !$this->isUserInConversation($message->conversation)) {
        return response()->json(['error' => 'Unauthorized action.'], 403);
    }

    // 2. التحقق من صحة المدخلات
    $validated = $request->validate([
        'slot_index' => 'required|integer'
    ]);
    $slots = $message->metadata['slots'] ?? [];
    $selectedIndex = $validated['slot_index'];
    if (!isset($slots[$selectedIndex])) {
        return response()->json(['error' => 'Invalid slot selected.'], 422);
    }
    $confirmedSlot = $slots[$selectedIndex];

    // === الجزء الأول: تحديث رسالة الطلب الأصلية ===
    $originalMetadata = $message->metadata;
    $originalMetadata['status'] = 'processed';
    $originalMetadata['confirmed_slot'] = $confirmedSlot;
    $originalMetadata['confirmed_by'] = Auth::id();
    $message->metadata = $originalMetadata;
    $message->save();


    // === الجزء الثاني: إنشاء رسالة التأكيد الجديدة بالبيانات الصحيحة ===
    $newMessage = new Message();
    $newMessage->conversation_id = $message->conversation->id;
    $newMessage->user_id = Auth::id();
    $newMessage->body = 'A viewing appointment has been confirmed.';
    $newMessage->type = 'viewing_confirmed';
    
    // نقرأ property_id من رسالة الطلب الأصلية ($message) ونضمن أنه رقم
    $propertyIdFromRequest = data_get($message, 'metadata.property_id');
    
    $newMessage->metadata = [
        'confirmed_slot' => $confirmedSlot,
        'original_message_id' => $message->id,
        // نستخدم المتغير الذي قرأناه للتو ونحوله إلى رقم
        'property_id' => $propertyIdFromRequest ? (int)$propertyIdFromRequest : null
    ];
    
    $newMessage->save();
    
    // 6. تحديث المحادثة وإرجاع الرد
    $message->conversation->touch();
    $newMessage->load('user');
    $newMessage->formatted_created_at = $newMessage->created_at->format('h:i A');
    
    // ملاحظة: كنا نرجع $newMessage، ولكن الأفضل إرجاع كائن يعبر عن النجاح
    // ثم يقوم الـ JavaScript بإعادة تحميل المحادثة بالكامل.
    // هذا يضمن ظهور كل التغييرات (تحديث الطلب الأصلي + رسالة التأكيد).
    return response()->json(['success' => true]);
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