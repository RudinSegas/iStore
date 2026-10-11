<?php
require dirname(__DIR__) . '/app/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') require dirname(__DIR__) . '/app/actions.php';
$page = (string)($_GET['page'] ?? 'home');
if (!in_array($page,['home','catalog','product','cart','login','register','orders'],true)) { http_response_code(404); $page='404'; }
if ($page === 'orders' && empty($_SESSION['user'])) redirect('/?page=login');
$categories = query($db,'SELECT * FROM categories ORDER BY id')->fetchAll();
$cartCount = array_sum($_SESSION['cart']);
$notice = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
require dirname(__DIR__) . '/app/layout.php';
