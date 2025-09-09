<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';
require_admin();

$pageTitle = 'Dashboard';

$counts = ['products' => 0, 'orders' => 0, 'customers' => 0];
try {
    $counts['products'] = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $counts['orders'] = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'paid'")->fetchColumn();
    $counts['customers'] = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
} catch (Throwable $e) {
    log_error('admin_dashboard_counts', $e);
}
?>
<?php include __DIR__ . '/partials/header.php'; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card__label">Total Products</div>
        <div class="stat-card__value"><?php echo (int)$counts['products']; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Paid Orders</div>
        <div class="stat-card__value"><?php echo (int)$counts['orders']; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Total Customers</div>
        <div class="stat-card__value"><?php echo (int)$counts['customers']; ?></div>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
