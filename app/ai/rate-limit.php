<?php
declare(strict_types=1);
// Atomic multi-scope reservation. Counters contain HMACs, never raw IPs or messages.
function aiReserve(PDO $db, array $rules, string $secret, ?int $now = null): void {
    $now ??= time(); $rows = [];
    foreach ($rules as [$scope, $seconds, $limit]) {
        $rows[] = [hash_hmac('sha256', $scope, $secret), intdiv($now, $seconds) * $seconds, max(0,(int)$limit), $seconds];
    }
    usort($rows, fn($a,$b) => strcmp($a[0],$b[0]));
    $db->beginTransaction();
    try {
        foreach ($rows as [$hash,$bucket,$limit,$seconds]) {
            query($db,'INSERT INTO ai_rate_limits (scope_hash,bucket,hits) VALUES (?,?,0) ON DUPLICATE KEY UPDATE hits=hits',[$hash,$bucket]);
            $hits = (int)query($db,'SELECT hits FROM ai_rate_limits WHERE scope_hash=? AND bucket=? FOR UPDATE',[$hash,$bucket])->fetchColumn();
            if ($hits >= $limit) throw new AiError($seconds >= 86400 ? 'quota' : 'rate',429,max(1,$bucket+$seconds-$now));
            query($db,'UPDATE ai_rate_limits SET hits=hits+1 WHERE scope_hash=? AND bucket=?',[$hash,$bucket]);
        }
        $db->commit();
    } catch (Throwable $e) { if ($db->inTransaction()) $db->rollBack(); throw $e; }
    // Bounded cleanup: only assistant counters, never customer data.
    query($db,'DELETE FROM ai_rate_limits WHERE bucket < ? LIMIT 100',[$now-172800]);
}
function aiReserveCall(PDO $db, array $config): void {
    aiReserve($db,[['global:minute',60,$config['global_calls_per_minute']],
        ['global:day',86400,$config['global_calls_per_day']]], $config['rate_secret']);
}
