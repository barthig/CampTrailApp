<?php
/**
 * @var array<int,array{id:int,message:string,is_read:bool,created_at:string}> $notifications
 * @var string $filter  
 */
use src\Core\SessionManager;

$filter = $_GET['filter'] ?? 'all';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Powiadomienia – CampTrail</title>
  <link rel="stylesheet" href="/public/css/sidebar.css">
  <link rel="stylesheet" href="/public/css/notifications.css">
  <link rel="stylesheet" href="/public/css/notifications_bell.css">
  <script defer src="/public/js/notifications.js"></script>
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
  <div class="page-wrapper">
    <?php include __DIR__ . '/../../components/sidebar.php'; ?>

    <div class="content-container">

      <div class="notifications-header">
        <h1>Powiadomienia</h1>
        <form method="POST" action="/notifications/mark-all-read" style="display:inline">
          <button type="submit" class="button mark-all">
            <i class="fas fa-check-double"></i> Oznacz wszystkie jako przeczytane
          </button>
        </form>
      </div>

      <div class="notifications-filters">
        <a href="?filter=all"    class="<?= $filter==='all'    ? 'active' : '' ?>">Wszystkie</a>
        <a href="?filter=unread" class="<?= $filter==='unread' ? 'active' : '' ?>">Nowe</a>
        <a href="?filter=read"   class="<?= $filter==='read'   ? 'active' : '' ?>">Przeczytane</a>
      </div>

      <?php foreach (SessionManager::getAllFlashes() as $type => $msg): ?>
        <div class="flash <?= $type ?>">
          <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endforeach; ?>

      <?php if (empty($notifications)): ?>
        <div class="empty-state">
          <p>Brak powiadomień w tej kategorii.</p>
        </div>
      <?php else: ?>
        <div class="notifications-box">
          <table class="notifications-table">
            <thead>
              <tr>
                <th>Data utworzenia</th>
                <th>Treść</th>
                <th>Status</th>
                <th>Akcja</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($notifications as $n): ?>
                <tr class="<?= $n['is_read'] ? 'read' : 'unread' ?>">
                  <td data-label="Data utworzenia">
                    <?= date('Y-m-d H:i', strtotime($n['created_at'])) ?>
                  </td>
                  <td data-label="Treść">
                    <?= htmlspecialchars($n['message'], ENT_QUOTES, 'UTF-8') ?>
                  </td>
                  <td data-label="Status">
                    <?= $n['is_read'] ? 'Przeczytane' : 'Nowe' ?>
                  </td>
                  <td data-label="Akcja">
                    <div class="actions-cell">
                      <?php if (! $n['is_read']): ?>
                        <form method="POST" action="/notifications/mark-read" class="mark-form">
                          <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
                          <button type="submit" title="Oznacz jako przeczytane">
                            <i class="fas fa-check"></i>
                          </button>
                        </form>
                      <?php else: ?>
                        <span class="dash">—</span>
                      <?php endif; ?>

                      <form method="POST" action="/notifications/delete" class="delete-form">
                        <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
                        <button type="submit" title="Usuń powiadomienie">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
