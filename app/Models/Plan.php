<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;
    protected $fillable = ['name',
     'slug',
     'price', 
     'currency',
     'duration_in_days',
     'description',
     'features',
     'is_active'
        ];
    protected $casts = ['features' => 'array', 'price' => 'decimal:2', 'is_active' => 'boolean'];
    
    public function subscriptions() {
         return $this->hasMany(Subscription::class); 
        }
    
}
