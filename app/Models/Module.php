<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends BaseModel
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'created_by',
        'updated_by',
    ];

    public function moduleEndpoints(): HasMany
    {
        return $this->hasMany(ModuleEndpoint::class);
    }

    public function companyModules(): HasMany
    {
        return $this->hasMany(CompanyModule::class);
    }

    public function usersModulesPermissions(): HasMany
    {
        return $this->hasMany(UsersModulesPermission::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
