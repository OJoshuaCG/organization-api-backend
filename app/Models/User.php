<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $fillable = [
        'organization_id',
        'user_role_id',
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'is_active',
        'whmcs_id',
        'phone_extension',
        'is_2fa_enabled',
        'topt_secret',
        'user_tag_id',
    ];

    protected $hidden = [
        'password',
        'topt_secret',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_2fa_enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(CatUserRole::class, 'user_role_id');
    }

    public function usersCompanyAccess(): HasMany
    {
        return $this->hasMany(UsersCompanyAccess::class);
    }

    public function usersModulesPermissions(): HasMany
    {
        return $this->hasMany(UsersModulesPermission::class);
    }

    public function userTags(): HasMany
    {
        return $this->hasMany(UserTag::class);
    }

    public function sessionTokens(): HasMany
    {
        return $this->hasMany(SessionToken::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'role' => $this->user_role_id,
            'organization_id' => $this->organization_id,
        ];
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->user_role_id === UserRole::SUPER_ADMIN->value;
    }

    public function isAdmin(): bool
    {
        return in_array($this->user_role_id, [UserRole::SUPER_ADMIN->value, UserRole::ADMIN->value]);
    }

    public function isOrganizationOwner(): bool
    {
        return $this->user_role_id === UserRole::ORGANIZATION_OWNER->value;
    }

    public function isCompanyOwner(): bool
    {
        return $this->user_role_id === UserRole::COMPANY_OWNER->value;
    }

    public function belongsToOrganization(int $organizationId): bool
    {
        return $this->organization_id === $organizationId;
    }

    public function hasAccessToCompany(int $companyId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->usersCompanyAccess()
            ->where('company_id', $companyId)
            ->where('enabled', true)
            ->exists();
    }

    public function hasPermission(int $moduleId, int $permissionId): bool
    {
        return $this->usersModulesPermissions()
            ->where('module_id', $moduleId)
            ->where('permission_id', $permissionId)
            ->exists();
    }
}
