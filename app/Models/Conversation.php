<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Conversation extends Model
{
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
}