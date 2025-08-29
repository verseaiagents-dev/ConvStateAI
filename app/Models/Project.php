<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'is_featured',
        'created_by',
        'knowledge_list',
    ];

    protected $casts = [
        'knowledge_list' => 'array',
        'is_featured' => 'boolean',
    ];

    /**
     * Projeyi oluşturan kullanıcı
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Projeye bağlı chat session'ları
     */
    public function chatSessions()
    {
        return $this->hasMany(EnhancedChatSession::class);
    }

    /**
     * Projeye bağlı knowledge base'ler
     */
    public function knowledgeBases()
    {
        return $this->hasMany(KnowledgeBase::class);
    }

    /**
     * Status badge rengi
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'active' => 'green',
            'inactive' => 'gray',
            'completed' => 'blue',
            'archived' => 'red',
            default => 'gray'
        };
    }

    /**
     * Status badge metni
     */
    public function getStatusTextAttribute()
    {
        return match($this->status) {
            'active' => 'Aktif',
            'inactive' => 'Pasif',
            'completed' => 'Tamamlandı',
            'archived' => 'Arşivlendi',
            default => 'Bilinmiyor'
        };
    }
}
