<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';
require_admin();

$customers = $pdo->query("SELECT id,name,email,phone,created_at FROM users WHERE role='customer' ORDER BY created_at DESC LIMIT 500")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><title>Customers — PerfumeStore</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
<main class="section">
  <h1>Customers</h1>
  <nav style="margin-bottom:12px;"><a href="/admin/index.php">← Back</a></nav>
  <table style="width:100%;border-collapse:collapse;">
    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Joined</th></tr></thead>
    <tbody>
      <?php foreach ($customers as $c): ?>
      <tr style="border-top:1px solid #eee;">
        <td><?php echo (int)$c['id']; ?></td>
        <td><?php echo e($c['name']); ?></td>
        <td><?php echo e($c['email']); ?></td>
        <td><?php echo e($c['phone']); ?></td>
        <td><?php echo e($c['created_at']); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</main>
</body>
</html>