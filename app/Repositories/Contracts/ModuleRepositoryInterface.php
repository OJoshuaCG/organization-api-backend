<?php

namespace App\Repositories\Contracts;

use App\Models\Module;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ModuleRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function findById(int $id): ?Module;
    public function create(array $data): Module;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getActive(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function getEndpoints(int $moduleId): Collection;
}
