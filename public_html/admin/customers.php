<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';
require_admin();

$pageTitle = 'Customers';

$customers = $pdo->query("SELECT id, name, email, created_at FROM users WHERE role = 'customer' ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include __DIR__ . '/partials/header.php'; ?>

<div class="admin-card">
    <h2>All Customers</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Registered On</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $customer): ?>
                <tr>
                    <td><?php echo (int)$customer['id']; ?></td>
                    <td><?php echo e($customer['name']); ?></td>
                    <td><a href="mailto:<?php echo e($customer['email']); ?>"><?php echo e($customer['email']); ?></a></td>
                    <td><?php echo date('d M Y, H:i', strtotime($customer['created_at'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
