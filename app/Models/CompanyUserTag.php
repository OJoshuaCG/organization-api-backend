<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyUserTag extends Model
{
    protected $table = 'company_user_tags';

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function userTags(): HasMany
    {
        return $this->hasMany(UserTag::class, 'company_user_tag_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
