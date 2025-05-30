<?php

declare(strict_types=1);

namespace src\Controllers;

require_once __DIR__ . '/AppController.php';
require_once __DIR__ . '/ProfileController.php';
require_once __DIR__ . '/../Core/SessionManager.php';
require_once __DIR__ . '/../Repositories/UserRepository.php';

use PDO;
use src\Controllers\AppController;
use src\Core\SessionManager;
use src\Repositories\UserRepository;

/**
 * Handles user authentication: login, registration, logout.
 */
class SecurityController extends AppController
{
    private UserRepository $userRepo;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->userRepo = new UserRepository($this->db);
    }

    public function showLoginForm(): void
    {
        $flashes = SessionManager::getAllFlashes();
        $this->render('login', ['flashes' => $flashes]);
    }

    public function login(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/login');
        }

        $email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            SessionManager::flash('error', 'Invalid email or password.');
            $this->redirect('/login');
        }

        $user = $this->userRepo->findByEmail($email);
        if (!$user || !password_verify($password, (string)$user['password_hash'])) {
            SessionManager::flash('error', 'Invalid email or password.');
            $this->redirect('/login');
        }

        SessionManager::login((int)$user['id'], (string)$user['role_name']);
        if (SessionManager::getUserRole() === 'admin') {
            $this->redirect('/admin/dashboard');
        } else {
            $this->redirect('/dashboard');
        }
    }

    public function showRegisterForm(): void
    {
        $flashes = SessionManager::getAllFlashes();
        $this->render('register', ['flashes' => $flashes]);
    }

    public function register(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/register');
        }

        $username        = trim($_POST['username']       ?? '');
        $firstName       = trim($_POST['first_name']     ?? '');
        $lastName        = trim($_POST['last_name']      ?? '');
        $email           = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password        = $_POST['password']           ?? '';
        $confirmPassword = $_POST['password_confirm']   ?? '';

        if (!$username || !$firstName || !$lastName || !$email) {
            SessionManager::flash('error', 'Wszystkie pola są wymagane.');
            $this->redirect('/register');
        }

        if ($password !== $confirmPassword) {
            SessionManager::flash('error', 'Hasła nie są takie same.');
            $this->redirect('/register');
        }

        if ($this->userRepo->findByEmail($email) || $this->userRepo->findByUsername($username)) {
            SessionManager::flash('error', 'Email lub nazwa użytkownika już istnieje.');
            $this->redirect('/register');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->userRepo->create([
            'email'         => $email,
            'username'      => $username,
            'password_hash' => $hash,
            'first_name'    => $firstName,
            'last_name'     => $lastName,
            'role'          => 'user',
        ]);

        SessionManager::flash('success', 'Registration successful. Please login.');
        $this->redirect('/login');
    }

    public function logout(): void
    {
        SessionManager::logout();
        $this->redirect('/login');
    }

    public function showPasswordResetForm(): void
    {
        include __DIR__ . '/../../public/views/password_reset.php';
    }
    public function inProgress(): void
    {
        include __DIR__ . '/../../public/views/inProgress.php';
    }
}
