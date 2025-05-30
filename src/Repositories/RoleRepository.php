<?php
declare(strict_types=1);

namespace src\Repositories;

use PDO;


class RoleRepository
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
        $stmt = $this->db->query('SELECT id, name FROM roles');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param array<string, mixed> $data
     * @return int New role ID
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO roles (name) VALUES (:name)'
        );
        $stmt->execute(['name' => $data['name']]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * @param int $id
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE roles SET name = :name WHERE id = :id'
        );
        $stmt->execute(['id' => $id, 'name' => $data['name']]);
    }

    /**
     * @param int $id
     */
    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM roles WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
