<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';
require_admin();

$msg=null;$err=null;

// Create / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['_csrf'] ?? '')) { $err = 'Invalid CSRF'; }
    $mode = sanitize_string($_POST['mode'] ?? '');
    $name = sanitize_string($_POST['name'] ?? '');
    $slug = sanitize_string($_POST['slug'] ?? '');
    $sku = sanitize_string($_POST['sku'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $mrp = $_POST['mrp'] !== '' ? (float)$_POST['mrp'] : null;
    $stock = (int)($_POST['stock'] ?? 0);
    $category_id = $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null;
    $scent_notes = sanitize_string($_POST['scent_notes'] ?? '');
    $is_bestseller = isset($_POST['is_bestseller']) ? 1 : 0;
    $description = (string)($_POST['description'] ?? '');

    if (!$err) {
        if ($slug === '') $slug = slugify($name);
        $imageName = null;
        if (!empty($_FILES['image']['name'])) {
            $upload = upload_image($_FILES['image'], __DIR__ . '/../uploads');
            if ($upload['success']) { $imageName = $upload['filename']; }
            else { $err = $upload['error']; }
        }
    }

    if (!$err) {
        try {
            if ($mode === 'create') {
                $stmt = $pdo->prepare("INSERT INTO products (sku,name,slug,description,price,mrp,stock,category_id,image,scent_notes,is_bestseller) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$sku,$name,$slug,$description,$price,$mrp,$stock,$category_id,$imageName,$scent_notes,$is_bestseller]);
                $msg = 'Product created';
            } elseif ($mode === 'update') {
                $id = (int)$_POST['id'];
                if ($imageName) {
                    $stmt = $pdo->prepare("UPDATE products SET sku=?,name=?,slug=?,description=?,price=?,mrp=?,stock=?,category_id=?,image=?,scent_notes=?,is_bestseller=? WHERE id=?");
                    $stmt->execute([$sku,$name,$slug,$description,$price,$mrp,$stock,$category_id,$imageName,$scent_notes,$is_bestseller,$id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE products SET sku=?,name=?,slug=?,description=?,price=?,mrp=?,stock=?,category_id=?,scent_notes=?,is_bestseller=? WHERE id=?");
                    $stmt->execute([$sku,$name,$slug,$description,$price,$mrp,$stock,$category_id,$scent_notes,$is_bestseller,$id]);
                }
                $msg = 'Product updated';
            }
        } catch (Throwable $e) { $err = 'DB error'; log_error('admin_products_save', $e); }
    }
}

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['mode'] ?? '') === 'delete') {
    if (!csrf_validate($_POST['_csrf'] ?? '')) { $err = 'Invalid CSRF'; }
    $id = (int)($_POST['id'] ?? 0);
    if (!$err && $id) {
        try {
            $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
            $msg = 'Product deleted';
        } catch (Throwable $e) { $err='Delete failed'; log_error('admin_products_delete',$e); }
    }
}

// Fetch
$products = $pdo->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id=p.category_id ORDER BY p.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT id,name FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$editing = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $st = $pdo->prepare("SELECT * FROM products WHERE id=?");
    $st->execute([$id]);
    $editing = $st->fetch(PDO::FETCH_ASSOC);
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><title>Admin Products — PerfumeStore</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
<main class="section">
  <h1>Products</h1>
  <nav style="margin-bottom:12px;"><a href="/admin/index.php">← Back</a></nav>
  <?php if ($msg): ?><div role="status" style="padding:8px;border:1px solid #0c0;background:#efe;color:#060;"><?php echo e($msg); ?></div><?php endif; ?>
  <?php if ($err): ?><div role="alert" style="padding:8px;border:1px solid #c00;background:#fee;color:#600;"><?php echo e($err); ?></div><?php endif; ?>

  <section style="margin:16px 0;">
    <h2 style="font-size:18px;"><?php echo $editing ? 'Edit' : 'Create'; ?> Product</h2>
    <form method="post" enctype="multipart/form-data" class="form">
      <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
      <input type="hidden" name="mode" value="<?php echo $editing?'update':'create'; ?>">
      <?php if ($editing): ?><input type="hidden" name="id" value="<?php echo (int)$editing['id']; ?>"><?php endif; ?>
      <div><label>Name</label><br><input name="name" value="<?php echo e($editing['name'] ?? ''); ?>" required></div>
      <div><label>Slug</label><br><input name="slug" value="<?php echo e($editing['slug'] ?? ''); ?>" placeholder="auto-generated if blank"></div>
      <div><label>SKU</label><br><input name="sku" value="<?php echo e($editing['sku'] ?? ''); ?>"></div>
      <div><label>Price (INR)</label><br><input type="number" step="0.01" name="price" value="<?php echo e($editing['price'] ?? ''); ?>" required></div>
      <div><label>MRP (INR)</label><br><input type="number" step="0.01" name="mrp" value="<?php echo e($editing['mrp'] ?? ''); ?>"></div>
      <div><label>Stock</label><br><input type="number" name="stock" value="<?php echo e($editing['stock'] ?? 0); ?>"></div>
      <div><label>Category</label><br>
        <select name="category_id">
          <option value="">None</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?php echo (int)$c['id']; ?>" <?php echo isset($editing['category_id']) && (int)$editing['category_id']===(int)$c['id']?'selected':''; ?>><?php echo e($c['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div><label>Scent notes</label><br><input name="scent_notes" value="<?php echo e($editing['scent_notes'] ?? ''); ?>"></div>
      <div><label>Description</label><br><textarea name="description" rows="5"><?php echo e($editing['description'] ?? ''); ?></textarea></div>
      <div><label><input type="checkbox" name="is_bestseller" <?php echo !empty($editing['is_bestseller'])?'checked':''; ?>> Bestseller</label></div>
      <div><label>Image</label><br><input type="file" name="image" accept=".jpg,.jpeg,.png,.webp"></div>
      <button class="btn btn--primary" type="submit"><?php echo $editing ? 'Update' : 'Create'; ?></button>
    </form>
  </section>

  <section>
    <h2 style="font-size:18px;">All Products</h2>
    <table style="width:100%;border-collapse:collapse;">
      <thead><tr><th>ID</th><th>Name</th><th>Price</th><th>Stock</th><th>Cat</th><th>Image</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($products as $p): ?>
          <tr style="border-top:1px solid #eee;">
            <td><?php echo (int)$p['id']; ?></td>
            <td><?php echo e($p['name']); ?></td>
            <td>₹<?php echo number_format((float)$p['price'],2); ?></td>
            <td><?php echo (int)$p['stock']; ?></td>
            <td><?php echo e($p['category_name']); ?></td>
            <td><?php if ($p['image']): ?><img src="/uploads/<?php echo e($p['image']); ?>" alt="" width="50"><?php endif; ?></td>
            <td>
              <a href="?edit=<?php echo (int)$p['id']; ?>">Edit</a>
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
  </section>
</main>
</body>
</html>