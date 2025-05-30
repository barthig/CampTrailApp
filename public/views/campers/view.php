<?php

/**
 * @var array{
 *     id: int,
 *     owner_id: int,
 *     name: string,
 *     type: string,
 *     capacity: int|null,
 *     vin: string|null,
 *     registration_number: string|null,
 *     brand: string|null,
 *     model: string|null,
 *     year: int|null,
 *     mileage: int|null,
 *     purchase_date: string|null,
 *     image_url: string|null
 * } $camper
 *
 * @var array<int, array{
 *     id: int,
 *     insurer_name: string|null,
 *     policy_number: string|null,
 *     start_date: string|null,
 *     end_date: string|null,
 *     premium: string|null
 * }> $insurance
 *
 * @var array<int, array{
 *     id: int,
 *     inspection_date: string,
 *     valid_until: string,
 *     result: string,
 *     inspector_name: string,
 *     station_name: string
 * }> $inspections
 */

use src\Core\SessionManager;

$disp = fn($v) => (isset($v) && trim((string)$v) !== '')
  ? htmlspecialchars($v, ENT_QUOTES)
  : 'Brak informacji';
?>
<!DOCTYPE html>
<html lang="pl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Szczegóły pojazdu – CampTrail</title>
  <link rel="stylesheet" href="/public/css/sidebar.css">
  <link rel="stylesheet" href="/public/css/camper_view.css">
  <link rel="stylesheet" href="/public/css/notifications_bell.css">
  <script defer src="/public/js/notifications.js"></script>
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
  <div class="page-wrapper">
    <?php include __DIR__ . '/../../components/sidebar.php'; ?>

    <div class="content-container">
      <div class="campers-box">
        <h1>Szczegóły pojazdu: <?= htmlspecialchars($camper['name'], ENT_QUOTES) ?></h1>

        <?php foreach (SessionManager::getAllFlashes() as $type => $msg): ?>
          <div class="flash <?= htmlspecialchars($type, ENT_QUOTES) ?>">
            <?= htmlspecialchars($msg, ENT_QUOTES) ?>
          </div>
        <?php endforeach; ?>

        <div class="form-actions">
          <a href="/campers" class="button secondary">
            <i class="fas fa-arrow-left"></i> Powrót do listy
          </a>
        </div>

        <div class="image-container">
          <?php
          $src = !empty($camper['image_url'])
            ? $camper['image_url']
            : 'https://place-hold.it/240x140?text=add%20photo'; ?>
          <img src="<?= htmlspecialchars($src, ENT_QUOTES) ?>"
            alt="Zdjęcie pojazdu"
            loading="lazy" />
        </div>

        <fieldset>
          <legend>Podstawowe informacje</legend>
          <dl>
            <dt>Nazwa:</dt>
            <dd><?= $disp($camper['name']) ?></dd>

            <dt>Typ:</dt>
            <dd><?= $disp($camper['type']) ?></dd>

            <dt>Pojemność:</dt>
            <dd>
              <?= $camper['capacity'] !== null && $camper['capacity'] > 0
                ? (int)$camper['capacity']
                : 'Brak informacji' ?>
            </dd>
          </dl>
        </fieldset>

        <fieldset>
          <legend>Dane techniczne</legend>
          <?php
          $hasTech = $camper['vin'] ||
            $camper['registration_number'] ||
            $camper['brand'] ||
            $camper['model'] ||
            $camper['year'] !== null ||
            ($camper['mileage'] !== null && $camper['mileage'] > 0) ||
            $camper['purchase_date'];
          ?>
          <?php if (!$hasTech): ?>
            <p>Brak szczegółowych danych.</p>
          <?php else: ?>
            <dl>
              <dt>VIN:</dt>
              <dd><?= $disp($camper['vin']) ?></dd>

              <dt>Nr rej.:</dt>
              <dd><?= $disp($camper['registration_number']) ?></dd>

              <dt>Marka / Model:</dt>
              <dd>
                <?php
                $bm = trim(($camper['brand'] ?? '') . ' ' . ($camper['model'] ?? ''));
                ?>
                <?= $disp($bm) ?>
              </dd>

              <dt>Rok produkcji:</dt>
              <dd>
                <?= $camper['year'] !== null
                  ? (int)$camper['year']
                  : 'Brak informacji' ?>
              </dd>

              <dt>Przebieg:</dt>
              <dd>
                <?= $camper['mileage'] !== null && $camper['mileage'] > 0
                  ? (int)$camper['mileage'] . ' km'
                  : 'Brak informacji' ?>
              </dd>

              <dt>Data zakupu:</dt>
              <dd>
                <?= $disp($camper['purchase_date']) ?>
              </dd>
            </dl>
          <?php endif; ?>
        </fieldset>

        <fieldset>
          <legend>Polisy ubezpieczeniowe</legend>
          <?php
          $validInsurance = array_filter($insurance, fn($polisa) => !(
            empty($polisa['insurer_name']) &&
            empty($polisa['policy_number']) &&
            empty($polisa['start_date']) &&
            empty($polisa['end_date'])
          ));
          ?>
          <?php if (count($validInsurance) > 0): ?>
            <?php foreach ($validInsurance as $polisa): ?>
              <div class="insurance-entry">
                <dl>
                  <dt>Ubezpieczyciel:</dt>
                  <dd><?= $disp($polisa['insurer_name']) ?></dd>

                  <dt>Nr polisy:</dt>
                  <dd><?= $disp($polisa['policy_number']) ?></dd>

                  <dt>Okres:</dt>
                  <dd>
                    <?= $disp($polisa['start_date']) ?> – <?= $disp($polisa['end_date']) ?>
                  </dd>

                  <dt>Składka:</dt>
                  <dd>
                    <?= isset($polisa['premium']) && trim($polisa['premium']) !== ''
                      ? htmlspecialchars($polisa['premium'], ENT_QUOTES) . ' PLN'
                      : 'Brak informacji' ?>
                  </dd>
                </dl>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>Brak zapisanych polis.</p>
          <?php endif; ?>
        </fieldset>

        <fieldset>
          <legend>Przeglądy techniczne</legend>
          <?php if (count($inspections) > 0): ?>
            <?php foreach ($inspections as $insp): ?>
              <div class="inspection-entry">
                <dl>
                  <dt>Data:</dt>
                  <dd><?= $disp($insp['inspection_date']) ?></dd>

                  <dt>Ważny do:</dt>
                  <dd><?= $disp($insp['valid_until']) ?></dd>

                  <dt>Wynik:</dt>
                  <dd><?= $disp($insp['result']) ?></dd>

                  <dt>Inspektor:</dt>
                  <dd><?= $disp($insp['inspector_name']) ?></dd>
                </dl>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>Brak zapisanych przeglądów technicznych.</p>
          <?php endif; ?>
        </fieldset>
      </div>
    </div>
  </div>
</body>

</html>