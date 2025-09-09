<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';

if (!is_logged_in()) {
    header('Location: /account/login.php');
    exit;
}

$orders = [];
$order_items = [];
try {
    // 1. Get all orders for the current user
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([(int)$_SESSION['user_id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Get all order IDs
    $order_ids = array_column($orders, 'id');

    // 3. Get all items for those orders in a single query
    if (!empty($order_ids)) {
        $in_clause = implode(',', array_fill(0, count($order_ids), '?'));
        $itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id IN ($in_clause)");
        $itemsStmt->execute($order_ids);
        $all_items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

        // 4. Map items to their orders
        foreach ($all_items as $item) {
            $order_items[$item['order_id']][] = $item;
        }
    }
} catch (Throwable $e) {
    log_error('account_orders_fetch', $e);
}

$pageTitle = 'My Orders';
?>
<?php include __DIR__ . '/../partials/header.php'; ?>

<main class="section">
    <div class="section__header">
        <h1 class="section__title">My Account</h1>
    </div>
    <div class="account-layout">
        <aside class="account-nav">
            <a href="/account/orders.php" class="active">My Orders</a>
            <a href="#">My Profile</a>
            <a href="#">Address Book</a>
            <a href="/account/logout.php">Logout</a>
        </aside>

        <div class="account-content">
            <h2><?php echo e($pageTitle); ?></h2>
            <?php if (empty($orders)): ?>
                <div class="cart-empty">
                    <p>You have not placed any orders yet.</p>
                    <a href="/shop.php" class="btn btn--primary">Start Shopping</a>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <article class="order-card">
                        <header class="order-card__header">
                            <div class="order-card__header-info">
                                <span class="order-card__id">Order #<?php echo (int)$order['id']; ?></span>
                                <span class="order-card__date">Placed on <?php echo date('d M Y', strtotime($order['created_at'])); ?></span>
                            </div>
                            <div class="order-card__status order-card__status--<?php echo e($order['status']); ?>">
                                <?php echo e(ucfirst($order['status'])); ?>
                            </div>
                        </header>
                        <div class="order-card__body">
                            <?php if (isset($order_items[$order['id']])): ?>
                                <?php foreach ($order_items[$order['id']] as $item): ?>
                                    <div class="order-item">
                                        <span><?php echo e($item['product_name']); ?> &times; <strong><?php echo (int)$item['quantity']; ?></strong></span>
                                        <span>₹<?php echo number_format((float)$item['line_total'], 2); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <footer class="order-card__footer">
                            <span class="order-card__total">Total: ₹<?php echo number_format((float)$order['total_amount'], 2); ?></span>
                            <a href="#" class="btn btn--outline">View Details</a>
                        </footer>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
