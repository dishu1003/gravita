<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';
require_admin();

$counts = ['products'=>0,'orders'=>0,'customers'=>0];
try {
    $counts['products'] = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $counts['orders'] = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $counts['customers'] = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
} catch (Throwable $e) { log_error('admin_dashboard', $e); }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><title>Admin Dashboard — PerfumeStore</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
<main class="section">
  <h1>Admin Dashboard</h1>
  <nav style="margin-bottom:12px;">
    <a href="/admin/products.php">Products</a> |
    <a href="/admin/categories.php">Categories</a> |
    <a href="/admin/orders.php">Orders</a> |
    <a href="/admin/customers.php">Customers</a> |
    <a href="/admin/testimonials.php">Testimonials</a> |
    <a href="/admin/newsletter.php">Newsletter</a> |
    <a href="/admin/settings.php">Settings</a> |
    <a href="/admin/logout.php">Logout</a>
  </nav>
  <div style="display:flex; gap:16px; flex-wrap:wrap;">
    <div style="border:1px solid #eee;padding:12px;border-radius:12px;">Products: <?php echo (int)$counts['products']; ?></div>
    <div style="border:1px solid #eee;padding:12px;border-radius:12px;">Orders: <?php echo (int)$counts['orders']; ?></div>
    <div style="border:1px solid #eee;padding:12px;border-radius:12px;">Customers: <?php echo (int)$counts['customers']; ?></div>
  </div>
</main>
</body>
</html>