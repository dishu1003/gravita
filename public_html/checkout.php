<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

$items = cart_items($pdo);
if (empty($items)) { header('Location: /cart.php'); exit; }
$totals = cart_totals($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    if (!csrf_validate($_POST['_csrf'] ?? '')) { echo json_encode(['success'=>false,'error'=>'Invalid CSRF']); exit; }
    $customer = [
        'name' => sanitize_string($_POST['name'] ?? ''),
        'email' => sanitize_string($_POST['email'] ?? ''),
        'phone' => preg_replace('/[^\d+]/','', (string)($_POST['phone'] ?? '')),
        'address' => sanitize_string($_POST['address'] ?? ''),
    ];
    if (!$customer['name'] || !filter_var($customer['email'], FILTER_VALIDATE_EMAIL) || strlen($customer['address']) < 5) {
        echo json_encode(['success'=>false,'error'=>'Invalid customer details']); exit;
    }
    $userId = $_SESSION['user_id'] ?? null;
    $orderRes = create_order($pdo, (int)$userId, $items, $totals, $customer);
    if (!$orderRes['success']) { echo json_encode(['success'=>false,'error'=>'Could not create order']); exit; }
    $orderId = (int)$orderRes['order_id'];
    $amountPaise = (int)round($totals['total'] * 100);
    try {
        $rzpOrder = create_razorpay_order($pdo, 'rcpt_'.$orderId, $amountPaise, 'INR');
        $upd = $pdo->prepare("UPDATE orders SET razorpay_order_id = ? WHERE id = ?");
        $upd->execute([$rzpOrder['id'], $orderId]);
    } catch (Throwable $e) { log_error('razorpay_create_order', $e); echo json_encode(['success'=>false,'error'=>'Payment gateway error']); exit; }
    cart_clear();
    echo json_encode(['success'=>true,'order_id'=>$orderId,'rzp_order'=>$rzpOrder,'key_id'=> get_setting($pdo,'razorpay_key_id', getenv('RAZORPAY_KEY_ID') ?: ''),'customer'=>$customer]);
    exit;
}

$pageTitle = 'Checkout — PerfumeStore';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?php echo e($pageTitle); ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/assets/css/main.css">
<script src="https://checkout.razorpay.com/v1/checkout.js" async></script>
<script>window.__CHECKOUT_ENDPOINT__='/checkout.php';</script>
</head>
<body>
<?php include __DIR__ . '/partials/header.php'; ?>
<main class="section">
  <h1 style="font-size:24px;margin-bottom:12px;">Checkout</h1>
  <div style="display:grid;gap:24px;grid-template-columns:1fr;">
    <form id="checkout-form" method="post" class="form" autocomplete="on">
      <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
      <div><label>Name</label><br><input name="name" required></div>
      <div><label>Email</label><br><input name="email" type="email" required></div>
      <div><label>Phone</label><br><input name="phone" type="tel" required></div>
      <div><label>Shipping Address</label><br><textarea name="address" rows="3" required></textarea></div>
      <button class="btn btn--primary" type="submit">Pay Now</button>
    </form>
    <aside style="border:1px solid #eee;border-radius:12px;padding:12px;max-width:360px;">
      <h2 style="font-size:18px;margin:0 0 10px;">Order Summary</h2>
      <?php foreach ($items as $it): ?>
        <div style="display:flex;justify-content:space-between;margin:6px 0;">
          <span><?php echo e($it['name']); ?> × <?php echo (int)$it['qty']; ?></span>
          <span>₹<?php echo number_format((float)$it['subtotal'],2); ?></span>
        </div>
      <?php endforeach; ?>
      <hr>
      <div>Subtotal: ₹<?php echo number_format($totals['subtotal'],2); ?></div>
      <div>Tax: ₹<?php echo number_format($totals['tax'],2); ?></div>
      <div>Shipping: ₹<?php echo number_format($totals['shipping'],2); ?></div>
      <strong>Total: ₹<?php echo number_format($totals['total'],2); ?></strong>
    </aside>
  </div>
</main>
<?php include __DIR__ . '/partials/footer.php'; ?>
<script src="/assets/js/checkout.js" defer></script>
</body>
</html>