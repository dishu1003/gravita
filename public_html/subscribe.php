<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate($_POST['_csrf'] ?? '')) {
    http_response_code(400); exit('Invalid request');
}
$email = sanitize_string($_POST['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: /?sub=invalid'); exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
    $stmt->execute([$email]);
} catch (Throwable $e) {
    // ignore dup
}

send_email($email, 'Welcome to PerfumeStore', '<p>Thanks for subscribing to PerfumeStore!</p>');

header('Location: /?sub=ok');
