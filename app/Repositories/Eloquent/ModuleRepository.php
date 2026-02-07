<?php

namespace App\Repositories\Eloquent;

use App\Models\Module;
use App\Repositories\Contracts\ModuleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ModuleRepository implements ModuleRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Module::query();

        if (isset($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['order_by'])) {
            $query->orderBy($filters['order_by'], $filters['order_direction'] ?? 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->with(['moduleEndpoints'])->paginate($perPage);
    }

    public function findById(int $id): ?Module
    {
        return Module::with(['moduleEndpoints'])->find($id);
    }

    public function create(array $data): Module
    {
        return Module::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $module = $this->findById($id);

        if (!$module) {
            return false;
        }

        return $module->update($data);
    }

    public function delete(int $id): bool
    {
        $module = $this->findById($id);

        if (!$module) {
            return false;
        }

        return $module->delete();
    }

    public function getActive(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Module::active();

        if (isset($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }

        if (isset($filters['order_by'])) {
            $query->orderBy($filters['order_by'], $filters['order_direction'] ?? 'asc');
        } else {
            $query->orderBy('name', 'asc');
        }

        return $query->with(['moduleEndpoints'])->paginate($perPage);
    }

    public function getEndpoints(int $moduleId): Collection
    {
        return \App\Models\ModuleEndpoint::where('module_id', $moduleId)
            ->with(['module'])
            ->get();
    }
}
