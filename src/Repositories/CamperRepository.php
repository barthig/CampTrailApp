<?php

declare(strict_types=1);

namespace src\Repositories;

use PDO;
use PDOException;
use Exception;

class CamperRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * @param int $userId
     * @return array
     * @throws PDOException
     */
    public function findByUser(int $userId): array
    {
        $sql = <<<SQL
SELECT cv.id,
       cv.name,
       cv.type,
       cv.capacity,
       cv.vin,
       cv.registration_number,
       cv.brand,
       cv.model,
       cv.year,
       cv.mileage,
       cv.purchase_date,
       cv.image_url
FROM camper_view AS cv
JOIN campers AS c ON c.id = cv.id
WHERE c.user_id = :uid
ORDER BY cv.name
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array
     */
    public function findAll(): array
    {
        $sql = <<<SQL
SELECT id,
       name,
       type,
       capacity,
       vin,
       registration_number,
       brand,
       model,
       year,
       mileage,
       purchase_date,
       image_url
FROM camper_view
ORDER BY name
SQL;
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM camper_view WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $camper = $stmt->fetch(PDO::FETCH_ASSOC);
        return $camper ?: null;
    }

    /**
     * @param array $data
     * @return int
     * @throws PDOException
     */
    public function create(array $data): int
    {
        $sql = <<<'SQL'
INSERT INTO campers
    (user_id, name, type, capacity, vin, registration_number, brand, model, year, mileage, purchase_date)
VALUES
    (:user_id, :name, :type, :capacity, :vin, :registration_number, :brand, :model, :year, :mileage, :purchase_date)
RETURNING id
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id'             => $data['user_id'],
            'name'                => $data['name'],
            'type'                => $data['type'],
            'capacity'            => $data['capacity'],
            'vin'                 => $data['vin']                 ?? null,
            'registration_number' => $data['registration_number'] ?? null,
            'brand'               => $data['brand']               ?? null,
            'model'               => $data['model']               ?? null,
            'year'                => $data['year']                ?? null,
            'mileage'             => $data['mileage']             ?? null,
            'purchase_date'       => $data['purchase_date']       ?? null,
        ]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * @param int $id
     * @param array $data
     * @throws PDOException
     */
    public function update(int $id, array $data): void
    {
        $sql = <<<'SQL'
UPDATE campers SET
    name                = :name,
    type                = :type,
    capacity            = :capacity,
    vin                 = :vin,
    registration_number = :registration_number,
    brand               = :brand,
    model               = :model,
    year                = :year,
    mileage             = :mileage,
    purchase_date       = :purchase_date,
    updated_at          = NOW()
WHERE id = :id
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id'                  => $id,
            'name'                => $data['name'],
            'type'                => $data['type'],
            'capacity'            => $data['capacity'],
            'vin'                 => $data['vin']                 ?? null,
            'registration_number' => $data['registration_number'] ?? null,
            'brand'               => $data['brand']               ?? null,
            'model'               => $data['model']               ?? null,
            'year'                => $data['year']                ?? null,
            'mileage'             => $data['mileage']             ?? null,
            'purchase_date'       => $data['purchase_date']       ?? null,
        ]);
    }

    /**
     * @param int $id
     * @throws PDOException
     */
    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM campers WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /**
     * @param int $camperId
     * @return array
     */
    public function findInsuranceByCamper(int $camperId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, camper_id, camper_name, insurer_name, policy_number, start_date, end_date, premium
               FROM camper_insurance_view
              WHERE camper_id = :cid
           ORDER BY start_date DESC'
        );
        $stmt->execute(['cid' => $camperId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param int $camperId
     * @return array
     */
    public function findInspectionsByCamper(int $camperId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, camper_id, camper_name, inspection_date, valid_until, result, inspector_name
               FROM camper_inspections_view
              WHERE camper_id = :cid
           ORDER BY inspection_date DESC'
        );
        $stmt->execute(['cid' => $camperId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param array $camperData
     * @param array $insuranceData
     * @param array $inspectionData
     * @return int
     * @throws Exception
     */
    public function createFull(array $camperData, array $insuranceData, array $inspectionData): int
    {
        $this->db->beginTransaction();
        try {
            $camperId = $this->create($camperData);
            $insSql = <<<'SQL'
INSERT INTO insurance
    (camper_id, insurer_name, policy_number, start_date, end_date, premium)
VALUES
    (:camper_id, :insurer_name, :policy_number, :start_date, :end_date, :premium)
SQL;
            $insStmt = $this->db->prepare($insSql);
            $insStmt->execute([
                'camper_id'     => $camperId,
                'insurer_name'  => $insuranceData['insurer_name'],
                'policy_number' => $insuranceData['policy_number'],
                'start_date'    => $insuranceData['policy_start_date'],
                'end_date'      => $insuranceData['policy_end_date'],
                'premium'       => $insuranceData['premium'],
            ]);

            if (!empty($inspectionData['last_inspection_date'])) {
                $inspSql = <<<'SQL'
INSERT INTO technical_inspection
    (camper_id, inspection_date, valid_until, inspector_name, result)
VALUES
    (:camper_id, :inspection_date, :valid_until, :inspector_name, :result)
SQL;
                $inspStmt = $this->db->prepare($inspSql);
                $inspStmt->execute([
                    'camper_id'        => $camperId,
                    'inspection_date'  => $inspectionData['last_inspection_date'],
                    'valid_until'      => $inspectionData['inspection_valid_until'],
                    'inspector_name'   => $inspectionData['inspection_person'],
                    'result'           => $inspectionData['inspection_result'],
                ]);
            }

            $this->db->commit();
            return $camperId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * @param int $id
     * @param array $camperData
     * @param array $insuranceData
     * @param array $inspectionData
     * @throws Exception
     */
    public function updateFull(int $id, array $camperData, array $insuranceData, array $inspectionData): void
    {
        $this->db->beginTransaction();
        try {
            $this->update($id, $camperData);

            $this->db->prepare('DELETE FROM insurance WHERE camper_id = :cid')
                ->execute(['cid' => $id]);
            $insSql = <<<'SQL'
INSERT INTO insurance
    (camper_id, insurer_name, policy_number, start_date, end_date, premium)
VALUES
    (:camper_id, :insurer_name, :policy_number, :start_date, :end_date, :premium)
SQL;
            $insStmt = $this->db->prepare($insSql);
            $insStmt->execute([
                'camper_id'     => $id,
                'insurer_name'  => $insuranceData['insurer_name'],
                'policy_number' => $insuranceData['policy_number'],
                'start_date'    => $insuranceData['policy_start_date'],
                'end_date'      => $insuranceData['policy_end_date'],
                'premium'       => $insuranceData['premium'],
            ]);

            $this->db->prepare('DELETE FROM technical_inspection WHERE camper_id = :cid')
                ->execute(['cid' => $id]);
            if (!empty($inspectionData['last_inspection_date'])) {
                $inspSql = <<<'SQL'
INSERT INTO technical_inspection
    (camper_id, inspection_date, valid_until, inspector_name, result)
VALUES
    (:camper_id, :inspection_date, :valid_until, :inspector_name, :result)
SQL;
                $inspStmt = $this->db->prepare($inspSql);
                $inspStmt->execute([
                    'camper_id'        => $id,
                    'inspection_date'  => $inspectionData['last_inspection_date'],
                    'valid_until'      => $inspectionData['inspection_valid_until'],
                    'inspector_name'   => $inspectionData['inspection_person'],
                    'result'           => $inspectionData['inspection_result'],
                ]);
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * @param int $camperId
     * @param string $imageUrl
     * @return int
     * @throws PDOException
     */
    public function addImage(int $camperId, string $imageUrl): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO camper_images (camper_id, image_url)
             VALUES (:cid, :url)
             RETURNING id'
        );
        $stmt->execute([
            'cid' => $camperId,
            'url' => $imageUrl,
        ]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * @param int $imageId
     * @throws PDOException
     */
    public function deleteImage(int $imageId): void
    {
        $stmt = $this->db->prepare(
            'DELETE FROM camper_images WHERE id = :id'
        );
        $stmt->execute(['id' => $imageId]);
    }

    /**
     * @param int $camperId
     * @return array
     */
    public function findImagesByCamper(int $camperId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, image_url, uploaded_at::text AS uploaded_at
               FROM camper_images
              WHERE camper_id = :cid
           ORDER BY uploaded_at DESC'
        );
        $stmt->execute(['cid' => $camperId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
