<?php
declare(strict_types=1);

function sanitize_string(?string $value): string {
    $value = (string)$value;
    $value = trim($value);
    $value = str_replace("\0", '', $value);
    return $value;
}

function csrf_token(): string {
    if (empty($_SESSION['_csrf'])) { $_SESSION['_csrf'] = bin2hex(random_bytes(32)); }
    return $_SESSION['_csrf'];
}
function csrf_validate(?string $token): bool {
    return is_string($token ?? null) && hash_equals($_SESSION['_csrf'] ?? '', $token ?? '');
}

function is_logged_in(): bool { return !empty($_SESSION['user_id']); }
function is_admin(): bool { return !empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'; }
function require_admin(): void { if (!is_admin()) { header('Location: /admin/login.php'); exit; } }

function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return $text ?: 'n-a';
}

// Cart
function get_cart(): array { return $_SESSION['cart'] ?? []; }
function get_cart_count(?PDO $pdo = null): int { $c=0; foreach (get_cart() as $q){$c+=(int)$q;} return $c; }
function cart_add(?PDO $pdo = null, int $productId, int $qty = 1): bool { if (!isset($_SESSION['cart'])) $_SESSION['cart']=[]; $_SESSION['cart'][$productId]=($_SESSION['cart'][$productId]??0)+max(1,$qty); return true; }
function cart_set(?PDO $pdo = null, int $productId, int $qty): void { if ($qty<=0){unset($_SESSION['cart'][$productId]);} else {$_SESSION['cart'][$productId]=$qty;} }
function cart_clear(): void { unset($_SESSION['cart']); }
function cart_items(?PDO $pdo = null): array {
    if ($pdo === null) return [];
    $items=[]; $cart=get_cart(); if (empty($cart)) return [];
    $ids = array_map('intval', array_keys($cart)); $in = implode(',', array_fill(0, count($ids), '?'));
    try {
        $stmt = $pdo->prepare("SELECT id, name, slug, price, mrp, image FROM products WHERE id IN ($in)");
        $stmt->execute($ids);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
            $pid=(int)$p['id']; $qty=(int)($cart[$pid]??0); if ($qty<=0) continue;
            $p['qty']=$qty; $p['subtotal']=$qty*(float)$p['price']; $items[]=$p;
        }
    } catch (Throwable $e) { log_error('cart_items', $e); }
    return $items;
}
function get_settings(?PDO $pdo = null): array { static $cache=null; if($cache!==null) return $cache;
    if ($pdo === null) return [];
    try { $stmt=$pdo->prepare("SELECT `key`,`value` FROM settings"); $stmt->execute(); $cache=$stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: []; return $cache; }
    catch(Throwable $e){ log_error('get_settings', $e); return []; } }
function get_setting(?PDO $pdo = null, string $key, ?string $default=null): ?string { $s=get_settings($pdo); return $s[$key] ?? $default; }
function cart_totals(?PDO $pdo = null): array {
    if ($pdo === null) return ['subtotal'=>0.0,'tax'=>0.0,'shipping'=>0.0,'total'=>0.0];
    $subtotal=0.0; foreach (cart_items($pdo) as $i){ $subtotal+=(float)$i['subtotal']; }
    $taxPercent=(float)(get_setting($pdo,'tax_percent','0') ?? 0); $shippingFlat=(float)(get_setting($pdo,'shipping_flat','0') ?? 0);
    $tax=$subtotal*($taxPercent/100); $total=$subtotal+$tax+$shippingFlat;
    return ['subtotal'=>$subtotal,'tax'=>$tax,'shipping'=>$shippingFlat,'total'=>$total];
}

