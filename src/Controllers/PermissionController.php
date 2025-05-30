<?php
declare(strict_types=1);

namespace src\Controllers;

use PDO;
use src\Controllers\AppController;
use src\Core\SessionManager;
use src\Repositories\PermissionRepository;

/**
 * Controller for managing permissions.
 * Requires admin role.
 */
class PermissionController extends AppController
{
    private PermissionRepository $permRepo;

    /**
     * @param PDO $pdo 
     */
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->ensureLoggedIn();
        $this->ensureRole('admin');
        $this->permRepo = new PermissionRepository($this->db);
    }

    /**
     * List all permissions.
     */
    public function list(): void
    {
        $permissions = $this->permRepo->findAll();
        $this->render('permissions/list', ['permissions' => $permissions]);
    }

    /**
     * Create or update permissions.
     */
    public function update(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/permissions');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');

        if ($name !== '') {
            if ($id > 0) {
                $this->permRepo->update($id, ['name' => $name]);
            } else {
                $this->permRepo->create(['name' => $name]);
            }
        }

        $this->redirect('/permissions');
    }

    /**
     * Delete a permission.
     */
    public function delete(): void
    {
        if ($this->isPost()) {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $this->permRepo->delete($id);
            }
        }
        $this->redirect('/permissions');
    }
}
