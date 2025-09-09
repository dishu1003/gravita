<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';
require_admin();

$msg=null;$err=null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['_csrf'] ?? '')) { $err='Invalid CSRF'; }
    $mode = sanitize_string($_POST['mode'] ?? '');
    $id = (int)($_POST['id'] ?? 0);

    if (!$err && $mode === 'approve') {
        $pdo->prepare("UPDATE testimonials SET is_approved=1 WHERE id=?")->execute([$id]);
        $msg='Approved';
    } elseif (!$err && $mode === 'hide') {
        $pdo->prepare("UPDATE testimonials SET is_approved=0 WHERE id=?")->execute([$id]);
        $msg='Hidden';
    } elseif (!$err && $mode === 'delete') {
        $pdo->prepare("DELETE FROM testimonials WHERE id=?")->execute([$id]);
        $msg='Deleted';
    }
}

$rows = $pdo->query("SELECT * FROM testimonials ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><title>Testimonials — PerfumeStore</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
<main class="section">
  <h1>Testimonials</h1>
  <nav style="margin-bottom:12px;"><a href="/admin/index.php">← Back</a></nav>
  <?php if ($msg): ?><div role="status" style="padding:8px;border:1px solid #0c0;background:#efe;color:#060;"><?php echo e($msg); ?></div><?php endif; ?>
  <?php if ($err): ?><div role="alert" style="padding:8px;border:1px solid #c00;background:#fee;color:#600;"><?php echo e($err); ?></div><?php endif; ?>

  <?php foreach ($rows as $t): ?>
    <article style="border:1px solid #eee;border-radius:12px;padding:12px;margin-bottom:10px;">
      <header><strong><?php echo e($t['name']); ?></strong> — <?php echo (int)$t['rating']; ?>/5</header>
      <p><?php echo nl2br(e($t['content'])); ?></p>
      <form method="post" style="display:inline;">
        <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
        <input type="hidden" name="id" value="<?php echo (int)$t['id']; ?>">
        <input type="hidden" name="mode" value="<?php echo $t['is_approved']?'hide':'approve'; ?>">
        <button class="btn btn--ghost" type="submit"><?php echo $t['is_approved']?'Hide':'Approve'; ?></button>
      </form>
      <form method="post" style="display:inline;" onsubmit="return confirm('Delete?');">
        <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
        <input type="hidden" name="id" value="<?php echo (int)$t['id']; ?>">
        <input type="hidden" name="mode" value="delete">
        <button class="btn btn--ghost" type="submit">Delete</button>
      </form>
    </article>
  <?php endforeach; ?>
</main>
</body>
</html>