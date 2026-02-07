<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsersModulesPermission extends Model
{
    protected $table = 'users_modules_permissions';

    protected $fillable = [
        'user_id',
        'module_id',
        'permission_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(CatPermissionType::class, 'permission_id');
    }
}
