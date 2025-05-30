<?php
/** @var array<string,mixed> $user */
/** @var array<string,mixed>|null $contact*/
/** @var string|null $error */
use src\Core\SessionManager;
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edytuj profil – CampTrail</title>
  <link rel="stylesheet" href="/public/css/sidebar.css">
  <link rel="stylesheet" href="/public/css/profile_edit.css">
  <link rel="stylesheet" href="/public/css/notifications_bell.css">
  <script defer src="/public/js/notifications.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
  <div class="page-wrapper">
    <?php include __DIR__ . '/../../components/sidebar.php'; ?>

    <div class="content-container">
      <h1>Edytuj profil</h1>

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
      </div>

      <form method="POST" action="/profile/update-avatar" enctype="multipart/form-data" class="avatar-form">
        <fieldset>
          <legend><i class="fas fa-upload"></i> Prześlij nowy avatar</legend>

          <div class="form-group">
            <label for="avatar">Wybierz plik (JPEG/PNG, max 2 MB):</label>
            <input id="avatar" type="file" name="avatar" accept="image/jpeg,image/png" required>
          </div>

          <button type="submit" class="button primary">
            <i class="fas fa-save"></i> Zapisz avatar
          </button>
        </fieldset>
      </form>

      <form method="POST" action="/profile/update" class="profile-form">
        <fieldset>
          <legend><i class="fas fa-user"></i> Twoje dane</legend>

          <div class="form-group">
            <label for="first_name">Imię:</label>
            <input id="first_name" name="first_name"
                   value="<?= htmlspecialchars($user['first_name'] ?? '') ?>"
                   required>
          </div>

          <div class="form-group">
            <label for="last_name">Nazwisko:</label>
            <input id="last_name" name="last_name"
                   value="<?= htmlspecialchars($user['last_name'] ?? '') ?>"
                   required>
          </div>

          <div class="form-group">
            <label for="email">Email:</label>
            <input id="email" type="email" name="email"
                   value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                   required>
          </div>

          <div class="form-group">
            <label for="bio">Biogram:</label>
            <textarea id="bio" name="bio" rows="4"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
          </div>

          <button type="submit" class="button primary">
            <i class="fas fa-save"></i> Zapisz profil
          </button>
        </fieldset>
      </form>

      <form method="POST" action="/profile/emergency-contact" class="emergency-contact-form">
        <fieldset>
         <legend> <i class="fas fa-phone-alt"></i>Edytuj kontakt alarmowy</legend>
          <div class="form-group">
        <label for="contact_name">Imię i nazwisko:</label>
        <input id="contact_name" name="contact_name"
               value="<?= htmlspecialchars($contact['contact_name'] ?? '') ?>"
               required>
      </div>

      <div class="form-group">
        <label for="contact_phone">Telefon:</label>
        <input id="contact_phone" name="contact_phone"
               value="<?= htmlspecialchars($contact['phone'] ?? '') ?>"
               required>
      </div>

      <div class="form-group">
        <label for="contact_relation">Relacja:</label>
        <input id="contact_relation" name="contact_relation"
               value="<?= htmlspecialchars($contact['relation'] ?? '') ?>"
               required>
      </div>

      <button type="submit" class="button primary">
        <i class="fas fa-save"></i> Zapisz kontakt alarmowy
      </button>
         
        </fieldset>
      </form>

      <form method="POST" action="/profile/change-password" class="password-form">
        <fieldset>
          <legend><i class="fas fa-key"></i> Zmiana hasła</legend>

          <div class="form-group">
            <label for="old_password">Obecne hasło:</label>
            <input id="old_password" type="password" name="old_password" required>
          </div>

          <div class="form-group">
            <label for="password">Nowe hasło:</label>
            <input id="password"
                   type="password"
                   name="password"
                   minlength="8"
                   pattern="(?=.*[A-Z])(?=.*[a-z])(?=.*\d).+"
                   title="Hasło musi mieć min. 8 znaków, zawierać wielką i małą literę oraz cyfrę."
                   required>
            <p class="password-requirements">
              Hasło musi spełniać wszystkie poniższe warunki:
              <ul>
                <li>Minimum 8 znaków</li>
                <li>Przynajmniej jedna wielka litera (A–Z)</li>
                <li>Przynajmniej jedna mała litera (a–z)</li>
                <li>Przynajmniej jedna cyfra (0–9)</li>
              </ul>
            </p>
          </div>

          <div class="form-group">
            <label for="password_confirm">Powtórz hasło:</label>
            <input id="password_confirm" type="password" name="password_confirm" required>
          </div>

          <button type="submit" class="button primary">
            <i class="fas fa-check"></i> Zmień hasło
          </button>
        </fieldset>
      </form>

      <section class="notifications-settings">
        <h2><i class="fas fa-bell"></i> Ustawienia powiadomień</h2>
        <form method="POST" action="/profile/save-notifications">
          <div class="checkbox-group">
            <label>
              <input type="checkbox" name="notify_services"
                     <?= !empty($user['notify_services']) ? 'checked' : '' ?>>
              Przypomnienia o polisie i przeglądzie
            </label>
          </div>
          <div class="checkbox-group">
            <label>
              <input type="checkbox" name="notify_routes"
                     <?= !empty($user['notify_routes']) ? 'checked' : '' ?>>
              Powiadomienia o nowych trasach
            </label>
          </div>
          <div class="checkbox-group">
            <label>
              <input type="checkbox" name="notify_destinations"
                     <?= !empty($user['notify_destinations']) ? 'checked' : '' ?>>
              Powiadomienia o nowych destynacjach
            </label>
          </div>
          <button type="submit" class="button primary">
            <i class="fas fa-save"></i> Zapisz ustawienia
          </button>
        </form>
      </section>

      <div class="danger-zone">
        <form method="POST" action="/profile/delete-account" onsubmit="return confirm('Na pewno chcesz usunąć konto?')">
          <button type="submit" class="button danger">
            <i class="fas fa-user-slash"></i> Usuń konto
          </button>
        </form>
      </div>

      <p class="back-link">
        <a href="/profile" class="button primary">
          <i class="fas fa-arrow-left"></i> Powrót do profilu
        </a>
      </p>
    </div>
  </div>
</body>
</html>
