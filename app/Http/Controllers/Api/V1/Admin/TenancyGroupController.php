<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Resources\Api\V1\TenancyModeGroupResource;
use App\Services\TenancyConnectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenancyGroupController
{
    public function __construct(
        private TenancyConnectionService $tenancyConnectionService
    ) {}

    public function index(Request $request): JsonResponse
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

    public function store(Request $request): JsonResponse
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

    public function show(int $id): JsonResponse
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

    public function update(Request $request, int $id): JsonResponse
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

    public function destroy(int $id): JsonResponse
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
}
