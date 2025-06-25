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

class ChatController extends Controller
{
    protected $firestore;
    protected $firebaseAuth;

    public function __construct(Firestore $firestore, FirebaseAuth $firebaseAuth)
    {
        $this->firestore = $firestore;
        $this->firebaseAuth = $firebaseAuth;
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
        $message->load('user');

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

        $message->formatted_created_at = $message->created_at->format('h:i A');
        return response()->json($message);
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
}