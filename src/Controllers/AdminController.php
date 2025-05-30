<?php

declare(strict_types=1);

namespace src\Controllers;

use PDO;
use src\Core\SessionManager;
use src\Repositories\PermissionRepository;
use src\Repositories\UserRepository;
use src\Repositories\CamperRepository;
use src\Repositories\DestinationRepository;
use src\Repositories\RouteRepository;
use src\Repositories\NotificationRepository;
use src\Repositories\RoleRepository;

require_once __DIR__ . '/AppController.php';
require_once __DIR__ . '/../Core/SessionManager.php';
require_once __DIR__ . '/../Repositories/PermissionRepository.php';
require_once __DIR__ . '/../Repositories/UserRepository.php';
require_once __DIR__ . '/../Repositories/CamperRepository.php';
require_once __DIR__ . '/../Repositories/DestinationRepository.php';
require_once __DIR__ . '/../Repositories/RouteRepository.php';
require_once __DIR__ . '/../Repositories/NotificationRepository.php';
require_once __DIR__ . '/../Repositories/RoleRepository.php';

class AdminController extends AppController
{
    private array $permissions = [];

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->ensureLoggedIn();


        $userId = SessionManager::getUserId();
        $permRepo = new PermissionRepository($this->db);
        $this->permissions = $permRepo->getUserPermissions((int)$userId);


        if (!in_array('manage_users', $this->permissions, true)) {
            header('HTTP/1.1 403 Forbidden');
            echo 'Brak dostępu. Wymagane uprawnienia administratora.';
            exit;
        }
    }

    public function dashboard(): void
    {
        $data = [
            'permissions' => $this->permissions,
        ];

        if (in_array('manage_users', $this->permissions, true)) {
            $data['users'] = (new UserRepository($this->db))->findAllWithRoles();
        }
        if (in_array('manage_campers', $this->permissions, true)) {
            $data['campers'] = (new CamperRepository($this->db))->findAll();
        }
        if (in_array('manage_destinations', $this->permissions, true)) {
            $data['destinations'] = (new DestinationRepository($this->db))->findAll();
        }
        if (in_array('manage_routes', $this->permissions, true)) {
            $data['routes'] = (new RouteRepository($this->db, new DestinationRepository($this->db)))->findAllAdmin();
        }
        if (in_array('manage_notifications', $this->permissions, true)) {
            $data['notifications'] = (new NotificationRepository($this->db))->findAllAdmin();
        }

        $this->render('admin/dashboard', $data);
    }
}
