<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';

if (!is_logged_in()) { header('Location: /account/login.php'); exit; }

$orders = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([ (int)$_SESSION['user_id'] ]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
} catch (Throwable $e) {
    log_error('account_orders', $e);
}

$pageTitle = 'My Orders — PerfumeStore';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><title><?php echo e($pageTitle); ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/../partials/header.php'; ?>
<main class="section">
  <h1>My Orders</h1>
  <?php if (empty($orders)): ?>
    <p>No orders yet. <a href="/shop.php">Shop now</a></p>
  <?php else: ?>
    <?php foreach ($orders as $o): ?>
      <article style="border:1px solid #eee;padding:12px;border-radius:12px;margin-bottom:12px;">
        <header>
          <strong>Order #<?php echo (int)$o['id']; ?></strong> — Status: <?php echo e($o['status']); ?> — Placed: <?php echo e($o['created_at']); ?>
        </header>
        <div style="margin-top:8px;">
          <?php
          $itemsStmt->execute([ (int)$o['id'] ]);
          $oi = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
          foreach ($oi as $it): ?>
            <div style="display:flex;justify-content:space-between;">
              <span><?php echo e($it['product_name']); ?> × <?php echo (int)$it['quantity']; ?></span>
              <span>₹<?php echo number_format((float)$it['line_total'],2); ?></span>
            </div>
          <?php endforeach; ?>
        </div>
        <footer style="margin-top:8px;">
          <strong>Total: ₹<?php echo number_format((float)$o['total_amount'],2); ?></strong>
        </footer>
      </article>
    <?php endforeach; ?>
  <?php endif; ?>
</main>
<?php include __DIR__ . '/../partials/footer.php'; ?>
<script src="/assets/js/main.js" defer></script>
</body>
</html>