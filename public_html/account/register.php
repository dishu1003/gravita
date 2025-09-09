<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';

$msg = null; $err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['_csrf'] ?? '')) { $err = 'Invalid CSRF'; }
    $name = sanitize_string($_POST['name'] ?? '');
    $email = sanitize_string($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    if (!$err) {
        $res = register_user($pdo, $name, $email, $password);
        if ($res['success']) { $msg = 'Account created. Please login.'; }
        else { $err = $res['error'] ?? 'Registration failed'; }
    }
}

$pageTitle = 'Register — PerfumeStore';
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
  <h1>Create Account</h1>
  <?php if ($msg): ?><div role="status" style="padding:10px;border:1px solid #0c0;color:#060;background:#efe;"><?php echo e($msg); ?></div><?php endif; ?>
  <?php if ($err): ?><div role="alert" style="padding:10px;border:1px solid #c00;color:#600;background:#fee;"><?php echo e($err); ?></div><?php endif; ?>
  <form method="post" class="form">
    <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
    <div><label>Name</label><br><input name="name" required></div>
    <div><label>Email</label><br><input type="email" name="email" required></div>
    <div><label>Password</label><br><input type="password" name="password" minlength="8" required></div>
    <button class="btn btn--primary" type="submit">Register</button>
  </form>
</main>
<?php include __DIR__ . '/../partials/footer.php'; ?>
<script src="/assets/js/main.js" defer></script>
</body>
</html>
