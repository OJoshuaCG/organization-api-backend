<?php

namespace App\Services;

use App\Repositories\Contracts\OrganizationRepositoryInterface;

class OrganizationService
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository
    ) {}

    public function getAll(array $filters = [], int $perPage = 15)
    {
        return $this->organizationRepository->getAll($filters, $perPage);
    }

    public function findById(int $id)
    {
        return $this->organizationRepository->findById($id);
    }

    public function create(array $data)
    {
        return $this->organizationRepository->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->organizationRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->organizationRepository->delete($id);
    }
}
