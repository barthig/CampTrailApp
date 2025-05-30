<?php
/** @var array<string,mixed>|null */
/** @var array<int,array<string,mixed>>  */
/** @var array<int,array{id:int,name:string}>  */

$isEdit      = !empty($route);
$userId      = (int)($_SESSION['user_id'] ?? 0);
$actionUrl   = $isEdit
    ? '/routes/edit?id=' . (int)$route['id']
    : '/routes/create';
$submitLabel = $isEdit ? 'Zapisz zmiany' : 'Dodaj trasę';
$title       = $isEdit ? 'Edytuj trasę'  : 'Nowa trasa';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($title,ENT_QUOTES) ?> – CampTrail</title>
  <link rel="stylesheet" href="/public/css/sidebar.css">
  <link rel="stylesheet" href="/public/css/routes_form.css">
  <link rel="stylesheet" href="/public/css/notifications_bell.css">
  <script defer src="/public/js/notifications.js"></script>
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
  <?php include __DIR__ . '/../../components/sidebar.php'; ?>

  <main class="content-container">
    <section class="form-header">
      <h1><?= htmlspecialchars($title,ENT_QUOTES) ?></h1>
      <p class="subtext">
        Wybierz dwie różne destynacje oraz kampera, którym będziesz podróżować.
      </p>
    </section>

    <form method="POST" action="<?= $actionUrl ?>" class="route-form">
      <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int)$route['id'] ?>">
      <?php endif; ?>
      <input type="hidden" name="user_id" value="<?= $userId ?>">

      <div class="form-row two-cols">
        <div class="form-group">
          <label for="origin_id">Start:</label>
          <select id="origin_id" name="origin_id" required>
            <option value="">— wybierz start —</option>
            <?php foreach ($destinations as $d): ?>
              <option
                value="<?= (int)$d['id'] ?>"
                <?= (isset($route['origin_id']) && (int)$route['origin_id'] === (int)$d['id'])
                      ? 'selected' : '' ?>
              >
                <?= htmlspecialchars($d['name'],ENT_QUOTES) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="destination_id">Meta:</label>
          <select id="destination_id" name="destination_id" required>
            <option value="">— wybierz metę —</option>
            <?php foreach ($destinations as $d): ?>
              <option
                value="<?= (int)$d['id'] ?>"
                <?= (isset($route['destination_id']) && (int)$route['destination_id'] === (int)$d['id'])
                      ? 'selected' : '' ?>
              >
                <?= htmlspecialchars($d['name'],ENT_QUOTES) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label for="camper_id">Kamper:</label>
        <select id="camper_id" name="camper_id" required>
          <option value="">— wybierz pojazd —</option>
          <?php foreach ($campers as $cam): ?>
            <option
              value="<?= $cam['id'] ?>"
              <?= (isset($route['camper_id']) && (int)$route['camper_id'] === $cam['id'])
                    ? 'selected' : '' ?>
            >
              <?= htmlspecialchars($cam['name'],ENT_QUOTES) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="distance">Dystans (km):</label>
        <input
          type="number"
          step="0.1"
          id="distance"
          name="distance"
          required
          placeholder="np. 12.5"
          value="<?= htmlspecialchars($route['distance'] ?? '',ENT_QUOTES) ?>"
        >
      </div>

      <?php if ($isEdit): ?>
      <div class="form-group">
        <label>Data utworzenia:</label>
        <input
          type="text"
          readonly
          value="<?= htmlspecialchars(
            date('Y-m-d H:i', strtotime($route['created_at'])),
            ENT_QUOTES
          ) ?>"
        >
      </div>
      <?php endif; ?>

      <div class="form-actions">
        <button type="submit" class="button primary">
          <i class="fas fa-save"></i> <?= htmlspecialchars($submitLabel,ENT_QUOTES) ?>
        </button>
        <a href="/routes" class="button secondary">
          <i class="fas fa-times"></i> Anuluj
        </a>
      </div>
    </form>
  </main>

  <script>
    const origin = document.getElementById('origin_id');
    const dest   = document.getElementById('destination_id');

    function syncOptions(changed, other) {
      const val = changed.value;
      for (let opt of other.options) {
        opt.disabled = (opt.value === val && val !== '');
      }
    }
    origin.addEventListener('change', () => syncOptions(origin,dest));
    dest.addEventListener   ('change', () => syncOptions(dest,origin));
    syncOptions(origin,dest);
    syncOptions(dest,origin);
  </script>
</body>
</html>
