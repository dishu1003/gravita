<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';
require_admin();

$pageTitle = 'Newsletter Subscribers';

$subscribers = $pdo->query("SELECT email, created_at FROM newsletter_subscribers ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include __DIR__ . '/partials/header.php'; ?>

<div class="admin-card">
    <h2>All Subscribers</h2>
    <p>
        <a href="#" id="copy-emails" class="btn btn--primary">Copy All Emails</a>
    </p>
    <textarea id="email-list" style="opacity: 0; position: absolute; left: -9999px;">
        <?php echo implode(', ', array_column($subscribers, 'email')); ?>
    </textarea>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Email</th>
                <th>Subscribed On</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($subscribers as $sub): ?>
                <tr>
                    <td><a href="mailto:<?php echo e($sub['email']); ?>"><?php echo e($sub['email']); ?></a></td>
                    <td><?php echo date('d M Y, H:i', strtotime($sub['created_at'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
document.getElementById('copy-emails').addEventListener('click', function(e) {
    e.preventDefault();
    const emailList = document.getElementById('email-list');
    emailList.select();
    document.execCommand('copy');
    alert('All emails copied to clipboard!');
});
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
