<?php

namespace App\Services;

use App\Repositories\Contracts\CompanyRepositoryInterface;

class CompanyService
{
    public function __construct(
        private CompanyRepositoryInterface $companyRepository
    ) {}

    public function getAll(array $filters = [], int $perPage = 15)
    {
        return $this->companyRepository->getAll($filters, $perPage);
    }

    public function findById(int $id)
    {
        return $this->companyRepository->findById($id);
    }

    public function create(array $data)
    {
        return $this->companyRepository->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->companyRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->companyRepository->delete($id);
    }

    public function getByOrganization(int $organizationId)
    {
        return $this->companyRepository->getByOrganization($organizationId);
    }

    public function getActive()
    {
        return $this->companyRepository->getActive();
    }
}
