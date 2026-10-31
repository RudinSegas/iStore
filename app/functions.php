<?php
declare(strict_types=1);
function e(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function money(int $cents): string { return '₪' . number_format($cents / 100, 2); }
function query(PDO $db, string $sql, array $params = []): PDOStatement { $stmt = $db->prepare($sql); $stmt->execute($params); return $stmt; }
function redirect(string $url): never { header('Location: ' . $url, true, 303); exit; }
function flash(string $message): void { $_SESSION['flash'] = $message; }
function csrf(): string { return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">'; }
function cartRows(PDO $db): array {
    if (!$_SESSION['cart']) return [];
    $ids = array_keys($_SESSION['cart']);
    $rows = query($db, 'SELECT * FROM products WHERE id IN (' . implode(',', array_fill(0, count($ids), '?')) . ') ORDER BY id', $ids)->fetchAll();
    foreach ($rows as &$row) { $row['quantity'] = $_SESSION['cart'][$row['id']]; $row['subtotal'] = (int)$row['price_cents'] * $row['quantity']; }
    return $rows;
}
function validQuantity(mixed $value): int {
    $n = filter_var($value, FILTER_VALIDATE_INT);
    if ($n === false || $n < 0 || $n > 20) throw new RuntimeException('הכמות חייבת להיות בין 0 ל־20.');
    return $n;
}

function scalarInput(array $input, string $key, string $default = '', int $maxBytes = 4096): string {
    if (!array_key_exists($key, $input)) return $default;
    $value = $input[$key];
    if (!is_string($value) || strlen($value) > $maxBytes || !preg_match('//u', $value) || str_contains($value, "\0")) {
        throw new InvalidArgumentException('Invalid input');
    }
    return $value;
}
function positiveId(string $value): int {
    if (!preg_match('/^[1-9][0-9]{0,9}$/D', $value) || (int)$value > 2147483647) throw new InvalidArgumentException('Invalid identifier');
    return (int)$value;
}
function priceInput(string $value): ?int {
    if ($value === '') return null;
    if (!preg_match('/^(\d{1,7})(?:\.(\d{1,2}))?$/D', $value, $match)) throw new InvalidArgumentException('Invalid price');
    $price = (int)$match[1] * 100 + (int)str_pad($match[2] ?? '', 2, '0');
    if ($price > 100000000) throw new InvalidArgumentException('Invalid price');
    return $price;
}
function imageFile(mixed $name): string {
    return is_string($name) && preg_match('/^[a-zA-Z0-9_-]+\.(svg|png|jpe?g|webp)$/D', $name) ? $name : 'hero.svg';
}
function failPage(int $status): never {
    http_response_code($status);
    if (ob_get_level() > 0) ob_clean();
    $message = $status === 400 ? 'הבקשה אינה תקינה. בדקו את הפרטים ונסו שוב.' : ($status === 403 ? 'תוקף הבקשה פג. רעננו את הדף ונסו שוב.' : 'השירות אינו זמין כרגע. נסו שוב בעוד רגע.');
    if (defined('ISTORE_JSON')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok'=>false,'error'=>'request','message'=>$message], JSON_UNESCAPED_UNICODE);
    } else {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><html lang="he" dir="rtl"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>iStore</title><link rel="stylesheet" href="/assets/style.css"><main class="container"><section class="empty"><h1>'.e($message).'</h1><a class="button dark" href="/">בחזרה לחנות</a></section></main></html>';
    }
    exit;
}
