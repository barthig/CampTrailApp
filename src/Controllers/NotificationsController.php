<?php

declare(strict_types=1);

namespace src\Controllers;

require_once __DIR__ . '/AppController.php';
require_once __DIR__ . '/../Repositories/NotificationRepository.php';

use PDO;
use src\Core\SessionManager;
use src\Repositories\NotificationRepository;

/**
 * Controller for user notifications.
 */
class NotificationsController extends AppController
{
    private NotificationRepository $notifRepo;

    /**
     * @param PDO $pdo Dependency-injected PDO instance
     */
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->ensureLoggedIn();
        $this->notifRepo = new NotificationRepository($this->db);
    }

    /**
     * List notifications, optionally filtered by read/unread.
     */
    public function list(): void
    {
        $userId = SessionManager::getUserId();
        $filter = $_GET['filter'] ?? 'all';
        if (!in_array($filter, ['all', 'unread', 'read'], true)) {
            $filter = 'all';
        }

        $notifications = match ($filter) {
            'unread' => $this->notifRepo->findUnreadForUser((int)$userId),
            'read'   => $this->notifRepo->findReadForUser((int)$userId),
            default  => $this->notifRepo->findAllForUser((int)$userId),
        };

        $this->render('notifications/list', [
            'notifications' => $notifications,
            'filter'        => $filter,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(): void
    {
        if ($this->isPost()) {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $this->notifRepo->markAsRead($id);
                SessionManager::addFlash('success', 'Powiadomienie oznaczone jako przeczytane.');
            }
        }
        $this->redirect('/notifications');
    }

    /**
     * Mark all notifications for current user as read.
     */
    public function markAllRead(): void
    {
        if ($this->isPost()) {
            $userId = SessionManager::getUserId();
            $this->notifRepo->markAllReadForUser((int)$userId);
            SessionManager::addFlash('success', 'Wszystkie powiadomienia oznaczone jako przeczytane.');
        }
        $this->redirect('/notifications');
    }

    /**
     * Delete a single notification.
     */
    public function delete(): void
    {
        if ($this->isPost()) {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $this->notifRepo->delete($id);
                SessionManager::addFlash('success', 'Powiadomienie usunięte.');
            }
        }
        $this->redirect('/notifications');
    }

    public function unreadJson(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $notifications = $this->notifRepo->findUnreadForUser($userId);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($notifications);
    }
}
