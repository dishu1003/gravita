<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

$pageTitle = 'Shop — PerfumeStore';
$pageDescription = 'Browse luxury solid perfumes and attars.';
$pageCanonical = SITE_URL . '/shop.php';

$category = isset($_GET['cat']) ? sanitize_string($_GET['cat']) : '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

$where = '1=1';
$params = [];
if ($category !== '') {
    $where .= ' AND c.slug = ?';
    $params[] = $category;
}

try {
    // Count
    $csql = "SELECT COUNT(*) AS c
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE $where";
    $cstmt = $pdo->prepare($csql);
    $cstmt->execute($params);
    $total = (int)$cstmt->fetchColumn();

    // Data (inline LIMIT/OFFSET ints to avoid MySQL placeholder issue)
    $limit = (int)$perPage;
    $off = (int)$offset;
    $sql = "SELECT p.id, p.name, p.slug, p.price, p.mrp, p.image, p.scent_notes, p.is_bestseller
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE $where
            ORDER BY p.created_at DESC
            LIMIT $limit OFFSET $off";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $cats = $pdo->query("SELECT name, slug FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    log_error('shop_fetch', $e);
    if (defined('DEBUG') && DEBUG) { die('SQL error: ' . e($e->getMessage())); }
    $products = [];
    $cats = [];
    $total = 0;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?php echo e($pageTitle); ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="<?php echo e($pageDescription); ?>">
<link rel="canonical" href="<?php echo e($pageCanonical); ?>">
<link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/partials/header.php'; ?>
<main class="section">
  <h1 style="font-size:24px;margin-bottom:16px;">Shop</h1>
  <form method="get" action="/shop.php" class="form" style="margin-bottom:16px;">
    <label for="cat" class="sr-only">Category</label>
    <select id="cat" name="cat" onchange="this.form.submit()">
      <option value="">All categories</option>
      <?php foreach ($cats as $c): ?>
        <option value="<?php echo e($c['slug']); ?>" <?php echo $category===$c['slug']?'selected':''; ?>><?php echo e($c['name']); ?></option>
      <?php endforeach; ?>
    </select>
  </form>

  <?php if (empty($products)): ?>
    <p>No products found. <a href="/admin/products.php">Add products</a> or clear filters.</p>
  <?php else: ?>
    <div class="grid">
      <?php foreach ($products as $product) { include __DIR__ . '/partials/product-card.php'; } ?>
    </div>
  <?php endif; ?>

  <?php
    $pages = max(1, (int)ceil($total / $perPage));
    if ($pages > 1):
  ?>
  <nav aria-label="Pagination" style="margin-top:16px;">
    <?php for ($i=1;$i<=$pages;$i++): ?>
      <a href="?<?php echo http_build_query(['cat'=>$category,'page'=>$i]); ?>" style="margin-right:8px;<?php if($i===$page) echo 'font-weight:700;'; ?>"><?php echo $i; ?></a>
    <?php endfor; ?>
  </nav>
  <?php endif; ?>
</main>
<?php include __DIR__ . '/partials/footer.php'; ?>
<script src="/assets/js/main.js" defer></script>
</body>
</html>