<?php

namespace App\Repositories\Contracts;

use App\Models\Company;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface CompanyRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function findById(int $id): ?\App\Models\Company;
    public function create(array $data): \App\Models\Company;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getByOrganization(int $organizationId): Collection;
    public function getActive(): Collection;
    public function getTenantCompanies(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function createForTenant(array $data): Company;
    public function assignModules(int $companyId, array $moduleIds, bool $isActive = true): void;
}
