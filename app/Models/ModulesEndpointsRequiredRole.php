<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModulesEndpointsRequiredRole extends Model
{
    protected $table = 'modules_endpoints_required_roles';

    protected $fillable = [
        'module_endpoint_id',
        'user_role_id',
    ];

    public function moduleEndpoint(): BelongsTo
    {
        return $this->belongsTo(ModuleEndpoint::class);
    }

    public function userRole(): BelongsTo
    {
        return $this->belongsTo(CatUserRole::class, 'user_role_id');
    }
}
