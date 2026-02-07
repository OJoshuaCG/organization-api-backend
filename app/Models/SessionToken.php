<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionToken extends Model
{
    protected $table = 'session_tokens';

    protected $fillable = [
        'user_id',
        'token_name',
        'token',
        'expires_at',
        'created_by_ip',
        'type',
        'locked',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'locked' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeUnlocked($query)
    {
        return $query->where('locked', false);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
