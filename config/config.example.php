<?php
return [
    'db_host' => getenv('DB_HOST') ?: '127.0.0.1',
    'db_port' => getenv('DB_PORT') ?: '3306',
    'db_name' => getenv('DB_NAME') ?: 'istore',
    'db_user' => getenv('DB_USER') ?: 'istore',
    'db_password' => getenv('DB_PASSWORD') ?: '',
    'secure_cookies' => getenv('SESSION_SECURE_COOKIE') === '1',
];
