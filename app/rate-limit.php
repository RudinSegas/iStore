<?php
declare(strict_types=1);
final class RateLimitException extends RuntimeException {
    public function __construct(public readonly int $retryAfter) { parent::__construct('rate_limited'); }
}
/** Reserve several fixed-window limits atomically; table names are never user supplied. */
function reserveRate(PDO $db, string $table, array $rules, string $secret, ?int $now = null): void {
    if (!in_array($table, ['request_rate_limits', 'ai_rate_limits'], true)) throw new LogicException('Invalid counter table');
    $now ??= time();
    $rows = [];
    foreach ($rules as [$scope, $seconds, $limit]) {
        if ($seconds < 1 || $limit < 0) throw new LogicException('Invalid rate configuration');
        $rows[] = [hash_hmac('sha256', $scope, $secret), intdiv($now, $seconds) * $seconds, $limit, $seconds];
    }
    usort($rows, static fn($a, $b) => strcmp($a[0], $b[0]) ?: $a[1] <=> $b[1]);
    $db->beginTransaction();
    try {
        foreach ($rows as [$hash, $bucket, $limit, $seconds]) {
            query($db, "INSERT INTO $table (scope_hash,bucket,hits) VALUES (?,?,0) ON DUPLICATE KEY UPDATE hits=hits", [$hash, $bucket]);
            $hits = (int)query($db, "SELECT hits FROM $table WHERE scope_hash=? AND bucket=? FOR UPDATE", [$hash, $bucket])->fetchColumn();
            if ($hits >= $limit) throw new RateLimitException(max(1, $bucket + $seconds - $now));
            query($db, "UPDATE $table SET hits=hits+1 WHERE scope_hash=? AND bucket=?", [$hash, $bucket]);
        }
        $db->commit();
    } catch (Throwable $error) {
        if ($db->inTransaction()) $db->rollBack();
        throw $error;
    }
    query($db, "DELETE FROM $table WHERE bucket < ? LIMIT 100", [$now - 172800]);
}
function applicationRate(PDO $db, array $config, string $action, string $identity = ''): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $secret = (string)($config['app_secret'] ?: hash('sha256', 'istore-limits|' . $config['db_password'] . '|' . $config['db_name']));
    $rules = match ($action) {
        'login' => [['login:ip:' . $ip, 600, 30], ['login:email:' . $identity, 600, 12]],
        'register' => [['register:ip:' . $ip, 3600, 8]],
        'contact' => [['contact:ip:' . $ip, 3600, 6]],
        default => throw new LogicException('Invalid action limit'),
    };
    reserveRate($db, 'request_rate_limits', $rules, $secret);
}
