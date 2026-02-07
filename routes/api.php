<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\V1\Admin\OrganizationController;
use App\Http\Controllers\Api\V1\Admin\CompanyController as AdminCompanyController;
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\V1\Admin\ModuleController;
use App\Http\Controllers\Api\V1\Admin\ModuleEndpointController;
use App\Http\Controllers\Api\V1\Admin\TenancyConnectionController;
use App\Http\Controllers\Api\V1\Tenant\CompanyController;
use App\Http\Controllers\Api\V1\Tenant\UserController;
use App\Http\Controllers\Api\V1\ModuleController as TenantModuleController;
use App\Http\Controllers\Api\V1\Tenant\TagController;
use App\Http\Controllers\Api\V1\Tenant\ProfileController;
use App\Http\Controllers\Api\V1\Tenant\ConnectionController;
use App\Http\Controllers\Api\V1\CatalogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Rutas públicas
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
    });

    // Catálogos (públicos)
    Route::prefix('catalogs')->group(function () {
        Route::get('user-roles', [CatalogController::class, 'userRoles']);
        Route::get('permission-types', [CatalogController::class, 'permissionTypes']);
        Route::get('tenancy-mode-groups', [CatalogController::class, 'tenancyModeGroups']);
    });

    // Rutas protegidas con JWT
    Route::middleware(['jwt.auth'])->group(function () {
        // Auth
        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::get('me', [AuthController::class, 'me']);
        });

        // Admin Routes
        Route::prefix('admin')->group(function () {
            Route::apiResource('organizations', OrganizationController::class);
            Route::apiResource('companies', AdminCompanyController::class);
            Route::post('companies/{id}/modules', [AdminCompanyController::class, 'assignModules']);
            Route::apiResource('users', AdminUserController::class);
            Route::post('users/{id}/company-access', [AdminUserController::class, 'assignCompanyAccess']);
            Route::post('users/{id}/permissions', [AdminUserController::class, 'assignPermission']);
            Route::apiResource('modules', ModuleController::class);
            Route::get('modules/{id}/endpoints', [ModuleController::class, 'getEndpoints']);
            Route::apiResource('modules.endpoints', ModuleEndpointController::class)->only(['store', 'update', 'destroy']);
            Route::post('modules/endpoints/{id}/roles', [ModuleEndpointController::class, 'assignRoles']);

            // Tenancy Connections
            Route::apiResource('tenancy-groups', \App\Http\Controllers\Api\V1\Admin\TenancyGroupController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
            Route::apiResource('shared-connections', TenancyConnectionController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
            Route::post('shared-connections/{id}/test', [TenancyConnectionController::class, 'testSharedConnection']);
            Route::apiResource('dedicated-connections', TenancyConnectionController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
            Route::post('dedicated-connections/{id}/test', [TenancyConnectionController::class, 'testDedicatedConnection']);
        });

        // Tenant Routes
        Route::prefix('tenant')->group(function () {
            Route::get('organizations/{id}/users', [UserController::class, 'indexByOrganization']);
            Route::apiResource('companies', CompanyController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::get('companies/{id}/users', [UserController::class, 'indexByCompany']);
            Route::apiResource('users', UserController::class)->only(['show', 'store', 'update', 'destroy']);
            Route::post('users/{id}/company-access', [UserController::class, 'assignCompanyAccess']);
            Route::post('users/{id}/permissions', [UserController::class, 'assignPermission']);
            Route::apiResource('modules', TenantModuleController::class)->only(['index', 'show']);
            Route::prefix('companies/{companyId}/tags')->group(function () {
                Route::get('/', [TagController::class, 'indexCompanyTags']);
                Route::post('/', [TagController::class, 'storeCompanyTag']);
            });
            Route::prefix('tags/{id}')->group(function () {
                Route::put('/', [TagController::class, 'updateCompanyTag']);
                Route::delete('/', [TagController::class, 'destroyCompanyTag']);
            });
            Route::prefix('users/{userId}/tags')->group(function () {
                Route::get('/', [TagController::class, 'indexUserTags']);
                Route::post('/', [TagController::class, 'assignUserTag']);
            });
            Route::delete('users/{userId}/tags/{tagId}', [TagController::class, 'removeUserTag']);
            Route::prefix('profile')->group(function () {
                Route::get('/', [ProfileController::class, 'show']);
                Route::put('/', [ProfileController::class, 'update']);
                Route::post('change-password', [ProfileController::class, 'changePassword']);
            });
            Route::get('connection', [ConnectionController::class, 'show']);
        });
    });
});
