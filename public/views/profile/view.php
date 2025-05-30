<?php

/** 
 * @var array<string,mixed> $user 
 * @var array<string,mixed>|null $contact
 * @var array<string,mixed> $stats  
 * @var string|null        $error
 */

use src\Core\SessionManager;
?>
<!DOCTYPE html>
<html lang="pl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mój Profil – CampTrail</title>
  <link rel="stylesheet" href="/public/css/sidebar.css">
  <link rel="stylesheet" href="/public/css/profile_view.css">
  <link rel="stylesheet" href="/public/css/notifications_bell.css">
  <script defer src="/public/js/notifications.js"></script>
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
  <div class="page-wrapper">
    <?php include __DIR__ . '/../../components/sidebar.php'; ?>

    <main class="profile-main">
      <div class="profile-box">
        <h1>Mój Profil</h1>

        <?php foreach (SessionManager::getAllFlashes() as $type => $msg): ?>
          <div class="flash <?= htmlspecialchars($type) ?>">
            <?= htmlspecialchars($msg) ?>
          </div>
        <?php endforeach; ?>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <div class="avatar-wrapper">
          <img src="<?= htmlspecialchars($user['avatar'] ?? '/public/img/default-avatar.png') ?>"
            alt="Twój avatar" class="avatar-img">
          <p><a href="/profile/edit" class="btn-link"><i class="fas fa-camera"></i> Zmień avatar</a></p>
        </div>

        <ul class="profile-details">
          <li><strong>Username:</strong> <span><?= htmlspecialchars($user['username'] ?? '-') ?></span></li>
          <li><strong>Imię i nazwisko:</strong> <span><?= htmlspecialchars(($user['first_name'] ?? '-') . ' ' . ($user['last_name'] ?? '-')) ?></span></li>
          <li><strong>Email:</strong>
            <span>
              <?php if (!empty($user['email'])):
                $parts = explode('@', $user['email']);
                $mask = substr($parts[0], 0, 2) . str_repeat('*', max(0, strlen($parts[0]) - 2));
                echo htmlspecialchars($mask . '@' . $parts[1]);
              else:
                echo '-';
              endif;
              ?>
            </span>
          </li>
          <li><strong>Bio:</strong>
            <span><?= nl2br(htmlspecialchars($user['bio'] ?? '-')) ?></span>
          </li>
          <li><strong>Rola:</strong> <span><?= htmlspecialchars($user['role_name'] ?? '-') ?></span></li>
          <?php if (!empty($user['created_at'])): ?>
            <li><strong>Data utworzenia konta:</strong> <span><?= date('d.m.Y', strtotime($user['created_at'])) ?></span></li>
          <?php endif; ?>

          <?php if ($contact): ?>
            <li><strong>Osoba kontaktowa:</strong> <span><?= htmlspecialchars($contact['contact_name']) ?></span></li>
            <li><strong>Telefon:</strong> <span><?= htmlspecialchars($contact['phone']) ?></span></li>
            <li><strong>Relacja:</strong> <span><?= htmlspecialchars($contact['relation']) ?></span></li>
          <?php else: ?>
            <li><em>Brak danych kontaktu alarmowego. <a href="/profile/edit">Dodaj kontakt alarmowy</a></em></li>
          <?php endif; ?>

          <?php if (!empty($user['role_name']) && $user['role_name'] === 'admin'): ?>
            <li>
              <button id="exportDbBtn" class="btn"><i class="fas fa-download"></i> Eksportuj bazę</button>
            </li>
          <?php endif; ?>
        </ul>

        <div class="stats">
          <div class="stat-card">
            <strong><?= (int)$stats['total_routes'] ?></strong>
            <br>Trasy
          </div>
          <div class="stat-card">
            <strong><?= number_format((float)$stats['total_km'], 1, ',', ' ') ?> km</strong>
            <br>Łączny dystans
          </div>
          <div class="stat-card">
            <strong><?= (int)$stats['total_vehicles'] ?></strong>
            <br>Kampery
          </div>
        </div>

        <div class="actions">
          <a href="/profile/edit" class="btn">
            <i class="fas fa-edit"></i> Edytuj profil
          </a>
          <a href="/dashboard" class="btn">
            <i class="fas fa-arrow-left"></i> Powrót do dashboard
          </a>
          <?php if (!empty($user['role_name']) && $user['role_name'] === 'admin'): ?>
            <a href="/admin/dashboard" class="btn">
              <i class="fas fa-user-shield"></i> Panel administratora
            </a>
          <?php endif; ?>
        </div>
    </main>
  </div>

  <script>
document.getElementById('exportDbBtn')?.addEventListener('click', () => {
  fetch('/profile/export-db', {
    credentials: 'same-origin'
  })
  .then(resp => {
    if (!resp.ok) throw new Error('Status ' + resp.status);
    return resp.blob();
  })
  .then(blob => {
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    const ts = new Date().toISOString().replace(/[:.]/g,'-');
    a.href = url;
    a.download = `db_export_${ts}.sql`;
    document.body.appendChild(a);
    a.click();
    a.remove();
  })
  .catch(err => alert('Błąd podczas eksportu: ' + err.message));
});
</script>
</body>
</html>