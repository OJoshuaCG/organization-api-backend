<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatUserRole extends Model
{
    protected $table = 'cat_user_roles';
    
    protected $fillable = [
        'id',
        'name',
        'description',
        'whmcs_login',
    ];

    public $timestamps = false;

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'user_role_id');
    }

    public function modulesEndpointsRequiredRoles(): HasMany
    {
        return $this->hasMany(ModulesEndpointsRequiredRole::class, 'user_role_id');
    }
}
