<?php
declare(strict_types=1);
ini_set('display_errors', '0');
ini_set('zend.exception_ignore_args', '1');
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/rate-limit.php';
set_exception_handler(static function (Throwable $error): void {
    // No exception messages/traces: they may contain database details or customer input.
    error_log('iStore request failure: ' . get_class($error));
    failPage($error instanceof InvalidArgumentException ? 400 : 503);
});
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'self'; connect-src 'self'; img-src 'self'; style-src 'self'; script-src 'self'; object-src 'none'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'");
header('Referrer-Policy: same-origin');
header('Cache-Control: no-store');
header('Content-Type: text/html; charset=utf-8');
$settingsPath = dirname(__DIR__) . '/config/';
$config = require $settingsPath . 'config.example.php';
if (is_file($settingsPath . 'config.php')) $config = array_replace($config, require $settingsPath . 'config.php');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_name('istore_session');
$isHttps = !empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off';
session_set_cookie_params(['httponly'=>true, 'secure'=>$isHttps || (bool)$config['secure_cookies'], 'samesite'=>'Lax', 'path'=>'/']);
if (!session_start()) failPage(503);
// Bound authenticated sessions; never trust forwarding headers supplied by a visitor.
if (!empty($_SESSION['user']) && time() - (int)($_SESSION['last_active'] ?? time()) > 7200) {
    unset($_SESSION['user'], $_SESSION['checkout_token'], $_SESSION['ai'], $_SESSION['ai_pending']);
    session_regenerate_id(true);
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$_SESSION['last_active'] = time();
try {
    $db = new PDO("mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']};charset=utf8mb4", $config['db_user'], $config['db_password'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false]);
} catch (PDOException) {
    error_log('iStore database unavailable');
    failPage(503);
}
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
$_SESSION['cart'] ??= [];
