<?php
declare(strict_types=1);

namespace src\Controllers;

use PDO;
use src\Controllers\AppController;
use src\Core\SessionManager;
use src\Repositories\RoleRepository;

/**
 * Controller for managing user roles.
 * Requires admin role.
 */
class RoleController extends AppController
{
    private RoleRepository $roleRepo;

    /**
     * @param PDO $pdo 
     */
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->ensureLoggedIn();
        $this->ensureRole('admin');
        $this->roleRepo = new RoleRepository($this->db);
    }

    /**
     * List all roles.
     */
    public function list(): void
    {
        $roles = $this->roleRepo->findAll();
        $this->render('roles/list', ['roles' => $roles]);
    }

    /**
     * Create or update a role.
     */
    public function update(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/roles');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');

        if ($name !== '') {
            if ($id > 0) {
                $this->roleRepo->update($id, ['name' => $name]);
            } else {
                $this->roleRepo->create(['name' => $name]);
            }
        }

        $this->redirect('/roles');
    }

    /**
     * Delete a role.
     */
    public function delete(): void
    {
        if ($this->isPost()) {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $this->roleRepo->delete($id);
            }
        }
        $this->redirect('/roles');
    }
}
