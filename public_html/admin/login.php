<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['_csrf'] ?? '')) { $error = 'Invalid CSRF'; }
    $email = sanitize_string($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    if (!$error && login_user($pdo, $email, $password) && is_admin()) {
        header('Location: /admin/index.php'); exit;
    }
    if (!$error) { $error = 'Invalid credentials'; }
}

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><title>Admin Login — PerfumeStore</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
<main class="section">
  <h1>Admin Login</h1>
  <?php if ($error): ?><div role="alert" style="padding:10px;border:1px solid #c00;color:#600;background:#fee;"><?php echo e($error); ?></div><?php endif; ?>
  <form method="post" class="form">
    <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
    <div><label>Email</label><br><input type="email" name="email" required></div>
    <div><label>Password</label><br><input type="password" name="password" required></div>
    <button class="btn btn--primary" type="submit">Login</button>
  </form>
</main>
</body>
</html>
