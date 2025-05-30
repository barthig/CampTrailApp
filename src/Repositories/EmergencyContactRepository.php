<?php
declare(strict_types=1);

namespace src\Repositories;

use PDO;


class EmergencyContactRepository {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getByUserId(int $userId): ?array {
        $stmt = $this->db->prepare("
            SELECT contact_name, phone, relation
            FROM emergency_contact
            WHERE user_id = :uid
        ");
        $stmt->execute(['uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function upsert(int $userId, string $name, string $phone, string $relation): void {
    try {
        $this->db->beginTransaction();
        $stmt = $this->db->prepare("
            INSERT INTO emergency_contact(user_id, contact_name, phone, relation)
            VALUES (:uid, :name, :phone, :relation)
            ON CONFLICT (user_id) DO UPDATE
              SET contact_name = EXCLUDED.contact_name,
                  phone        = EXCLUDED.phone,
                  relation     = EXCLUDED.relation,
                  updated_at   = now()
        ");
        $stmt->execute([
            'uid'      => $userId,
            'name'     => $name,
            'phone'    => $phone,
            'relation' => $relation,
        ]);
        $this->db->commit();
    } catch (\Exception $e) {
        $this->db->rollBack();
        throw $e;
    }
}
}
