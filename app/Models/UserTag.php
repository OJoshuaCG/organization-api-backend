<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTag extends Model
{
    protected $table = 'user_tags';

    protected $fillable = [
        'user_id',
        'company_user_tag_id',
        'is_primary',
        'assigned_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'assigned_at' => 'datetime',
    ];

    public $timestamps = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function companyUserTag(): BelongsTo
    {
        return $this->belongsTo(CompanyUserTag::class);
    }
}
