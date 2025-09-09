<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';
require_admin();

$msg=null;$err=null;
$keys = ['site_title','site_url','tax_percent','shipping_flat','razorpay_key_id','razorpay_key_secret','razorpay_webhook_secret','smtp_host','smtp_user','smtp_pass','smtp_port'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['_csrf'] ?? '')) { $err='Invalid CSRF'; }
    if (!$err) {
        try {
            $ins = $pdo->prepare("INSERT INTO settings (`key`,`value`) VALUES (?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
            foreach ($keys as $k) { $ins->execute([$k, sanitize_string($_POST[$k] ?? '')]); }
            $msg='Settings saved';
        } catch (Throwable $e) { $err='Save failed'; log_error('admin_settings_save',$e); }
    }
}
$settings = get_settings($pdo);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><title>Settings — PerfumeStore</title>
<meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
<main class="section">
  <h1>Settings</h1>
  <nav style="margin-bottom:12px;"><a href="/admin/index.php">← Back</a></nav>
  <?php if ($msg): ?><div role="status" style="padding:8px;border:1px solid #0c0;background:#efe;color:#060;"><?php echo e($msg); ?></div><?php endif; ?>
  <?php if ($err): ?><div role="alert" style="padding:8px;border:1px solid #c00;background:#fee;color:#600;"><?php echo e($err); ?></div><?php endif; ?>
  <form method="post" class="form" autocomplete="off">
    <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
    <h3>Site</h3>
    <div><label>Title</label><br><input name="site_title" value="<?php echo e($settings['site_title'] ?? 'PerfumeStore'); ?>"></div>
    <div><label>Site URL</label><br><input name="site_url" value="<?php echo e($settings['site_url'] ?? SITE_URL); ?>"></div>
    <div><label>Tax %</label><br><input name="tax_percent" type="number" step="0.01" value="<?php echo e($settings['tax_percent'] ?? '5'); ?>"></div>
    <div><label>Shipping Flat</label><br><input name="shipping_flat" type="number" step="0.01" value="<?php echo e($settings['shipping_flat'] ?? '49'); ?>"></div>
    <h3>Razorpay</h3>
    <div><label>Key ID</label><br><input name="razorpay_key_id" value="<?php echo e($settings['razorpay_key_id'] ?? ''); ?>"></div>
    <div><label>Key Secret</label><br><input name="razorpay_key_secret" value="<?php echo e($settings['razorpay_key_secret'] ?? ''); ?>"></div>
    <div><label>Webhook Secret</label><br><input name="razorpay_webhook_secret" value="<?php echo e($settings['razorpay_webhook_secret'] ?? ''); ?>"></div>
    <h3>SMTP</h3>
    <div><label>Host</label><br><input name="smtp_host" value="<?php echo e($settings['smtp_host'] ?? SMTP_HOST); ?>"></div>
    <div><label>User</label><br><input name="smtp_user" value="<?php echo e($settings['smtp_user'] ?? SMTP_USER); ?>"></div>
    <div><label>Pass</label><br><input name="smtp_pass" value="<?php echo e($settings['smtp_pass'] ?? ''); ?>"></div>
    <div><label>Port</label><br><input name="smtp_port" value="<?php echo e($settings['smtp_port'] ?? SMTP_PORT); ?>"></div>
    <button class="btn btn--primary" type="submit">Save</button>
  </form>
</main>
</body>
</html>