<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
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
        'description'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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
}
