<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'organization' => $this->whenLoaded('organization', fn () => [
                'id' => $this->organization->id,
                'name' => $this->organization->name,
            ]),
            'company_id' => $this->company_id,
            'company' => $this->whenLoaded('company', fn () => [
                'id' => $this->company->id,
                'name' => $this->company->name,
            ]),
            'user_role_id' => $this->user_role_id,
            'role' => $this->whenLoaded('role', fn () => [
                'id' => $this->role->id,
                'name' => $this->role->name,
            ]),
            'username' => $this->username,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'is_active' => $this->is_active,
            'whmcs_id' => $this->whmcs_id,
            'phone_extension' => $this->phone_extension,
            'is_2fa_enabled' => $this->is_2fa_enabled,
            'company_access' => $this->whenLoaded('usersCompanyAccess', fn () => $this->usersCompanyAccess->map(fn ($access) => [
                'company_id' => $access->company_id,
                'company_name' => $access->company?->name,
                'enabled' => $access->enabled,
            ])),
            'permissions' => $this->whenLoaded('usersModulesPermissions', fn () => $this->usersModulesPermissions->map(fn ($perm) => [
                'module_id' => $perm->module_id,
                'module_name' => $perm->module?->name,
                'permission_id' => $perm->permission_id,
                'permission' => $perm->permission?->permission,
            ])),
            'tags' => $this->whenLoaded('userTags', fn () => $this->userTags->map(fn ($tag) => [
                'id' => $tag->companyUserTag?->id,
                'name' => $tag->companyUserTag?->name,
                'is_primary' => $tag->is_primary,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
