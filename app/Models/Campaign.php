<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Campaign extends Model
{
    protected $fillable = [
        'title',
        'description',
        'category',
        'discount',
        'valid_until',
        'is_active',
        'site_id',
        'start_date',
        'end_date',
        'discount_type', // percentage, fixed, buy_x_get_y, free_shipping
        'discount_value',
        'minimum_order_amount',
        'max_usage',
        'current_usage',
        'image_url',
        'terms_conditions'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'valid_until' => 'datetime',
        'minimum_order_amount' => 'decimal:2',
        'max_usage' => 'integer',
        'current_usage' => 'integer',
        'discount_value' => 'decimal:2'
    ];

    // Relationships
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    // Scope methods
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        $now = now();
        return $query->where('start_date', '<=', $now)
                    ->where(function($q) use ($now) {
                        $q->where('end_date', '>=', $now)
                          ->orWhereNull('end_date');
                    });
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Helper methods
    public function getIsValidAttribute()
    {
        $now = now();
        return $this->is_active && 
               $this->start_date <= $now && 
               ($this->end_date === null || $this->end_date >= $now);
    }

    public function getRemainingUsageAttribute()
    {
        if ($this->max_usage === null) {
            return null;
        }
        return max(0, $this->max_usage - $this->current_usage);
    }

    public function getFormattedDiscountAttribute()
    {
        switch ($this->discount_type) {
            case 'percentage':
                return '%' . $this->discount_value . ' İndirim';
            case 'fixed':
                return $this->discount_value . ' TL İndirim';
            case 'buy_x_get_y':
                return $this->discount;
            case 'free_shipping':
                return 'Ücretsiz Kargo';
            default:
                return $this->discount;
        }
    }
}
