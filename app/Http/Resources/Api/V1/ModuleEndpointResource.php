<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModuleEndpointResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'module_id' => $this->module_id,
            'module' => $this->whenLoaded('module', fn () => [
                'id' => $this->module->id,
                'name' => $this->module->name,
            ]),
            'route' => $this->route,
            'prefix' => $this->prefix,
            'http_method' => $this->http_method,
            'description' => $this->description,
            'required_permission_id' => $this->required_permission_id,
            'required_permission' => $this->whenLoaded('permission', fn () => [
                'id' => $this->permission->id,
                'name' => $this->permission->permission,
            ]),
            'tenancy_mode_group_id' => $this->tenancy_mode_group_id,
            'tenancy_mode_group' => $this->whenLoaded('tenancyModeGroup', fn () => [
                'id' => $this->tenancyModeGroup->id,
                'name' => $this->tenancyModeGroup->name,
                'tenancy_mode' => $this->tenancyModeGroup->tenancy_mode,
            ]),
            'is_active' => $this->is_active,
            'required_roles' => $this->whenLoaded('modulesEndpointsRequiredRoles', fn () => $this->modulesEndpointsRequiredRoles->map(fn ($role) => [
                'id' => $role->user_role_id,
                'name' => $role->userRole?->name,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
