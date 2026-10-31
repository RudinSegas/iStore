<?php
declare(strict_types=1);
function aiConfig(): array {
    $base = dirname(__DIR__, 2) . '/config/';
    $settings = require $base . 'ai.example.php';
    if (is_file($base . 'ai.php')) $settings = array_replace($settings, require $base . 'ai.php');
    return $settings;
}
function aiReady(array $config): bool {
    return ($config['enabled'] ?? false) === true && is_string($config['api_key'] ?? null)
        && $config['api_key'] !== '' && !preg_match('/[\r\n]/',$config['api_key']) && strlen((string)($config['rate_secret'] ?? '')) >= 32;
}
