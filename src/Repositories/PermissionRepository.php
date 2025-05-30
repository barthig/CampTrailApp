<?php
declare(strict_types=1);

namespace src\Repositories;

use PDO;

class PermissionRepository
{
    private PDO $db;

    /**
     * @param PDO $db 
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findAll(): array
    {
        $stmt = $this->db->query('SELECT id, name FROM permissions');
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @param array<string, mixed> $data
     * @return int New permission ID
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO permissions (name) VALUES (:name)');
        $stmt->execute(['name' => $data['name']]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * @param int $id
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare('UPDATE permissions SET name = :name WHERE id = :id');
        $stmt->execute(['id' => $id, 'name' => $data['name']]);
    }

    /**
     * @param int $id
     */
    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM permissions WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /**
     * @param int $userId
     * @return string[]
     */
    public function getUserPermissions(int $userId): array
    {
        $sql = <<<SQL
SELECT p.name
  FROM permissions p
  JOIN role_permissions rp ON rp.permission_id = p.id
  JOIN roles r ON r.id = rp.role_id
  JOIN users u ON u.role_id = r.id
 WHERE u.id = :userId
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['userId' => $userId]);
        $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $permissions ?: [];
    }
}
