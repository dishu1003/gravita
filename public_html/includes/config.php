<?php
declare(strict_types=1);

// Composer autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Load environment variables from .env file if it exists
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
    ini_set('session.cookie_secure', '1');
}
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
date_default_timezone_set('Asia/Kolkata');

// ==== CONFIGURATION ====
// DB credentials should be set in environment variables
const DB_HOST = getenv('DB_HOST') ?: 'localhost';
const DB_NAME = getenv('DB_NAME') ?: '';
const DB_USER = getenv('DB_USER') ?: '';
const DB_PASS = getenv('DB_PASS') ?: '';

const SITE_URL = getenv('SITE_URL') ?: 'http://localhost';
const ADMIN_EMAIL = getenv('ADMIN_EMAIL') ?: 'admin@example.com';

const DEBUG = filter_var(getenv('DEBUG'), FILTER_VALIDATE_BOOLEAN) ?: false;
const SMTP_FROM_NAME = 'PerfumeStore'; // This can remain as it's not a secret
// =======================

// Stricter CSP (no inline scripts; we moved checkout inline js to /assets/js/checkout.js)
header("Content-Security-Policy: default-src 'self'; base-uri 'self'; object-src 'none'; script-src 'self' https://checkout.razorpay.com; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; connect-src 'self'; frame-src https://api.razorpay.com https://checkout.razorpay.com; frame-ancestors 'self';");

// HSTS if HTTPS
if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

// Ensure logs dir
$logsDir = __DIR__ . '/../logs';
if (!is_dir($logsDir)) {
    // Attempt to create, but don't suppress errors.
    // This might fail if permissions are wrong, and we want to know about it.
    mkdir($logsDir, 0700, true);
}

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
    $logFile = __DIR__ . '/../logs/error.log';
    $line = sprintf("[%s] %s: %s in %s:%d\nTrace: %s\n\n", date('Y-m-d H:i:s'), $tag, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
    // Use error_log as a fallback if file is not writable.
    if (!file_put_contents($logFile, $line, FILE_APPEND)) {
        error_log('Could not write to ' . $logFile . ': ' . $line);
    }
    if (DEBUG) { error_log($line); }
}

function e(?string $v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
