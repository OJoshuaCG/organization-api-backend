<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Resources\Api\V1\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController
{
    public function __construct(
        private UserService $userService
    ) {}

    public function show(): JsonResponse
    {
        $user = auth()->user();

        return response()->json([
            'success' => true,
            'data' => new UserResource($user->load(['organization', 'company', 'role'])),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'first_name' => 'nullable|string|max:60',
            'last_name' => 'nullable|string|max:60',
            'phone_extension' => 'nullable|integer',
        ]);

        $this->userService->update($user->id, $validated);

        return response()->json([
            'success' => true,
            'data' => new UserResource($this->userService->findById($user->id)),
            'message' => 'Perfil actualizado exitosamente',
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8',
            'new_password_confirmation' => 'required|string|same:new_password',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'La contraseña actual es incorrecta',
            ], 400);
        }

        $this->userService->update($user->id, [
            'password' => Hash::make($validated['new_password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contraseña cambiada exitosamente',
        ]);
    }
}
