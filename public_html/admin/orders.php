<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';
require_admin();

$pageTitle = 'Orders';
$msg = null;
$err = null;

// Update Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mode']) && $_POST['mode'] === 'update_status') {
    if (!csrf_validate($_POST['_csrf'] ?? '')) { $err = 'Invalid CSRF token.'; }
    else {
        $id = (int)($_POST['id'] ?? 0);
        $status = sanitize_string($_POST['status'] ?? '');
        if ($id > 0 && update_order_status($pdo, $id, $status)) {
            $msg = "Order #{$id} status updated to '{$status}'.";
        } else {
            $err = "Failed to update order #{$id}.";
        }
    }
}

$orders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$allowed_statuses = ['pending', 'paid', 'processing', 'shipped', 'delivered', 'canceled'];
?>
<?php include __DIR__ . '/partials/header.php'; ?>

<?php if ($msg): ?><div class="alert alert--success"><?php echo e($msg); ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert--error"><?php echo e($err); ?></div><?php endif; ?>

<div class="admin-card">
    <h2>All Orders</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?php echo (int)$order['id']; ?></td>
                    <td><?php echo e($order['customer_name']); ?><br><small><?php echo e($order['customer_email']); ?></small></td>
                    <td>₹<?php echo number_format((float)$order['total_amount'], 2); ?></td>
                    <td>
                        <form method="post" style="display: flex; align-items: center; gap: 8px;">
                            <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
                            <input type="hidden" name="mode" value="update_status">
                            <input type="hidden" name="id" value="<?php echo (int)$order['id']; ?>">
                            <select name="status" class="form-control" style="padding: 4px 8px;">
                                <?php foreach($allowed_statuses as $status): ?>
                                    <option value="<?php echo e($status); ?>" <?php echo $order['status'] === $status ? 'selected' : ''; ?>>
                                        <?php echo e(ucfirst($status)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn--primary" style="min-height: auto; padding: 4px 8px;">Save</button>
                        </form>
                    </td>
                    <td><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></td>
                    <td class="actions">
                        <a href="#" class="btn btn--outline">View Details</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
