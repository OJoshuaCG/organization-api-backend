<?php

namespace App\Models;

use App\Enums\TenancyMode;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenancyModeGroup extends Model
{
    protected $table = 'tenancy_mode_groups';

    protected $fillable = [
        'name',
        'description',
        'tenancy_mode',
    ];

    protected $casts = [
        'tenancy_mode' => TenancyMode::class,
    ];

    public function moduleEndpoints(): HasMany
    {
        return $this->hasMany(ModuleEndpoint::class);
    }

    public function dbSharedConnections(): HasMany
    {
        return $this->hasMany(DbSharedConnection::class);
    }

    public function dbDedicatedConnections(): HasMany
    {
        return $this->hasMany(DbDedicatedConnection::class);
    }
}
