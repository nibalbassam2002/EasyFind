<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Notifications\NewFeedbackSubmitted;
use App\Notifications\FeedbackReplied;

class FeedbackController extends Controller
{
    public function storeUserFeedback(Request $request) 
    {
        $request->validate([
            'feedback_type' => 'required|string|in:complaint,suggestion,improvement,other',
            'feedback_subject' => 'nullable|string|max:255',
            'feedback_message' => 'required|string|min:10',
        ]);

        $feedback = Feedback::create([
            'user_id' => Auth::id(),
            'type' => $request->feedback_type,
            'subject' => $request->feedback_subject,
            'message' => $request->feedback_message,
            'status' => 'new',
        ]);

        // إرسال إشعار للمشرفين
        $admins = User::whereIn('role', ['admin', 'content_moderator'])->get();
        foreach ($admins as $admin) {
            
            if ($admin->id != Auth::id()) {
                $admin->notify(new NewFeedbackSubmitted($feedback));
            }
        }

        return back()->with('success', 'Thank you! Your comment has been sent successfully.');
    }
    // إضافة دالة لعرض ملاحظات المستخدم 
    public function showUserFeedbacks() {}
    public function indexAdminFeedbacks() 
    {
        
        if (!in_array(Auth::user()->role, ['admin', 'content_moderator'])) {
            abort(403, 'You do not have permission to access this page.');
        }

        $feedbacks = Feedback::with('user')->latest()->paginate(15);
        return view('dashboard.usermanagement.feedback.index', compact('feedbacks')); 
    }

   
    public function showAdminFeedback(Feedback $feedback) 
    {
        if (!in_array(Auth::user()->role, ['admin', 'content_moderator'])) {
            abort(403, 'You do not have permission to access this page.');
        }

        if ($feedback->status == 'new') {
            $feedback->update(['status' => 'seen']);
        }
        $feedback->load('user', 'replier');
        return view('dashboard.usermanagement.feedback.show', compact('feedback')); 
    }

    
    public function replyToFeedback(Request $request, Feedback $feedback) 
    {
        if (!in_array(Auth::user()->role, ['admin', 'content_moderator'])) {
            abort(403, 'You are not authorized to perform this action.');
        }

        $request->validate([
            'admin_reply' => 'required|string|min:5',
        ]);

        $feedback->update([
            'admin_reply' => $request->admin_reply,
            'replied_by' => Auth::id(),
            'replied_at' => now(),
            'status' => 'replied',
        ]);

        // إرسال إشعار للمستخدم الأصلي
        if ($feedback->user) {
            $feedback->user->notify(new FeedbackReplied($feedback));
        }

        return redirect()->route('moderator.feedback.show', $feedback)->with('success', 'The reply was sent successfully.');
    }

  
    public function updateFeedbackStatus(Request $request, Feedback $feedback) 
    {
        if (!in_array(Auth::user()->role, ['admin', 'content_moderator'])) {
            abort(403, 'You are not authorized to perform this action.');
        }

        $request->validate([
            'status' => 'required|string|in:new,seen,replied,resolved'
        ]);

        $feedback->update(['status' => $request->status]);

        return back()->with('success', 'Note status updated.');
    }
}
