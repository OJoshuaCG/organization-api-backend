<?php

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Services\TenancyConnectionService;
use Illuminate\Http\JsonResponse;

class ConnectionController
{
    public function __construct(
        private TenancyConnectionService $tenancyConnectionService
    ) {}

    public function show(): JsonResponse
    {
        $user = auth()->user();

        $company = $user->company;

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario no tiene una empresa asignada',
            ], 404);
        }

        $connection = $this->tenancyConnectionService->resolveConnection($company->id);

        return response()->json([
            'success' => true,
            'data' => $connection,
        ]);
    }
}
