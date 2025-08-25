<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WidgetCustomization extends Model
{
    protected $fillable = [
        'user_id',
        'ai_name',
        'welcome_message',
        'customization_data',
        'is_active'
    ];

    protected $casts = [
        'customization_data' => 'array',
        'is_active' => 'boolean'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
