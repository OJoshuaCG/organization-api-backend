<?php

namespace App\Providers;

use App\Repositories\Contracts\CompanyRepositoryInterface;
use App\Repositories\Contracts\ModuleRepositoryInterface;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\CompanyRepository;
use App\Repositories\Eloquent\ModuleRepository;
use App\Repositories\Eloquent\OrganizationRepository;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrganizationRepositoryInterface::class, OrganizationRepository::class);
        $this->app->bind(CompanyRepositoryInterface::class, CompanyRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ModuleRepositoryInterface::class, ModuleRepository::class);
    }
}
