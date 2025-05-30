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

class StatsController extends AppController
{
    private RouteRepository  $routeRepo;
    private CamperRepository $camperRepo;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->ensureLoggedIn();
        $destRepo         = new DestinationRepository($this->db);
        $this->routeRepo  = new RouteRepository($this->db, $destRepo);
        $this->camperRepo = new CamperRepository($this->db);
    }

    /**
     * Wyświetla miesięczne statystyki
     */
    public function monthly(): void
    {
        $this->dashboard('monthly');
    }

    /**
     * Wyświetla roczne statystyki
     */
    public function yearly(): void
    {
        $this->dashboard('yearly');
    }

    /**
     * Dashboard statystyk – obsługuje okres i filtr kampera
     *
     * @param string|null $periodOverride 'monthly' albo 'yearly'
     */
    public function dashboard(?string $periodOverride = null): void
    {
        $userId = SessionManager::getUserId();

        $allowed = ['monthly', 'yearly'];
        $period = $periodOverride ?? (string)($_GET['period'] ?? 'monthly');
        if (!in_array($period, $allowed, true)) {
            $period = 'monthly';
        }

        $selected = null;
        if (isset($_GET['camper_id']) && ctype_digit((string)$_GET['camper_id'])) {
            $selected = (int)$_GET['camper_id'];
        }

        $campers = $this->camperRepo->findByUser((int)$userId);

        if ($period === 'yearly') {
            $data = $this->routeRepo->statsByYear((int)$userId, $selected);
        } else {
            $data = $this->routeRepo->statsByMonth((int)$userId, $selected);
        }

        $this->render('stats/dashboard', [
            'campers'  => $campers,
            'period'   => $period,
            'selected' => $selected,
            'data'     => $data,
        ]);
    }
}
