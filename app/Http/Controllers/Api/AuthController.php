<?php

namespace App\Http\Controllers\Api;

use App\Services\Auth\JwtAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController
{
    public function __construct(
        private JwtAuthService $authService
    ) {}

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $tokenData = $this->authService->attemptLogin(
            $request->input('username'),
            $request->input('password')
        );

        if (!$tokenData) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales inválidas',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => $tokenData,
            'message' => 'Inicio de sesión exitoso',
        ]);
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente',
        ]);
    }

    public function refresh(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->authService->refresh(),
            'message' => 'Token refrescado',
        ]);
    }

    public function me(): JsonResponse
    {
        $user = $this->authService->me();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => $user->full_name,
                'role' => $user->role?->name,
                'organization' => $user->organization?->name,
                'company' => $user->company?->name,
            ],
        ]);
    }
}
