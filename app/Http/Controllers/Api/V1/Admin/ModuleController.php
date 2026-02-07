<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Resources\Api\V1\ModuleResource;
use App\Services\ModuleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModuleController
{
    public function __construct(
        private ModuleService $moduleService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['name', 'is_active', 'order_by', 'order_direction']);
        $perPage = $request->input('per_page', 15);

        $modules = $this->moduleService->getAll($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => ModuleResource::collection($modules),
            'meta' => [
                'current_page' => $modules->currentPage(),
                'last_page' => $modules->lastPage(),
                'per_page' => $modules->perPage(),
                'total' => $modules->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $module = $this->moduleService->findById($id);

        if (!$module) {
            return response()->json([
                'success' => false,
                'message' => 'Módulo no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ModuleResource($module),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:modules',
            'description' => 'nullable|string|max:150',
            'is_active' => 'boolean',
        ]);

        $module = $this->moduleService->create($validated);

        return response()->json([
            'success' => true,
            'data' => new ModuleResource($module),
            'message' => 'Módulo creado exitosamente',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:50|unique:modules,name,' . $id,
            'description' => 'nullable|string|max:150',
            'is_active' => 'boolean',
        ]);

        $updated = $this->moduleService->update($id, $validated);

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Módulo no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ModuleResource($this->moduleService->findById($id)),
            'message' => 'Módulo actualizado exitosamente',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->moduleService->delete($id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Módulo no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Módulo eliminado exitosamente',
        ]);
    }

    public function getEndpoints(int $id): JsonResponse
    {
        $module = $this->moduleService->findById($id);

        if (!$module) {
            return response()->json([
                'success' => false,
                'message' => 'Módulo no encontrado',
            ], 404);
        }

        $endpoints = $this->moduleService->getEndpoints($id);

        return response()->json([
            'success' => true,
            'data' => $endpoints,
        ]);
    }
}
