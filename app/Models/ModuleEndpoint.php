<?php

namespace App\Models;

use App\Enums\TenancyMode;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuleEndpoint extends Model
{
    protected $table = 'modules_endpoints';

    protected $fillable = [
        'module_id',
        'required_permission_id',
        'prefix',
        'route',
        'http_method',
        'is_active',
        'description',
        'tenancy_mode_group_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(CatPermissionType::class, 'required_permission_id');
    }

    public function tenancyModeGroup(): BelongsTo
    {
        return $this->belongsTo(TenancyModeGroup::class);
    }

    public function modulesEndpointsRequiredRoles(): HasMany
    {
        return $this->hasMany(ModulesEndpointsRequiredRole::class, 'module_endpoint_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
