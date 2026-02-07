<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Resources\Api\V1\TenancyModeGroupResource;
use App\Http\Resources\Api\V1\DbSharedConnectionResource;
use App\Http\Resources\Api\V1\DbDedicatedConnectionResource;
use App\Services\TenancyConnectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenancyConnectionController
{
    public function __construct(
        private TenancyConnectionService $tenancyConnectionService
    ) {}

    public function indexTenancyGroups(Request $request): JsonResponse
    {
        $filters = $request->only(['name', 'tenancy_mode']);
        $perPage = $request->input('per_page', 15);

        $groups = $this->tenancyConnectionService->getTenancyGroups($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => TenancyModeGroupResource::collection($groups),
            'meta' => [
                'current_page' => $groups->currentPage(),
                'last_page' => $groups->lastPage(),
                'per_page' => $groups->perPage(),
                'total' => $groups->total(),
            ],
        ]);
    }

    public function storeTenancyGroup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:150',
            'tenancy_mode' => 'required|in:shared,dedicated',
        ]);

        $group = $this->tenancyConnectionService->createTenancyGroup($validated);

        return response()->json([
            'success' => true,
            'data' => new TenancyModeGroupResource($group),
            'message' => 'Grupo de tenancy creado exitosamente',
        ], 201);
    }

    public function showTenancyGroup(int $id): JsonResponse
    {
        $group = $this->tenancyConnectionService->findTenancyGroup($id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Grupo de tenancy no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new TenancyModeGroupResource($group),
        ]);
    }

    public function updateTenancyGroup(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:50',
            'description' => 'nullable|string|max:150',
            'tenancy_mode' => 'sometimes|in:shared,dedicated',
        ]);

        $updated = $this->tenancyConnectionService->updateTenancyGroup($id, $validated);

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Grupo de tenancy no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new TenancyModeGroupResource($this->tenancyConnectionService->findTenancyGroup($id)),
            'message' => 'Grupo de tenancy actualizado exitosamente',
        ]);
    }

    public function destroyTenancyGroup(int $id): JsonResponse
    {
        $deleted = $this->tenancyConnectionService->deleteTenancyGroup($id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Grupo de tenancy no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Grupo de tenancy eliminado exitosamente',
        ]);
    }

    public function indexSharedConnections(Request $request): JsonResponse
    {
        $filters = $request->only(['tenancy_mode_group_id', 'env', 'is_active']);
        $perPage = $request->input('per_page', 15);

        $connections = $this->tenancyConnectionService->getSharedConnections($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => DbSharedConnectionResource::collection($connections),
            'meta' => [
                'current_page' => $connections->currentPage(),
                'last_page' => $connections->lastPage(),
                'per_page' => $connections->perPage(),
                'total' => $connections->total(),
            ],
        ]);
    }

    public function storeSharedConnection(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenancy_mode_group_id' => 'nullable|exists:tenancy_mode_groups,id',
            'env' => 'required|in:development,production',
            'db_host' => 'required|string|max:250',
            'db_name' => 'required|string|max:250',
            'db_user' => 'required|string|max:250',
            'db_pass' => 'required|string|max:250',
            'db_port' => 'integer|min:1|max:65535',
            'is_active' => 'boolean',
        ]);

        $connection = $this->tenancyConnectionService->createSharedConnection($validated);

        return response()->json([
            'success' => true,
            'data' => new DbSharedConnectionResource($connection),
            'message' => 'Conexión compartida creada exitosamente',
        ], 201);
    }

    public function showSharedConnection(int $id): JsonResponse
    {
        $connection = $this->tenancyConnectionService->findSharedConnection($id);

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Conexión compartida no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new DbSharedConnectionResource($connection),
        ]);
    }

    public function updateSharedConnection(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'tenancy_mode_group_id' => 'nullable|exists:tenancy_mode_groups,id',
            'env' => 'sometimes|in:development,production',
            'db_host' => 'sometimes|string|max:250',
            'db_name' => 'sometimes|string|max:250',
            'db_user' => 'sometimes|string|max:250',
            'db_pass' => 'sometimes|string|max:250',
            'db_port' => 'sometimes|integer|min:1|max:65535',
            'is_active' => 'boolean',
        ]);

        $updated = $this->tenancyConnectionService->updateSharedConnection($id, $validated);

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Conexión compartida no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new DbSharedConnectionResource($this->tenancyConnectionService->findSharedConnection($id)),
            'message' => 'Conexión compartida actualizada exitosamente',
        ]);
    }

    public function destroySharedConnection(int $id): JsonResponse
    {
        $deleted = $this->tenancyConnectionService->deleteSharedConnection($id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Conexión compartida no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Conexión compartida eliminada exitosamente',
        ]);
    }

    public function testSharedConnection(int $id): JsonResponse
    {
        $result = $this->tenancyConnectionService->testConnection($id, 'shared');

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ]);
    }

    public function indexDedicatedConnections(Request $request): JsonResponse
    {
        $filters = $request->only(['tenancy_mode_group_id', 'company_id', 'is_active']);
        $perPage = $request->input('per_page', 15);

        $connections = $this->tenancyConnectionService->getDedicatedConnections($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => DbDedicatedConnectionResource::collection($connections),
            'meta' => [
                'current_page' => $connections->currentPage(),
                'last_page' => $connections->lastPage(),
                'per_page' => $connections->perPage(),
                'total' => $connections->total(),
            ],
        ]);
    }

    public function storeDedicatedConnection(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenancy_mode_group_id' => 'required|exists:tenancy_mode_groups,id',
            'company_id' => 'required|exists:companies,id',
            'db_host' => 'required|string|max:512',
            'db_name' => 'required|string|max:512',
            'db_user' => 'required|string|max:512',
            'db_pass' => 'required|string|max:512',
            'db_port' => 'integer|min:1|max:65535',
            'is_active' => 'boolean',
            'dek_encrypted' => 'nullable|string|max:512',
        ]);

        $connection = $this->tenancyConnectionService->createDedicatedConnection($validated);

        return response()->json([
            'success' => true,
            'data' => new DbDedicatedConnectionResource($connection),
            'message' => 'Conexión dedicada creada exitosamente',
        ], 201);
    }

    public function showDedicatedConnection(int $id): JsonResponse
    {
        $connection = $this->tenancyConnectionService->findDedicatedConnection($id);

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Conexión dedicada no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new DbDedicatedConnectionResource($connection),
        ]);
    }

    public function updateDedicatedConnection(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'tenancy_mode_group_id' => 'sometimes|exists:tenancy_mode_groups,id',
            'company_id' => 'sometimes|exists:companies,id',
            'db_host' => 'sometimes|string|max:512',
            'db_name' => 'sometimes|string|max:512',
            'db_user' => 'sometimes|string|max:512',
            'db_pass' => 'sometimes|string|max:512',
            'db_port' => 'sometimes|integer|min:1|max:65535',
            'is_active' => 'boolean',
            'dek_encrypted' => 'nullable|string|max:512',
        ]);

        $updated = $this->tenancyConnectionService->updateDedicatedConnection($id, $validated);

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Conexión dedicada no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new DbDedicatedConnectionResource($this->tenancyConnectionService->findDedicatedConnection($id)),
            'message' => 'Conexión dedicada actualizada exitosamente',
        ]);
    }

    public function destroyDedicatedConnection(int $id): JsonResponse
    {
        $deleted = $this->tenancyConnectionService->deleteDedicatedConnection($id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Conexión dedicada no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Conexión dedicada eliminada exitosamente',
        ]);
    }

    public function testDedicatedConnection(int $id): JsonResponse
    {
        $result = $this->tenancyConnectionService->testConnection($id, 'dedicated');

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ]);
    }
}
