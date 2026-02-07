<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsersCompanyAccess extends Model
{
    protected $table = 'users_company_access';

    protected $fillable = [
        'user_id',
        'company_id',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }
}
