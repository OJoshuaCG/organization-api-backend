<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Resources\Api\V1\CompanyUserTagResource;
use App\Http\Resources\Api\V1\UserTagResource;
use App\Services\TagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController
{
    public function __construct(
        private TagService $tagService
    ) {}

    public function indexCompanyTags(int $companyId, Request $request): JsonResponse
    {
        $filters = $request->only(['name', 'is_active']);
        $perPage = $request->input('per_page', 15);

        $tags = $this->tagService->getCompanyTags($companyId, $filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => CompanyUserTagResource::collection($tags),
            'meta' => [
                'current_page' => $tags->currentPage(),
                'last_page' => $tags->lastPage(),
                'per_page' => $tags->perPage(),
                'total' => $tags->total(),
            ],
        ]);
    }

    public function storeCompanyTag(Request $request, int $companyId): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
        ]);

        $tag = $this->tagService->createCompanyTag($companyId, $validated);

        return response()->json([
            'success' => true,
            'data' => new CompanyUserTagResource($tag),
            'message' => 'Tag creado exitosamente',
        ], 201);
    }

    public function updateCompanyTag(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
        ]);

        $updated = $this->tagService->updateCompanyTag($id, $validated);

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Tag no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new CompanyUserTagResource($this->tagService->findCompanyTag($id)),
            'message' => 'Tag actualizado exitosamente',
        ]);
    }

    public function destroyCompanyTag(int $id): JsonResponse
    {
        $deleted = $this->tagService->deleteCompanyTag($id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Tag no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tag eliminado exitosamente',
        ]);
    }

    public function indexUserTags(int $userId): JsonResponse
    {
        $tags = $this->tagService->getUserTags($userId);

        return response()->json([
            'success' => true,
            'data' => UserTagResource::collection($tags),
        ]);
    }

    public function assignUserTag(Request $request, int $userId): JsonResponse
    {
        $validated = $request->validate([
            'company_user_tag_id' => 'required|exists:company_user_tags,id',
            'is_primary' => 'boolean',
        ]);

        $tag = $this->tagService->assignUserTag($userId, $validated['company_user_tag_id'], $validated['is_primary'] ?? false);

        return response()->json([
            'success' => true,
            'data' => new UserTagResource($tag),
            'message' => 'Tag asignado exitosamente',
        ], 201);
    }

    public function removeUserTag(int $userId, int $tagId): JsonResponse
    {
        $removed = $this->tagService->removeUserTag($userId, $tagId);

        if (!$removed) {
            return response()->json([
                'success' => false,
                'message' => 'Tag no encontrado en el usuario',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tag removido exitosamente',
        ]);
    }
}
