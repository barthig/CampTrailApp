<?php

declare(strict_types=1);

namespace src\Controllers;

require_once __DIR__ . '/AppController.php';
require_once __DIR__ . '/../Repositories/CamperRepository.php';


use PDO;
use Exception;
use src\Controllers\AppController;
use src\Repositories\CamperRepository;
use src\Core\SessionManager;


class CamperController extends AppController
{
    private CamperRepository $camperRepo;
    private string $uploadDir;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->ensureLoggedIn();
        $this->camperRepo = new CamperRepository($this->db);
        $this->uploadDir = __DIR__ . '/../../public/uploads/';
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Lista kamperów zalogowanego użytkownika
     */
    public function list(): void
    {
        $userId = (int) SessionManager::getUserId();
        $campers = $this->camperRepo->findByUser($userId);

        foreach ($campers as &$c) {
            $insurances = $this->camperRepo->findInsuranceByCamper((int)$c['id']);
            $c['insurance_end'] = $insurances[0]['end_date'] ?? null;
            $inspections = $this->camperRepo->findInspectionsByCamper((int)$c['id']);
            $c['inspection_valid'] = $inspections[0]['valid_until'] ?? null;
        }
        unset($c);

        $this->render('campers/list', ['campers' => $campers]);
    }

    public function view(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('/campers');
            return;
        }

        $camper = $this->camperRepo->findById($id);
        if (!$camper || $camper['user_id'] !== (int) SessionManager::getUserId()) {
            http_response_code(403);
            $this->render('errors/403');
            return;
        }

        $insurance   = $this->camperRepo->findInsuranceByCamper($id);
        $inspections = $this->camperRepo->findInspectionsByCamper($id);
        $images      = $this->camperRepo->findImagesByCamper($id);