// Upload
function upload_image(array $file, string $targetDir): array {
    $r=['success'=>false,'filename'=>null,'error'=>null];
    if (!isset($file['tmp_name']) || $file['error']!==UPLOAD_ERR_OK){ $r['error']='Upload failed with error code ' . $file['error']; return $r; }
    try {
        $finfo=new finfo(FILEINFO_MIME_TYPE); $mime=$finfo->file($file['tmp_name']); $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
        if (!isset($allowed[$mime])){ $r['error']='Unsupported file type'; return $r; }
        if ($file['size']>2*1024*1024){ $r['error']='File too large (max 2MB)'; return $r; }
        $name=bin2hex(random_bytes(32)).'.'.$allowed[$mime]; $dest=rtrim($targetDir,'/\\').DIRECTORY_SEPARATOR.$name;
        if (!move_uploaded_file($file['tmp_name'],$dest)){ $r['error']='Could not save uploaded file'; return $r; }
        $r['success']=true; $r['filename']=$name;
    } catch (Throwable $e) {
        log_error('upload_image', $e);
        $r['error'] = 'An internal error occurred during file upload.';
    }
    return $r;
}

// Email
function send_email(?PDO $pdo, string $to, string $subject, string $htmlBody, ?string $textBody=null): bool {
    // Fallback to mail() if PDO is not provided for some reason
    if ($pdo === null) {
        $headers=['MIME-Version: 1.0','Content-type: text/html; charset=UTF-8','From: '.SMTP_FROM_NAME.' <no-reply@example.com>'];
        return mail($to, $subject, $htmlBody, implode("\r\n",$headers));
    }

    // The Composer autoloader is included in config.php, so we can use PHPMailer directly.
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = get_setting($pdo, 'smtp_host', 'localhost');
            $mail->Port = (int)get_setting($pdo, 'smtp_port', 587);
            $mail->Username = get_setting($pdo, 'smtp_user');
            $mail->Password = get_setting($pdo, 'smtp_pass');
            $mail->SMTPAuth = !empty($mail->Username) && !empty($mail->Password);
            $mail->SMTPSecure = $mail->Port === 587 ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS : false;
            $mail->CharSet = 'UTF-8';

            $fromUser = get_setting($pdo, 'smtp_user', 'no-reply@example.com');
            $mail->setFrom($fromUser, SMTP_FROM_NAME);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $textBody ?? strip_tags($htmlBody);
            return $mail->send();
        } catch (Throwable $e) {
            log_error('send_email_phpmailer', $e);
            return false;
        }
    }

    // Fallback for systems where PHPMailer might fail but mail() works.
    $fromUser = get_setting($pdo, 'smtp_user', 'no-reply@example.com');
    $headers=['MIME-Version: 1.0','Content-type: text/html; charset=UTF-8','From: '.SMTP_FROM_NAME.' <'.$fromUser.'>'];
    return mail($to, $subject, $htmlBody, implode("\r\n",$headers));
}

// Auth
function login_user(PDO $pdo, string $email, string $password): bool {
    $stmt=$pdo->prepare("SELECT id,name,email,password,role FROM users WHERE email=? LIMIT 1");
    $stmt->execute([sanitize_string($email)]);
    $user=$stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) return false;
    if (!empty($user['password']) && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id']=(int)$user['id']; $_SESSION['user_name']=(string)$user['name']; $_SESSION['user_role']=(string)$user['role'];
        return true;
    }
    return false;
}
function register_user(PDO $pdo, string $name, string $email, string $password): array {
    $name=sanitize_string($name); $email=filter_var($email,FILTER_VALIDATE_EMAIL)?$email:''; if(!$email||strlen($name)<2||strlen($password)<8){ return ['success'=>false,'error'=>'Invalid data']; }
    $hash=password_hash($password,PASSWORD_DEFAULT);
    try { $stmt=$pdo->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,'customer')"); $stmt->execute([$name,$email,$hash]); return ['success'=>true]; }
    catch(Throwable $e){ log_error('register_user',$e); return ['success'=>false,'error'=>'Email already in use']; }
}

