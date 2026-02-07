<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Resources\Api\V1\OrganizationResource;
use App\Services\OrganizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationController
{
    public function __construct(
        private OrganizationService $organizationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['name', 'is_active', 'order_by', 'order_direction']);
        $perPage = $request->input('per_page', 15);

        $organizations = $this->organizationService->getAll($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => OrganizationResource::collection($organizations),
            'meta' => [
                'current_page' => $organizations->currentPage(),
                'last_page' => $organizations->lastPage(),
                'per_page' => $organizations->perPage(),
                'total' => $organizations->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $organization = $this->organizationService->findById($id);

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organización no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new OrganizationResource($organization),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'description' => 'nullable|string|max:150',
            'whmcs_id' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $organization = $this->organizationService->create($validated);

        return response()->json([
            'success' => true,
            'data' => new OrganizationResource($organization),
            'message' => 'Organización creada exitosamente',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:50',
            'description' => 'nullable|string|max:150',
            'whmcs_id' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $updated = $this->organizationService->update($id, $validated);

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Organización no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new OrganizationResource($this->organizationService->findById($id)),
            'message' => 'Organización actualizada exitosamente',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->organizationService->delete($id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Organización no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Organización eliminada exitosamente',
        ]);
    }
}
