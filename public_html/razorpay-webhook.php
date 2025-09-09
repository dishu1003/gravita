<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }

$body = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? ($_SERVER['X_RAZORPAY_SIGNATURE'] ?? '');

$secret = get_setting($pdo, 'razorpay_webhook_secret', RAZORPAY_WEBHOOK_SECRET);
if (!$signature || !$secret) { http_response_code(400); exit('missing'); }

if (!verify_razorpay_signature($body, $signature, $secret)) {
    log_error('webhook_sig_invalid', new RuntimeException('Invalid signature'));
    http_response_code(400); echo 'invalid'; exit;
}

$data = json_decode($body, true);
if (!is_array($data)) { http_response_code(200); exit('ok'); }

$eventId = $data['id'] ?? null;
if ($eventId && webhook_seen($pdo, $eventId)) { http_response_code(200); exit('seen'); }
if ($eventId) { webhook_remember($pdo, $eventId, $body); }

try {
    $rzpOrderId = null;
    $paymentId = null;

    if (!empty($data['payload']['payment']['entity'])) {
        $entity = $data['payload']['payment']['entity'];
        $rzpOrderId = $entity['order_id'] ?? null;
        $paymentId = $entity['id'] ?? null;
    } elseif (!empty($data['payload']['order']['entity'])) {
        $entity = $data['payload']['order']['entity'];
        $rzpOrderId = $entity['id'] ?? null;
    }

    if ($rzpOrderId) {
        $stmt = $pdo->prepare("SELECT id FROM orders WHERE razorpay_order_id = ? LIMIT 1");
        $stmt->execute([$rzpOrderId]);
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $oid = (int)$row['id'];
            $upd = $pdo->prepare("UPDATE orders SET status='paid', razorpay_payment_id=?, razorpay_signature=? WHERE id=?");
            $upd->execute([$paymentId, $signature, $oid]);
            decrement_stock_for_order($pdo, $oid);
        }
    }
} catch (Throwable $e) { log_error('webhook_process', $e); }

http_response_code(200); echo 'ok';