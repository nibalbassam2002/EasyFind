<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Conversation extends Model
{
    protected $appends = ['unread_messages_count'];
    protected $fillable = [
        'property_id',
        
    ];
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
    
    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }
    
    protected function otherParticipant(): Attribute
    {
        return Attribute::make(
            // نستخدم ->users التي تكون محملة مسبقاً من الـ Controller
            get: fn () => $this->users->firstWhere('id', '!=', Auth::id())
        );
    }
    public function property()
{
    return $this->belongsTo(Property::class);
}
public function getUnreadMessagesCountAttribute()
{
    // إذا لم يكن المستخدم مسجلاً، لا يوجد رسائل غير مقروءة
    if (!Auth::check()) {
        return 0;
    }

    // احسب عدد الرسائل في هذه المحادثة التي لم يقرأها المستخدم الحالي
    return $this->messages()
                ->where('user_id', '!=', Auth::id())
                ->whereNull('read_at')
                ->count();
}
}