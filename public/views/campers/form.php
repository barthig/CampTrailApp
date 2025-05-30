<?php

use src\Core\SessionManager;

/** @var array<string,mixed>|null $camper */
/** @var array<int,array<string,string>>      $images */
/** @var array<string,mixed>                 $insurance */
/** @var array<string,mixed>                 $inspection */
$isEdit      = !empty($camper['id']);
$userId      = (int)($_SESSION['user_id'] ?? 0);
$formAction  = $isEdit ? '/campers/edit?id=' . (int)$camper['id'] : '/campers/create';
$submitLabel = $isEdit ? 'Zapisz zmiany' : 'Dodaj pojazd';
$pageTitle   = $isEdit ? 'Edytuj kamper' : 'Nowy kamper';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES) ?> – CampTrail</title>
  <link rel="stylesheet" href="/public/css/sidebar.css">
  <link rel="stylesheet" href="/public/css/camper_form.css">
  <link rel="stylesheet" href="/public/css/notifications_bell.css">
  <script defer src="/public/js/notifications.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
  <?php include __DIR__ . '/../../components/sidebar.php'; ?>

  <main class="content-container">
    <section class="form-header">
      <h1><?= htmlspecialchars($pageTitle, ENT_QUOTES) ?></h1>
      <p class="subtext"><?= $isEdit
            ? 'Zaktualizuj dane kampera — wszystkie pola zostały wypełnione na podstawie istniejących danych.'
            : 'Wypełnij formularz, aby dodać nowy kamper do bazy.' ?></p>
    </section>

    <?php foreach (SessionManager::getAllFlashes() as $type => $msg): ?>
      <div class="flash <?= htmlspecialchars($type, ENT_QUOTES) ?>"><?= htmlspecialchars($msg, ENT_QUOTES) ?></div>
    <?php endforeach; ?>

    <?php if (!empty($error)): ?>
      <div class="flash error"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= $formAction ?>" class="camper-form" enctype="multipart/form-data">
      <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int)$camper['id'] ?>">
      <?php endif; ?>
      <input type="hidden" name="user_id" value="<?= $userId ?>">

      <div class="form-group">
        <label for="name">Nazwa:</label>
        <input type="text" id="name" name="name" required
               value="<?= htmlspecialchars($camper['name'] ?? '', ENT_QUOTES) ?>">
      </div>
      <div class="form-group two-cols">
        <div>
          <label for="type">Typ:</label>
          <input type="text" id="type" name="type" required
                 value="<?= htmlspecialchars($camper['type'] ?? '', ENT_QUOTES) ?>">
        </div>
        <div>
          <label for="capacity">Pojemność:</label>
          <input type="number" id="capacity" name="capacity" min="1" required
                 value="<?= isset($camper['capacity']) ? htmlspecialchars($camper['capacity'], ENT_QUOTES) : '' ?>">
        </div>
      </div>

      <fieldset>
        <legend>Zdjęcia kampera</legend>
        <?php if (!empty($images)): ?>
          <div class="existing-images">
            <?php foreach ($images as $img): ?>
              <div class="thumb">
                <img src="<?= htmlspecialchars($img['image_url'], ENT_QUOTES) ?>"
                     alt="Zdjęcie kampera" loading="lazy">
                <small>Dodane: <?= htmlspecialchars($img['uploaded_at'], ENT_QUOTES) ?></small>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <div class="form-group">
          <label for="image">Dodaj nowe zdjęcie:</label>
          <input type="file" id="image" name="image" accept="image/*">
        </div>
      </fieldset>

      <fieldset>
        <legend>Dane techniczne</legend>
        <div class="form-group two-cols">
          <div>
            <label for="vin">Numer VIN:</label>
            <input type="text" id="vin" name="vin" maxlength="17"
                   value="<?= htmlspecialchars($camper['vin'] ?? '', ENT_QUOTES) ?>">
          </div>
          <div>
            <label for="registration_number">Nr rejestracyjny:</label>
            <input type="text" id="registration_number" name="registration_number" maxlength="10"
                   value="<?= htmlspecialchars($camper['registration_number'] ?? '', ENT_QUOTES) ?>">
          </div>
        </div>
        <div class="form-group two-cols">
          <div>
            <label for="brand">Marka:</label>
            <input type="text" id="brand" name="brand"
                   value="<?= htmlspecialchars($camper['brand'] ?? '', ENT_QUOTES) ?>">
          </div>
          <div>
            <label for="model">Model:</label>
            <input type="text" id="model" name="model"
                   value="<?= htmlspecialchars($camper['model'] ?? '', ENT_QUOTES) ?>">
          </div>
        </div>
        <div class="form-group two-cols">
          <div>
            <label for="year">Rok produkcji:</label>
            <input type="number" id="year" name="year" min="1900" max="<?= date('Y') ?>"
                   value="<?= isset($camper['year']) ? htmlspecialchars($camper['year'], ENT_QUOTES) : '' ?>">
          </div>
          <div>
            <label for="mileage">Przebieg:</label>
            <input type="number" id="mileage" name="mileage" min="0"
                   value="<?= isset($camper['mileage']) ? htmlspecialchars($camper['mileage'], ENT_QUOTES) : '' ?>">
          </div>
        </div>
        <div class="form-group">
          <label for="purchase_date">Data zakupu:</label>
          <?php $pd = $camper['purchase_date'] ?? null; ?>
          <input type="date" id="purchase_date" name="purchase_date"
                 value="<?= $pd ? date('Y-m-d', strtotime($pd)) : '' ?>">
        </div>
      </fieldset>

      <fieldset>
        <legend>Polisa ubezpieczeniowa</legend>
        <div class="form-group two-cols">
          <div>
            <label for="insurer_name">Ubezpieczyciel:</label>
            <input type="text" id="insurer_name" name="insurer_name"
                   value="<?= htmlspecialchars($insurance['insurer_name'] ?? '', ENT_QUOTES) ?>">
          </div>
          <div>
            <label for="policy_number">Nr polisy:</label>
            <input type="text" id="policy_number" name="policy_number"
                   value="<?= htmlspecialchars($insurance['policy_number'] ?? '', ENT_QUOTES) ?>">
          </div>
        </div>
        <div class="form-group two-cols">
          <div>
            <label for="policy_start_date">Data rozpoczęcia:</label>
            <?php $ps = $insurance['start_date'] ?? null; ?>
            <input type="date" id="policy_start_date" name="policy_start_date"
                   value="<?= $ps ? date('Y-m-d', strtotime($ps)) : '' ?>">
          </div>
          <div>
            <label for="policy_end_date">Data zakończenia:</label>
            <?php $pe = $insurance['end_date'] ?? null; ?>
            <input type="date" id="policy_end_date" name="policy_end_date"
                   value="<?= $pe ? date('Y-m-d', strtotime($pe)) : '' ?>">
          </div>
        </div>
        <div class="form-group">
          <label for="premium">Składka:</label>
          <input type="number" id="premium" name="premium" step="0.01" min="0"
                 value="<?= isset($insurance['premium']) ? htmlspecialchars($insurance['premium'], ENT_QUOTES) : '' ?>">
        </div>
      </fieldset>

      <fieldset>
        <legend>Przegląd techniczny</legend>
        <div class="form-group two-cols">
          <div>
            <label for="last_inspection_date">Data ostatniego przeglądu:</label>
            <?php $lid = $inspection['inspection_date'] ?? null; ?>
            <input type="date" id="last_inspection_date" name="last_inspection_date"
                   value="<?= $lid ? date('Y-m-d', strtotime($lid)) : '' ?>">
          </div>
          <div>
            <label for="inspection_valid_until">Ważny do:</label>
            <?php $iv = $inspection['valid_until'] ?? null; ?>
            <input type="date" id="inspection_valid_until" name="inspection_valid_until"
                   value="<?= $iv ? date('Y-m-d', strtotime($iv)) : '' ?>">
          </div>
        </div>
        <div class="form-group two-cols">
          <div>
            <label for="inspection_person">Osoba kontrolująca:</label>
            <input type="text" id="inspection_person" name="inspection_person"
                   value="<?= htmlspecialchars($inspection['inspector_name'] ?? '', ENT_QUOTES) ?>">
          </div>
          <div>
            <label for="inspection_result">Wynik badania:</label>
            <input type="text" id="inspection_result" name="inspection_result"
                   value="<?= htmlspecialchars($inspection['result'] ?? '', ENT_QUOTES) ?>">
          </div>
        </div>
      </fieldset>

      <div class="form-actions">
        <button type="submit" class="button primary">
          <i class="fas fa-save"></i> <?= htmlspecialchars($submitLabel, ENT_QUOTES) ?>
        </button>
        <a href="/campers" class="button secondary">
          <i class="fas fa-times"></i> Anuluj
        </a>
      </div>
    </form>
  </main>
</body>
</html>
