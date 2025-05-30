<?php
/**
 * @var array<int,array{id:int,name:string}>                    
 * @var int|null                                               
 * @var array<int,array{label:string,routes:string,km:string}> 
 */
$totalRoutes = 0;
$totalKm     = 0.0;
foreach ($data as $row) {
    $totalRoutes += (int)$row['routes'];
    $totalKm     += (float)$row['km'];
}

$currentYear = date('Y');
$months = [];
for ($m = 1; $m <= 12; $m++) {
    $key = sprintf('%s-%02d', $currentYear, $m);
    $months[$key] = ['routes' => 0, 'km' => 0.0];
}
foreach ($data as $row) {
    if (isset($months[$row['label']])) {
        $months[$row['label']] = [
            'routes' => (int)$row['routes'],
            'km'     => (float)$row['km'],
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Statystyki tras – CampTrail</title>
  <link rel="stylesheet" href="/public/css/sidebar.css">
  <link rel="stylesheet" href="/public/css/stats.css">
  <link rel="stylesheet" href="/public/css/notifications_bell.css">
  <script defer src="/public/js/notifications.js"></script>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
  <div class="page-wrapper">
    <?php include __DIR__ . '/../../components/sidebar.php'; ?>
    <main class="content-container">
      <header class="stats-header">
        <h1>Statystyki tras</h1>
      </header>
      <form method="get" class="stats-filters">
        <label>
          Kamper:
          <select name="camper_id">
            <option value="" <?= $selected===null?'selected':'' ?>>Wszystkie</option>
            <?php foreach ($campers as $c): ?>
              <option value="<?= $c['id'] ?>" <?= $selected===$c['id']?'selected':''?>>
                <?= htmlspecialchars($c['name'],ENT_QUOTES) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>
        <button type="submit" class="button apply">Zastosuj</button>
      </form>
      <div class="year-summary">
        <h2>Dane za rok <?= $currentYear ?></h2>
        <p><strong>Liczba tras:</strong> <?= $totalRoutes ?></p>
        <p><strong>Łączny dystans (km):</strong> <?= number_format($totalKm,2,',',' ') ?></p>
      </div>
      <table class="stats-table">
        <thead>
          <tr>
            <th>Okres</th>
            <th>Liczba tras</th>
            <th>Dystans (km)</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Roczne</td>
            <td><?= $totalRoutes ?></td>
            <td><?= number_format($totalKm,2,',',' ') ?></td>
          </tr>
          <?php foreach ($months as $label => $vals): ?>
            <tr>
              <td data-label="Okres"><?= $label ?></td>
              <td data-label="Liczba tras">
                <?php if ($vals['routes'] > 0): ?>
                  <?= $vals['routes'] ?>
                <?php else: ?>
                  <span class="text-muted">Brak tras</span>
                <?php endif; ?>
              </td>
              <td data-label="Dystans (km)">
                <?php if ($vals['routes'] > 0): ?>
                  <?= number_format($vals['km'],2,',',' ') ?>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </main>
  </div>
</body>
</html>
