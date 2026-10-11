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
