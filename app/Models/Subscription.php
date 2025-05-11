<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;
    protected $fillable = ['user_id',
     'plan_id',
     'starts_at',
     'ends_at',
     'status',
     'payment_gateway',
     'payment_transaction_id',
     'payment_details'];
     protected $casts = ['payment_details' => 'array', 'starts_at' => 'datetime', 'ends_at' => 'datetime'];
     public function user() { 
        return $this->belongsTo(User::class); 
    }
     public function plan() { 
        return $this->belongsTo(Plan::class); 
    }
}
