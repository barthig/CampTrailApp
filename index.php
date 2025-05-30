<?php
declare(strict_types=1);

// Załaduj konfigurację i utwórz instancję PDO
require_once __DIR__ . '/src/config.php';

// Inicjalizacja sesji przez SessionManager
require_once __DIR__ . '/src/Core/SessionManager.php';
use src\Core\SessionManager;
SessionManager::start();

// Wczytaj router oraz definicje tras
require_once __DIR__ . '/Routing.php';
require_once __DIR__ . '/src/RouterConfig.php';

// Pobranie "ładnego" URL (np. ?url=...)
$rawUrl = $_GET['url'] ?? '';

// Normalizacja ścieżki (usunięcie wiodących i końcowych slashes)
$path = trim($rawUrl, '/');

// Uruchomienie routera z wstrzykniętym połączeniem PDO
Router::run($path, $pdo);
