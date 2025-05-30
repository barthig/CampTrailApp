<?php
declare(strict_types=1);

namespace src\Controllers;

require_once __DIR__ . '/AppController.php';
require_once __DIR__ . '/../Core/SessionManager.php';
require_once __DIR__ . '/../Repositories/RouteRepository.php';
require_once __DIR__ . '/../Repositories/DestinationRepository.php';
require_once __DIR__ . '/../Repositories/CamperRepository.php';

use PDO;
use src\Core\SessionManager;
use src\Controllers\AppController;
use src\Repositories\RouteRepository;
use src\Repositories\DestinationRepository;
use src\Repositories\CamperRepository;

/**
 * Controller for managing routes between destinations.
 */
class RouteController extends AppController
{
    private DestinationRepository $destRepo;
    private RouteRepository       $routeRepo;
    private CamperRepository      $camperRepo;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->ensureLoggedIn();
        $this->destRepo   = new DestinationRepository($this->db);
        $this->routeRepo  = new RouteRepository($this->db, $this->destRepo);
        $this->camperRepo = new CamperRepository($this->db);
    }

    /**
     * List all routes.
     */
    public function list(): void
    {
        $routes = $this->routeRepo->findAll();
        $this->render('routes/list', ['routes' => $routes]);
    }

    /**
     * Create a new route.
     */
    public function create(): void
    {
        $userId       = SessionManager::getUserId();
        $destinations = $this->destRepo->findAll();
        $campers      = $this->camperRepo->findByUser((int)$userId);

        if ($this->isPost()) {
            $originId      = (int) ($_POST['origin_id']      ?? 0);
            $destinationId = (int) ($_POST['destination_id'] ?? 0);
            $distance      = (float) ($_POST['distance']     ?? 0.0);
            $camperId      = (int) ($_POST['camper_id']     ?? 0);

            $errors = [];
            if ($originId <= 0 || $destinationId <= 0) {
                $errors[] = 'Musisz wybrać punkt startu i metę.';
            }
            if ($originId === $destinationId) {
                $errors[] = 'Start i meta nie mogą być takie same.';
            }
            if ($distance <= 0) {
                $errors[] = 'Dystans musi być większy niż zero.';
            }
            if ($camperId <= 0) {
                $errors[] = 'Musisz wybrać kampera.';
            }

            if (!empty($errors)) {
                $this->render('routes/form', [
                    'errors'       => $errors,
                    'route'        => [
                        'origin_id'      => $originId,
                        'destination_id' => $destinationId,
                        'distance'       => $distance,
                        'camper_id'      => $camperId,
                    ],
                    'destinations' => $destinations,
                    'campers'      => $campers,
                ]);
                return;
            }

            $this->routeRepo->create([
                'origin_id'      => $originId,
                'destination_id' => $destinationId,
                'distance'       => $distance,
                'user_id'        => (int)$userId,
                'camper_id'      => $camperId,
            ]);

            SessionManager::flash('success', 'Trasa została dodana.');
            $this->redirect('/routes');
        }

    
        $this->render('routes/form', [
            'destinations' => $destinations,
            'campers'      => $campers,
        ]);
    }

    /**
     * Edit an existing route.
     */
    public function edit(): void
    {
        $id           = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        $userId       = SessionManager::getUserId();
        $destinations = $this->destRepo->findAll();
        $campers      = $this->camperRepo->findByUser((int)$userId);

        if ($id <= 0) {
            $this->redirect('/routes');
            return;
        }

        $route = $this->routeRepo->findById($id);
        if (!$route) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        if ($this->isPost()) {
            $originId      = (int) ($_POST['origin_id']      ?? 0);
            $destinationId = (int) ($_POST['destination_id'] ?? 0);
            $distance      = (float) ($_POST['distance']     ?? 0.0);
            $camperId      = (int) ($_POST['camper_id']     ?? 0);

            $errors = [];
            if ($originId <= 0 || $destinationId <= 0) {
                $errors[] = 'Musisz wybrać punkt startu i metę.';
            }
            if ($originId === $destinationId) {
                $errors[] = 'Start i meta nie mogą być takie same.';
            }
            if ($distance <= 0) {
                $errors[] = 'Dystans musi być większy niż zero.';
            }
            if ($camperId <= 0) {
                $errors[] = 'Musisz wybrać kampera.';
            }

            if (!empty($errors)) {
                $this->render('routes/form', [
                    'errors'       => $errors,
                    'route'        => [
                        'id'              => $id,
                        'origin_id'       => $originId,
                        'destination_id'  => $destinationId,
                        'distance'        => $distance,
                        'camper_id'       => $camperId,
                    ],
                    'destinations' => $destinations,
                    'campers'      => $campers,
                ]);
                return;
            }

            $this->routeRepo->update($id, [
                'origin_id'      => $originId,
                'destination_id' => $destinationId,
                'distance'       => $distance,
                'camper_id'      => $camperId,
            ]);

            SessionManager::flash('success', 'Trasa została zaktualizowana.');
            $this->redirect('/routes');
        }

        $this->render('routes/form', [
            'route'        => $route,
            'destinations' => $destinations,
            'campers'      => $campers,
        ]);
    }

    /**
     * Delete a route.
     */
    public function delete(): void
    {
        if ($this->isPost()) {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $this->routeRepo->delete($id);
                SessionManager::flash('success', 'Trasa została usunięta.');
            } else {
                SessionManager::flash('error', 'Nieprawidłowe ID trasy.');
            }
        }
        $this->redirect('/routes');
    }
}
