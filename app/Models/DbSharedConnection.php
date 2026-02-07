<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DbSharedConnection extends Model
{
    protected $table = 'db_shared_connections';

    protected $fillable = [
        'tenancy_mode_group_id',
        'env',
        'db_host',
        'db_name',
        'db_user',
        'db_pass',
        'db_port',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'db_port' => 'integer',
    ];

    public function tenancyModeGroup(): BelongsTo
    {
        return $this->belongsTo(TenancyModeGroup::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
