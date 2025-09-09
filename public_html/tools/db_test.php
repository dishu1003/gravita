<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors','1');

require __DIR__ . '/../includes/config.php';

try {
  // Will reuse $pdo from config if connection succeeded
  echo "<pre>Connected OK with DSN: mysql:host=".DB_HOST.";dbname=".DB_NAME."</pre>";
  $drivers = PDO::getAvailableDrivers();
  echo "<pre>Available PDO drivers: ".htmlspecialchars(implode(', ', $drivers), ENT_QUOTES)."</pre>";
} catch (Throwable $e) {
  echo "<pre>Connection failed: ".htmlspecialchars($e->getMessage(), ENT_QUOTES)."</pre>";
}
