<?php

namespace App\Services;

use App\Models\CompanyUserTag;
use App\Models\UserTag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TagService
{
    public function getCompanyTags(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = CompanyUserTag::where('company_id', $companyId);

        if (isset($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->withCount('userTags')->paginate($perPage);
    }

    public function findCompanyTag(int $id): ?CompanyUserTag
    {
        return CompanyUserTag::with('userTags')->find($id);
    }

    public function createCompanyTag(int $companyId, array $data): CompanyUserTag
    {
        $data['company_id'] = $companyId;
        return CompanyUserTag::create($data);
    }

    public function updateCompanyTag(int $id, array $data): bool
    {
        $tag = $this->findCompanyTag($id);

        if (!$tag) {
            return false;
        }

        return $tag->update($data);
    }

    public function deleteCompanyTag(int $id): bool
    {
        $tag = $this->findCompanyTag($id);

        if (!$tag) {
            return false;
        }

        return $tag->delete();
    }

    public function getUserTags(int $userId): Collection
    {
        return UserTag::where('user_id', $userId)
            ->with('companyUserTag')
            ->get();
    }

    public function assignUserTag(int $userId, int $tagId, bool $isPrimary = false): UserTag
    {
        return UserTag::updateOrCreate(
            ['user_id' => $userId, 'company_user_tag_id' => $tagId],
            ['is_primary' => $isPrimary, 'assigned_at' => now()]
        );
    }

    public function removeUserTag(int $userId, int $tagId): bool
    {
        $deleted = UserTag::where('user_id', $userId)
            ->where('company_user_tag_id', $tagId)
            ->delete();

        return $deleted > 0;
    }
}
