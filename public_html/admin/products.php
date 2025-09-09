<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';
require_admin();

$pageTitle = 'Products';
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
        $price = (float)($_POST['price'] ?? 0);
        $mrp = !empty($_POST['mrp']) ? (float)$_POST['mrp'] : null;
        $stock = (int)($_POST['stock'] ?? 0);
        $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $description = sanitize_string($_POST['description'] ?? '');
        $is_bestseller = isset($_POST['is_bestseller']) ? 1 : 0;

        if (empty($name)) { $errors['name'] = 'Product name is required.'; }
        if ($price <= 0) { $errors['price'] = 'Price must be positive.'; }
        if (empty($slug)) { $slug = slugify($name); }

        $imageName = null;
        if (!empty($_FILES['image']['name'])) {
            $upload = upload_image($_FILES['image'], __DIR__ . '/../uploads');
            if ($upload['success']) { $imageName = $upload['filename']; }
            else { $errors['image'] = $upload['error']; }
        }

        if (empty($errors)) {
            try {
                if ($mode === 'create') {
                    $stmt = $pdo->prepare("INSERT INTO products (name, slug, description, price, mrp, stock, category_id, image, is_bestseller) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $slug, $description, $price, $mrp, $stock, $category_id, $imageName, $is_bestseller]);
                    $msg = 'Product created.';
                } elseif ($mode === 'update' && $id > 0) {
                    $fields = ['name=?,', 'slug=?,', 'description=?,', 'price=?,', 'mrp=?,', 'stock=?,', 'category_id=?,', 'is_bestseller=?'];
                    $params = [$name, $slug, $description, $price, $mrp, $stock, $category_id, $is_bestseller];

                    if ($imageName) {
                        $oldImageStmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
                        $oldImageStmt->execute([$id]);
                        $oldImage = $oldImageStmt->fetchColumn();
                        $fields[] = 'image=?';
                        $params[] = $imageName;
                    }

                    $sql = "UPDATE products SET " . implode(' ', $fields) . " WHERE id=?";
                    $params[] = $id;
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);

                    if ($imageName && !empty($oldImage) && file_exists(__DIR__ . '/../uploads/' . $oldImage)) {
                        unlink(__DIR__ . '/../uploads/' . $oldImage);
                    }
                    $msg = 'Product updated.';
                }
            } catch (Throwable $e) { $err = 'DB error.'; log_error('admin_products_save', $e); }
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
                $oldImageStmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
                $oldImageStmt->execute([$id]);
                $oldImage = $oldImageStmt->fetchColumn();
                $stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
                $stmt->execute([$id]);
                if (!empty($oldImage) && file_exists(__DIR__ . '/../uploads/' . $oldImage)) { unlink(__DIR__ . '/../uploads/' . $oldImage); }
                $msg = 'Product deleted.';
            } catch (Throwable $e) { $err='Delete failed.'; log_error('admin_products_delete',$e); }
        }
    }
}

$products = $pdo->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id=p.category_id ORDER BY p.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT id,name FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$editing = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
    $stmt->execute([$id]);
    $editing = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<?php include __DIR__ . '/partials/header.php'; ?>

<?php if ($msg): ?><div class="alert alert--success"><?php echo e($msg); ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert--error"><?php echo e($err); ?></div><?php endif; ?>

<div class="admin-card">
    <h2><?php echo $editing ? 'Edit Product' : 'Create New Product'; ?></h2>
    <form method="post" enctype="multipart/form-data" class="form">
      <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
      <input type="hidden" name="mode" value="<?php echo $editing ? 'update' : 'create'; ?>">
      <?php if ($editing): ?><input type="hidden" name="id" value="<?php echo (int)$editing['id']; ?>"><?php endif; ?>

      <div class="form-group">
        <label class="form-label" for="name">Name</label>
        <input class="form-control" id="name" name="name" value="<?php echo e($editing['name'] ?? ''); ?>" required>
        <?php if(isset($errors['name'])): ?><p class="form-error-msg"><?php echo e($errors['name']); ?></p><?php endif; ?>
      </div>
      <div class="form-group">
        <label class="form-label" for="price">Price (INR)</label>
        <input class="form-control" id="price" type="number" step="0.01" name="price" value="<?php echo e($editing['price'] ?? ''); ?>" required>
        <?php if(isset($errors['price'])): ?><p class="form-error-msg"><?php echo e($errors['price']); ?></p><?php endif; ?>
      </div>
      <div class="form-group">
        <label class="form-label" for="stock">Stock</label>
        <input class="form-control" id="stock" type="number" name="stock" value="<?php echo e($editing['stock'] ?? 0); ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="category_id">Category</label>
        <select class="form-control" id="category_id" name="category_id">
          <option value="">None</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?php echo (int)$c['id']; ?>" <?php echo (isset($editing['category_id']) && (int)$editing['category_id'] === (int)$c['id']) ? 'selected' : ''; ?>><?php echo e($c['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label" for="description">Description</label>
        <textarea class="form-control" id="description" name="description" rows="5"><?php echo e($editing['description'] ?? ''); ?></textarea>
      </div>
      <div class="form-group">
        <label class="form-label"><input type="checkbox" name="is_bestseller" <?php echo !empty($editing['is_bestseller']) ? 'checked' : ''; ?>> Bestseller</label>
      </div>
      <div class="form-group">
        <label class="form-label" for="image">Image</label>
        <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">
        <?php if(isset($errors['image'])): ?><p class="form-error-msg"><?php echo e($errors['image']); ?></p><?php endif; ?>
      </div>
      <button class="btn btn--primary" type="submit"><?php echo $editing ? 'Update Product' : 'Create Product'; ?></button>
    </form>
</div>

<div class="admin-card">
    <h2>All Products</h2>
    <table class="admin-table">
      <thead><tr><th>ID</th><th>Name</th><th>Price</th><th>Stock</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($products as $p): ?>
          <tr>
            <td><?php echo (int)$p['id']; ?></td>
            <td><?php echo e($p['name']); ?></td>
            <td>₹<?php echo number_format((float)$p['price'], 2); ?></td>
            <td><?php echo (int)$p['stock']; ?></td>
            <td class="actions">
              <a href="?edit=<?php echo (int)$p['id']; ?>" class="btn btn--outline">Edit</a>
              <form action="/admin/products.php" method="post" style="display:inline;" onsubmit="return confirm('Delete?');">
                <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
                <input type="hidden" name="mode" value="delete">
                <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                <button type="submit" class="btn btn--ghost">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
