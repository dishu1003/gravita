<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

$sent = false; $err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['_csrf'] ?? '')) { $err = 'Invalid CSRF'; }
    $name = sanitize_string($_POST['name'] ?? '');
    $email = sanitize_string($_POST['email'] ?? '');
    $message = sanitize_string($_POST['message'] ?? '');
    if (!$err && (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($message) < 5)) {
        $err = 'Invalid input';
    }
    if (!$err) {
        $html = '<p>New contact message:</p><p><strong>Name:</strong> '.e($name).'<br><strong>Email:</strong> '.e($email).'</p><p>'.nl2br(e($message)).'</p>';
        $sent = send_email(ADMIN_EMAIL, 'New contact form message', $html);
        if (!$sent) { $err = 'Could not send email'; }
    }
}

$pageTitle = 'Contact — PerfumeStore';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?php echo e($pageTitle); ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/partials/header.php'; ?>
<main class="section">
  <h1 style="font-size:24px;margin-bottom:12px;">Contact Us</h1>
  <?php if ($sent): ?>
    <div role="status" style="padding:10px;border:1px solid #0c0;color:#060;background:#efe;">Thank you! We’ll get back to you shortly.</div>
  <?php elseif ($err): ?>
    <div role="alert" style="padding:10px;border:1px solid #c00;color:#600;background:#fee;"><?php echo e($err); ?></div>
  <?php endif; ?>
  <form method="post" class="form">
    <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
    <div><label>Name</label><br><input name="name" required></div>
    <div><label>Email</label><br><input name="email" type="email" required></div>
    <div><label>Message</label><br><textarea name="message" rows="5" required></textarea></div>
    <button class="btn btn--primary" type="submit">Send</button>
  </form>
</main>
<?php include __DIR__ . '/partials/footer.php'; ?>
<script src="/assets/js/main.js" defer></script>
</body>
</html>
