<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends BaseModel
{
    protected $fillable = [
        'whmcs_id',
        'name',
        'description',
        'is_active',
        'created_by',
        'updated_by',
    ];

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
