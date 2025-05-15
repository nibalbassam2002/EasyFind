<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
   use HasFactory;

    protected $fillable = [
        'last_message_at' 
    ];

   
    public function users()
    {
        return $this->belongsToMany(User::class, 'conversation_user')
                    ->withTimestamps()
                    ->withPivot('last_read_at', 'joined_at');
    }

  
    public function messages()
    {
        return $this->hasMany(Message::class)->latest(); // جلب الرسائل مرتبة بالأحدث
    }

    
    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function getOtherParticipant(User $currentUser)
    {
        return $this->users()->where('users.id', '!=', $currentUser->id)->first();
    }
}
