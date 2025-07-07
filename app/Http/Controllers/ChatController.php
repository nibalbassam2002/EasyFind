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
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class ChatController extends Controller
{
    protected Firestore $firestore;
    protected FirebaseAuth $firebaseAuth;
    protected Messaging $messaging;


    public function __construct(Firestore $firestore, FirebaseAuth $firebaseAuth, Messaging $messaging)
    {
        $this->firestore = $firestore;
        $this->firebaseAuth = $firebaseAuth;
        $this->messaging = $messaging;
    }



    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;

        $conversations = $user->conversations()
            ->with(['users', 'lastMessage.user'])
            ->latest('updated_at')
            ->get();

        $userOwnedPropertyIds = Property::where('user_id', $userId)->pluck('id')->toArray();

        $conversations->each(function ($conversation) use ($userOwnedPropertyIds) { 
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
            

            $conversation->last_discussed_property_id = $propertyId;

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
                
                $this->messaging->send($messageToSend);
            } catch (\Throwable $e) {
                Log::error('FCM_SEND_ERROR: ' . $e->getMessage());
            }
        }

       
        try {
          
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
    $suggestedSlots = array_map(fn($slot) => Carbon::parse($slot)->format('Y-m-d H:i:s'), $validated['slots']);
    
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
        'original_request_message_id' => $message->id,
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
public function makeOffer(Request $request, Conversation $conversation): JsonResponse
{
    // 1. التحقق من الصلاحيات: هل المستخدم الحالي جزء من المحادثة؟
    if (!$this->isUserInConversation($conversation)) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // 2. التحقق من أن مرسل العرض ليس مالك العقار
    $property = Property::find($request->input('property_id'));
    if (!$property || $property->user_id == Auth::id()) {
        return response()->json(['message' => 'Property owner cannot make an offer on their own property.'], 422);
    }

    // 3. التحقق من صحة البيانات المدخلة
    $validated = $request->validate([
        'property_id'    => 'required|integer|exists:properties,id',
        'amount'         => 'required|numeric|min:1',
        'payment_method' => 'required|string|in:online,offline',
        'notes'          => 'nullable|string|max:1000',
        'viewing_request_message_id' => 'nullable|integer|exists:messages,id'
    ]);

    // 4. إنشاء وحفظ رسالة "تقديم العرض"
    $message = new Message();
    $message->conversation_id = $conversation->id;
    $message->user_id = Auth::id(); // المرسل هو المستخدم الحالي
    $message->body = "An offer of $" . number_format($validated['amount']) . " has been made."; // رسالة توضيحية
    $message->type = 'offer_made'; // نوع جديد للرسالة
    $message->metadata = [
        'property_id'    => $validated['property_id'],
        'amount'         => (float) $validated['amount'],
        'payment_method' => $validated['payment_method'],
        'notes'          => $validated['notes'],
        'status'         => 'pending', // الحالة الأولية للعرض هي "معلق"
        'original_request_message_id' => $validated['viewing_request_message_id'] ?? null
    ];
    $message->save();

    // 5. تحديث المحادثة وإرجاع الرد
    $conversation->touch();
    
    // سنقوم بإعادة تحميل المحادثة في الواجهة الأمامية، لذا نرسل رد نجاح فقط
    return response()->json(['success' => true, 'message' => 'Offer sent successfully!']);
}
public function acceptOffer(Request $request, Message $message): JsonResponse
{
    // 1. التحقق من الصلاحيات
    if ($message->type !== 'offer_made' || $message->user_id == Auth::id()) {
        return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
    }
    if (data_get($message, 'metadata.status') !== 'pending') {
        return response()->json(['success' => false, 'message' => 'This offer has already been processed.'], 422);
    }

    // 2. جلب البيانات اللازمة
    $metadata = $message->metadata;
    $propertyId = data_get($metadata, 'property_id');
    $property = Property::find($propertyId);

    if (!$property) {
        return response()->json(['success' => false, 'message' => 'Property not found.'], 404);
    }
    
    // 3. تنفيذ الإجراءات داخل Transaction
    try {
        DB::transaction(function () use ($message, $property, &$metadata) {
            
            // الخطوة أ: تحديث حالة رسالة العرض إلى "مقبول"
            $metadata['status'] = 'accepted';
            $metadata['processed_by'] = Auth::id();
            $metadata['processed_at'] = now()->toDateTimeString();
            
            // الخطوة ب: إذا كان الدفع Offline، نوثق الصفقة
            if ($metadata['payment_method'] === 'offline') {
                
                Transaction::create([
                    'user_id'         => $message->user_id,
                    'property_id'     => $property->id,
                    'amount'          => $metadata['amount'],
                    'type'            => $property->purpose,
                    'status'          => 'completed',
                    'payment_method'  => 'offline',
                ]);

                $property->status = ($property->purpose === 'rent') ? 'rented' : 'sold';
                $property->save();
                
                // ▼▼▼ التغيير الأهم هنا ▼▼▼
                // نضع علامة أن الصفقة تمت بالكامل
                $metadata['deal_completed'] = true;
                
                // إرسال رسالة نظام
                $message->conversation->messages()->create([
                    'user_id' => 0,
                    'type'    => 'system',
                    'body'    => "The offline deal for '{$property->title}' has been confirmed and completed."
                ]);
            }
            
            // الخطوة ج: حفظ التغييرات على رسالة العرض في كل الحالات
            // الآن سيتم حفظ 'deal_completed' = true بشكل صحيح
            $message->metadata = $metadata;
            $message->save();
        });

    } catch (\Throwable $e) {
        Log::error("Accept Offer Failed for message ID {$message->id}: " . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
    }

    $message->conversation->touch();
    return response()->json(['success' => true]);
}

public function rejectOffer(Request $request, Message $message): JsonResponse
{
    // التحقق من أن الرسالة هي عرض وأن المستخدم الحالي هو البائع
    if ($message->type !== 'offer_made' || $message->user_id == Auth::id()) {
        return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
    }
    // التأكد من أن العرض لا يزال معلقاً
    if (data_get($message, 'metadata.status') !== 'pending') {
        return response()->json(['success' => false, 'message' => 'This offer has already been processed.'], 422);
    }

    $metadata = $message->metadata;
    $metadata['status'] = 'rejected'; // تحديث الحالة
    $metadata['processed_by'] = Auth::id();
    $metadata['processed_at'] = now()->toDateTimeString();
    $message->metadata = $metadata;
    $message->save();
    
    $message->conversation->touch();
    return response()->json(['success' => true]);
}
public function simulatePayment(Request $request, Message $message): JsonResponse
{
    // 1. تحقق من الصلاحيات: هل الرسالة عرض مقبول؟ هل المستخدم هو المشتري؟
    if ($message->type !== 'offer_made' || data_get($message, 'metadata.status') !== 'accepted' || $message->user_id !== Auth::id()) {
        return response()->json(['success' => false, 'message' => 'Invalid action.'], 403);
    }

    $metadata = $message->metadata;

    // 2. تحقق من أن الدفع لم يتم محاكاته من قبل
    if (data_get($metadata, 'payment_simulated') === true) {
        return response()->json(['success' => false, 'message' => 'Payment has already been simulated.'], 422);
    }
    
    $propertyId = data_get($metadata, 'property_id');
    $property = Property::find($propertyId);
    if (!$property) {
        return response()->json(['success' => false, 'message' => 'Property not found.'], 404);
    }

    // 3. ابدأ عملية التوثيق
    try {
        DB::transaction(function () use ($message, $property, &$metadata) {
            // أ. إنشاء سجل المعاملة (Transaction)
            Transaction::create([
                'user_id' => Auth::id(), // المشتري
                'property_id' => $property->id,
                'amount' => $metadata['amount'],
                'type' => $property->purpose, // 'sale' or 'rent'
                'status' => 'completed', // بما أنها محاكاة، نعتبرها مكتملة
                'payment_method' => 'simulated_online',
            ]);

            // ب. تحديث حالة العقار
            $property->status = 'sold'; // أو 'rented' بناءً على الغرض
            $property->save();
            
            // ج. تحديث رسالة العرض لوضع علامة أن الدفع تم
            $metadata['payment_simulated'] = true;
            $metadata['payment_simulated_at'] = now()->toDateTimeString();
            $metadata['deal_completed'] = true;
            $message->metadata = $metadata;
            $message->save();

            $message->conversation->messages()->create([
                'user_id' => 0,
                'type' => 'system',
                'body' => "Payment confirmed for property '{$property->title}'. The deal is now complete."
            ]);
        });
    } catch (\Throwable $e) {
        Log::error("Simulated Payment Failed: " . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'An error occurred while processing the transaction.'], 500);
    }

    $message->conversation->touch();
    return response()->json(['success' => true]);
}

}