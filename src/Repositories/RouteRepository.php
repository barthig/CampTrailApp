<?php

declare(strict_types=1);

namespace src\Repositories;

use PDO;
use src\Core\SessionManager;
use src\Repositories\DestinationRepository;


class RouteRepository
{
    private PDO $db;
    private ?int $userId;
    private DestinationRepository $destRepo;

    /**
     * @param PDO                    $db
     * @param DestinationRepository  $destRepo 
     */
    public function __construct(PDO $db, DestinationRepository $destRepo)
    {
        $this->db       = $db;
        $this->destRepo = $destRepo;
        $this->userId   = SessionManager::getUserId();
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function findAll(): array
    {
        $sql = <<<SQL
SELECT
    route_id,
    created_at,
    origin,
    destination,
    camper_name,
    distance
FROM
    route_overview
WHERE
    user_id = :uid
ORDER BY
    created_at DESC
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $this->userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function findAllAdmin(): array
    {
        $sql = <<<SQL
SELECT
    route_id,
    created_at,
    origin,
    destination,
    camper_name,
    distance,
    user_id
FROM
    route_overview
ORDER BY
    created_at DESC
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param int $id
     * @return array<string,mixed>|null
     */
    public function findById(int $id): ?array
    {
        $sql = <<<SQL
SELECT
    r.id,
    r.origin_id,
    r.destination_id,
    r.camper_id,
    to_char(r.created_at, 'YYYY-MM-DD HH24:MI') AS created_at,
    o.name   AS origin,
    m.name   AS destination,
    c.name   AS camper_name,
    r.distance
FROM routes r
JOIN destinations o ON r.origin_id      = o.id
JOIN destinations m ON r.destination_id = m.id
JOIN campers      c ON r.camper_id      = c.id
WHERE r.id = :id
  AND r.user_id = :uid
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id'  => $id,
            'uid' => $this->userId,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * @param array{
     *   origin_id:int,
     *   destination_id:int,
     *   distance:float,
     *   user_id:int,
     *   camper_id:int
     * } $data
     * @return int
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            <<<SQL
INSERT INTO routes
    (origin_id, destination_id, distance, user_id, camper_id)
VALUES
    (:origin_id, :destination_id, :distance, :user_id, :camper_id)
SQL
        );
        $stmt->execute([
            'origin_id'      => $data['origin_id'],
            'destination_id' => $data['destination_id'],
            'distance'       => $data['distance'],
            'user_id'        => $this->userId,   
            'camper_id'      => $data['camper_id'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * @param int $id
     * @param array{
     *   origin_id:int,
     *   destination_id:int,
     *   distance:float,
     *   camper_id:int
     * } $data
     */
    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            <<<SQL
UPDATE routes SET
    origin_id      = :origin_id,
    destination_id = :destination_id,
    distance       = :distance,
    camper_id      = :camper_id
WHERE id = :id
  AND user_id = :uid
SQL
        );
        $stmt->execute([
            'origin_id'      => $data['origin_id'],
            'destination_id' => $data['destination_id'],
            'distance'       => $data['distance'],
            'camper_id'      => $data['camper_id'],
            'id'             => $id,
            'uid'            => $this->userId,
        ]);
    }

    /**
     * @param int $id
     */
    public function delete(int $id): void
    {
        $stmt = $this->db->prepare(
            'DELETE FROM routes WHERE id = :id AND user_id = :uid'
        );
        $stmt->execute([
            'id'  => $id,
            'uid' => $this->userId,
        ]);
    }

    public function statsByMonth(int $userId, ?int $camperId = null): array
    {
        $sql = 'SELECT month_label AS label, routes_count AS routes, km_sum AS km
            FROM route_stats_monthly
            WHERE user_id = :uid'
            . ($camperId ? ' AND camper_id = :cid' : '')
            . ' ORDER BY month_label';

        $stmt = $this->db->prepare($sql);
        $params = ['uid' => $userId];
        if ($camperId) $params['cid'] = $camperId;
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function statsByYear(int $userId, ?int $camperId = null): array
    {
        $sql = 'SELECT year_label AS label, routes_count AS routes, km_sum AS km
            FROM route_stats_yearly
            WHERE user_id = :uid'
            . ($camperId ? ' AND camper_id = :cid' : '')
            . ' ORDER BY year_label';

        $stmt = $this->db->prepare($sql);
        $params = ['uid' => $userId];
        if ($camperId) $params['cid'] = $camperId;
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
