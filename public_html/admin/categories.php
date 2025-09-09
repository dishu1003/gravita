<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';
require_admin();

$pageTitle = 'Categories';
$msg = null;
$err = null;
$errors = [];

// Create / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mode'])) {
    if (!csrf_validate($_POST['_csrf'] ?? '')) { $err = 'Invalid CSRF token.'; }
    else {
        $mode = sanitize_string($_POST['mode']);
        $id = (int)($_POST['id'] ?? 0);
        $name = sanitize_string($_POST['name'] ?? '');
        $slug = sanitize_string($_POST['slug'] ?? '');
        $description = sanitize_string($_POST['description'] ?? '');

        if (empty($name)) { $errors['name'] = 'Category name is required.'; }
        if (empty($slug)) { $slug = slugify($name); }

        if (empty($errors)) {
            try {
                if ($mode === 'create') {
                    $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
                    $stmt->execute([$name, $slug, $description]);
                    $msg = 'Category created.';
                } elseif ($mode === 'update' && $id > 0) {
                    $stmt = $pdo->prepare("UPDATE categories SET name=?, slug=?, description=? WHERE id=?");
                    $stmt->execute([$name, $slug, $description, $id]);
                    $msg = 'Category updated.';
                }
            } catch (Throwable $e) { $err = 'DB error.'; log_error('admin_categories_save', $e); }
        } else { $err = 'Please correct the errors.'; }
    }
}

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['mode'] ?? '') === 'delete') {
    if (!csrf_validate($_POST['_csrf'] ?? '')) { $err = 'Invalid CSRF token.'; }
    else {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            try {
                $stmt = $pdo->prepare("DELETE FROM categories WHERE id=?");
                $stmt->execute([$id]);
                $msg = 'Category deleted.';
            } catch (Throwable $e) { $err='Delete failed.'; log_error('admin_categories_delete',$e); }
        }
    }
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$editing = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id=?");
    $stmt->execute([$id]);
    $editing = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<?php include __DIR__ . '/partials/header.php'; ?>

<?php if ($msg): ?><div class="alert alert--success"><?php echo e($msg); ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert--error"><?php echo e($err); ?></div><?php endif; ?>

<div class="admin-card">
    <h2><?php echo $editing ? 'Edit Category' : 'Create New Category'; ?></h2>
    <form method="post" class="form">
      <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
      <input type="hidden" name="mode" value="<?php echo $editing ? 'update' : 'create'; ?>">
      <?php if ($editing): ?><input type="hidden" name="id" value="<?php echo (int)$editing['id']; ?>"><?php endif; ?>

      <div class="form-group">
        <label class="form-label" for="name">Name</label>
        <input class="form-control" id="name" name="name" value="<?php echo e($editing['name'] ?? ''); ?>" required>
        <?php if(isset($errors['name'])): ?><p class="form-error-msg"><?php echo e($errors['name']); ?></p><?php endif; ?>
      </div>
      <div class="form-group">
        <label class="form-label" for="slug">Slug</label>
        <input class="form-control" id="slug" name="slug" value="<?php echo e($editing['slug'] ?? ''); ?>" placeholder="Auto-generated from name if blank">
      </div>
      <div class="form-group">
        <label class="form-label" for="description">Description</label>
        <textarea class="form-control" id="description" name="description" rows="3"><?php echo e($editing['description'] ?? ''); ?></textarea>
      </div>
      <button class="btn btn--primary" type="submit"><?php echo $editing ? 'Update Category' : 'Create Category'; ?></button>
      <?php if ($editing): ?><a href="/admin/categories.php" style="margin-left: 8px;">Cancel Edit</a><?php endif; ?>
    </form>
</div>

<div class="admin-card">
    <h2>All Categories</h2>
    <table class="admin-table">
      <thead><tr><th>ID</th><th>Name</th><th>Slug</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($categories as $c): ?>
          <tr>
            <td><?php echo (int)$c['id']; ?></td>
            <td><?php echo e($c['name']); ?></td>
            <td><?php echo e($c['slug']); ?></td>
            <td class="actions">
              <a href="?edit=<?php echo (int)$c['id']; ?>" class="btn btn--outline">Edit</a>
              <form action="/admin/categories.php" method="post" style="display:inline;" onsubmit="return confirm('Delete?');">
                <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
                <input type="hidden" name="mode" value="delete">
                <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
                <button type="submit" class="btn btn--ghost">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
