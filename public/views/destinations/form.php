<?php

/** @var array<string,mixed>|null $destination */

use src\Core\SessionManager;

$isEdit      = !empty($destination);
$pageTitle   = $isEdit ? 'Edytuj destynację' : 'Nowa destynacja';
$formAction  = $isEdit ? '/destinations/edit' : '/destinations/create';
$submitLabel = $isEdit ? 'Zapisz zmiany'   : 'Dodaj destynację';
?>
<!DOCTYPE html>
<html lang="pl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= $pageTitle ?> – CampTrail</title>
  <link rel="stylesheet" href="/public/css/sidebar.css">
  <link rel="stylesheet" href="/public/css/destination_form.css">
  <link rel="stylesheet" href="/public/css/notifications_bell.css">
  <script defer src="/public/js/notifications.js"></script>
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
  <div class="page-wrapper">
    <?php include __DIR__ . '/../../components/sidebar.php'; ?>
    <div class="content-container">
      <div class="destination-form">
        <h1><?= $pageTitle ?></h1>

        <?php foreach (SessionManager::getAllFlashes() as $type => $msg): ?>
          <div class="flash <?= $type ?>"><?= htmlspecialchars($msg, ENT_QUOTES) ?></div>
        <?php endforeach; ?>

        <form method="POST" action="<?= $formAction ?>" enctype="multipart/form-data">
          <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= (int)$destination['id'] ?>">
          <?php endif; ?>

          <div class="form-group">
            <label for="name">Nazwa miejsca:</label>
            <input type="text" id="name" name="name" required
              value="<?= htmlspecialchars($destination['name'] ?? '', ENT_QUOTES) ?>">
          </div>
          <div class="form-group two-cols">
            <div>
              <label for="location">Adres / lokalizacja:</label>
              <input type="text" id="location" name="location"
                value="<?= htmlspecialchars($destination['location'] ?? '', ENT_QUOTES) ?>">
            </div>
            <div>
              <label for="short_description">Krótki opis:</label>
              <input type="text" id="short_description" name="short_description"
                value="<?= htmlspecialchars($destination['short_description'] ?? '', ENT_QUOTES) ?>">
            </div>
          </div>
          <div class="form-group">
            <label for="description">Pełny opis:</label>
            <textarea id="description" name="description" rows="4"><?= htmlspecialchars($destination['description'] ?? '', ENT_QUOTES) ?></textarea>
          </div>

          <div class="form-group two-cols">
            <div>
              <label for="price">Cena za dobę (PLN):</label>
              <input type="number" step="0.01" id="price" name="price"
                value="<?= htmlspecialchars($destination['price'] ?? '', ENT_QUOTES) ?>">
            </div>
            <div>
              <label for="capacity">Maks. osób:</label>
              <input type="number" id="capacity" name="capacity" min="1"
                value="<?= htmlspecialchars($destination['capacity'] ?? '', ENT_QUOTES) ?>">
            </div>
          </div>

          <div class="form-group two-cols">
            <div>
              <label for="phone"><i class="fas fa-phone"></i> Telefon:</label>
              <input type="tel" id="phone" name="phone"
                value="<?= htmlspecialchars($destination['phone'] ?? '', ENT_QUOTES) ?>">
            </div>
            <div>
              <label for="contact_email"><i class="fas fa-envelope"></i> Email:</label>
              <input type="email" id="contact_email" name="contact_email"
                value="<?= htmlspecialchars($destination['contact_email'] ?? '', ENT_QUOTES) ?>">
            </div>
          </div>
          <div class="form-group">
            <label for="website"><i class="fas fa-globe"></i> Strona WWW:</label>
            <input type="url" id="website" name="website"
              value="<?= htmlspecialchars($destination['website'] ?? '', ENT_QUOTES) ?>">
          </div>

          <div class="form-group two-cols">
            <div>
              <label for="latitude"><i class="fas fa-map-pin"></i> Szer. geogr.:</label>
              <input type="text" id="latitude" name="latitude" placeholder="50.0614"
                value="<?= htmlspecialchars($destination['latitude'] ?? '', ENT_QUOTES) ?>">
            </div>
            <div>
              <label for="longitude"><i class="fas fa-map-pin"></i> Dł. geogr.:</label>
              <input type="text" id="longitude" name="longitude" placeholder="19.9383"
                value="<?= htmlspecialchars($destination['longitude'] ?? '', ENT_QUOTES) ?>">
            </div>
          </div>

          <div class="form-group two-cols">
            <div>
              <label for="season_from"><i class="fas fa-calendar-alt"></i> Sezon od:</label>
              <input type="date" id="season_from" name="season_from"
                value="<?= htmlspecialchars($destination['season_from'] ?? '', ENT_QUOTES) ?>">
            </div>
            <div>
              <label for="season_to"><i class="fas fa-calendar-alt"></i> Sezon do:</label>
              <input type="date" id="season_to" name="season_to"
                value="<?= htmlspecialchars($destination['season_to'] ?? '', ENT_QUOTES) ?>">
            </div>
          </div>
          <div class="form-group two-cols">
            <div>
              <label for="checkin_time"><i class="fas fa-clock"></i> Zameld.:</label>
              <input type="time" id="checkin_time" name="checkin_time"
                value="<?= htmlspecialchars($destination['checkin_time'] ?? '', ENT_QUOTES) ?>">
            </div>
            <div>
              <label for="checkout_time"><i class="fas fa-clock"></i> Wymeld.:</label>
              <input type="time" id="checkout_time" name="checkout_time"
                value="<?= htmlspecialchars($destination['checkout_time'] ?? '', ENT_QUOTES) ?>">
            </div>
          </div>

          <fieldset class="form-group amenities">
            <legend><i class="fas fa-concierge-bell"></i> Udogodnienia:</legend>
            <?php

            $raw = $destination['amenities'] ?? [];
            if (is_string($raw)) {
              $have = array_filter(explode(',', trim($raw, '{}')));
            } elseif (is_array($raw)) {
              $have = $raw;
            } else {
              $have = [];
            }

            $opts = [
              'electricity' => ['Prąd', 'bolt'],
              'water'       => ['Woda pitna', 'tint'],
              'sewage'      => ['Ścieki', 'toilet'],
              'wifi'        => ['Wi-Fi', 'wifi'],
              'shop'        => ['Sklep', 'shopping-bag'],
              'access'      => ['Bezp. niepełn.', 'wheelchair']
            ];

            foreach ($opts as $key => [$lbl, $icon]):
            ?>
              <label>
                <input
                  type="checkbox"
                  name="amenities[]"
                  value="<?= htmlspecialchars($key, ENT_QUOTES) ?>"
                  <?= in_array($key, $have, true) ? 'checked' : '' ?>>
                <i class="fas fa-<?= htmlspecialchars($icon, ENT_QUOTES) ?>"></i>
                <?= htmlspecialchars($lbl, ENT_QUOTES) ?>
              </label>
            <?php endforeach; ?>
          </fieldset>

          <?php
          $rawImages = $destination['images'] ?? [];
          if (is_string($rawImages)) {
            $images = array_filter(explode(',', trim($rawImages, '{}')));
          } elseif (is_array($rawImages)) {
            $images = $rawImages;
          } else {
            $images = [];
          }
          ?>

          <div class="form-group">
            <label for="images"><i class="fas fa-camera"></i> Dodaj zdjęcia:</label>
            <input type="file" id="images" name="images[]" accept="image/*" multiple>
          </div>

          <?php if ($isEdit && !empty($images)): ?>
            <div class="form-group existing-images">
              <label>Obecne zdjęcia (zaznacz, by usunąć):</label>
              <div class="image-thumbs">
                <?php foreach ($images as $img): ?>
                  <div class="thumb">
                    <img src="<?= htmlspecialchars($img, ENT_QUOTES) ?>" alt="">
                    <label>
                      <input
                        type="checkbox"
                        name="delete_images[]"
                        value="<?= htmlspecialchars($img, ENT_QUOTES) ?>"> Usuń
                    </label>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <div class="form-group">
            <label for="rules"><i class="fas fa-book"></i> Zasady / uwagi:</label>
            <textarea id="rules" name="rules" rows="3"><?= htmlspecialchars($destination['rules'] ?? '', ENT_QUOTES) ?></textarea>
          </div>

          <div class="form-actions">
            <button type="submit" class="button">
              <i class="fas fa-save"></i> <?= $submitLabel ?>
            </button>
            <a href="/destinations" class="button secondary">
              <i class="fas fa-times"></i> Anuluj
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>