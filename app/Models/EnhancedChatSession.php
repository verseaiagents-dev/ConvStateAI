<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class EnhancedChatSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'project_id',
        'intent_history',
        'chat_history',
        'daily_view_count',
        'daily_view_limit',
        'last_activity',
        'user_preferences',
        'product_interactions',
        'status',
        'expires_at'
    ];

    protected $casts = [
        'intent_history' => 'array',
        'chat_history' => 'array',
        'user_preferences' => 'array',
        'product_interactions' => 'array',
        'last_activity' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Boot the model and add encryption/decryption
     */
    protected static function boot()
    {
        parent::boot();

        // Temporarily disable encryption for testing
        if (config('app.env') === 'testing') {
            return;
        }

        // Encrypt sensitive data before saving
        static::saving(function ($session) {
            $session->encryptSensitiveData();
        });

        // Decrypt sensitive data after retrieving
        static::retrieved(function ($session) {
            $session->decryptSensitiveData();
        });
    }

    /**
     * Encrypt sensitive data before saving
     */
    protected function encryptSensitiveData(): void
    {
        if (!empty($this->user_preferences)) {
            $this->user_preferences = \App\Services\EncryptionService::encrypt($this->user_preferences);
        }
        
        if (!empty($this->intent_history)) {
            $this->intent_history = \App\Services\EncryptionService::encrypt($this->intent_history);
        }
        
        if (!empty($this->chat_history)) {
            $this->chat_history = \App\Services\EncryptionService::encrypt($this->chat_history);
        }
    }

    /**
     * Decrypt sensitive data after retrieving
     */
    protected function decryptSensitiveData(): void
    {
        if (!empty($this->user_preferences)) {
            $this->user_preferences = \App\Services\EncryptionService::decrypt($this->user_preferences) ?? [];
        }
        
        if (!empty($this->intent_history)) {
            $this->intent_history = \App\Services\EncryptionService::decrypt($this->intent_history) ?? [];
        }
        
        if (!empty($this->chat_history)) {
            $this->chat_history = \App\Services\EncryptionService::decrypt($this->chat_history) ?? [];
        }
    }

    /**
     * User ile ilişki
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Project ile ilişki
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Product interactions ile ilişki
     */
    public function productInteractions(): HasMany
    {
        return $this->hasMany(ProductInteraction::class, 'session_id', 'session_id');
    }

    /**
     * Session'ın aktif olup olmadığını kontrol et
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               (!$this->expires_at || $this->expires_at->isFuture());
    }

    /**
     * Session'ın süresi dolmuş mu kontrol et
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if session can view more products today
     */
    public function canViewMore(): bool
    {
        // Check if session is active
        if ($this->status !== 'active') {
            return false;
        }

        // Check if session has expired
        if ($this->isExpired()) {
            return false;
        }

        // Check daily view count
        return $this->daily_view_count < $this->daily_view_limit;
    }

    /**
     * Daily view count'u artır
     */
    public function incrementViewCount(): bool
    {
        if ($this->canViewMore()) {
            $this->increment('daily_view_count');
            $this->updateLastActivity();
            return true;
        }
        return false;
    }

    /**
     * Last activity'yi güncelle
     */
    public function updateLastActivity(): void
    {
        $this->update(['last_activity' => now()]);
    }

    /**
     * Add intent to history
     */
    public function addIntent(string $intent): void
    {
        $intentHistory = is_array($this->intent_history) ? $this->intent_history : [];
        $intentHistory[] = [
            'intent' => $intent,
            'timestamp' => now()->toISOString()
        ];
        
        $this->update(['intent_history' => $intentHistory]);
    }

    /**
     * Add chat message to history
     */
    public function addChatMessage(string $role, string $content): void
    {
        $chatHistory = is_array($this->chat_history) ? $this->chat_history : [];
        $chatHistory[] = [
            'role' => $role,
            'content' => $content,
            'timestamp' => now()->toISOString()
        ];
        
        $this->update(['chat_history' => $chatHistory]);
    }

    /**
     * Update user preferences
     */
    public function updateUserPreferences(array $preferences): void
    {
        $currentPreferences = is_array($this->user_preferences) ? $this->user_preferences : [];
        $updatedPreferences = array_merge($currentPreferences, $preferences);
        
        $this->update(['user_preferences' => $updatedPreferences]);
    }

    /**
     * Product interaction ekle
     */
    public function addProductInteraction(int $productId, string $action, array $metadata = []): void
    {
        $interactions = $this->product_interactions ?? [];
        $interactions[] = [
            'product_id' => $productId,
            'action' => $action,
            'metadata' => $metadata,
            'timestamp' => now()->toISOString()
        ];

        $this->update(['product_interactions' => $interactions]);
    }

    /**
     * Refresh daily view limits (reset at midnight)
     */
    public function refreshDailyLimits(): void
    {
        $lastActivity = $this->last_activity ?? $this->created_at;
        
        if ($lastActivity && $lastActivity->isToday()) {
            // Already today, no need to refresh
            return;
        }

        // Reset daily view count
        $this->update([
            'daily_view_count' => 0,
            'last_activity' => now()
        ]);
    }

    /**
     * Session'ı expire et
     */
    public function expire(): void
    {
        $this->update([
            'status' => 'expired',
            'expires_at' => now()
        ]);
    }

    /**
     * Session'ı tamamla
     */
    public function complete(): void
    {
        $this->update([
            'status' => 'completed',
            'expires_at' => now()
        ]);
    }

    /**
     * Scope: Aktif session'lar
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope: Bugün aktif olan session'lar
     */
    public function scopeActiveToday($query)
    {
        return $query->whereDate('last_activity', today())
                    ->where('status', 'active');
    }

    /**
     * Scope: Daily limit'i aşan session'lar
     */
    public function scopeExceededDailyLimit($query)
    {
        return $query->whereRaw('daily_view_count >= daily_view_limit');
    }
}
