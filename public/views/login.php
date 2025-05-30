<?php
require __DIR__ . '/../../src/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard');
    exit;
}
$error   = '';
$success = '';
$inputEmail = '';
if (!empty($_GET['registered'])) {
    $success = 'Rejestracja przebiegła pomyślnie! Możesz się teraz zalogować.';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputEmail = trim($_POST['email'] ?? '');
    try {
        $stmt = $pdo->prepare("SELECT id, password_hash, role, first_name FROM users WHERE email = :email");
        $stmt->execute(['email' => $inputEmail]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            $error = 'Użytkownik o podanym adresie email nie istnieje.';
        } elseif (!password_verify($_POST['password'] ?? '', $user['password_hash'])) {
            $error = 'Nieprawidłowe hasło.';
        } else {
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['role']       = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            if (!empty($_POST['remember_me'])) {
                setcookie('remember_me', $inputEmail, [
                    'expires'  => time() + 60*60*24*30,
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }
            header('Location: /dashboard');
            exit;
        }
    } catch (PDOException $e) {
        error_log('PDO error on login: ' . $e->getMessage());
        $error = 'Wystąpił problem z połączeniem. Spróbuj ponownie później.';
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Logowanie – CampTrail</title>
  <link rel="stylesheet" href="/public/css/login.css">
  <link rel="stylesheet" href="/public/css/sidebar.css">
  <link rel="stylesheet" href="/public/css/footer.css">
  <link rel="stylesheet" href="/public/css/notifications_bell.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script defer src="/public/js/notifications.js"></script>
</head>
<body>
  <?php include __DIR__ . '/../components/sidebar.php'; ?>
  <main class="login-page container">
    <div class="login-container">
      <div class="login-box">
        <div class="logo-container">
          <img src="/public/img/logo_2.svg" alt="CampTrail Logo">
        </div>
        <h1>🌎 Welcome back, traveler!</h1>
        <p>Log in to continue your adventure.</p>
        <?php if ($success): ?>
          <p class="success" role="alert"><?= htmlspecialchars($success, ENT_QUOTES) ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
          <p class="error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES) ?></p>
        <?php endif; ?>
        <form method="POST" class="login-form" novalidate>
          <div class="input-group">
            <input type="email" name="email" placeholder="Email Address" required value="<?= htmlspecialchars($inputEmail, ENT_QUOTES) ?>">
          </div>
          <div class="input-group">
            <input type="password" name="password" placeholder="Password" required>
          </div>
          <div class="checkbox-group">
            <input type="checkbox" id="remember-me" name="remember_me" <?= !empty($_POST['remember_me']) ? 'checked' : '' ?>>
            <label for="remember-me">Remember me</label>
          </div>
          <button type="submit">Login</button>
        </form>
        <div class="links">
          <a href="/password_reset">Forgot password?</a> |
          <a href="/register">Create an account</a>
        </div>
      </div>
    </div>
  </main>
  <?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
