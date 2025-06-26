<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Message extends Model
{
    use HasFactory, SoftDeletes;
    protected $appends = ['formatted_created_at'];
    protected $fillable = [
        'conversation_id',
        'user_id',
        'body',
        'read_at',
        'type', 
        'metadata'
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
    protected function formattedCreatedAt(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                $createdAt = \Carbon\Carbon::parse($attributes['created_at']);

                if ($createdAt->isToday()) {
                    // إذا كانت الرسالة اليوم، اعرض الوقت فقط
                    return $createdAt->format('h:i A'); // e.g., 10:30 AM
                }

                if ($createdAt->isYesterday()) {
                    // إذا كانت الرسالة بالأمس
                    return 'Yesterday';
                }

                // إذا كانت الرسالة أقدم من الأمس
                return $createdAt->format('M d'); // e.g., Jun 10
            },
        );
    }
}
