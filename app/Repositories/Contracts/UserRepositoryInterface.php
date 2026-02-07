<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function findById(int $id): ?\App\Models\User;
    public function findByEmail(string $email): ?\App\Models\User;
    public function create(array $data): \App\Models\User;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getByOrganization(int $organizationId): Collection;
    public function getByCompany(int $companyId): Collection;
    public function getActive(): Collection;
}
