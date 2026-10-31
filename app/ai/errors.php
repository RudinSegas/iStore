<?php
declare(strict_types=1);
final class AiError extends RuntimeException {
    public function __construct(public readonly string $kind, public readonly int $status = 503,
        public readonly int $retryAfter = 0) { parent::__construct($kind); }
}
function aiErrorMessage(string $kind): string {
    return match ($kind) {
        'input' => 'יש להזין הודעה תקינה באורך של עד 1,000 תווים.',
        'csrf' => 'תוקף החלון פג. רעננו את הדף ונסו שוב.',
        'method' => 'סוג הבקשה אינו נתמך.',
        'rate' => 'נשלחו יותר מדי הודעות. נסו שוב בעוד כדקה.',
        'quota' => 'מכסת העוזר נוצלה כרגע. אפשר להמשיך לחפש בקטלוג או לנסות מאוחר יותר.',
        'catalog_changed' => 'פרטי הקטלוג השתנו במהלך התשובה. נסו שוב לקבלת נתונים עדכניים.',
        'busy' => 'כבר נשלחה הודעה. המתינו לסיום התשובה.',
        'timeout' => 'העוזר לא הספיק לענות. אפשר לנסות שוב או לחפש בקטלוג.',
        'disabled' => 'עוזר הקניות אינו זמין כרגע. הקטלוג ממשיך לפעול כרגיל.',
        default => 'לא ניתן לקבל תשובה כרגע. נסו שוב מאוחר יותר או חפשו בקטלוג.',
    };
}
function aiJson(array $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
    exit;
}
function aiReadRequest(string $raw, string $contentType): array {
    if (!str_starts_with(strtolower($contentType), 'application/json') || strlen($raw) > 8192) throw new AiError('input', 400);
    try { $value = json_decode($raw, true, 8, JSON_THROW_ON_ERROR); }
    catch (JsonException) { throw new AiError('input', 400); }
    if (!is_array($value) || array_is_list($value)) throw new AiError('input', 400);
    $action = $value['action'] ?? 'message';
    if (!is_string($action) || !in_array($action, ['message','reset','history'], true)) throw new AiError('input',400);
    if ($action !== 'message') return ['action'=>$action];
    $text = $value['message'] ?? null;
    if (!is_string($text) || !preg_match('//u',$text) || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u',$text)) throw new AiError('input',400);
    $text = trim($text);
    if ($text === '' || mb_strlen($text,'UTF-8') > 1000) throw new AiError('input',400);
    return ['action'=>'message','message'=>$text];
}
