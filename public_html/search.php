<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

$q = sanitize_string($_GET['q'] ?? '');
$results = [];
if ($q !== '') {
    try {
        $stmt = $pdo->prepare("SELECT id, name, slug, price, mrp, image, scent_notes FROM products WHERE name LIKE ? OR description LIKE ? OR scent_notes LIKE ? ORDER BY created_at DESC LIMIT 24");
        $like = '%'.$q.'%';
        $stmt->execute([$like, $like, $like]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        log_error('search', $e);
    }
}

$pageTitle = 'Search — PerfumeStore';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><title><?php echo e($pageTitle); ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/partials/header.php'; ?>
<main class="section">
  <h1>Search</h1>
  <form method="get" action="/search.php" class="form">
    <input type="search" name="q" value="<?php echo e($q); ?>" placeholder="Search scents, notes..." style="min-width:260px;">
    <button class="btn btn--primary" type="submit">Search</button>
  </form>
  <?php if ($q !== ''): ?>
    <h2 style="font-size:18px;margin-top:12px;">Results for “<?php echo e($q); ?>”</h2>
    <div class="grid">
      <?php foreach ($results as $product) { include __DIR__ . '/partials/product-card.php'; } ?>
    </div>
  <?php endif; ?>
</main>
<?php include __DIR__ . '/partials/footer.php'; ?>
<script src="/assets/js/main.js" defer></script>
</body>
</html>
