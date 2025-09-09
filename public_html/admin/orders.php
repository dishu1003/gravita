<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';
require_admin();

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="orders.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['OrderID','Status','Total','Tax','Shipping','Customer','Email','Created']);
    $rows = $pdo->query("SELECT id,status,total_amount,tax_amount,shipping_amount,customer_name,customer_email,created_at FROM orders ORDER BY created_at DESC")->fetchAll(PDO::FETCH_NUM);
    foreach ($rows as $r) fputcsv($out, $r);
    fclose($out); exit;
}

$msg=null;$err=null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['mode'] ?? '') === 'status') {
    if (!csrf_validate($_POST['_csrf'] ?? '')) { $err='Invalid CSRF'; }
    $id = (int)($_POST['id'] ?? 0);
    $status = sanitize_string($_POST['status'] ?? '');
    if (!$err && update_order_status($pdo, $id, $status)) { $msg = 'Status updated'; }
    elseif (!$err) { $err = 'Update failed'; }
}

$orders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 200")->fetchAll(PDO::FETCH_ASSOC);
$itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id=?");
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><title>Admin Orders — PerfumeStore</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
<main class="section">
  <h1>Orders</h1>
  <nav style="margin-bottom:12px;"><a href="/admin/index.php">← Back</a> | <a href="?export=csv">Export CSV</a></nav>
  <?php if ($msg): ?><div role="status" style="padding:8px;border:1px solid #0c0;background:#efe;color:#060;"><?php echo e($msg); ?></div><?php endif; ?>
  <?php if ($err): ?><div role="alert" style="padding:8px;border:1px solid #c00;background:#fee;color:#600;"><?php echo e($err); ?></div><?php endif; ?>
  <?php foreach ($orders as $o): ?>
    <article style="border:1px solid #eee;padding:12px;border-radius:12px;margin-bottom:12px;">
      <header>
        <strong>#<?php echo (int)$o['id']; ?></strong> — <?php echo e($o['customer_name']); ?> (<?php echo e($o['customer_email']); ?>) — ₹<?php echo number_format((float)$o['total_amount'],2); ?>
      </header>
      <div>Status: <strong><?php echo e($o['status']); ?></strong></div>
      <form method="post" style="margin:6px 0;">
        <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
        <input type="hidden" name="mode" value="status">
        <input type="hidden" name="id" value="<?php echo (int)$o['id']; ?>">
        <select name="status">
          <?php foreach (['pending','paid','processing','shipped','delivered','canceled'] as $s): ?>
          <option value="<?php echo e($s); ?>" <?php echo $o['status']===$s?'selected':''; ?>><?php echo e(ucfirst($s)); ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn--ghost" type="submit">Update</button>
      </form>
      <div style="margin-top:8px;">
        <em>Items:</em>
        <?php $itemsStmt->execute([(int)$o['id']]); $oi = $itemsStmt->fetchAll(PDO::FETCH_ASSOC); ?>
        <?php foreach ($oi as $it): ?>
          <div><?php echo e($it['product_name']); ?> × <?php echo (int)$it['quantity']; ?> — ₹<?php echo number_format((float)$it['line_total'],2); ?></div>
        <?php endforeach; ?>
      </div>
      <small>Order ID: <?php echo e($o['razorpay_order_id']); ?> | Payment ID: <?php echo e($o['razorpay_payment_id']); ?></small>
    </article>
  <?php endforeach; ?>
</main>
</body>
</html>