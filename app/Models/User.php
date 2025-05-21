<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use App\Models\Conversation; 
use App\Models\Message;

class User extends Authenticatable
{
     use  HasFactory, Notifiable;


    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
       'area_id',
       'address',
        'role',
        'status',
        'profile_image',
        'description',
        'provider_name',
        'provider_id',
        'provider_avatar',
    ];


    protected $hidden = [
        'password',
        'remember_token',
        
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            
        ];
    }
   
    public function area()
{
    return $this->belongsTo(Area::class, 'area_id');
}
public function getProfileImageUrlAttribute(): string
{

    if ($this->profile_image && Storage::disk('public')->exists('images/' . $this->profile_image)) {

        return Storage::url('images/' . $this->profile_image);
    }

    return asset('assets/img/profile-img.jpg');
}
public function favoriteProperties()
{

    return $this->belongsToMany(Property::class, 'favorites', 'user_id', 'property_id')->withTimestamps();
}

public function favorites()
{
    return $this->hasMany(Favorite::class);
}
public function subscriptions() {
    return $this->hasMany(Subscription::class);
}

public function activeSubscription() {
    return $this->hasOne(Subscription::class)->where('status', 'active')->where(function ($query) {
        $query->whereNull('ends_at')->orWhere('ends_at', '>', now());
    });
}

public function activePlan() {
    return $this->hasOneThrough(Plan::class, Subscription::class, 'user_id', 'id', 'id', 'plan_id')
                ->where('subscriptions.status', 'active')
                ->where(function ($query) {
                    $query->whereNull('subscriptions.ends_at')->orWhere('subscriptions.ends_at', '>', now());
                });
}
public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_user')
                    ->withTimestamps() 
                    ->withPivot('last_read_at', 'joined_at')
                    ->latest('updated_at'); 
    }

   
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
    public function unreadMessagesCount(): int
    {
        if (!$this->relationLoaded('conversations')) {
            $this->load('conversations'); 
        }

        $unreadCount = 0;
        foreach ($this->conversations as $conversation) {
            $pivotData = $conversation->users()->where('users.id', $this->id)->first()?->pivot;
            $lastReadAt = $pivotData ? $pivotData->last_read_at : null;

            $unreadInConversation = $conversation->messages()
                                    ->where('user_id', '!=', $this->id) 
                                    ->when($lastReadAt, function ($query) use ($lastReadAt) {
                                        return $query->where('messages.created_at', '>', $lastReadAt);
                                    })
                                    ->when(!$lastReadAt, function ($query) { 
                                        return $query; 
                                    })
                                    ->count();
            $unreadCount += $unreadInConversation;
        }
        return $unreadCount;
    }
    public function unreadMessagesCountAlternative(): int
    {
        return Message::whereIn('conversation_id', $this->conversations()->pluck('conversations.id')) 
                        ->where('user_id', '!=', $this->id)
                        ->whereNull('read_at') 
                        ->count();
    }
        public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }
}
