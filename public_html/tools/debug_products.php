<?php
require __DIR__ . '/../includes/config.php';
echo "<pre>DB: ".DB_NAME."\n";
echo "Products: ".$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn()."\n";
print_r($pdo->query("SELECT id,name,slug,category_id,created_at FROM products ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC));
print_r($pdo->query("SELECT id,name,slug FROM categories")->fetchAll(PDO::FETCH_ASSOC));