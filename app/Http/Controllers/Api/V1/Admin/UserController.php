<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Resources\Api\V1\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController
{
    public function __construct(
        private UserService $userService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'username', 'email', 'first_name', 'last_name',
            'is_active', 'user_role_id', 'organization_id', 'company_id'
        ]);
        $perPage = $request->input('per_page', 15);

        $users = $this->userService->getAll($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->userService->findById($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users',
            'email' => 'nullable|email|unique:users',
            'password' => 'required|string|min:8',
            'first_name' => 'nullable|string|max:60',
            'last_name' => 'nullable|string|max:60',
            'user_role_id' => 'required|exists:cat_user_roles,id',
            'organization_id' => 'nullable|exists:organizations,id',
            'company_id' => 'nullable|exists:companies,id',
            'whmcs_id' => 'nullable|integer',
            'phone_extension' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $user = $this->userService->create($validated);

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
            'message' => 'Usuario creado exitosamente',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'sometimes|string|max:50|unique:users,username,' . $id,
            'email' => 'nullable|email|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:8',
            'first_name' => 'nullable|string|max:60',
            'last_name' => 'nullable|string|max:60',
            'user_role_id' => 'sometimes|exists:cat_user_roles,id',
            'organization_id' => 'nullable|exists:organizations,id',
            'company_id' => 'nullable|exists:companies,id',
            'whmcs_id' => 'nullable|integer',
            'phone_extension' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $updated = $this->userService->update($id, $validated);

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new UserResource($this->userService->findById($id)),
            'message' => 'Usuario actualizado exitosamente',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->userService->delete($id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Usuario eliminado exitosamente',
        ]);
    }

    public function assignCompanyAccess(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'enabled' => 'boolean',
        ]);

        $user = $this->userService->findById($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado',
            ], 404);
        }

        $this->userService->assignCompanyAccess($id, $validated['company_id'], $validated['enabled'] ?? true);

        return response()->json([
            'success' => true,
            'message' => 'Acceso a empresa actualizado',
        ]);
    }

    public function assignPermission(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'module_id' => 'required|exists:modules,id',
            'permission_id' => 'required|exists:cat_permission_types,id',
        ]);

        $user = $this->userService->findById($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado',
            ], 404);
        }

        $this->userService->assignPermission($id, $validated['module_id'], $validated['permission_id']);

        return response()->json([
            'success' => true,
            'message' => 'Permiso asignado exitosamente',
        ]);
    }
}
