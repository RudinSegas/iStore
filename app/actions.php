<?php
declare(strict_types=1);
if (!hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) { http_response_code(403); exit('Invalid CSRF token'); }
$action = (string)($_POST['action'] ?? '');
$return = '/?page=cart';
try {
    switch ($action) {
        case 'register':
        case 'login':
            $return = '/?page=' . $action;
            $email = strtolower(trim((string)($_POST['email'] ?? '')));
            $password = (string)($_POST['password'] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190 || strlen($password) > 72) throw new RuntimeException('יש להזין כתובת אימייל וסיסמה תקינות.');
            if ($action === 'register') {
                $name = trim((string)($_POST['name'] ?? ''));
                if ($name === '' || strlen($name) > 180 || strlen($password) < 8) throw new RuntimeException('יש להזין שם וסיסמה באורך 8–72 תווים באנגלית.');
                try { query($db, 'INSERT INTO users (name,email,password_hash) VALUES (?,?,?)', [$name,$email,password_hash($password, PASSWORD_DEFAULT)]); }
                catch (PDOException $ex) { if ($ex->getCode() === '23000') throw new RuntimeException('כתובת האימייל כבר רשומה.'); throw $ex; }
            }
            $attempts = (int)($_SESSION['login_attempts'] ?? 0);
            if ($attempts >= 10 && time() - (int)($_SESSION['last_attempt'] ?? 0) < 300) throw new RuntimeException('יותר מדי ניסיונות. נסו שוב בעוד חמש דקות.');
            $user = query($db, 'SELECT * FROM users WHERE email = ?', [$email])->fetch();
            if (!$user || !password_verify($password, $user['password_hash'])) {
                $_SESSION['login_attempts'] = $attempts + 1; $_SESSION['last_attempt'] = time();
                throw new RuntimeException('האימייל או הסיסמה אינם נכונים.');
            }
            session_regenerate_id(true);
            $_SESSION['user'] = ['id'=>(int)$user['id'], 'name'=>$user['name']];
            $_SESSION['csrf'] = bin2hex(random_bytes(32)); unset($_SESSION['login_attempts']);
            flash('התחברתם בהצלחה.'); $return = '/?page=catalog'; break;
        case 'logout':
            unset($_SESSION['user'], $_SESSION['checkout_token']); session_regenerate_id(true); $_SESSION['csrf'] = bin2hex(random_bytes(32));
            flash('התנתקתם בהצלחה.'); $return = '/'; break;
        case 'add':
        case 'update':
            $id = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
            $product = $id ? query($db, 'SELECT id,stock FROM products WHERE id=?', [$id])->fetch() : false;
            if (!$product) throw new RuntimeException('המוצר לא נמצא.');
            $quantity = validQuantity($_POST['quantity'] ?? 1);
            if ($action === 'add') $quantity += $_SESSION['cart'][$id] ?? 0;
            if ($quantity > 20 || $quantity > (int)$product['stock']) throw new RuntimeException('אין מספיק מלאי או שהכמות עולה על 20.');
            if ($quantity === 0) unset($_SESSION['cart'][$id]); else $_SESSION['cart'][$id] = $quantity;
            unset($_SESSION['checkout_token']); flash('סל הקניות עודכן.'); break;
        case 'checkout':
            if (empty($_SESSION['user'])) { flash('יש להתחבר כדי להשלים הזמנה.'); redirect('/?page=login'); }
            $token = (string)($_POST['checkout_token'] ?? '');
            if (!$token || !hash_equals((string)($_SESSION['checkout_token'] ?? ''), $token)) throw new RuntimeException('ההזמנה כבר נשלחה או פג תוקפה.');
            $address = trim((string)($_POST['address'] ?? ''));
            if (strlen($address) < 5 || strlen($address) > 500) throw new RuntimeException('יש להזין כתובת משלוח מלאה.');
            if (!$_SESSION['cart']) throw new RuntimeException('סל הקניות ריק.');
            $db->beginTransaction(); $items = []; $total = 0;
            $cart = $_SESSION['cart']; ksort($cart);
            foreach ($cart as $id => $qty) {
                $p = query($db, 'SELECT * FROM products WHERE id=? FOR UPDATE', [$id])->fetch();
                if (!$p || (int)$p['stock'] < $qty) throw new RuntimeException('המלאי השתנה. עדכנו את סל הקניות ונסו שוב.');
                $items[] = [$p,$qty]; $total += (int)$p['price_cents'] * $qty;
            }
            query($db, 'INSERT INTO orders (user_id,total_cents,address,checkout_token) VALUES (?,?,?,?)', [$_SESSION['user']['id'],$total,$address,$token]);
            $orderId = (int)$db->lastInsertId();
            foreach ($items as [$p,$qty]) {
                query($db, 'INSERT INTO order_items (order_id,product_id,product_name,unit_price_cents,quantity) VALUES (?,?,?,?,?)', [$orderId,$p['id'],$p['name'],$p['price_cents'],$qty]);
                query($db, 'UPDATE products SET stock=stock-? WHERE id=?', [$qty,$p['id']]);
            }
            $db->commit(); $_SESSION['cart'] = []; unset($_SESSION['checkout_token']);
            flash('הזמנת ההדגמה נשמרה בהצלחה! לא בוצע חיוב.'); $return = '/?page=orders'; break;
        case 'contact':
            $return = '/#contact';
            $name = trim((string)($_POST['name'] ?? '')); $email = trim((string)($_POST['email'] ?? '')); $phone = trim((string)($_POST['phone'] ?? ''));
            if (!$name || strlen($name)>180 || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>190 || !preg_match('/^[0-9+ ()-]{7,25}$/', $phone)) throw new RuntimeException('יש למלא שם, אימייל ומספר טלפון תקינים.');
            if (time() - (int)($_SESSION['contact_time'] ?? 0) < 60) throw new RuntimeException('הפרטים כבר נשלחו. נסו שוב בעוד דקה.');
            query($db,'INSERT INTO contact_requests (name,email,phone) VALUES (?,?,?)',[$name,$email,$phone]); $_SESSION['contact_time']=time(); flash('הפרטים נשמרו בהצלחה.'); break;
        default: http_response_code(400); exit('Unknown action');
    }
} catch (RuntimeException $ex) {
    if ($db->inTransaction()) $db->rollBack();
    if ($ex instanceof PDOException) { error_log('iStore database action failed: '.$ex->getCode()); flash('אירעה תקלה. נסו שוב מאוחר יותר.'); }
    else flash($ex->getMessage());
}
redirect($return);
