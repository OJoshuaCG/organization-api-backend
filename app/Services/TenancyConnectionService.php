<?php

namespace App\Services;

use App\Models\TenancyModeGroup;
use App\Models\DbSharedConnection;
use App\Models\DbDedicatedConnection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TenancyConnectionService
{
    public function getTenancyGroups(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = TenancyModeGroup::query();

        if (isset($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }

        if (isset($filters['tenancy_mode'])) {
            $query->where('tenancy_mode', $filters['tenancy_mode']);
        }

        return $query->paginate($perPage);
    }

    public function findTenancyGroup(int $id): ?TenancyModeGroup
    {
        return TenancyModeGroup::find($id);
    }

    public function createTenancyGroup(array $data): TenancyModeGroup
    {
        return TenancyModeGroup::create($data);
    }

    public function updateTenancyGroup(int $id, array $data): bool
    {
        $group = $this->findTenancyGroup($id);

        if (!$group) {
            return false;
        }

        return $group->update($data);
    }

    public function deleteTenancyGroup(int $id): bool
    {
        $group = $this->findTenancyGroup($id);

        if (!$group) {
            return false;
        }

        return $group->delete();
    }

    public function getSharedConnections(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = DbSharedConnection::query();

        if (isset($filters['tenancy_mode_group_id'])) {
            $query->where('tenancy_mode_group_id', $filters['tenancy_mode_group_id']);
        }

        if (isset($filters['env'])) {
            $query->where('env', $filters['env']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->with('tenancyModeGroup')->paginate($perPage);
    }

    public function findSharedConnection(int $id): ?DbSharedConnection
    {
        return DbSharedConnection::with('tenancyModeGroup')->find($id);
    }

    public function createSharedConnection(array $data): DbSharedConnection
    {
        return DbSharedConnection::create($data);
    }

    public function updateSharedConnection(int $id, array $data): bool
    {
        $connection = $this->findSharedConnection($id);

        if (!$connection) {
            return false;
        }

        return $connection->update($data);
    }

    public function deleteSharedConnection(int $id): bool
    {
        $connection = $this->findSharedConnection($id);

        if (!$connection) {
            return false;
        }

        return $connection->delete();
    }

    public function getDedicatedConnections(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = DbDedicatedConnection::query();

        if (isset($filters['tenancy_mode_group_id'])) {
            $query->where('tenancy_mode_group_id', $filters['tenancy_mode_group_id']);
        }

        if (isset($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->with(['tenancyModeGroup', 'company'])->paginate($perPage);
    }

    public function findDedicatedConnection(int $id): ?DbDedicatedConnection
    {
        return DbDedicatedConnection::with(['tenancyModeGroup', 'company'])->find($id);
    }

    public function createDedicatedConnection(array $data): DbDedicatedConnection
    {
        return DbDedicatedConnection::create($data);
    }

    public function updateDedicatedConnection(int $id, array $data): bool
    {
        $connection = $this->findDedicatedConnection($id);

        if (!$connection) {
            return false;
        }

        return $connection->update($data);
    }

    public function deleteDedicatedConnection(int $id): bool
    {
        $connection = $this->findDedicatedConnection($id);

        if (!$connection) {
            return false;
        }

        return $connection->delete();
    }

    public function testConnection(int $id, string $type): array
    {
        try {
            if ($type === 'shared') {
                $connection = $this->findSharedConnection($id);
                if (!$connection) {
                    return ['success' => false, 'message' => 'Conexión no encontrada'];
                }
            } else {
                $connection = $this->findDedicatedConnection($id);
                if (!$connection) {
                    return ['success' => false, 'message' => 'Conexión no encontrada'];
                }
            }

            $dsn = "mysql:host={$connection->db_host};port={$connection->db_port};dbname={$connection->db_name}";
            $pdo = new \PDO($dsn, $connection->db_user, $connection->db_pass);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            return ['success' => true, 'message' => 'Conexión exitosa'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()];
        }
    }

    public function resolveConnection(int $companyId): ?array
    {
        $dedicatedConnection = DbDedicatedConnection::where('company_id', $companyId)
            ->where('is_active', true)
            ->with(['tenancyModeGroup', 'company'])
            ->first();

        if ($dedicatedConnection) {
            return [
                'type' => 'dedicated',
                'connection' => [
                    'host' => $dedicatedConnection->db_host,
                    'port' => $dedicatedConnection->db_port,
                    'database' => $dedicatedConnection->db_name,
                    'tenancy_mode' => $dedicatedConnection->tenancyModeGroup?->tenancy_mode,
                ],
            ];
        }

        $sharedConnection = DbSharedConnection::where('is_active', true)
            ->with('tenancyModeGroup')
            ->first();

        if ($sharedConnection) {
            return [
                'type' => 'shared',
                'connection' => [
                    'host' => $sharedConnection->db_host,
                    'port' => $sharedConnection->db_port,
                    'database' => $sharedConnection->db_name,
                    'tenancy_mode' => $sharedConnection->tenancyModeGroup?->tenancy_mode,
                ],
            ];
        }

        return null;
    }
}
