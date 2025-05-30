<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$dsn     = 'pgsql:host=db;dbname=kamper_app';
$dbUser  = 'postgres';
$dbPass  = 'password';

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log('Błąd połączenia z bazą: ' . $e->getMessage());
    die('Wystąpił błąd wewnętrzny.');
}

$is_logged_in = !empty($_SESSION['user_id']);
