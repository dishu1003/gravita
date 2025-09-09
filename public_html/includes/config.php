<?php
declare(strict_types=1);

ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
    ini_set('session.cookie_secure', '1');
}
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
date_default_timezone_set('Asia/Kolkata');

// ==== EDIT FOR HOSTINGER ====
const DB_HOST = 'localhost';
const DB_NAME = 'u782093275_perfume';
const DB_USER = 'u782093275_perfume';
const DB_PASS = 'Vktmdp@2025';

const SITE_URL = 'https://grayvita.dishantparihar.com';
const ADMIN_EMAIL = 'admin@yourdomain.com';

const DEBUG = false;

const RAZORPAY_KEY_ID = 'rzp_test_xxxxxxxxxxxxx';
const RAZORPAY_SECRET = 'xxxxxxxxxxxxxxxxxxxxx';
const RAZORPAY_WEBHOOK_SECRET = 'xxxxxxxx_webhook_secret_here';

const SMTP_HOST = 'smtp.hostinger.com';
const SMTP_PORT = 587;
const SMTP_USER = 'no-reply@grayvita.dishantparihar.com';
const SMTP_PASS = 'your_smtp_password';
const SMTP_FROM_NAME = 'PerfumeStore';
// ============================

// Stricter CSP (no inline scripts; we moved checkout inline js to /assets/js/checkout.js)
header("Content-Security-Policy: default-src 'self'; base-uri 'self'; object-src 'none'; script-src 'self' https://checkout.razorpay.com; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; connect-src 'self'; frame-src https://api.razorpay.com https://checkout.razorpay.com; frame-ancestors 'self';");

// HSTS if HTTPS
if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

// Ensure logs dir
$logsDir = __DIR__ . '/../logs';
if (!is_dir($logsDir)) { @mkdir($logsDir, 0700, true); }

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (Throwable $e) {
    error_log('[DB_CONNECT_ERROR] ' . $e->getMessage());
    if (DEBUG) { die('Database connection failed: ' . e($e->getMessage())); }
    http_response_code(500); die('Service temporarily unavailable.');
}

function log_error(string $tag, Throwable $e): void {
    $line = sprintf("[%s] %s: %s in %s:%d\nTrace: %s\n\n", date('Y-m-d H:i:s'), $tag, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
    @file_put_contents(__DIR__ . '/../logs/error.log', $line, FILE_APPEND);
    if (DEBUG) { error_log($line); }
}

// Seed admin password if empty
try {
    $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ? AND role = 'admin' LIMIT 1");
    $stmt->execute([ADMIN_EMAIL]);
    $admin = $stmt->fetch();
    if ($admin && (empty($admin['password']))) {
        $hash = password_hash('tempPass123!', PASSWORD_DEFAULT);
        $u = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $u->execute([$hash, $admin['id']]);
    }
} catch (Throwable $e) { log_error('seed_admin_password', $e); }

function e(?string $v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