// Orders
function create_order(PDO $pdo, int $userId, array $cartItems, array $totals, array $customer): array {
    $pdo->beginTransaction();
    try {
        // Some hosts may not like NULL via ? when strict; ensure null typed:
        $uid = $userId ?: null;
        $stmt=$pdo->prepare("INSERT INTO orders (user_id,status,total_amount,tax_amount,shipping_amount,customer_name,customer_email,customer_phone,shipping_address) VALUES (?, 'pending', ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$uid, $totals['total'], $totals['tax'], $totals['shipping'], $customer['name'] ?? '', $customer['email'] ?? '', $customer['phone'] ?? '', $customer['address'] ?? '']);
        $orderId=(int)$pdo->lastInsertId();

        $oi=$pdo->prepare("INSERT INTO order_items (order_id,product_id,product_name,unit_price,quantity,line_total) VALUES (?,?,?,?,?,?)");
        foreach ($cartItems as $item) {
            $oi->execute([$orderId,(int)$item['id'],$item['name'],(float)$item['price'],(int)$item['qty'],(float)$item['subtotal']]);
        }
        $pdo->commit();
        return ['success'=>true,'order_id'=>$orderId];
    } catch (Throwable $e) {
        $pdo->rollBack(); log_error('create_order',$e); return ['success'=>false,'error'=>'Could not create order'];
    }
}
function update_order_status(PDO $pdo, int $orderId, string $status): bool {
    $allowed=['pending','paid','processing','shipped','delivered','canceled']; if(!in_array($status,$allowed,true)) return false;
    try { $stmt=$pdo->prepare("UPDATE orders SET status=?, updated_at=CURRENT_TIMESTAMP WHERE id=?"); return $stmt->execute([$status,$orderId]); }
    catch(Throwable $e){ log_error('update_order_status',$e); return false; }
}

// Inventory after payment
function decrement_stock_for_order(PDO $pdo, int $orderId): void {
    try {
        $items=$pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id=?");
        $items->execute([$orderId]);
        $upd=$pdo->prepare("UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ?");
        foreach ($items->fetchAll(PDO::FETCH_ASSOC) as $it) {
            if (!empty($it['product_id'])) { $upd->execute([(int)$it['quantity'], (int)$it['product_id']]); }
        }
    } catch (Throwable $e) { log_error('decrement_stock_for_order',$e); }
}

// Razorpay
function create_razorpay_order(PDO $pdo, string $receipt, int $amountPaise, string $currency='INR'): array {
    $keyId = get_setting($pdo, 'razorpay_key_id');
    $secret = get_setting($pdo, 'razorpay_key_secret');
    if (empty($keyId) || empty($secret)) {
        throw new RuntimeException('Razorpay API keys are not configured.');
    }
    $payload=json_encode(['amount'=>$amountPaise,'currency'=>$currency,'receipt'=>$receipt,'payment_capture'=>1]);
    $ch=curl_init('https://api.razorpay.com/v1/orders');
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$payload,CURLOPT_HTTPHEADER=>['Content-Type: application/json'],CURLOPT_USERPWD=>$keyId.':'.$secret]);
    $response=curl_exec($ch); if($response===false){$err=curl_error($ch); curl_close($ch); throw new RuntimeException('Razorpay create order failed: '.$err);}
    $code=curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch); if($code>=400){ throw new RuntimeException('Razorpay create order HTTP '.$code.' => '.$response); }
    $data=json_decode($response,true); if(!is_array($data)||empty($data['id'])){ throw new RuntimeException('Invalid Razorpay order response'); }
    return $data;
}
function verify_razorpay_signature(string $payload, string $signature, string $secret): bool {
    $expected=hash_hmac('sha256',$payload,$secret); return hash_equals($expected,$signature);
}

// Webhook idempotency
function webhook_seen(PDO $pdo, string $eventId): bool {
    try { $st=$pdo->prepare("SELECT 1 FROM webhook_events WHERE event_id=? LIMIT 1"); $st->execute([$eventId]); return (bool)$st->fetchColumn(); }
    catch(Throwable $e){ log_error('webhook_seen',$e); return false; }
}
function webhook_remember(PDO $pdo, string $eventId, string $payload): void {
    try {
        $st=$pdo->prepare("INSERT INTO webhook_events (event_id, payload) VALUES (?, ?)");
        $st->execute([$eventId, $payload]);
    } catch (Throwable $e) {
        // Log error only if it's not a duplicate key exception
        if (strpos($e->getMessage(), 'Duplicate entry') === false) {
            log_error('webhook_remember', $e);
        }
    }
}