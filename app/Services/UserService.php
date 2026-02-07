<?php

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function getAll(array $filters = [], int $perPage = 15)
    {
        return $this->userRepository->getAll($filters, $perPage);
    }

    public function findById(int $id)
    {
        return $this->userRepository->findById($id);
    }

    public function findByEmail(string $email)
    {
        return $this->userRepository->findByEmail($email);
    }

    public function create(array $data)
    {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->userRepository->create($data);
    }

    public function update(int $id, array $data): bool
    {
        // Hash password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return $this->userRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->userRepository->delete($id);
    }

    public function getByOrganization(int $organizationId)
    {
        return $this->userRepository->getByOrganization($organizationId);
    }

    public function getByCompany(int $companyId)
    {
        return $this->userRepository->getByCompany($companyId);
    }

    public function getActive()
    {
        return $this->userRepository->getActive();
    }
}
