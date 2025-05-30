<?php
declare(strict_types=1);

namespace src\Repositories;

use PDO;

class NotificationRepository
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

     *
     * @param int $userId
     * @return array<int, array{id:int,message:string,is_read:bool,created_at:string}>
     */
    public function findAllForUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, message, is_read, created_at
             FROM notifications
             WHERE user_id = :userId
             ORDER BY created_at DESC'
        );
        $stmt->execute(['userId' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     *
     * @param int $userId
     * @return array<int, array{id:int,message:string,is_read:bool,created_at:string}>
     */
    public function findUnreadForUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, message, is_read, created_at
             FROM notifications
             WHERE user_id = :userId AND is_read = FALSE
             ORDER BY created_at DESC'
        );
        $stmt->execute(['userId' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     *
     * @param int $userId
     * @return array<int, array{id:int,message:string,is_read:bool,created_at:string}>
     */
    public function findReadForUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, message, is_read, created_at
             FROM notifications
             WHERE user_id = :userId AND is_read = TRUE
             ORDER BY created_at DESC'
        );
        $stmt->execute(['userId' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param int $id
     */
    public function markAsRead(int $id): void
    {
        $stmt = $this->db->prepare(
            'UPDATE notifications
             SET is_read = TRUE
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }

    /**
     * @param int $userId
     */
    public function markAllReadForUser(int $userId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE notifications
             SET is_read = TRUE
             WHERE user_id = :userId'
        );
        $stmt->execute(['userId' => $userId]);
    }

   public function findAllAdmin(): array
{
    $sql = <<<SQL
SELECT id,
       user_id,
       message,
       is_read,
       created_at
  FROM notifications
 ORDER BY created_at DESC
SQL;
    $stmt = $this->db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

    /**
     * @param int $id
     */
    public function delete(int $id): void
    {
        $stmt = $this->db->prepare(
            'DELETE FROM notifications
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }
}
