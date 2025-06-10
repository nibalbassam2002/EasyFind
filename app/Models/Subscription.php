<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;
    protected $fillable = [
    'user_id',
     'plan_id',
     'starts_at',
     'ends_at',
     'status',
     'properties_listed_count',
     'metadata',
     'payment_gateway',
     'payment_transaction_id',
     'payment_details',
     'featured_slots_used',
    ];
     protected $casts = ['payment_details' => 'array',
      'starts_at' => 'datetime',
       'ends_at' => 'datetime',
    'metadata' => 'array'];
    protected $attributes = [
        'status' => 'active', 
        'properties_listed_count' => 0,
    ];
     public function user() { 
        return $this->belongsTo(User::class); 
    }
     public function plan() { 
        return $this->belongsTo(Plan::class); 
    }
    public function isActive(): bool
    {
        return $this->status === 'active' && (!$this->ends_at || $this->ends_at->isFuture());
    }
    public function hasReachedPropertyLimit(): bool
    {
        // الطريقة 1: الاعتماد على عمود مخصص في الاشتراك
        // $limit = $this->plan->features['max_properties'] ?? ($this->metadata['max_properties'] ?? 0);

        // الطريقة 2: إذا كان plan مُحمل بالفعل ولديه features
        if (!$this->relationLoaded('plan') || !$this->plan) {
             $this->load('plan'); // تحميل الخطة إذا لم تكن محملة
        }

        $limit = 0;
        if ($this->plan && isset($this->plan->features['max_properties'])) {
            $limit = (int) $this->plan->features['max_properties'];
        } elseif (isset($this->metadata['max_properties'])) { // كاحتياطي إذا خزنتها في metadata
            $limit = (int) $this->metadata['max_properties'];
        }
        
        return $limit > 0 && $this->properties_listed_count >= $limit;
    }

    /**
     * تحقق مما إذا كان نوع عقار معين مسموحًا به.
     */
    public function allowsPropertyType(string $propertyTypeSlug): bool
    {
        if (!$this->relationLoaded('plan') || !$this->plan) {
             $this->load('plan');
        }

        $allowedTypes = [];
        if ($this->plan && isset($this->plan->features['allowed_types'])) {
            $allowedTypes = array_map('strtolower', (array) $this->plan->features['allowed_types']);
        } elseif (isset($this->metadata['allowed_types'])) {
            $allowedTypes = array_map('strtolower', (array) $this->metadata['allowed_types']);
        }


        if (in_array('all', $allowedTypes)) {
            return true;
        }
        return in_array(strtolower($propertyTypeSlug), $allowedTypes);
    }
}
