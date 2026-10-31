<?php
require dirname(__DIR__) . '/app/bootstrap.php';
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET','HEAD','POST'], true)) { header('Allow: GET, HEAD, POST'); http_response_code(405); exit; }
foreach (['page','q','category','sort','id','pg','min_price','max_price'] as $key) {
    if (array_key_exists($key, $_GET)) $_GET[$key] = scalarInput($_GET, $key, '', 400);
}
foreach (['id','pg'] as $key) if (isset($_GET[$key])) positiveId($_GET[$key]);
if (($_GET['category'] ?? '') !== '') positiveId($_GET['category']);
if (isset($_GET['q']) && mb_strlen($_GET['q']) > 100) throw new InvalidArgumentException('Search too long');
if (isset($_GET['sort']) && !in_array($_GET['sort'], ['new','price_asc','price_desc'], true)) throw new InvalidArgumentException('Invalid sort');
foreach (['min_price','max_price'] as $key) priceInput($_GET[$key] ?? '');
if ($_SERVER['REQUEST_METHOD'] === 'POST') require dirname(__DIR__) . '/app/actions.php';
$page = (string)($_GET['page'] ?? 'home');
if (!in_array($page,['home','catalog','product','cart','login','register','orders'],true)) { http_response_code(404); $page='404'; }
if ($page === 'orders' && empty($_SESSION['user'])) redirect('/?page=login');
$categories = query($db,'SELECT * FROM categories ORDER BY id')->fetchAll();
$cartCount = array_sum($_SESSION['cart']);
$notice = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
if ($page === 'catalog') require dirname(__DIR__) . '/app/catalog-page.php';
ob_start();
require dirname(__DIR__) . '/app/layout.php';
ob_end_flush();
