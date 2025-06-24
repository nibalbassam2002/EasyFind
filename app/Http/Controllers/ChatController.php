<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 
use Kreait\Firebase\Contract\Firestore; 
use Kreait\Firebase\Contract\Auth as FirebaseAuth;



class ChatController extends Controller
{
    
    protected $firestore;
    protected $firebaseAuth;

    public function __construct(Firestore $firestore, FirebaseAuth $firebaseAuth) // <-- هذا هو التعديل
{
        
        $this->firestore = $firestore;
        $this->firebaseAuth = $firebaseAuth;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $conversations = $user->conversations()
            ->with(['users', 'lastMessage.user', 'property']) 
            ->latest('updated_at')
            ->get();

        
        $customToken = $this->firebaseAuth->createCustomToken((string) $user->id, ['name' => $user->name]);

        // قم بتحويل التوكن إلى سلسلة نصية لتمريره
        $firebaseToken = $customToken->toString();
        // ========================

        // **مرر التوكن الجديد إلى الـ view**
        return view('frontend.chat.index', compact('conversations', 'user', 'firebaseToken'));
    }

    public function fetchMessages(Conversation $conversation): JsonResponse
    {
        if (!Auth::user()->conversations()->find($conversation->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $conversation->messages()->where('user_id', '!=', Auth::id())->whereNull('read_at')->update(['read_at' => now()]);
        
        $messages = $conversation->messages()->with('user')->latest()->paginate(20);

        return response()->json($messages);
    }


    public function sendMessage(Request $request, Conversation $conversation): JsonResponse
    {
        // 1. التحقق من الصلاحية والبيانات (يبقى كما هو)
        if (!Auth::user()->conversations()->find($conversation->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $validated = $request->validate(['body' => 'required|string|max:2000']);

        // 2. حفظ الرسالة في قاعدة بياناتك الأساسية (MySQL) - هذا ممتاز ويجب أن يبقى
        $message = $conversation->messages()->create([
            'user_id' => Auth::id(),
            'body' => $validated['body']
        ]);
        
        $conversation->touch();
        $message->load('user');

        // 3. === الجزء الجديد: إرسال الرسالة إلى FIRESTORE ===
        try {
            $this->firestore
                ->database()
                ->collection('conversations') // أنشئ مجموعة اسمها "conversations"
                ->document($conversation->id) // بداخلها، أنشئ مستندًا لكل محادثة
                ->collection('messages')      // وبداخل كل محادثة، أنشئ مجموعة للرسائل
                ->add([                       // أضف رسالة جديدة كـ مستند
                    'userId'    => (int) auth()->id(),
                    'userName'  => auth()->user()->name,
                    'message'   => $validated['body'],
                    'timestamp' => new \Google\Cloud\Core\Timestamp(new \DateTime()),
                ]);
        } catch (\Exception $e) {
            // في حالة فشل الاتصال بـ Firebase، سنقوم بتسجيل الخطأ في ملفات log
            Log::error('FIREBASE_SEND_FAILED: ' . $e->getMessage());
            // لا توقف العملية، لأن الرسالة تم حفظها في قاعدة بياناتك الأساسية
        }

        // 4. أزلنا استدعاء broadcast القديم
        // broadcast(new MessageSent($message));

        // 5. أرجع الرسالة كما كنت تفعل
        return response()->json($message);
    }
    
    public function createOrFindConversation(User $recipient, $property_id = null)
{
    $currentUser = Auth::user();

    if ($currentUser->id === $recipient->id) {
        return redirect()->back()->with('error', 'You cannot start a conversation with yourself.');
    }

    // ابحث عن محادثة قائمة بين هذين المستخدمين حول هذا العقار تحديداً
    $query = $currentUser->conversations()
        ->whereHas('users', fn($q) => $q->where('user_id', $recipient->id));

    if ($property_id) {
        $query->where('property_id', $property_id);
    }

    $conversation = $query->first();

    // إذا لم يتم العثور على محادثة، أنشئ واحدة جديدة مع ربطها بالعقار
    if (!$conversation) {
        $conversation = Conversation::create([
            'property_id' => $property_id
        ]);
        $conversation->users()->attach([$currentUser->id, $recipient->id]);
    }

    return redirect()->route('chat.index', ['activeConversation' => $conversation->id]);
}
}