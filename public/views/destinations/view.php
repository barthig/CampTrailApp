<?php

declare(strict_types=1);

use src\Core\SessionManager;

/** @var array<string,mixed>                              $destination */
/** @var array<int,array{id:int,image_url:string}>         $images */
/** @var array<int,array<string,mixed>>                   $otherDestinations */

SessionManager::start();

$id        = (int)($destination['id'] ?? 0);
$name      = htmlspecialchars((string)($destination['name'] ?? '—'), ENT_QUOTES);
$shortDesc = htmlspecialchars((string)($destination['short_description'] ?? ''), ENT_QUOTES);
$location  = htmlspecialchars((string)($destination['location'] ?? ''), ENT_QUOTES);

$seasonFrom = $destination['season_from']   ?: null;
$seasonTo   = $destination['season_to']     ?: null;
$checkin    = $destination['checkin_time']  ?: null;
$checkout   = $destination['checkout_time'] ?: null;

$capacity = isset($destination['capacity'])
  ? (int)$destination['capacity']
  : null;
$price = isset($destination['price'])
  ? number_format((float)$destination['price'], 2, ',', ' ')
  : null;

$latitude  = htmlspecialchars((string)($destination['latitude'] ?? ''), ENT_QUOTES);
$longitude = htmlspecialchars((string)($destination['longitude'] ?? ''), ENT_QUOTES);

$description = nl2br(htmlspecialchars((string)($destination['description'] ?? ''), ENT_QUOTES));
$rules       = nl2br(htmlspecialchars((string)($destination['rules'] ?? ''), ENT_QUOTES));

$rawAm = $destination['amenities'] ?? [];
if (is_string($rawAm)) {
  $amenities = array_filter(explode(',', trim($rawAm, '{}')));
} elseif (is_array($rawAm)) {
  $amenities = $rawAm;
} else {
  $amenities = [];
}

