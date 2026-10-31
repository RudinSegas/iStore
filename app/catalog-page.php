<?php
declare(strict_types=1);
// Query parameters have already been validated before any markup is written.
$where = []; $params = [];
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $like = '%' . strtr($q, ['!'=>'!!', '%'=>'!%', '_'=>'!_']) . '%';
    $where[] = "(name LIKE ? ESCAPE '!' OR short_description LIKE ? ESCAPE '!' OR description LIKE ? ESCAPE '!')";
    array_push($params, $like, $like, $like);
}
if (($_GET['category'] ?? '') !== '') { $where[] = 'category_id=?'; $params[] = (int)$_GET['category']; }
$minimum = priceInput($_GET['min_price'] ?? '');
$maximum = priceInput($_GET['max_price'] ?? '');
if ($minimum !== null && $maximum !== null && $minimum > $maximum) throw new InvalidArgumentException('Invalid range');
if ($minimum !== null) { $where[] = 'price_cents>=?'; $params[] = $minimum; }
if ($maximum !== null) { $where[] = 'price_cents<=?'; $params[] = $maximum; }
$sqlWhere = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$totalProducts = (int)query($db, 'SELECT COUNT(*) FROM products' . $sqlWhere, $params)->fetchColumn();
$catalogPage = max(1, (int)($_GET['pg'] ?? 1));
$totalPages = max(1, (int)ceil($totalProducts / 24));
$catalogPage = min($catalogPage, $totalPages);
$sort = ['price_asc'=>'price_cents ASC,id ASC', 'price_desc'=>'price_cents DESC,id ASC', 'new'=>'id DESC'][$_GET['sort'] ?? 'new'];
$products = query($db, 'SELECT * FROM products' . $sqlWhere . ' ORDER BY ' . $sort . ' LIMIT 24 OFFSET ' . (($catalogPage - 1) * 24), $params)->fetchAll();
