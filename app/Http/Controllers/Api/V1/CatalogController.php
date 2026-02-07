<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\CatUserRoleResource;
use App\Http\Resources\Api\V1\CatPermissionTypeResource;
use App\Http\Resources\Api\V1\TenancyModeGroupResource;
use App\Models\CatUserRole;
use App\Models\CatPermissionType;
use App\Models\TenancyModeGroup;
use Illuminate\Http\JsonResponse;

class CatalogController
{
    public function userRoles(): JsonResponse
    {
        $roles = CatUserRole::all();

        return response()->json([
            'success' => true,
            'data' => CatUserRoleResource::collection($roles),
        ]);
    }

    public function permissionTypes(): JsonResponse
    {
        $permissions = CatPermissionType::all();

        return response()->json([
            'success' => true,
            'data' => CatPermissionTypeResource::collection($permissions),
        ]);
    }

    public function tenancyModeGroups(): JsonResponse
    {
        $groups = TenancyModeGroup::all();

        return response()->json([
            'success' => true,
            'data' => TenancyModeGroupResource::collection($groups),
        ]);
    }
}
