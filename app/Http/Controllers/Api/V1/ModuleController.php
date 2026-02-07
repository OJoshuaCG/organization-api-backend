<?php

namespace App\Http\Controllers\Api\V1;

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

        $modules = $this->moduleService->getActive($filters, $perPage);

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

        if (!$module || !$module->is_active) {
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
}
