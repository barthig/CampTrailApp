<?php
require __DIR__ . '/../../src/config.php';
$is_logged_in = !empty($_SESSION['user_id']);
if ($is_logged_in) {
    header('Location: /dashboard');
    exit;
}

$error       = '';
$username    = '';
$first_name  = '';
$last_name   = '';
$email       = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username      = trim($_POST['username']      ?? '');
    $first_name    = trim($_POST['first_name']    ?? '');
    $last_name     = trim($_POST['last_name']     ?? '');
    $email         = trim($_POST['email']         ?? '');
    $password      = $_POST['password']           ?? '';
    $password_conf = $_POST['password_confirm']   ?? ''; 
    if (strlen($password) < 8) {
        $error = 'Hasło musi mieć co najmniej 8 znaków.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = 'Hasło musi zawierać co najmniej jedną wielką literę.';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $error = 'Hasło musi zawierać co najmniej jedną małą literę.';
    } elseif (!preg_match('/\d/', $password)) {
        $error = 'Hasło musi zawierać co najmniej jedną cyfrę.';
    } elseif ($password !== $password_conf) {
        $error = 'Hasła nie są takie same.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $error = 'Nazwa użytkownika musi mieć 3–20 znaków: litery, cyfry lub podkreślenia.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Podaj poprawny adres email.';
    } else {
        try {
            $stmt_check = $pdo->prepare(
                'SELECT COUNT(*) FROM users WHERE email = :email OR username = :username'
            );
            $stmt_check->execute([':email' => $email, ':username' => $username]);
            if ((int)$stmt_check->fetchColumn() > 0) {
                $error = 'Email lub nazwa użytkownika już istnieje.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare(
                    'INSERT INTO users
                       (email, username, password_hash, first_name, last_name, role_id)
                     VALUES
                       (:email, :username, :hash, :first, :last,
                        (SELECT id FROM roles WHERE name = \'user\'))'
                );
                $stmt->execute([
                    ':email'    => $email,
                    ':username' => $username,
                    ':hash'     => $hash,
                    ':first'    => $first_name,
                    ':last'     => $last_name,
                ]);
                header('Location: /login');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Błąd rejestracji: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Rejestracja – CampTrail</title>
  <link rel="stylesheet" href="/public/css/sidebar.css">
  <link rel="stylesheet" href="/public/css/footer.css">
  <link rel="stylesheet" href="/public/css/register.css">
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="page-register">
  <?php include __DIR__ . '/../components/sidebar.php'; ?>
  <main class="register-main">
    <div class="login-container">
      <div class="login-box">
        <h1>Rejestracja</h1>
        <?php if ($error): ?>
          <p class="error"><?= htmlspecialchars($error, ENT_QUOTES) ?></p>
        <?php endif; ?>
        <form method="POST" action="/register" class="login-form" novalidate>
          <div class="input-group">
            <input type="text" name="username" placeholder="Nazwa użytkownika" required
                   value="<?= htmlspecialchars($username, ENT_QUOTES) ?>">
          </div>
          <div class="input-group">
            <input type="text" name="first_name" placeholder="Imię" required
                   value="<?= htmlspecialchars($first_name, ENT_QUOTES) ?>">
          </div>
          <div class="input-group">
            <input type="text" name="last_name" placeholder="Nazwisko" required
                   value="<?= htmlspecialchars($last_name, ENT_QUOTES) ?>">
          </div>
          <div class="input-group">
            <input type="email" name="email" placeholder="Email" required
                   value="<?= htmlspecialchars($email, ENT_QUOTES) ?>">
          </div>
          <div class="input-group">
            <input type="password" name="password" placeholder="Hasło" required
                   minlength="8" pattern="(?=.*[A-Z])(?=.*[a-z])(?=.*\d).+"
                   title="Hasło musi mieć min. 8 znaków, zawierać wielką i małą literę oraz cyfrę.">
          </div>
          <div class="input-group">
            <input type="password" name="password_confirm" placeholder="Powtórz hasło" required>
          </div>
          <div id="js-errors" class="error-messages"></div>
          <button type="submit">Zarejestruj się</button>
        </form>
        <div class="links">
          Masz już konto? <a href="/login">Zaloguj się</a>
        </div>
      </div>
    </div>
  </main>
  <?php include __DIR__ . '/../components/footer.php'; ?>
  <script src="/public/js/register.js"></script>
</body>
</html>
