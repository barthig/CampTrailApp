<?php
/** 
 * @var array<int,array{
 *   id:int,
 *   name:string,
 *   location:string,
 *   price:float,
 *   capacity:int,
 *   average_rating:float,
 *   thumbnail:string|null,
 *   short_description:string|null
 * }> $destinations 
 */
use src\Core\SessionManager;
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Destynacje – CampTrail</title>
  <link rel="stylesheet" href="/public/css/sidebar.css">
  <link rel="stylesheet" href="/public/css/destinations_list.css">
  <link rel="stylesheet" href="/public/css/notifications_bell.css">
  <script defer src="/public/js/notifications.js"></script>
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
  <div class="page-wrapper">
    <?php include __DIR__ . '/../../components/sidebar.php'; ?>
    <div class="content-container">
      <div class="destinations-box">
        <header class="dest-header">
          <h1>Destynacje</h1>
        <?php foreach(SessionManager::getAllFlashes() as $type => $msg): ?>
          <div class="flash <?= $type ?>"><?= htmlspecialchars($msg, ENT_QUOTES) ?></div>
        <?php endforeach; ?>
          <a href="/destinations/create" class="button add-dest">
            <i class="fas fa-plus"></i> Dodaj destynację
          </a>
        </header>
        <div class="cards-grid">
        <?php foreach($destinations as $d): ?>
  <div class="dest-card">
    <div class="dest-thumb">
      <img
        src="<?= htmlspecialchars(
                $d['thumbnail'] ?? 'https://place-hold.it/240x140?text=add%20photo', ENT_QUOTES ) ?>"
        alt="<?= htmlspecialchars($d['name'], ENT_QUOTES) ?>"
        loading="lazy"
      >
    </div>
    <div class="dest-body">
      <h2 class="dest-name"><?= htmlspecialchars($d['name'], ENT_QUOTES) ?></h2>
      <ul class="dest-meta">
        <li><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($d['location'], ENT_QUOTES) ?></li>
        <li><i class="fas fa-money-bill-wave"></i><?= number_format($d['price'] ?? 0, 2, ',', ' ') ?> PLN/dobę</li>
        <li><i class="fas fa-users"></i><?= intval($d['capacity'] ?? 0) ?></li>
      </ul>
      <?php if (!empty($d['short_description'])): ?>
        <p class="dest-desc"><?= htmlspecialchars($d['short_description'], ENT_QUOTES) ?></p>
      <?php endif; ?>
    </div>
    <a href="/destinations/view?id=<?= $d['id'] ?>" class="button view-more">
      Zobacz
    </a>
  </div>
<?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
