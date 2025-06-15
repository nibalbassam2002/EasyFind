<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        if (!Auth::user()->conversations()->find($conversation->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $validated = $request->validate(['body' => 'required|string|max:2000']);
        $message = $conversation->messages()->create(['user_id' => Auth::id(), 'body' => $validated['body']]);
        $conversation->touch();
        $message->load('user');
        return response()->json($message);
    }
    
    public function createOrFindConversation(User $recipient)
    {
        $currentUser = Auth::user();
        if ($currentUser->id === $recipient->id) return redirect()->back()->with('error', 'You cannot start a conversation with yourself.');
        $conversation = $currentUser->conversations()->whereHas('users', fn($q) => $q->where('user_id', $recipient->id))->has('users', 2)->first();
        if (!$conversation) {
            $conversation = Conversation::create();
            $conversation->users()->attach([$currentUser->id, $recipient->id]);
        }
        return redirect()->route('chat.index', ['activeConversation' => $conversation->id]);
    }
}