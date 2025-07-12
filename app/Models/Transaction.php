<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'property_id',
        'amount',
        'type',
        'status',
        'payment_method',
        // أضف أي حقول أخرى تقوم بملئها في دالة create إذا كانت موجودة
    ];

    /**
     * Get the user that owns the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the property associated with the transaction.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}