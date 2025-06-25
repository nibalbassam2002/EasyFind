<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'user_id',
        'category_id',
        'sub_category_id',
        'title',
        'description',
        'purpose',
        'price',
        'currency',
        'area_id',
        'address',
        'location',
        'area',
        'property_condition',
        'finishing_type',
        'land_area',
        'rooms',
        'bathrooms',
        'floors',
        'land_type',
        'tent_type',
        'caravan_type',
        'commercial_type',     
        'commercial_purpose',   
        'amenities',        
        'view_type',           
        'additional_details',
        'images',
        'video_url',
        'rating',
        'views_count',
        'status',
        'moderated_by',
        'moderated_at',
        'rejection_reason',
        'is_featured', 
        'featured_at', 
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2',
        'rating' => 'decimal:2',
        'amenities' => 'array',
        'moderated_at' => 'datetime',
        'is_featured' => 'boolean',   
        'featured_at' => 'datetime',  
    ];

    // العلاقات
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

    public function listarea()
     {
    return $this->belongsTo(Area::class, 'area_id');
     }
     public function moderator()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }
    public function reviews()
{
    return $this->hasMany(Review::class);
}
public function getFirstImageUrlAttribute()
{
    if ($this->images) {
        $images = json_decode($this->images, true);
        if (is_array($images) && !empty($images[0])) {
            return Storage::url($images[0]);
        }
    }
    return asset('frontend/assets/no-image-available.jpg'); // صورة افتراضية
}
}
