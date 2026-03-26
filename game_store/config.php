<?php
// Настройки базы данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'game_store');
define('DB_USER', 'postgres');
define('DB_PASS', '123456');
define('DB_PORT', '5432');

// Настройки приложения
define('SITE_NAME', 'GameStore');
define('SITE_URL', 'http://localhost/game_store');

// Подключение к базе данных
try {
    $pdo = new PDO("pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Старт сессии
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>