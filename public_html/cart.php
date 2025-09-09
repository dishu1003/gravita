<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['_csrf'] ?? '')) {
        if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success'=>false,'error'=>'Invalid CSRF']); exit; }
        http_response_code(400); exit('Invalid CSRF');
    }
    $action = sanitize_string($_POST['action'] ?? '');
    if ($action === 'add') {
        $pid = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['qty'] ?? 1));
        try {
            $check = $pdo->prepare("SELECT id FROM products WHERE id = ? LIMIT 1");
            $check->execute([$pid]);
            if ($check->fetch()) {
                cart_add($pdo, $pid, $qty);
                $out = ['success'=>true,'cartCount'=>get_cart_count($pdo)];
            } else {
                $out = ['success'=>false,'error'=>'Product not found'];
            }
        } catch (Throwable $e) {
            log_error('cart_add', $e);
            $out = ['success'=>false,'error'=>'Error adding to cart'];
        }
        if ($isAjax) { header('Content-Type: application/json'); echo json_encode($out); exit; }
        header('Location: /cart.php'); exit;
    } elseif ($action === 'update') {
        foreach (($_POST['qty'] ?? []) as $pid => $qty) {
            cart_set($pdo, (int)$pid, max(0, (int)$qty));
        }
        if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success'=>true,'cartCount'=>get_cart_count($pdo)]); exit; }
        header('Location: /cart.php'); exit;
    } elseif ($action === 'remove') {
        $pid = (int)($_POST['product_id'] ?? 0);
        cart_set($pdo, $pid, 0);
        if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success'=>true,'cartCount'=>get_cart_count($pdo)]); exit; }
        header('Location: /cart.php'); exit;
    }
}

$items = cart_items($pdo);
$totals = cart_totals($pdo);
$pageTitle = 'Cart — PerfumeStore';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?php echo e($pageTitle); ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="assets/css/main.css?v=2">
</head>
<body>
<?php include __DIR__ . '/partials/header.php'; ?>
<main class="section">
  <h1 style="font-size:24px;margin-bottom:12px;">Your Cart</h1>
  <?php if (empty($items)): ?>
    <p>Your cart is empty. <a href="/shop.php">Continue shopping</a></p>
  <?php else: ?>
    <form method="post" action="/cart.php">
      <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
      <input type="hidden" name="action" value="update">
      <table style="width:100%;border-collapse:collapse;">
        <thead>
          <tr><th style="text-align:left;">Item</th><th>Price</th><th>Qty</th><th>Subtotal</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach ($items as $it): ?>
            <tr style="border-top:1px solid #eee;">
              <td>
                <a href="/product.php?slug=<?php echo e($it['slug']); ?>">
                  <img src="/uploads/<?php echo e($it['image']); ?>" alt="" width="60" height="60" style="border-radius:8px;vertical-align:middle;margin-right:8px;">
                </a>
                <?php echo e($it['name']); ?>
              </td>
              <td style="text-align:center;">₹<?php echo number_format((float)$it['price'],2); ?></td>
              <td style="text-align:center;">
                <input type="number" min="0" name="qty[<?php echo (int)$it['id']; ?>]" value="<?php echo (int)$it['qty']; ?>" style="width:70px;">
              </td>
              <td style="text-align:center;">₹<?php echo number_format((float)$it['subtotal'],2); ?></td>
              <td style="text-align:center;">
                <form method="post" action="/cart.php" style="display:inline;">
                  <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
                  <input type="hidden" name="action" value="remove">
                  <input type="hidden" name="product_id" value="<?php echo (int)$it['id']; ?>">
                  <button class="btn btn--ghost" type="submit">Remove</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div style="margin-top:12px;">
        <button class="btn btn--ghost" type="submit">Update Cart</button>
        <a class="btn btn--primary" href="/checkout.php" style="margin-left:8px;">Checkout</a>
      </div>
    </form>
    <aside style="margin-top:16px;border:1px solid #eee;border-radius:12px;padding:12px;max-width:360px;">
      <div>Subtotal: ₹<?php echo number_format($totals['subtotal'],2); ?></div>
      <div>Tax: ₹<?php echo number_format($totals['tax'],2); ?></div>
      <div>Shipping: ₹<?php echo number_format($totals['shipping'],2); ?></div>
      <hr>
      <strong>Total: ₹<?php echo number_format($totals['total'],2); ?></strong>
    </aside>
  <?php endif; ?>
</main>
<?php include __DIR__ . '/partials/footer.php'; ?>
<script src="/assets/js/main.js" defer></script>
</body>
</html>
