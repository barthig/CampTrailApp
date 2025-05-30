<?php
declare(strict_types=1);

$isLoggedIn = !empty($_SESSION['user_id']);


$defaultAvatar = '/public/img/default-avatar.png';

if ($isLoggedIn && !empty($_SESSION['avatar'])) {
  $avatarUrl = $_SESSION['avatar'];
} else {
  $avatarUrl = $defaultAvatar;
}


$avatarUrl = htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8');
?>

<nav class="sidebar">
  <div class="sidebar-top">
    <?php if ($isLoggedIn): ?>
     
      <a href="/dashboard" class="sidebar-logo" title="CampTrail">
        <img src="/public/img/logo_small.svg" alt="CampTrail Logo">
      </a>
      <!-- Menu dla zalogowanych -->
      <ul class="sidebar-menu">
        <li><a href="/dashboard" title="Dashboard"><i class="fas fa-home"></i></a></li>
        <li><a href="/destinations" title="Destiantions"><i class="fas fa-map-marker-alt"></i></a></li>
        <li><a href="/routes" title="Twoje trasy"><i class="fas fa-route"></i></a></li>
        <li><a href="/campers" title="Moje kampery"><i class="fas fa-caravan"></i></a></li>
        <li class="has-notifications"><a href="/notifications" title="Powiadomienia"><i class="fas fa-bell"></i>
            <span id="notif-count" class="badge" style="display:none">0</span></a>
        </li>
      </ul>
    <?php else: ?>
     
      <a href="/" class="sidebar-logo" title="CampTrail">
        <img src="/public/img/logo_small.svg" alt="CampTrail Logo">
      </a>
      <!-- Menu dla niezalogowanych -->
      <ul class="sidebar-menu">
        <li><a href="/" title="Strona główna"><i class="fas fa-home"></i></a></li>
        <li><a href="/login" title="Zaloguj się"><i class="fas fa-sign-in-alt"></i></a></li>
        <li><a href="/register" title="Zarejestruj się"><i class="fas fa-user-plus"></i></a></li>
      </ul>
    <?php endif; ?>
  </div>

  <?php if ($isLoggedIn): ?>
    <!-- Profil i wylogowanie dla zalogowanych -->
    <div class="sidebar-bottom">
      <a href="/profile" class="profile-link" title="Profil"> <img src="<?= $avatarUrl ?>" alt="Avatar użytkownika"> </a>
      <a href="/logout" class="logout-link" title="Wyloguj się">
        <i class="fas fa-sign-out-alt"></i>
      </a>
    </div>
  <?php endif; ?>
</nav>