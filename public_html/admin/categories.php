<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';
require_admin();

$msg=null;$err=null;

// Create/Update
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!csrf_validate($_POST['_csrf'] ?? '')) { $err='Invalid CSRF'; }
    $mode=sanitize_string($_POST['mode'] ?? '');
    $name=sanitize_string($_POST['name'] ?? '');
    $slug=sanitize_string($_POST['slug'] ?? '');
    $desc=(string)($_POST['description'] ?? '');
    if (!$slug) $slug = slugify($name);

    if (!$err && $mode==='create') {
        try { $st=$pdo->prepare("INSERT INTO categories (name,slug,description) VALUES (?,?,?)"); $st->execute([$name,$slug,$desc]); $msg='Category created'; }
        catch(Throwable $e){ $err='Create failed'; log_error('admin_cat_create',$e); }
    } elseif (!$err && $mode==='update') {
        $id=(int)($_POST['id'] ?? 0);
        try { $st=$pdo->prepare("UPDATE categories SET name=?, slug=?, description=? WHERE id=?"); $st->execute([$name,$slug,$desc,$id]); $msg='Category updated'; }
        catch(Throwable $e){ $err='Update failed'; log_error('admin_cat_update',$e); }
    } elseif (!$err && $mode==='delete') {
        $id=(int)($_POST['id'] ?? 0);
        try { $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([$id]); $msg='Category deleted'; }
        catch(Throwable $e){ $err='Delete failed'; log_error('admin_cat_delete',$e); }
    }
}

// Fetch data
$cats=$pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$editing=null;
if (isset($_GET['edit'])) {
    $id=(int)$_GET['edit'];
    $st=$pdo->prepare("SELECT * FROM categories WHERE id=?"); $st->execute([$id]);
    $editing=$st->fetch(PDO::FETCH_ASSOC);
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><title>Categories — PerfumeStore</title>
<meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
<main class="section">
  <h1>Categories</h1>
  <nav style="margin-bottom:12px;"><a href="/admin/index.php">← Back</a></nav>
  <?php if ($msg): ?><div role="status" style="padding:8px;border:1px solid #0c0;background:#efe;color:#060;"><?php echo e($msg); ?></div><?php endif; ?>
  <?php if ($err): ?><div role="alert" style="padding:8px;border:1px solid #c00;background:#fee;color:#600;"><?php echo e($err); ?></div><?php endif; ?>

  <section style="margin:16px 0;">
    <h2 style="font-size:18px;"><?php echo $editing?'Edit':'Create'; ?> Category</h2>
    <form method="post" class="form">
      <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
      <input type="hidden" name="mode" value="<?php echo $editing?'update':'create'; ?>">
      <?php if ($editing): ?><input type="hidden" name="id" value="<?php echo (int)$editing['id']; ?>"><?php endif; ?>
      <div><label>Name</label><br><input name="name" value="<?php echo e($editing['name'] ?? ''); ?>" required></div>
      <div><label>Slug</label><br><input name="slug" value="<?php echo e($editing['slug'] ?? ''); ?>" placeholder="auto if blank"></div>
      <div><label>Description</label><br><textarea name="description" rows="3"><?php echo e($editing['description'] ?? ''); ?></textarea></div>
      <button class="btn btn--primary" type="submit"><?php echo $editing?'Update':'Create'; ?></button>
    </form>
  </section>

  <section>
    <h2 style="font-size:18px;">All Categories</h2>
    <table style="width:100%;border-collapse:collapse;">
      <thead><tr><th>ID</th><th>Name</th><th>Slug</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($cats as $c): ?>
          <tr style="border-top:1px solid #eee;">
            <td><?php echo (int)$c['id']; ?></td>
            <td><?php echo e($c['name']); ?></td>
            <td><?php echo e($c['slug']); ?></td>
            <td>
              <a href="?edit=<?php echo (int)$c['id']; ?>">Edit</a>
              <form method="post" action="/admin/categories.php" style="display:inline;" onsubmit="return confirm('Delete?');">
                <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
                <input type="hidden" name="mode" value="delete">
                <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
                <button class="btn btn--ghost" type="submit">Delete</button>
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