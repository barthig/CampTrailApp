<?php

declare(strict_types=1);

namespace src\Controllers;

require_once __DIR__ . '/AppController.php';
require_once __DIR__ . '/../Core/SessionManager.php';
require_once __DIR__ . '/../Repositories/UserRepository.php';
require_once __DIR__ . '/../Repositories/CamperRepository.php';
require_once __DIR__ . '/../Repositories/EmergencyContactRepository.php';

use PDO;
use src\Core\SessionManager;
use src\Controllers\AppController;
use src\Repositories\UserRepository;
use src\Repositories\CamperRepository;
use src\Repositories\EmergencyContactRepository;

class ProfileController extends AppController
{
    private UserRepository $userRepo;
    private CamperRepository $camperRepo;
    private EmergencyContactRepository $emergencyRepo;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->ensureLoggedIn();
        $this->userRepo = new UserRepository($this->db);
        $this->camperRepo = new CamperRepository($this->db);
        $this->emergencyRepo = new EmergencyContactRepository($this->db);
    }


    public function view(): void
    {
        $this->renderProfile('profile/view');
    }


    public function edit(): void
    {
        $this->renderProfile('profile/edit');
    }

    public function update(): void
    {
        if (! $this->isPost()) {
            $this->redirect('/profile');
            return;
        }

        $userId = SessionManager::getUserId();
        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name'  => trim($_POST['last_name']  ?? ''),
            'email'      => trim($_POST['email']      ?? ''),
            'bio'        => trim($_POST['bio']        ?? ''),
        ];

        try {
            $this->userRepo->update($userId, $data);
            SessionManager::flash('success', 'Profil został zaktualizowany.');
            $this->redirect('/profile');
        } catch (\Exception $e) {
            $this->renderProfile('profile/edit', ['error' => $e->getMessage()]);
        }
    }
    public function updateAvatar(): void
    {
        if (! $this->isPost()) {
            $this->redirect('/profile/edit');
            return;
        }

        $userId = SessionManager::getUserId();

        if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            SessionManager::flash('error', 'Błąd podczas przesyłania pliku.');
            $this->redirect('/profile/edit');
            return;
        }

        $file     = $_FILES['avatar'];
        $maxSize  = 2 * 1024 * 1024;
        $allowed  = ['image/jpeg' => 'jpg', 'image/png' => 'png'];

        if ($file['size'] > $maxSize) {
            SessionManager::flash('error', 'Plik jest za duży (maks. 2 MB).');
            $this->redirect('/profile/edit');
            return;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (! isset($allowed[$mime])) {
            SessionManager::flash('error', 'Nieobsługiwany format. Wybierz JPEG lub PNG.');
            $this->redirect('/profile/edit');
            return;
        }
        $ext      = $allowed[$mime];
        $filename = sprintf('%s_%s.%s', $userId, bin2hex(random_bytes(8)), $ext);
        $destDir  = __DIR__ . '/../../public/uploads/avatars/';
        $destPath = $destDir . $filename;

        if (! is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        if (! move_uploaded_file($file['tmp_name'], $destPath)) {
            SessionManager::flash('error', 'Nie udało się zapisać pliku na serwerze.');
            $this->redirect('/profile/edit');
            return;
        }

        $user = $this->userRepo->findById($userId);
        if (! empty($user['avatar']) && basename($user['avatar']) !== 'default-avatar.png') {
            @unlink(__DIR__ . '/../../public' . $user['avatar']);
        }

        $avatarUrl = '/public/uploads/avatars/' . $filename;
        try {
            $this->userRepo->updateAvatar($userId, $avatarUrl);
            SessionManager::flash('success', 'Avatar został zaktualizowany.');
        } catch (\Exception $e) {
            SessionManager::flash('error', 'Błąd zapisu w bazie: ' . $e->getMessage());
        }

        $this->redirect('/profile/edit');
    }

    public function changePassword(): void
    {
        if (! $this->isPost()) {
            $this->redirect('/profile/edit');
            return;
        }

        $userId      = SessionManager::getUserId();
        $oldPassword = $_POST['old_password']     ?? '';
        $newPassword = $_POST['password']         ?? '';
        $confirmPass = $_POST['password_confirm'] ?? '';

        try {
            $currentHash = $this->userRepo->getPasswordHashById($userId)
                ?? throw new \Exception('Brak użytkownika.');

            if (! password_verify($oldPassword, $currentHash)) {
                throw new \Exception('Nieprawidłowe stare hasło.');
            }
            if ($newPassword === '' || $newPassword !== $confirmPass) {
                throw new \Exception('Nowe hasła nie są zgodne lub są puste.');
            }
            if (
                strlen($newPassword) < 8
                || ! preg_match('/[A-Z]/', $newPassword)
                || ! preg_match('/[a-z]/', $newPassword)
                || ! preg_match('/\d/', $newPassword)
            ) {
                throw new \Exception('Hasło musi mieć min. 8 znaków, wielką i małą literę oraz cyfrę.');
            }

            $this->userRepo->update($userId, [
                'password_hash' => password_hash($newPassword, PASSWORD_BCRYPT),
            ]);
            SessionManager::flash('success', 'Hasło zostało zmienione.');
            $this->redirect('/profile');
        } catch (\Exception $e) {
            $this->renderProfile('profile/edit', ['error' => $e->getMessage()]);
        }
    }
    public function updateEmergencyContact(): void
    {
        if (! $this->isPost()) {
            $this->redirect('/profile');
            return;
        }

        $userId = SessionManager::getUserId();

        $contactName     = trim($_POST['contact_name'] ?? '');
        $contactPhone    = trim($_POST['contact_phone'] ?? '');
        $contactRelation = trim($_POST['contact_relation'] ?? '');

        if ($contactName === '' || $contactPhone === '' || $contactRelation === '') {
            SessionManager::flash('error', 'Wszystkie pola kontaktu alarmowego są wymagane.');
            $this->redirect('/profile/edit');
            return;
        }

        try {
            $this->emergencyRepo->upsert($userId, $contactName, $contactPhone, $contactRelation);
            SessionManager::flash('success', 'Kontakt alarmowy został zaktualizowany.');
            $this->redirect('/profile');
        } catch (\Exception $e) {
            SessionManager::flash('error', 'Błąd zapisu kontaktu alarmowego: ' . $e->getMessage());
            $this->redirect('/profile/edit');
        }
    }

    public function saveNotifications(): void
    {
        if (! $this->isPost()) {
            $this->redirect('/profile');
            return;
        }

        $userId = SessionManager::getUserId();
        $prefs = [
            'notify_services'     => ! empty($_POST['notify_services']),
            'notify_routes'       => ! empty($_POST['notify_routes']),
            'notify_destinations' => ! empty($_POST['notify_destinations']),
        ];

        try {
            $this->userRepo->update($userId, $prefs);
            SessionManager::flash('success', 'Ustawienia powiadomień zapisane.');
            $this->redirect('/profile');
        } catch (\Exception $e) {
            $this->renderProfile('profile/edit', ['error' => $e->getMessage()]);
        }
    }

    private function renderProfile(string $template, array $extra = []): void
    {
        $userId = SessionManager::getUserId();
        $user   = $this->userRepo->findById($userId) ?: [];

        if (!empty($user['avatar'])) {
            $_SESSION['avatar'] = $user['avatar'];
        }

        $stmt = $this->db->prepare(
            'SELECT COUNT(*) AS total_routes, COALESCE(SUM(distance),0) AS total_km
             FROM routes WHERE user_id = :uid'
        );
        $stmt->execute(['uid' => $userId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC) + ['total_routes' => 0, 'total_km' => 0];

        $campersCount = count($this->camperRepo->findByUser($userId));

        $stats = [
            'total_routes'   => (int)$r['total_routes'],
            'total_km'       => (float)$r['total_km'],
            'total_vehicles' => $campersCount,
        ];
        $contact = $this->emergencyRepo->getByUserId($userId);

        $this->render($template, array_merge([
            'user'  => $user,
            'contact' => $contact,
            'stats' => $stats,
        ], $extra));
    }

    public function exportDb(): void
    {

        $role = SessionManager::getUserRole();
        if ($role !== 'admin') {
            http_response_code(403);
            exit('Brak dostępu.');
        }

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="db_export_' . date('Y-m-d_H-i-s') . '.sql"');

        $dbHost     = 'db';
        $dbPort     = '5432';
        $dbName     = 'kamper_app';
        $dbUser     = 'postgres';
        $dbPassword = 'password';

        $cmd = sprintf(
            'PGPASSWORD=%s pg_dump --host=%s --port=%s --username=%s --no-owner --no-privileges %s',
            escapeshellarg($dbPassword),
            escapeshellarg($dbHost),
            escapeshellarg($dbPort),
            escapeshellarg($dbUser),
            escapeshellarg($dbName)
        );

        flush();
        passthru($cmd);
        exit;
    }
    public function deleteAccount(): void
    {
        include __DIR__ . '/../../public/views/inProgress.php';
    }
}
