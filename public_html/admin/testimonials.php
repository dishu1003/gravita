<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';
require_admin();

$pageTitle = 'Testimonials';
$msg = null;
$err = null;

// Approve / Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mode'])) {
    if (!csrf_validate($_POST['_csrf'] ?? '')) { $err = 'Invalid CSRF token.'; }
    else {
        $id = (int)($_POST['id'] ?? 0);
        $mode = sanitize_string($_POST['mode']);
        if ($id > 0) {
            try {
                if ($mode === 'toggle_approval') {
                    $stmt = $pdo->prepare("UPDATE testimonials SET is_approved = !is_approved WHERE id = ?");
                    $stmt->execute([$id]);
                    $msg = 'Testimonial approval toggled.';
                } elseif ($mode === 'delete') {
                    $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
                    $stmt->execute([$id]);
                    $msg = 'Testimonial deleted.';
                }
            } catch (Throwable $e) { $err = 'DB error.'; log_error('admin_testimonials_action', $e); }
        }
    }
}


$testimonials = $pdo->query("SELECT * FROM testimonials ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include __DIR__ . '/partials/header.php'; ?>

<?php if ($msg): ?><div class="alert alert--success"><?php echo e($msg); ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert--error"><?php echo e($err); ?></div><?php endif; ?>

<div class="admin-card">
    <h2>All Testimonials</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Author</th>
                <th>Rating</th>
                <th>Content</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($testimonials as $t): ?>
                <tr>
                    <td><?php echo e($t['name']); ?></td>
                    <td><?php echo str_repeat('⭐', (int)$t['rating']); ?></td>
                    <td><?php echo e(substr($t['content'], 0, 100)); ?>...</td>
                    <td><?php echo $t['is_approved'] ? 'Approved' : 'Pending'; ?></td>
                    <td class="actions">
                        <form action="" method="post" style="display:inline;">
                            <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
                            <input type="hidden" name="id" value="<?php echo (int)$t['id']; ?>">
                            <input type="hidden" name="mode" value="toggle_approval">
                            <button type="submit" class="btn btn--outline">
                                <?php echo $t['is_approved'] ? 'Unapprove' : 'Approve'; ?>
                            </button>
                        </form>
                        <form action="" method="post" style="display:inline;" onsubmit="return confirm('Delete?');">
                            <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
                            <input type="hidden" name="id" value="<?php echo (int)$t['id']; ?>">
                            <input type="hidden" name="mode" value="delete">
                            <button type="submit" class="btn btn--ghost">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
