<?php
declare(strict_types=1);
$config = require dirname(__DIR__) . '/config/' . (is_file(dirname(__DIR__) . '/config/config.php') ? 'config.php' : 'config.example.php');
ini_set('session.use_strict_mode', '1');
session_name('istore_session');
session_set_cookie_params(['httponly' => true, 'secure' => $config['secure_cookies'], 'samesite' => 'Lax', 'path' => '/']);
if (!session_start()) { http_response_code(503); exit('Session storage unavailable. Check your PHP session.save_path configuration.'); }
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'self'; img-src 'self'; style-src 'self'; script-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'");
header('Referrer-Policy: same-origin');
header('Cache-Control: no-store');
require __DIR__ . '/functions.php';
try {
    $db = new PDO("mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']};charset=utf8mb4", $config['db_user'], $config['db_password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);
} catch (PDOException $e) {
    error_log('iStore database connection failed.');
    http_response_code(503);
    exit('Database unavailable. Follow README.md to configure MySQL and import database/schema.sql and database/seed.sql.');
}
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
$_SESSION['cart'] ??= [];
