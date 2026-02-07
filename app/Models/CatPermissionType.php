<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatPermissionType extends Model
{
    protected $table = 'cat_permission_types';
    
    protected $fillable = [
        'id',
        'permission',
    ];

    public $timestamps = false;

    public function modulesEndpoints(): HasMany
    {
        return $this->hasMany(ModuleEndpoint::class, 'required_permission_id');
    }

    public function usersModulesPermissions(): HasMany
    {
        return $this->hasMany(UsersModulesPermission::class, 'permission_id');
    }
}
