<?php
declare(strict_types=1);

namespace src\Controllers;

use PDO;
use src\Core\SessionManager;

abstract class AppController
{
    protected PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
        SessionManager::start();
    }

    /**
     * Ensure the user is logged in; otherwise redirect to login.
     */
    protected function ensureLoggedIn(): void
    {
        if (!SessionManager::isLoggedIn()) {
            $this->redirect('/login');
        }
    }

    /**
     * Ensure the user has the required role; otherwise send 403.
     * @param string $requiredRole
     */
    protected function ensureRole(string $requiredRole): void
    {
        if (SessionManager::getUserRole() !== $requiredRole) {
            http_response_code(403);
            echo 'Forbidden';
            exit;
        }
    }

    /**

     * @param string $view 
     * @param array<string,mixed> $params Variables to extract into view
     */
    protected function render(string $view, array $params = []): void
    {
        extract($params, EXTR_SKIP);
        require __DIR__ . '/../../public/views/' . $view . '.php';
    }

    /**
     * Redirect to a URL and exit.
     * @param string $url
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Check if the current request method is POST.
     * @return bool
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
}
