<?php

namespace App\Services;

use App\Repositories\Contracts\ModuleRepositoryInterface;

class ModuleService
{
    public function __construct(
        private ModuleRepositoryInterface $moduleRepository
    ) {}

    public function getAll(array $filters = [], int $perPage = 15)
    {
        return $this->moduleRepository->getAll($filters, $perPage);
    }

    public function findById(int $id)
    {
        return $this->moduleRepository->findById($id);
    }

    public function create(array $data)
    {
        return $this->moduleRepository->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->moduleRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->moduleRepository->delete($id);
    }

    public function getActive(array $filters = [], int $perPage = 15)
    {
        return $this->moduleRepository->getActive($filters, $perPage);
    }

    public function getEndpoints(int $moduleId)
    {
        return $this->moduleRepository->getEndpoints($moduleId);
    }
}
