<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['_csrf'] ?? '')) { $error = 'Invalid CSRF'; }
    $email = sanitize_string($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    if (!$error && login_user($pdo, $email, $password)) {
        header('Location: /account/orders.php'); exit;
    }
    if (!$error) { $error = 'Invalid email or password'; }
}

$pageTitle = 'Login — PerfumeStore';
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
  <h1>Login</h1>
  <?php if ($error): ?><div role="alert" style="padding:10px;border:1px solid #c00;color:#600;background:#fee;"><?php echo e($error); ?></div><?php endif; ?>
  <form method="post" class="form">
    <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
    <div><label>Email</label><br><input type="email" name="email" required></div>
    <div><label>Password</label><br><input type="password" name="password" required></div>
    <button class="btn btn--primary" type="submit">Login</button>
  </form>
  <p>New here? <a href="/account/register.php">Create an account</a></p>
</main>
<?php include __DIR__ . '/../partials/footer.php'; ?>
<script src="/assets/js/main.js" defer></script>
</body>
</html>
