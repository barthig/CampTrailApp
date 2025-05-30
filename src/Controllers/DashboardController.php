<?php
declare(strict_types=1);

namespace src\Controllers;

require_once __DIR__ . '/AppController.php';

use PDO;

/**
 * DashboardController
 * Renders the main dashboard for logged-in users.
 */
class DashboardController extends AppController
{
    /**
     * @param PDO $pdo 
     */
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->ensureLoggedIn();
    }

    /**
     * Display the dashboard view.
     */
    public function index(): void
    {
        $this->render('dashboard');
    }
}
