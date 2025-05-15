<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse; 
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
   public function index(Request $request)
    {
        $user = Auth::user();

        // جلب المحادثات مع الترتيب والترقيم
        $conversationsQuery = $user->conversations()
                                ->with(['users' => function ($query) use ($user) {
                                    $query->where('users.id', '!=', $user->id);
                                }, 'lastMessage.user']);

        // الترتيب: يفضل الترتيب حسب آخر رسالة (إذا كان العمود last_message_at موجوداً ومحدثاً في conversations)
        // أو حسب updated_at للمحادثة نفسها
        $conversations = $conversationsQuery->orderByDesc('conversations.updated_at') // <-- الترتيب هنا قبل paginate
                                           ->paginate(15); // <-- الترقيم هنا

        $messages = collect(); // مجموعة فارغة مبدئياً
        $activeConversation = null;

        // جلب المحادثة النشطة إذا تم تمرير ID في الطلب
        $activeConversationId = $request->query('activeConversation'); // <-- جلب من بارامتر URL
        if ($activeConversationId) {
            $activeConversation = Conversation::with('users')->find($activeConversationId);
            // التحقق من أن المستخدم مشارك في المحادثة النشطة
            if ($activeConversation && $user->conversations()->find($activeConversation->id)) {
                $messages = $activeConversation->messages()->with('user')->latest()->paginate(20); // ترقيم الرسائل أيضاً
                // تحديث وقت القراءة
                $user->conversations()->updateExistingPivot($activeConversation->id, ['last_read_at' => now()]);
            } else {
                $activeConversation = null; // لم يتم العثور على المحادثة أو غير مصرح له
            }
        } elseif ($conversations->isNotEmpty() && !$activeConversation) {
            // (اختياري) إذا لم يتم تحديد محادثة، يمكنك اختيار أول محادثة كنشطة
            // $activeConversation = $conversations->first();
            // إذا فعلت هذا، ستحتاج لجلب رسائلها هنا أيضاً
            // $messages = $activeConversation->messages()->with('user')->latest()->paginate(20);
            // $user->conversations()->updateExistingPivot($activeConversation->id, ['last_read_at' => now()]);
        }

        return view('frontend.chat.index', compact('conversations', 'activeConversation', 'messages', 'user'));
    }


    public function sendMessage(Request $request, Conversation $conversation): JsonResponse
    {
        $user = Auth::user();

        
        if (!$user->conversations()->find($conversation->id)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to send message in this conversation.'], 403);
        }

        $validated = $request->validate([
            'body' => 'required|string|max:2000', 
        ]);

        try {
            $message = $conversation->messages()->create([
                'user_id' => $user->id,
                'body' => $validated['body'],
            ]);

            
            $conversation->touch();

            

            return response()->json(['success' => true, 'message' => $message->load('user')]);

        } catch (\Exception $e) {
            logger("Send Message Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send message.'], 500);
        }
    }

    public function createOrFindConversation( Request $request, User $recipient)
    {
        $currentUser = Auth::user();

        if ($currentUser->id === $recipient->id) {
            return redirect()->back()->with('error', 'You cannot start a conversation with yourself.');
        }

        $conversation = $currentUser->conversations()
                                    ->whereHas('users', function ($query) use ($recipient) {
                                        $query->where('users.id', $recipient->id);
                                    })
                                    ->where(function ($query) { 
                                        $query->has('users', '=', 2);
                                    })
                                    ->first();

        if (!$conversation) {
            
            DB::beginTransaction();
            try {
                $conversation = Conversation::create();
                $conversation->users()->attach([$currentUser->id, $recipient->id]);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                logger("Create Conversation Error: " . $e->getMessage());
                return redirect()->back()->with('error', 'Failed to start conversation.');
            }
        }

    
        return redirect()->route('chat.index', ['activeConversation' => $conversation->id]);
    }


    
    public function fetchMessages(Conversation $conversation): JsonResponse
    {
        $user = Auth::user();
        if (!$user->conversations()->find($conversation->id)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $messages = $conversation->messages()->with('user')->latest()->paginate(20); 

        $user->conversations()->updateExistingPivot($conversation->id, ['last_read_at' => now()]);

        return response()->json(['success' => true, 'messages' => $messages]);
    }
}
