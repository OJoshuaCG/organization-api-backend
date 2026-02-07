<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::query();

        if (isset($filters['username'])) {
            $query->where('username', 'like', "%{$filters['username']}%");
        }

        if (isset($filters['email'])) {
            $query->where('email', 'like', "%{$filters['email']}%");
        }

        if (isset($filters['first_name'])) {
            $query->where('first_name', 'like', "%{$filters['first_name']}%");
        }

        if (isset($filters['last_name'])) {
            $query->where('last_name', 'like', "%{$filters['last_name']}%");
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['user_role_id'])) {
            $query->where('user_role_id', $filters['user_role_id']);
        }

        if (isset($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }

        if (isset($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }

        if (isset($filters['order_by'])) {
            $query->orderBy($filters['order_by'], $filters['order_direction'] ?? 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->with(['organization', 'company', 'role'])->paginate($perPage);
    }

    public function getByOrganization(int $organizationId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $filters['organization_id'] = $organizationId;
        return $this->getAll($filters, $perPage);
    }

    public function getByCompany(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $filters['company_id'] = $companyId;
        return $this->getAll($filters, $perPage);
    }

    public function findById(int $id): ?User
    {
        return User::with(['organization', 'company', 'role', 'usersCompanyAccess.company'])->find($id);
    }

    public function findByUsername(string $username): ?User
    {
        return User::where('username', $username)->first();
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $user = $this->findById($id);
        
        if (!$user) {
            return false;
        }

        return $user->update($data);
    }

    public function delete(int $id): bool
    {
        $user = $this->findById($id);
        
        if (!$user) {
            return false;
        }

        return $user->delete();
    }

    public function getActive(): Collection
    {
        return User::active()->get();
    }
}