$phone   = htmlspecialchars((string)($destination['phone'] ?? ''), ENT_QUOTES);
$email   = htmlspecialchars((string)($destination['contact_email'] ?? ''), ENT_QUOTES);
$website = htmlspecialchars((string)($destination['website'] ?? ''), ENT_QUOTES);
?>
<!DOCTYPE html>
<html lang="pl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= $name ?> – CampTrail</title>
  <link rel="stylesheet" href="/public/css/sidebar.css">
  <link rel="stylesheet" href="/public/css/destination_view.css">
  <link rel="stylesheet" href="/public/css/notifications_bell.css">
  <script defer src="/public/js/notifications.js"></script>
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
  <div class="page-wrapper">
    <?php include __DIR__ . '/../../components/sidebar.php'; ?>
    <main class="content-container">

      <article class="destination-details">
        <header>
          <h1><?= $name ?></h1>
          <a href="/destinations/edit?id=<?= $id ?>" class="button apply">
            <i class="fas fa-edit"></i> Edytuj
          </a>
          <?php if ($shortDesc !== ''): ?>
            <p class="short-desc"><?= $shortDesc ?></p>
          <?php endif; ?>
        </header>

        <section class="meta-row">
          <span><i class="fas fa-map-marker-alt"></i>
            <?= $location ?: '<span class="text-muted">Brak informacji</span>' ?>
          </span>
          <span><i class="fas fa-calendar-alt"></i>
            <?= ($seasonFrom && $seasonTo)
              ? "$seasonFrom – $seasonTo"
              : '<span class="text-muted">Brak informacji</span>' ?>
          </span>
          <span><i class="fas fa-clock"></i>
            <?= ($checkin && $checkout)
              ? "Zameld. $checkin, wymeld. $checkout"
              : '<span class="text-muted">Brak informacji</span>' ?>
          </span>
          <span><i class="fas fa-user-friends"></i>
            <?= $capacity !== null
              ? "$capacity os."
              : '<span class="text-muted">Brak informacji</span>' ?>
          </span>
          <span><i class="fas fa-money-bill-wave"></i>
            <?= $price !== null
              ? "$price PLN/dobę"
              : '<span class="text-muted">Brak informacji</span>' ?>
          </span>
          <span><i class="fas fa-map-pin"></i>
            <?= ($latitude && $longitude)
              ? "$latitude, $longitude"
              : '<span class="text-muted">Brak informacji</span>' ?>
          </span>
        </section>

        <?php
        /** @var array<int,array{id:int,image_url:string}> $images */
        $images = $images ?? [];
        ?>
        <section class="image-gallery">
          <h2>Galeria zdjęć</h2>
          <div class="gallery-grid">
            <?php if ($images): ?>
              <?php foreach ($images as $img): ?>
                <div class="thumb">
                  <img
                    src="<?= htmlspecialchars($img['image_url'], ENT_QUOTES) ?>"
                    alt="Zdjęcie destynacji"
                    loading="lazy">
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="thumb">
                <img
                  src="https://place-hold.it/240x140?text=No%20Photo"
                  alt="Brak zdjęć"
                  loading="lazy">
              </div>
            <?php endif; ?>
          </div>
        </section>

        <section class="description">
          <h2>Opis</h2>
          <?= $description !== ''
            ? "<div>$description</div>"
            : '<div class="text-muted">Brak informacji</div>' ?>
        </section>

        <section class="amenities">
          <h2><i class="fas fa-concierge-bell"></i> Udogodnienia</h2>
          <?php if ($amenities): ?>
            <ul class="amenities-list">
              <?php
              $icons = [
                'electricity' => 'bolt',
                'water' => 'tint',
                'sewage' => 'toilet',
                'wifi' => 'wifi',
                'shop' => 'shopping-bag',
                'access' => 'wheelchair'
              ];
              foreach ($amenities as $amen):
                $icon = $icons[$amen] ?? 'circle';
              ?>
                <li><i class="fas fa-<?= $icon ?>"></i>
                  <?= htmlspecialchars(ucfirst($amen), ENT_QUOTES) ?>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <div class="text-muted">Brak informacji</div>
          <?php endif; ?>
        </section>

        <section class="rules">
          <h2><i class="fas fa-book"></i> Zasady i uwagi</h2>
          <?= $rules !== ''
            ? "<div>$rules</div>"
            : '<div class="text-muted">Brak informacji</div>' ?>
        </section>

        <section class="contact">
          <h2><i class="fas fa-address-book"></i> Kontakt</h2>
          <p><i class="fas fa-phone"></i>
            <?= $phone ?: '<span class="text-muted">Brak informacji</span>' ?>
          </p>
          <p><i class="fas fa-envelope"></i>
            <?= $email ?: '<span class="text-muted">Brak informacji</span>' ?>
          </p>
          <p><i class="fas fa-globe"></i>
            <?= $website
              ? "<a href=\"$website\" target=\"_blank\">$website</a>"
              : '<span class="text-muted">Brak informacji</span>' ?>
          </p>
        </section>
      </article>

      <?php if ($otherDestinations): ?>
        <aside class="other-list">
          <h2>Inne destynacje</h2>
          <div class="cards-row">
            <?php foreach ($otherDestinations as $od):
              $oid = (int)($od['id'] ?? 0);
              $n   = htmlspecialchars($od['name'] ?? '—', ENT_QUOTES);
              $imgUrl = $od['image_url'] ?? '';
            ?>
              <a href="/destinations/view?id=<?= $oid ?>" class="card">
                <?php
                $imgSrc = !empty($imgUrl) ? $imgUrl : 'https://place-hold.it/240x140?text=add%20photo';
                ?>
                <img
                  src="<?= htmlspecialchars($imgSrc, ENT_QUOTES, 'UTF-8') ?>"
                  alt="<?= htmlspecialchars($n, ENT_QUOTES, 'UTF-8') ?>"
                  loading="lazy" />
                <div class="card-info">
                  <h3><?= $n ?></h3>
                  <p class="card-location">
                    <i class="fas fa-map-marker-alt"></i>
                    <?= htmlspecialchars($od['location'] ?? '', ENT_QUOTES) ?: '<span class="text-muted">Brak informacji</span>' ?>
                  </p>
                  <p class="card-price">
                    <i class="fas fa-money-bill-wave"></i>
                    <?= isset($od['price'])
                      ? number_format((float)$od['price'], 2, ',', ' ') . ' PLN/dobę'
                      : '<span class="text-muted">Brak informacji</span>' ?>
                  </p>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </aside>
      <?php endif; ?>

    </main>
  </div>
</body>
</html>