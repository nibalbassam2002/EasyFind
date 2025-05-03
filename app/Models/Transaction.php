<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    //
    protected $fillable = [
        'property_id', 
        'user_id',
        'type',
        'amount',
        'status'
    ];
    
    // العلاقة مع العقار
    public function property()
    {
        return $this->belongsTo(Property::class);
    }
    
    // العلاقة مع المستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
