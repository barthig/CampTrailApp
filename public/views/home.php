<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CampTrail – Strona Główna</title>
  <link rel="stylesheet" href="/public/css/sidebar.css">
  <link rel="stylesheet" href="/public/css/footer.css">
   <link rel="stylesheet" href="/public/css/main.css">
  <link rel="stylesheet" href="/public/css/notifications_bell.css">
  <script defer src="/public/js/notifications.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
  <div class="page-wrapper">
    <?php include __DIR__ . '/../components/sidebar.php'; ?>
    <main class="content-container">
      <header class="dest-header">
        <h1>Witaj w CampTrail!</h1>
        <div class="intro-buttons">
          <a href="/register" class="button">Zarejestruj się</a>
          <a href="/login" class="button outline">Zaloguj się</a>
        </div>
      </header>
      <div class="cards-grid">
        <?php foreach ([
          ['img' => 'feature1.jpg', 'title' => 'Nie martw się już o polisę i przeglądy!', 'desc' => 'Wprowadź dane swojego kampera lub samochodu — nr rejestracyjny, typ ubezpieczenia oraz terminy przeglądów.'],
          ['img' => 'feature2.jpg', 'title' => 'Twoje ostatnie przygody na wyciągnięcie ręki', 'desc' => 'Przeglądaj trasy, które niedawno odwiedziłeś – notuj zdjęcia i wspomnienia z podróży.'],
          ['img' => 'feature3.webp', 'title' => 'Zarekomenduj swoje perełki', 'desc' => 'Dodaj ulubione miejsca: kempingi, punkty widokowe i lokalne knajpki. Podziel się opinią i zdjęciami.'],
        ] as $feature): ?>
          <article class="dest-card">
            <div class="dest-thumb">
              <img src="/public/img/<?= $feature['img'] ?>" alt="<?= htmlspecialchars($feature['title'], ENT_QUOTES) ?>">
            </div>
            <div class="dest-body">
              <h2 class="dest-name"><?= htmlspecialchars($feature['title'], ENT_QUOTES) ?></h2>
              <p class="short-desc"><?= htmlspecialchars($feature['desc'], ENT_QUOTES) ?></p>
              <a href="/login" class="view-more">Dowiedz się więcej</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <?php include __DIR__ . '/../components/footer.php'; ?>
    </main>
  </div>
</body>
</html>
