<?php
/** 
 * @var array<int, array{
 *     id: int,
 *     origin: string,
 *     destination: string,
 *     distance: string,
 *     created_at: string,
 *     camper_name: string
 * }> $routes
 */
use src\Core\SessionManager;
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Twoje Trasy – CampTrail</title>
  <link rel="stylesheet" href="/public/css/sidebar.css">
  <link rel="stylesheet" href="/public/css/routes_list.css">
  <link rel="stylesheet" href="/public/css/notifications_bell.css">
  <script defer src="/public/js/notifications.js"></script>
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
  <?php include __DIR__ . '/../../components/sidebar.php'; ?>

  <main class="content-container">
    <div class="routes-header">
      <h1>Twoje trasy</h1>
      <?php foreach(SessionManager::getAllFlashes() as $type => $msg): ?>
          <div class="flash <?= $type ?>"><?= htmlspecialchars($msg, ENT_QUOTES) ?></div>
        <?php endforeach; ?>
      <a href="/routes/create" class="button new-route">
        <i class="fas fa-plus"></i> Dodaj trasę
      </a>
    </div>

    <?php if (empty($routes)): ?>
      <div class="empty-state">
        <p>Nie masz jeszcze żadnych tras.</p>
        <a href="/routes/create" class="button new-route">
          <i class="fas fa-plus"></i> Dodaj trasę
        </a>
      </div>
    <?php else: ?>
      <table class="routes-table">
        <thead>
          <tr>
            <th>Data utworzenia</th>
            <th>Start</th>
            <th>Meta</th>
            <th>Kamper</th>
            <th>Dystans</th>
            <th>Akcje</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($routes as $r): ?>
            <tr>
              <td data-label="Data utworzenia">
                <?= htmlspecialchars(date('Y-m-d', strtotime($r['created_at'])), ENT_QUOTES) ?>
              </td>
              <td data-label="Start">
                <?= htmlspecialchars($r['origin'], ENT_QUOTES) ?>
              </td>
              <td data-label="Meta">
                <?= htmlspecialchars($r['destination'], ENT_QUOTES) ?>
              </td>
              <td data-label="Kamper">
                <?= htmlspecialchars($r['camper_name'], ENT_QUOTES) ?>
              </td>
              <td data-label="Dystans (km)">
                <?= number_format((float)$r['distance'], 1, ',', ' ') ?>km
              </td>
              <td data-label="Akcje" class="actions-cell">
                <a href="/routes/edit?id=<?= (int)$r['route_id'] ?>" class="action-btn edit">
                  <i class="fas fa-edit"></i> Edytuj
                </a>
                <form action="/routes/delete" method="POST" class="delete-form"
                      onsubmit="return confirm('Na pewno usunąć tę trasę?')">
                  <input type="hidden" name="id" value="<?= (int)$r['route_id'] ?>">
                  <button type="submit" class="action-btn delete">
                    <i class="fas fa-trash"></i> Usuń
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </main>
</body>
</html>
