<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'governorate_id']; 

 
    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    
    public function users()
    {
        return $this->hasMany(User::class);
    }

   
    public function properties()
    {
        return $this->hasMany(Property::class);
    }
}
