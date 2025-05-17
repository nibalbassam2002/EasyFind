<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function markAsRead(Request $request, DatabaseNotification $notification)
    {
        // تأكد أن الإشعار يخص المستخدم الحالي
        if (Auth::id() == $notification->notifiable_id && Auth::user()->getMorphClass() == $notification->notifiable_type) {
            $notification->markAsRead();
            return response()->json(['success' => true, 'message' => 'Notification marked as read.']);
        }
        return response()->json(['success' => false, 'message' => 'Unauthorized or notification not found.'], 403);
    }

    public function unreadCount()
    {
        return response()->json(['count' => Auth::user()->unreadNotifications->count()]);
    }

    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->latest()->paginate(15); 
        $user->unreadNotifications->markAsRead();
        if ($user->role === 'customer') {
        return view('frontend.notification.index', compact('notifications'));
    }else{
        return view('notifications.index', compact('notifications'));
    }

    
}
}