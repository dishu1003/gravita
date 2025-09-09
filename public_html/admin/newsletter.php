<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';
require_admin();

if (isset($_GET['export']) && $_GET['export']==='csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="newsletter.csv"');
    $out=fopen('php://output','w'); fputcsv($out,['Email','Subscribed At']);
    $rows=$pdo->query("SELECT email, created_at FROM newsletter_subscribers ORDER BY created_at DESC")->fetchAll(PDO::FETCH_NUM);
    foreach ($rows as $r) fputcsv($out,$r);
    fclose($out); exit;
}

$list=$pdo->query("SELECT email, created_at FROM newsletter_subscribers ORDER BY created_at DESC LIMIT 1000")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><title>Newsletter — PerfumeStore</title>
<meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
<main class="section">
  <h1>Newsletter Subscribers</h1>
  <nav style="margin-bottom:12px;"><a href="/admin/index.php">← Back</a> | <a href="?export=csv">Export CSV</a></nav>
  <table style="width:100%;border-collapse:collapse;">
    <thead><tr><th>Email</th><th>Subscribed At</th></tr></thead>
    <tbody>
      <?php foreach ($list as $r): ?>
      <tr style="border-top:1px solid #eee;">
        <td><?php echo e($r['email']); ?></td>
        <td><?php echo e($r['created_at']); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</main>
</body>
</html>