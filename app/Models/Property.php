<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'status'
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2',
        'rating' => 'decimal:2',
        'amenities' => 'array',
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
}
