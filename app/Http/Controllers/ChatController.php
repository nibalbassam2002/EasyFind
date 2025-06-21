<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Events\MessageSent; 
use App\Models\Property;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $conversations = $user->conversations()
            ->with(['users', 'lastMessage.user']) // تحميل مسبق للمستخدمين
            ->latest('updated_at')
            ->get();
        return view('frontend.chat.index', compact('conversations', 'user'));
    }

   // في ملف ChatController.php

    public function fetchMessages(Conversation $conversation): JsonResponse
    {
        // التحقق من الصلاحية (هذا الجزء سليم)
        if (!Auth::user()->conversations()->find($conversation->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // تحديث الرسائل كمقروءة (هذا الجزء سليم)
        $conversation->messages()->where('user_id', '!=', Auth::id())->whereNull('read_at')->update(['read_at' => now()]);
        
        // ▼▼▼ هذا هو السطر الذي قمنا بتعديله ▼▼▼
        $messages = $conversation->messages()->with('user')->latest()->paginate(20);

        return response()->json($messages);
    }

    public function sendMessage(Request $request, Conversation $conversation): JsonResponse
    {
        // ... (الكود الحالي للتحقق من الصلاحية والـ validation) ...
        if (!Auth::user()->conversations()->find($conversation->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $validated = $request->validate(['body' => 'required|string|max:2000']);

        // ... (الكود الحالي لإنشاء الرسالة) ...
        $message = $conversation->messages()->create([
            'user_id' => Auth::id(),
            'body' => $validated['body']
        ]);
        
        $conversation->touch(); // تحديث وقت المحادثة
        
        $message->load('user'); // تحميل بيانات المستخدم مع الرسالة

        broadcast(new MessageSent($message));// ▼▼▼ السطر الجديد والمهم ▼▼▼
        // قم ببث الحدث إلى المستخدمين الآخرين
        

        return response()->json($message);
    }
    
   public function createOrFindConversation(Request $request, User $recipient)
{
    $currentUser = Auth::user();

    // 1. التحقق الأساسي
    if ($currentUser->id === $recipient->id) {
        return redirect()->back()->with('error', 'You cannot start a conversation with yourself.');
    }

    $propertyId = $request->query('property_id');
    $property = null;

    if ($propertyId) {
        $property = Property::find($propertyId);
        // إذا كان هناك property_id، تأكدي من أن المستقبل هو مالك العقار
        if ($property && $property->user_id !== $recipient->id) {
            // هذا يمنع أي شخص من بدء محادثة مع شخص آخر بحجة عقار لا يملكه
             return redirect()->route('frontend.home')->with('error', 'Invalid chat request.');
        }
    }

    // 2. البحث عن محادثة قائمة بين المستخدمين
    // البحث عن محادثة مشتركة بين المستخدمين الاثنين فقط.
    $conversation = $currentUser->conversations()
        ->whereHas('users', function ($query) use ($recipient) {
            $query->where('user_id', $recipient->id);
        })
        ->has('users', 2) // تأكد من أن المحادثة بين شخصين فقط
        ->first();

    // 3. إذا لم توجد محادثة، قم بإنشاء واحدة جديدة
    if (!$conversation) {
        DB::beginTransaction();
        try {
            $conversation = Conversation::create();
            $conversation->users()->attach([$currentUser->id, $recipient->id]);

            // (اختياري ولكن محبذ جداً) إرسال رسالة نظام تلقائية
            if ($property) {
                $initialMessage = "Hello, I'm interested in your property: '{$property->title}'.";
                $conversation->messages()->create([
                    'user_id' => $currentUser->id,
                    'body' => $initialMessage
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Could not start the conversation. Please try again.');
        }
    }
    
    // 4. التوجيه إلى صفحة الشات مع جعل المحادثة الجديدة/الموجودة نشطة
    return redirect()->route('chat.index', ['activeConversation' => $conversation->id]);
}
}