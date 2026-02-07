<?php

namespace App\Services\Auth;

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtAuthService
{
    public function attemptLogin(string $username, string $password): ?array
    {
        $credentials = ['username' => $username, 'password' => $password];

        if (!$token = auth('api')->attempt($credentials)) {
            return null;
        }

        return $this->respondWithToken($token);
    }

    public function logout(): void
    {
        auth('api')->logout();
    }

    public function refresh(): array
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    public function me(): ?User
    {
        return auth('api')->user();
    }

    private function respondWithToken(string $token): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ];
    }
}
