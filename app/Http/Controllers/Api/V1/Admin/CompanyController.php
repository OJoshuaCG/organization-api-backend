<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Resources\Api\V1\CompanyResource;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController
{
    public function __construct(
        private CompanyService $companyService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['name', 'organization_id', 'is_active', 'order_by', 'order_direction']);
        $perPage = $request->input('per_page', 15);

        $companies = $this->companyService->getAll($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => CompanyResource::collection($companies),
            'meta' => [
                'current_page' => $companies->currentPage(),
                'last_page' => $companies->lastPage(),
                'per_page' => $companies->perPage(),
                'total' => $companies->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $company = $this->companyService->findById($id);

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Empresa no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new CompanyResource($company),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:150',
            'whmcs_id' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $company = $this->companyService->create($validated);

        return response()->json([
            'success' => true,
            'data' => new CompanyResource($company),
            'message' => 'Empresa creada exitosamente',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'organization_id' => 'sometimes|exists:organizations,id',
            'name' => 'sometimes|string|max:50',
            'description' => 'nullable|string|max:150',
            'whmcs_id' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $updated = $this->companyService->update($id, $validated);

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Empresa no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new CompanyResource($this->companyService->findById($id)),
            'message' => 'Empresa actualizada exitosamente',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->companyService->delete($id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Empresa no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Empresa eliminada exitosamente',
        ]);
    }

    public function assignModules(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'module_ids' => 'required|array',
            'module_ids.*' => 'exists:modules,id',
            'is_active' => 'boolean',
        ]);

        $company = $this->companyService->findById($id);

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Empresa no encontrada',
            ], 404);
        }

        $this->companyService->assignModules($id, $validated['module_ids'], $validated['is_active'] ?? true);

        return response()->json([
            'success' => true,
            'message' => 'Módulos asignados exitosamente',
        ]);
    }
}
