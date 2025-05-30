<?php
require __DIR__ . '/../../src/config.php';
$message = $message ?? '';
$error   = $error   ?? '';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reset Hasła – CampTrail</title>
  <link rel="stylesheet" href="/public/css/sidebar.css">
  <link rel="stylesheet" href="/public/css/footer.css">
  <link rel="stylesheet" href="/public/css/password_reset.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
  <?php include __DIR__ . '/../components/sidebar.php'; ?>
  <div class="login-page container">
    <div class="login-container">
      <div class="login-box">
        <div class="logo-container">
          <img src="/public/img/logo_small.svg" alt="CampTrail Logo">
        </div>
        <h1>Reset Hasła</h1>
        <?php if ($message): ?>
          <div class="alert success"><?= htmlspecialchars($message) ?></div>
        <?php elseif ($error): ?>
          <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="/password_reset" class="login-form">
          <div class="input-group">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" placeholder="Twój adres email" required>
          </div>
          <button type="submit" class="btn">Wyślij link resetujący</button>
        </form>

        <div class="links">
          <a href="/login"><i class="fa fa-arrow-left"></i> Powrót do logowania</a>
        </div>
      </div>
    </div>
  </div>
  <?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
