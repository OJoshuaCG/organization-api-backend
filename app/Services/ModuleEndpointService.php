<?php

namespace App\Services;

use App\Models\ModuleEndpoint;
use App\Models\ModulesEndpointsRequiredRole;
use Illuminate\Database\Eloquent\Collection;

class ModuleEndpointService
{
    public function getByModule(int $moduleId): Collection
    {
        return ModuleEndpoint::where('module_id', $moduleId)
            ->with(['module', 'permission', 'tenancyModeGroup', 'modulesEndpointsRequiredRoles.userRole'])
            ->get();
    }

    public function findById(int $id): ?ModuleEndpoint
    {
        return ModuleEndpoint::with(['module', 'permission', 'tenancyModeGroup', 'modulesEndpointsRequiredRoles.userRole'])
            ->find($id);
    }

    public function create(array $data): ModuleEndpoint
    {
        return ModuleEndpoint::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $endpoint = $this->findById($id);

        if (!$endpoint) {
            return false;
        }

        return $endpoint->update($data);
    }

    public function delete(int $id): bool
    {
        $endpoint = $this->findById($id);

        if (!$endpoint) {
            return false;
        }

        return $endpoint->delete();
    }

    public function assignRoles(int $endpointId, array $roleIds): void
    {
        $records = [];
        $now = now();

        foreach ($roleIds as $roleId) {
            $records[] = [
                'module_endpoint_id' => $endpointId,
                'user_role_id' => $roleId,
            ];
        }

        ModulesEndpointsRequiredRole::where('module_endpoint_id', $endpointId)->delete();
        ModulesEndpointsRequiredRole::insert($records);
    }
}
