<?php
declare(strict_types=1);

namespace src\Repositories;

use PDO;
use PDOException;

/**
 * DestinationRepository handles CRUD operations for destinations,
 * using the database view for full detail retrieval.
 */
class DestinationRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Pobierz podstawowe informacje o wszystkich destynacjach.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findAll(): array
{
    $sql = <<<SQL
SELECT 
    d.id,
    d.name,
    d.location,
    d.short_description,
    d.price,
    d.capacity,
    -- pobiera pierwszy obrazek jako thumbnail
    (SELECT di.image_url 
       FROM destination_images di 
      WHERE di.destination_id = d.id 
      ORDER BY di.uploaded_at DESC 
      LIMIT 1
    ) AS thumbnail
  FROM destinations d
 ORDER BY d.name
SQL;

    $stmt = $this->db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    /**
     * Pobierz dane destynacji po ID.
     *
     * @param int $id
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, name, location, short_description, description,
                    price, capacity, phone, contact_email, website,
                    latitude, longitude, season_from, season_to,
                    checkin_time, checkout_time, rules
             FROM destinations
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Pobierz pełne dane destynacji (pola podstawowe + images + amenities)
     * z widoku `destination_full_details_view`.
     *
     * @param int $id
     * @return array<string, mixed>|null
     */
    public function findByIdWithDetails(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT *
             FROM destination_full_details_view
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Pobierz inne destynacje wyłączając wskazane ID.
     *
     * @param int $excludeId
     * @return array<int, array<string, mixed>>
     */
    public function findAllExcept(int $excludeId): array
    {
        $stmt = $this->db->prepare(
            'SELECT d.id, d.name, d.location, d.short_description, d.price,
                    (SELECT image_url FROM destination_images di
                     WHERE di.destination_id = d.id LIMIT 1) AS image_url
             FROM destinations d
             WHERE d.id != :id
             ORDER BY d.name'
        );
        $stmt->execute(['id' => $excludeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
 * Pobierz wszystkie zdjęcia dla danej destynacji.
 *
 * @param int $destinationId
 * @return array<int,array{id:int,image_url:string,uploaded_at:string}>
 */
public function findImagesByDestination(int $destinationId): array
{
    $sql = <<<SQL
SELECT id,
       image_url,
       uploaded_at::text AS uploaded_at
  FROM destination_images
 WHERE destination_id = :did
 ORDER BY uploaded_at DESC
SQL;
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['did' => $destinationId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    /**
     * Utwórz destynację wraz z udogodnieniami i zdjęciami.
     *
     * @param array<string, mixed> $data
     * @return int
     * @throws PDOException
     */
    public function createWithDetails(array $data): int
    {
        $price        = is_numeric($data['price'] ?? '')     ? (float)$data['price']   : null;
        $capacity     = is_numeric($data['capacity'] ?? '')  ? (int)$data['capacity']  : null;
        $latitude     = is_numeric($data['latitude'] ?? '')  ? (float)$data['latitude'] : null;
        $longitude    = is_numeric($data['longitude'] ?? '') ? (float)$data['longitude']: null;
        $seasonFrom   = trim((string)($data['season_from'] ?? '')) !== '' ? $data['season_from']   : null;
        $seasonTo     = trim((string)($data['season_to'] ?? ''))   !== '' ? $data['season_to']     : null;
        $checkinTime  = trim((string)($data['checkin_time'] ?? '')) !== '' ? $data['checkin_time']  : null;
        $checkoutTime = trim((string)($data['checkout_time'] ?? ''))!== '' ? $data['checkout_time'] : null;

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO destinations
                 (name, location, short_description, description, price, capacity,
                  phone, contact_email, website, latitude, longitude,
                  season_from, season_to, checkin_time, checkout_time, rules)
                 VALUES
                 (:name,:location,:short_description,:description,:price,:capacity,
                  :phone,:contact_email,:website,:latitude,:longitude,
                  :season_from,:season_to,:checkin_time,:checkout_time,:rules)'
            );
            $stmt->execute([
                'name'              => $data['name'],
                'location'          => $data['location'],
                'short_description' => $data['short_description'],
                'description'       => $data['description'],
                'price'             => $price,
                'capacity'          => $capacity,
                'phone'             => $data['phone'],
                'contact_email'     => $data['contact_email'],
                'website'           => $data['website'],
                'latitude'          => $latitude,
                'longitude'         => $longitude,
                'season_from'       => $seasonFrom,
                'season_to'         => $seasonTo,
                'checkin_time'      => $checkinTime,
                'checkout_time'     => $checkoutTime,
                'rules'             => $data['rules'],
            ]);
            $destId = (int)$this->db->lastInsertId('destinations_id_seq');

            if (!empty($data['amenities'])) {
                $codes = '{'.implode(',', $data['amenities']).'}';
                $stmtAm = $this->db->prepare(
                    'INSERT INTO destination_amenity(destination_id, amenity_id)
                     SELECT :did, a.id FROM amenities a WHERE a.code = ANY(:codes)'
                );
                $stmtAm->execute(['did' => $destId, 'codes' => $codes]);
            }

            if (!empty($data['images'])) {
                $stmtImg = $this->db->prepare(
                    'INSERT INTO destination_images(destination_id, image_url)
                     VALUES(:did,:url)'
                );
                foreach ($data['images'] as $url) {
                    $stmtImg->execute(['did' => $destId, 'url' => $url]);
                }
            }

            $this->db->commit();
            return $destId;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Zaktualizuj destynację wraz z udogodnieniami i zdjęciami.
     *
     * @param array<string, mixed> $data
     * @param string[] $deleteImages
     * @param string[] $newImages
     * @throws PDOException
     */
    public function updateWithDetails(array $data, array $deleteImages = [], array $newImages = []): void
    {
        $this->db->beginTransaction();
        try {
            if (!empty($deleteImages)) {
                $arr = '{'.implode(',',
                       array_map(fn($u) => '"'.str_replace('"','\\"',$u).'"', $deleteImages)
                     ).'}';
                $stmtDel = $this->db->prepare(
                    'DELETE FROM destination_images WHERE destination_id = :did AND image_url = ANY(:urls)'
                );
                $stmtDel->execute(['did' => $data['id'], 'urls' => $arr]);
                foreach ($deleteImages as $url) {
                    $path = $_SERVER['DOCUMENT_ROOT'] . $url;
                    if (file_exists($path)) {
                        @unlink($path);
                    }
                }
            }

            $stmt = $this->db->prepare(
                'UPDATE destinations SET
                    name              = :name,
                    location          = :location,
                    short_description = :short_description,
                    description       = :description,
                    price             = :price,
                    capacity          = :capacity,
                    phone             = :phone,
                    contact_email     = :contact_email,
                    website           = :website,
                    latitude          = :latitude,
                    longitude         = :longitude,
                    season_from       = :season_from,
                    season_to         = :season_to,
                    checkin_time      = :checkin_time,
                    checkout_time     = :checkout_time,
                    rules             = :rules
                 WHERE id = :id'
            );
            $stmt->execute([
                'name'              => $data['name'],
                'location'          => $data['location'],
                'short_description' => $data['short_description'],
                'description'       => $data['description'],
                'price'             => is_numeric($data['price'] ?? '') ? (float)$data['price'] : null,
                'capacity'          => is_numeric($data['capacity'] ?? '') ? (int)$data['capacity'] : null,
                'phone'             => $data['phone'],
                'contact_email'     => $data['contact_email'],
                'website'           => $data['website'],
                'latitude'          => is_numeric($data['latitude'] ?? '') ? (float)$data['latitude'] : null,
                'longitude'         => is_numeric($data['longitude'] ?? '') ? (float)$data['longitude'] : null,
                'season_from'       => trim((string)($data['season_from'] ?? '')) !== '' ? $data['season_from'] : null,
                'season_to'         => trim((string)($data['season_to'] ?? '')) !== ''   ? $data['season_to']   : null,
                'checkin_time'      => trim((string)($data['checkin_time'] ?? '')) !== '' ? $data['checkin_time']  : null,
                'checkout_time'     => trim((string)($data['checkout_time'] ?? ''))!== '' ? $data['checkout_time'] : null,
                'rules'             => $data['rules'],
                'id'                => $data['id'],
            ]);

            // odśwież amenities
            $this->db->prepare('DELETE FROM destination_amenity WHERE destination_id = :id')
                     ->execute(['id' => $data['id']]);
            if (!empty($data['amenities'])) {
                $codes = '{'.implode(',', $data['amenities']).'}';
                $stmtAm = $this->db->prepare(
                    'INSERT INTO destination_amenity(destination_id, amenity_id)
                     SELECT :did, a.id FROM amenities a WHERE a.code = ANY(:codes)'
                );
                $stmtAm->execute(['did' => $data['id'], 'codes' => $codes]);
            }

            if (!empty($newImages)) {
                $stmtImg = $this->db->prepare(
                    'INSERT INTO destination_images(destination_id, image_url)
                     VALUES(:did,:url)'
                );
                foreach ($newImages as $url) {
                    $stmtImg->execute(['did' => $data['id'], 'url' => $url]);
                }
            }

            $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Usuń destynację.
     *
     * @param int $id
     */
    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM destinations WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

}