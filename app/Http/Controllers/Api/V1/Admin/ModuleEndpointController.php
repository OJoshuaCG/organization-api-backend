<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Resources\Api\V1\ModuleEndpointResource;
use App\Services\ModuleEndpointService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModuleEndpointController
{
    public function __construct(
        private ModuleEndpointService $moduleEndpointService
    ) {}

    public function index(int $moduleId): JsonResponse
    {
        $endpoints = $this->moduleEndpointService->getByModule($moduleId);

        return response()->json([
            'success' => true,
            'data' => ModuleEndpointResource::collection($endpoints),
        ]);
    }

    public function store(Request $request, int $moduleId): JsonResponse
    {
        $validated = $request->validate([
            'route' => 'required|string|max:100',
            'http_method' => 'required|in:GET,POST,PUT,DELETE,PATCH',
            'prefix' => 'nullable|string|max:50',
            'required_permission_id' => 'nullable|exists:cat_permission_types,id',
            'description' => 'nullable|string|max:150',
            'tenancy_mode_group_id' => 'nullable|exists:tenancy_mode_groups,id',
            'is_active' => 'boolean',
        ]);

        $validated['module_id'] = $moduleId;

        $endpoint = $this->moduleEndpointService->create($validated);

        return response()->json([
            'success' => true,
            'data' => new ModuleEndpointResource($endpoint),
            'message' => 'Endpoint creado exitosamente',
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $endpoint = $this->moduleEndpointService->findById($id);

        if (!$endpoint) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ModuleEndpointResource($endpoint),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'route' => 'sometimes|string|max:100',
            'http_method' => 'sometimes|in:GET,POST,PUT,DELETE,PATCH',
            'prefix' => 'nullable|string|max:50',
            'required_permission_id' => 'nullable|exists:cat_permission_types,id',
            'description' => 'nullable|string|max:150',
            'tenancy_mode_group_id' => 'nullable|exists:tenancy_mode_groups,id',
            'is_active' => 'boolean',
        ]);

        $updated = $this->moduleEndpointService->update($id, $validated);

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ModuleEndpointResource($this->moduleEndpointService->findById($id)),
            'message' => 'Endpoint actualizado exitosamente',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->moduleEndpointService->delete($id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Endpoint eliminado exitosamente',
        ]);
    }

    public function assignRoles(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'user_role_ids' => 'required|array',
            'user_role_ids.*' => 'exists:cat_user_roles,id',
        ]);

        $this->moduleEndpointService->assignRoles($id, $validated['user_role_ids']);

        return response()->json([
            'success' => true,
            'message' => 'Roles asignados exitosamente',
        ]);
    }
}