        $this->render('campers/view', compact('camper', 'insurance', 'inspections', 'images'));
    }

    public function create(): void
    {
        $defaults = $this->getDefaultData();

        if ($this->isPost()) {
            $data = $this->buildDataFromPost();
            $data['camper']['user_id'] = (int) SessionManager::getUserId();

            try {
                $camperId = $this->camperRepo->createFull(
                    $data['camper'],
                    $data['insurance'],
                    $data['inspection']
                );

                if (!empty($_FILES['image']['name'])) {
                    $file     = $_FILES['image'];
                    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = uniqid('img_', true) . '.' . $ext;
                    $target   = $this->uploadDir . $filename;
                    if (move_uploaded_file($file['tmp_name'], $target)) {
                        $imageUrl = '/public/uploads/' . $filename;
                        $this->camperRepo->addImage($camperId, $imageUrl);
                    }
                }

                $this->redirect('/campers');
                return;
            } catch (Exception $e) {
                $defaults = array_merge($defaults, $data, ['error' => $e->getMessage()]);
            }
        }

        $this->render('campers/form', $defaults);
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('/campers');
            return;
        }

        $camper = $this->camperRepo->findById($id);
        if (!$camper || $camper['user_id'] !== (int) SessionManager::getUserId()) {
            http_response_code(403);
            $this->render('errors/403');
            return;
        }

        $oldImages = $this->camperRepo->findImagesByCamper($id);

        $insurance   = $this->camperRepo->findInsuranceByCamper($id)[0] ?? [];
        $inspections = $this->camperRepo->findInspectionsByCamper($id);
        $inspection  = $inspections[0] ?? [];
        $images      = $oldImages;

        $defaults = [
            'camper'     => $camper,
            'insurance'  => $insurance,
            'inspection' => $inspection,
            'images'     => $images,
            'error'      => null,
        ];

        if ($this->isPost()) {
            $data = $this->buildDataFromPost();
            $data['camper']['user_id'] = (int) SessionManager::getUserId();

            try {
             
                $this->camperRepo->updateFull(
                    $id,
                    $data['camper'],
                    $data['insurance'],
                    $data['inspection']
                );

                if (!empty($_FILES['image']['name'])) {
                    if (!empty($oldImages)) {
                        $old = $oldImages[0];
                        $relative = ltrim($old['image_url'], '/');
                        $path = __DIR__ . '/../../' . $relative;
                        if (file_exists($path)) {
                            unlink($path);
                        }
                        $this->camperRepo->deleteImage((int)$old['id']);
                    }

                    $file     = $_FILES['image'];
                    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = uniqid('img_', true) . '.' . $ext;
                    $target   = $this->uploadDir . $filename;
                    if (move_uploaded_file($file['tmp_name'], $target)) {
                        $imageUrl = '/public/uploads/' . $filename;
                        $this->camperRepo->addImage($id, $imageUrl);
                    }
                }

                $this->redirect('/campers');
                return;
            } catch (Exception $e) {
                $defaults = [
                    'camper'     => $data['camper'],
                    'insurance'  => $data['insurance'],
                    'inspection' => $data['inspection'],
                    'images'     => $images,
                    'error'      => $e->getMessage(),
                ];
            }
        }

        $this->render('campers/form', $defaults);
    }
    public function delete(): void
    {
        if ($this->isPost()) {
            $id = (int)($_POST['id'] ?? 0);
            $camper = $this->camperRepo->findById($id);
            if ($id > 0 && $camper && $camper['user_id'] === (int) SessionManager::getUserId()) {
                $this->camperRepo->delete($id);
            }
        }
        $this->redirect('/campers');
    }

    private function getDefaultData(): array
    {
        return [
            'camper' => [
                'user_id'             => (int) SessionManager::getUserId(),
                'name'                => '',
                'type'                => '',
                'capacity'            => 0,
                'vin'                 => '',
                'registration_number' => '',
                'brand'               => '',
                'model'               => '',
                'year'                => (int)date('Y'),
                'mileage'             => 0,
                'purchase_date'       => null,
            ],
            'insurance' => [
                'insurer_name'       => '',
                'policy_number'      => '',
                'policy_start_date'  => null,
                'policy_end_date'    => null,
                'premium'            => '',
            ],
            'inspection' => [
                'last_inspection_date'   => null,
                'inspection_valid_until' => null,
                'inspection_person'      => '',
                'inspection_result'      => '',
            ],
            'images' => [],
            'error'  => null,
        ];
    }

    private function buildDataFromPost(): array
    {
        $toDate         = fn($v) => (isset($v) && $v !== '') ? $v : null;
        $toIntOrNull    = fn($v) => (isset($v) && $v !== '') ? (int)$v : null;
        $toFloatOrNull  = fn($v) => (isset($v) && $v !== '') ? (float)$v : null;
        $toStringOrNull = fn($v) => isset($v) && trim($v) !== '' ? trim($v) : null;

        return [
            'camper' => [
                'user_id'             => (int) SessionManager::getUserId(),
                'name'                => trim($_POST['name'] ?? ''),
                'type'                => trim($_POST['type'] ?? ''),
                'capacity'            => $toIntOrNull($_POST['capacity'] ?? ''),
                'vin'                 => trim($_POST['vin'] ?? ''),
                'registration_number' => $toStringOrNull($_POST['registration_number'] ?? ''),
                'brand'               => trim($_POST['brand'] ?? ''),
                'model'               => trim($_POST['model'] ?? ''),
                'year'                => $toIntOrNull($_POST['year'] ?? ''),
                'mileage'             => $toIntOrNull($_POST['mileage'] ?? ''),
                'purchase_date'       => $toDate($_POST['purchase_date'] ?? ''),
            ],
            'insurance' => [
                'insurer_name'       => trim($_POST['insurer_name'] ?? ''),
                'policy_number'      => trim($_POST['policy_number'] ?? ''),
                'policy_start_date'  => $toDate($_POST['policy_start_date'] ?? ''),
                'policy_end_date'    => $toDate($_POST['policy_end_date'] ?? ''),
                'premium'            => $toFloatOrNull($_POST['premium'] ?? ''),
            ],
            'inspection' => [
                'last_inspection_date'   => $toDate($_POST['last_inspection_date'] ?? ''),
                'inspection_valid_until' => $toDate($_POST['inspection_valid_until'] ?? ''),
                'inspection_person'      => trim($_POST['inspection_person'] ?? ''),
                'inspection_result'      => trim($_POST['inspection_result'] ?? ''),
            ],
        ];
    }
}
