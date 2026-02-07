<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrganizationRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function findById(int $id): ?\App\Models\Organization;
    public function create(array $data): \App\Models\Organization;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getActive(): Collection;
}
