<?php

namespace App\Repositories\Eloquent;

use App\Models\Company;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CompanyRepository implements CompanyRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Company::query();

        if (isset($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }

        if (isset($filters['order_by'])) {
            $query->orderBy($filters['order_by'], $filters['order_direction'] ?? 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->with(['organization', 'creator', 'updater'])->paginate($perPage);
    }

    public function getByOrganization(int $organizationId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $filters['organization_id'] = $organizationId;
        return $this->getAll($filters, $perPage);
    }

    public function findById(int $id): ?Company
    {
        return Company::with(['organization', 'companyModules.module', 'creator', 'updater'])->find($id);
    }

    public function create(array $data): Company
    {
        return Company::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $company = $this->findById($id);
        
        if (!$company) {
            return false;
        }

        return $company->update($data);
    }

    public function delete(int $id): bool
    {
        $company = $this->findById($id);
        
        if (!$company) {
            return false;
        }

        return $company->delete();
    }

    public function getActiveByOrganization(int $organizationId): Collection
    {
        return Company::active()
            ->byOrganization($organizationId)
            ->get();
    }
}
