<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WidgetActions extends Model
{
    protected $fillable = [
        'siparis_durumu_endpoint',
        'kargo_durumu_endpoint',
        'http_action',
        'widget_customization_id'
    ];

    protected $casts = [
        'http_action' => 'string'
    ];

    // WidgetCustomization ile ilişki
    public function widgetCustomization(): BelongsTo
    {
        return $this->belongsTo(WidgetCustomization::class);
    }
}
