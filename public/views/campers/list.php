<?php

/** @var array<int,array{
 *   id:int,name:string,type:string,capacity:int,
 *   image_url?:string,
 *   insurance_end?:string,
 *   inspection_valid?:string,
 *   mileage?:int
 * }> $campers */

use src\Core\SessionManager;
?>
<!DOCTYPE html>
<html lang="pl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Pojazdy – CampTrail</title>
  <link rel="stylesheet" href="/public/css/sidebar.css">
  <link rel="stylesheet" href="/public/css/camper_list.css">
  <link rel="stylesheet" href="/public/css/notifications_bell.css">
  <script defer src="/public/js/notifications.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
  <div class="page-wrapper">
    <?php include __DIR__ . '/../../components/sidebar.php'; ?>
    <div class="content-container">
      <div class="campers-box">
        <h1>Moje pojazdy</h1>

        <?php foreach (SessionManager::getAllFlashes() as $type => $msg): ?>
          <div class="flash <?= $type ?>"><?= htmlspecialchars($msg, ENT_QUOTES) ?></div>
        <?php endforeach; ?>

        <a href="/campers/create" class="button">
          <i class="fas fa-plus"></i> Dodaj pojazd
        </a>

        <?php if (empty($campers)): ?>
          <div class="empty-state">
            <p>Nie dodałeś jeszcze żadnego pojazdu.</p>
            <a href="/campers/create" class="button">Dodaj pojazd</a>
          </div>
        <?php else: ?>
          <div class="campers-grid">
            <?php foreach ($campers as $c): ?>
              <div class="camper-card">

                <div class="card-thumb">
                  <img src="<?= htmlspecialchars(
                              !empty($c['image_url'])
                                ? $c['image_url']
                                : 'https://place-hold.it/240x140?text=add%20photo',
                              ENT_QUOTES
                            ) ?>"
                    alt="Zdjęcie pojazdu">
                </div>
                <div class="card-header">
                  <h2><?= htmlspecialchars($c['name'], ENT_QUOTES) ?></h2>
                  <span class="camper-type"><?= htmlspecialchars($c['type'], ENT_QUOTES) ?></span>
                </div>

                <div class="card-body">
                  <p><strong>Pojemność:</strong> <?= (int)$c['capacity'] ?> osób</p>
                  <p><strong>Przebieg:</strong> <?= isset($c['mileage']) ? (int)$c['mileage'] . ' km' : '–' ?></p>
                  <p><strong>Polisa ważna do:</strong> <?= $c['insurance_end'] ?? '–' ?></p>
                  <p><strong>Przegląd ważny do:</strong> <?= $c['inspection_valid'] ?? '–' ?></p>
                </div>

                <div class="card-actions">
                  <a href="/campers/view?id=<?= (int)$c['id'] ?>" class="action-link"><i class="fas fa-eye"></i> Szczegóły</a>
                  <a href="/campers/edit?id=<?= (int)$c['id'] ?>" class="action-link"><i class="fas fa-edit"></i> Edytuj</a>
                  <form method="POST" action="/campers/delete" style="display:inline">
                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                    <button type="submit" class="delete-button" onclick="return confirm('Na pewno usunąć?')">
                      <i class="fas fa-trash"></i> Usuń
                    </button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>