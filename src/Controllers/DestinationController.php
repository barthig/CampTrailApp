<?php
declare(strict_types=1);

namespace src\Controllers;

require_once __DIR__ . '/AppController.php';
require_once __DIR__ . '/../Core/SessionManager.php';
require_once __DIR__ . '/../Repositories/DestinationRepository.php';

use PDO;
use src\Core\SessionManager;
use src\Repositories\DestinationRepository;

class DestinationController extends AppController
{
    private DestinationRepository $destRepo;
    private string $uploadDir;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->ensureLoggedIn();
        $this->destRepo = new DestinationRepository($this->db);
        $this->uploadDir = __DIR__ . '/../../public/uploads/';
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function list(): void
    {
        $destinations = $this->destRepo->findAll();
        $this->render('destinations/list', ['destinations' => $destinations]);
    }

    public function view(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('/destinations');
            return;
        }

        $destination = $this->destRepo->findByIdWithDetails($id);
        if (!$destination) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $otherDestinations = $this->destRepo->findAllExcept($id);
        $images            = $this->destRepo->findImagesByDestination($id);

        $this->render('destinations/view', [
            'destination'       => $destination,
            'otherDestinations' => $otherDestinations,
            'images'            => $images,
        ]);
    }

    public function create(): void
    {
        if ($this->isPost()) {
            $data = [
                'name'              => trim($_POST['name'] ?? ''),
                'location'          => trim($_POST['location'] ?? ''),
                'short_description' => trim($_POST['short_description'] ?? ''),
                'description'       => trim($_POST['description'] ?? ''),
                'price'             => $_POST['price'] ?? null,
                'capacity'          => $_POST['capacity'] ?? null,
                'phone'             => trim($_POST['phone'] ?? ''),
                'contact_email'     => trim($_POST['contact_email'] ?? ''),
                'website'           => trim($_POST['website'] ?? ''),
                'latitude'          => trim($_POST['latitude'] ?? ''),
                'longitude'         => trim($_POST['longitude'] ?? ''),
                'season_from'       => $_POST['season_from'] ?? null,
                'season_to'         => $_POST['season_to'] ?? null,
                'checkin_time'      => $_POST['checkin_time'] ?? null,
                'checkout_time'     => $_POST['checkout_time'] ?? null,
                'rules'             => trim($_POST['rules'] ?? ''),
                'amenities'         => $_POST['amenities'] ?? [],
                'images'            => [],
            ];

            if ($data['name'] === '') {
                SessionManager::flash('error', 'Nazwa destynacji nie może być pusta.');
                $this->redirect('/destinations/create');
                return;
            }

            $uploaded = $_FILES['images'] ?? null;
            if ($uploaded && !empty($uploaded['tmp_name'][0])) {
                $data['images'] = $this->processUploadedImages($uploaded);
            }

            $this->destRepo->createWithDetails($data);
            SessionManager::flash('success', 'Nowa destynacja została dodana.');
            $this->redirect('/destinations');
            return;
        }

        $this->render('destinations/form');
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('/destinations');
            return;
        }

        if ($this->isPost()) {
            $data = [
                'id'                => $id,
                'name'              => trim($_POST['name'] ?? ''),
                'location'          => trim($_POST['location'] ?? ''),
                'short_description' => trim($_POST['short_description'] ?? ''),
                'description'       => trim($_POST['description'] ?? ''),
                'price'             => $_POST['price'] ?? null,
                'capacity'          => $_POST['capacity'] ?? null,
                'phone'             => trim($_POST['phone'] ?? ''),
                'contact_email'     => trim($_POST['contact_email'] ?? ''),
                'website'           => trim($_POST['website'] ?? ''),
                'latitude'          => trim($_POST['latitude'] ?? ''),
                'longitude'         => trim($_POST['longitude'] ?? ''),
                'season_from'       => $_POST['season_from'] ?? null,
                'season_to'         => $_POST['season_to'] ?? null,
                'checkin_time'      => $_POST['checkin_time'] ?? null,
                'checkout_time'     => $_POST['checkout_time'] ?? null,
                'rules'             => trim($_POST['rules'] ?? ''),
                'amenities'         => $_POST['amenities'] ?? [],
            ];

            if ($data['name'] === '') {
                SessionManager::flash('error', 'Nazwa destynacji nie może być pusta.');
                $this->redirect("/destinations/edit?id={$id}");
                return;
            }

            $deleteImages = $_POST['delete_images'] ?? [];

            $uploaded  = $_FILES['images'] ?? null;
            $newImages = [];
            if ($uploaded && !empty($uploaded['tmp_name'][0])) {
                $newImages = $this->processUploadedImages($uploaded);
            }

            $this->destRepo->updateWithDetails($data, $deleteImages, $newImages);
            SessionManager::flash('success', 'Destynacja została zaktualizowana.');
            $this->redirect('/destinations');
            return;
        }

        $destination = $this->destRepo->findByIdWithDetails($id);
        if (!$destination) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $this->render('destinations/form', ['destination' => $destination]);
    }

    public function delete(): void
    {
        if ($this->isPost()) {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $this->destRepo->delete($id);
                SessionManager::flash('success', 'Destynacja została usunięta.');
            } else {
                SessionManager::flash('error', 'Nieprawidłowe ID destynacji.');
            }
        }
        $this->redirect('/destinations');
    }

    /**
     * @param array<string,mixed> $files
     * @return string[] 
     * @throws \RuntimeException
     */
    private function processUploadedImages(array $files): array
    {
        $urls = [];
        foreach ($files['tmp_name'] as $i => $tmpPath) {
            if (!is_uploaded_file($tmpPath)) {
                continue;
            }
            $orig     = basename($files['name'][$i]);
            $filename = time() . '_' . uniqid() . '_' . $orig;
            $target   = $this->uploadDir . $filename;
            if (!move_uploaded_file($tmpPath, $target)) {
                throw new \RuntimeException("Nie można przenieść pliku: $tmpPath → $target");
            }
            $urls[] = '/public/uploads/' . $filename;
        }
        return $urls;
    }
}
