<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';

const MAX_LOGIN_ATTEMPTS = 5;
const LOCKOUT_PERIOD = 300; // 5 minutes

$error = null;

// --- Brute-force Protection Check ---
if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
    $time_since_last_attempt = time() - ($_SESSION['last_attempt_time'] ?? 0);
    if ($time_since_last_attempt < LOCKOUT_PERIOD) {
        $error = 'You have made too many failed login attempts. Please wait ' . ceil((LOCKOUT_PERIOD - $time_since_last_attempt) / 60) . ' minutes before trying again.';
    } else {
        // If lockout period has passed, reset the counter
        unset($_SESSION['login_attempts']);
        unset($_SESSION['last_attempt_time']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error === null) {
    if (!csrf_validate($_POST['_csrf'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = sanitize_string($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        if (login_user($pdo, $email, $password) && is_admin()) {
            // On successful login, clear any attempt counters
            unset($_SESSION['login_attempts']);
            unset($_SESSION['last_attempt_time']);
            header('Location: /admin/index.php');
            exit;
        } else {
            // On failed login, increment attempt counter
            if (!isset($_SESSION['login_attempts'])) {
                $_SESSION['login_attempts'] = 1;
            } else {
                $_SESSION['login_attempts']++;
            }
            $_SESSION['last_attempt_time'] = time();
            $error = 'Invalid credentials or access denied.';
        }
    }
}

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin Login — PerfumeStore</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/assets/css/main.css?v=5">
<style>
  body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: var(--bg-alt); }
</style>
</head>
<body>
<main class="section" style="max-width: 400px; width: 100%;">
    <div class="contact-form-container">
        <div class="section__header" style="text-align: center; margin-bottom: 24px;">
            <h1 class="section__title">Admin Login</h1>
        </div>

        <?php if ($error): ?>
            <div class="alert alert--error" role="alert">
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="form">
            <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required <?php if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) echo 'disabled'; ?>>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" required <?php if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) echo 'disabled'; ?>>
            </div>

            <button class="btn btn--primary" type="submit" style="width: 100%;" <?php if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) echo 'disabled'; ?>>
                Login
            </button>
        </form>
    </div>
</main>
</body>
</html>
