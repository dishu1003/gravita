<?php
// This determines which nav item is active
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' — Admin' : 'Admin Panel'; ?></title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="/assets/css/main.css?v=6">
</head>
<body class="admin-page">

<aside class="admin-sidebar">
    <h2><a href="/admin/index.php">PerfumeStore</a></h2>
    <nav class="admin-nav">
        <a href="/admin/index.php" class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">Dashboard</a>
        <a href="/admin/products.php" class="<?php echo $current_page === 'products.php' ? 'active' : ''; ?>">Products</a>
        <a href="/admin/categories.php" class="<?php echo $current_page === 'categories.php' ? 'active' : ''; ?>">Categories</a>
        <a href="/admin/orders.php" class="<?php echo $current_page === 'orders.php' ? 'active' : ''; ?>">Orders</a>
        <a href="/admin/customers.php" class="<?php echo $current_page === 'customers.php' ? 'active' : ''; ?>">Customers</a>
        <a href="/admin/testimonials.php" class="<?php echo $current_page === 'testimonials.php' ? 'active' : ''; ?>">Testimonials</a>
        <a href="/admin/newsletter.php" class="<?php echo $current_page === 'newsletter.php' ? 'active' : ''; ?>">Newsletter</a>
        <a href="/admin/settings.php" class="<?php echo $current_page === 'settings.php' ? 'active' : ''; ?>">Settings</a>
    </nav>
</aside>

<div class="admin-main">
    <header class="admin-header">
        <h1><?php echo isset($pageTitle) ? e($pageTitle) : 'Dashboard'; ?></h1>
        <div>
            <a href="/" class="btn btn--ghost" target="_blank">View Site</a>
            <a href="/admin/logout.php" class="btn btn--outline" style="margin-left: 8px;">Logout</a>
        </div>
    </header>
    <main>
