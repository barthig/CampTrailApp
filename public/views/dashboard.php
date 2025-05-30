<?php

require __DIR__ . '/../../src/config.php';   
require __DIR__ . '/../../src/auth.php';     

$userId = (int) ($_SESSION['user_id'] ?? 0);

$stmtUser = $pdo->prepare('SELECT username, first_name FROM users WHERE id = :uid');
$stmtUser->execute([':uid' => $userId]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC) ?: [];
$username = isset($user['username']) ? $user['username'] : ''; 
$firstName = isset($user['first_name']) ? $user['first_name'] : ''; 
$displayName = $firstName !== '' ? $firstName : $username;

$statsStmt = $pdo->prepare(
  <<<SQL
  SELECT
    COUNT(*)            AS total_routes,
    COALESCE(SUM(distance),0) AS total_km
  FROM route_overview
  WHERE user_id = :uid
SQL
);
$statsStmt->execute([':uid' => $userId]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC) ?: ['total_routes' => 0, 'total_km' => 0.0];

$routesStmt = $pdo->prepare(
  <<<SQL
  SELECT
    route_id,
    origin,
    destination,
    distance,
    created_at
  FROM route_overview
  WHERE user_id = :uid
  ORDER BY created_at DESC
  LIMIT 5
SQL
);
$routesStmt->execute(['uid' => $userId]);
$routes = $routesStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>CampTrail Dashboard</title>
  <link rel="stylesheet" href="/public/css/sidebar.css">
  <link rel="stylesheet" href="/public/css/dashboard.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="/public/css/notifications_bell.css">
  <script defer src="/public/js/notifications.js"></script>
</head>

<body>
  <?php include __DIR__ . '/../components/sidebar.php'; ?>

  <main class="main">
    <header class="dashboard-header">
      <h1>Witaj, <?= htmlspecialchars($displayName, ENT_QUOTES) ?>!</h1>
    </header>

    <section class="quick-actions">
      <a href="/routes/create" class="action-card"><i class="fas fa-route"></i><span>Dodaj trasę</span></a>
      <a href="/campers/create" class="action-card"><i class="fas fa-caravan"></i><span>Dodaj pojazd</span></a>
      <a href="/destinations" class="action-card"><i class="fas fa-map-marker-alt"></i><span>Destynacje</span></a>
      <a href="/notifications" class="action-card"><i class="fas fa-bell"></i><span>Powiadomienia</span></a>
      <a href="/stats" class="action-card"><i class="fas fa-chart-line"></i><span>Statystyki miesięczne</span></a>
      <a href="/profile" class="action-card"><i class="fas fa-user-cog"></i><span>Profil</span></a>
      <a href="/help" class="action-card"><i class="fas fa-question-circle"></i><span>Pomoc / FAQ</span></a>
      <a href="/logout" class="action-card"><i class="fas fa-sign-out-alt"></i><span>Wyloguj się</span></a>
    </section>

    <section class="stats-cards">
      <div class="stat-card">
        <div class="stat-value"><?= (int)$stats['total_routes'] ?></div>
        <div class="stat-label">Trasy</div>
      </div>
      <div class="stat-card">
        <div class="stat-value"><?= number_format((float)$stats['total_km'], 1, ',', ' ') ?> km</div>
        <div class="stat-label">Łączny dystans</div>
      </div>
      <div class="stat-card">
        <section class="recent-routes">
          <h2>Ostatnie trasy</h2>
          <?php if (empty($routes)): ?>
            <p>Nie masz jeszcze zapisanych tras.</p>
          <?php else: ?>
            <ul class="route-list">
              <?php foreach ($routes as $r): ?>
                <li class="route-item">
                  <div class="route-info">
                    <strong><?= htmlspecialchars($r['origin'], ENT_QUOTES) ?></strong> &rarr;
                    <strong><?= htmlspecialchars($r['destination'], ENT_QUOTES) ?></strong>
                    <span class="distance"><?= number_format((float)$r['distance'], 1, ',', ' ') ?> km</span>
                    <span class="date"><?= htmlspecialchars(date('Y-m-d', strtotime($r['created_at'])), ENT_QUOTES) ?></span>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </section>
      </div>
    </section>
  </main>
</body>

</html>