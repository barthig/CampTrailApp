<?php
declare(strict_types=1);

namespace src\Controllers;

require_once __DIR__ . '/AppController.php';

use PDO;
use src\Core\SessionManager;
use src\Controllers\AppController;

/**
 * DefaultController handles the home page, help page, and error pages.
 */
class DefaultController extends AppController
{
    /**
     * @param PDO $pdo 
     */
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    /**
     * Home page: redirects logged-in users to campers list or renders public home.
     */
    public function index(): void
    {
        if (SessionManager::isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        $this->render('home');
    }

    /**
     * Help page: displays application help and documentation.
     */
    public function help(): void
    {
        if (!SessionManager::isLoggedIn()) {
            $this->redirect('/login');
        }

        $this->render('help', [
            'appName' => $this->config['appName'] ?? 'Kamper',
            'version' => $this->config['version'] ?? '1.0.0',
        ]);
    }

    /**
     * 404 Not Found page.
     */
    public function error404(): void
    {
        http_response_code(404);
        $this->render('errors/404');
    }

    /**
     * 500 Internal Server Error page.
     */
    public function error500(): void
    {
        http_response_code(500);
        $this->render('errors/500');
    }
}