<?php

namespace App\Repositories\Eloquent;

use App\Models\Organization;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class OrganizationRepository implements OrganizationRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Organization::query();

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

        return $query->with(['creator', 'updater'])->paginate($perPage);
    }

    public function findById(int $id): ?Organization
    {
        return Organization::with(['companies', 'creator', 'updater'])->find($id);
    }

    public function create(array $data): Organization
    {
        return Organization::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $organization = $this->findById($id);
        
        if (!$organization) {
            return false;
        }

        return $organization->update($data);
    }

    public function delete(int $id): bool
    {
        $organization = $this->findById($id);
        
        if (!$organization) {
            return false;
        }

        return $organization->delete();
    }

    public function getActive(): Collection
    {
        return Organization::active()->get();
    }
}
