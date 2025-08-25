<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'avatar',
        'bio',
        'personal_token',
        'token_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    /**
     * Get user's display name
     */
    public function getDisplayName(): string
    {
        return $this->name ?: explode('@', $this->email)[0];
    }

    /**
     * Get user's avatar URL
     */
    public function getAvatarUrl(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        
        // Generate initials avatar
        $initials = strtoupper(substr($this->getDisplayName(), 0, 2));
        $colors = ['#8B5CF6', '#A855F7', '#EC4899', '#F59E0B', '#10B981'];
        $color = $colors[array_rand($colors)];
        
        return "data:image/svg+xml," . urlencode("
            <svg xmlns='http://www.w3.org/2000/svg' width='100' height='100'>
                <rect width='100' height='100' fill='{$color}'/>
                <text x='50' y='50' font-family='Arial' font-size='40' fill='white' text-anchor='middle' dy='.3em'>{$initials}</text>
            </svg>
        ");
    }

    /**
     * Generate new personal token for user
     */
    public function generatePersonalToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->update([
            'personal_token' => $token,
            'token_expires_at' => now()->addYears(10) // 10 yıl geçerli
        ]);
        
        return $token;
    }

    /**
     * Check if personal token is valid
     */
    public function hasValidPersonalToken(): bool
    {
        return $this->personal_token && 
               $this->token_expires_at && 
               $this->token_expires_at->isFuture();
    }

    /**
     * Revoke personal token
     */
    public function revokePersonalToken(): void
    {
        $this->update([
            'personal_token' => null,
            'token_expires_at' => null
        ]);
    }

    /**
     * Find user by personal token
     */
    public static function findByPersonalToken(string $token): ?self
    {
        return static::where('personal_token', $token)
                    ->where('token_expires_at', '>', now())
                    ->first();
    }
}
