<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

// --- Filtering & Sorting Logic ---

// 1. Fetch all categories for the filter list
$categories = [];
try {
    $stmt = $pdo->prepare("SELECT id, name, slug FROM categories ORDER BY name ASC");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    log_error('shop_categories_fetch', $e);
}

// 2. Define allowed sort options
$sort_options = [
    'default' => 'p.is_bestseller DESC, p.created_at DESC',
    'price_asc' => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'name_asc' => 'p.name ASC',
    'name_desc' => 'p.name DESC',
];

// 3. Get current filter/sort values from URL
$current_category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$current_sort = isset($_GET['sort']) && isset($sort_options[$_GET['sort']]) ? $_GET['sort'] : 'default';

// 4. Build the SQL query
$sql = "SELECT p.id, p.name, p.slug, p.price, p.mrp, p.image, p.scent_notes, p.is_bestseller FROM products p";
$params = [];

if ($current_category_id) {
    $sql .= " WHERE p.category_id = ?";
    $params[] = $current_category_id;
}

$sql .= " ORDER BY " . $sort_options[$current_sort];

// --- End of Logic ---


// Fetch products based on the constructed query
$products = [];
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    log_error('shop_products_fetch', $e);
    $products = []; // Ensure products is empty on error
}

// SEO and page variables
$pageTitle = 'Shop Our Collection';
$pageDescription = 'Explore our full range of handcrafted solid perfumes and attars. Find your perfect scent today.';
$pageCanonical = SITE_URL . '/shop.php';
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
    <style>
      .shop-controls {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 32px;
        padding-bottom: 24px;
        border-bottom: 1px solid var(--border);
      }
      .filter-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
      }
      .filter-nav a {
        padding: 8px 16px;
        border-radius: var(--radius-sm);
        text-decoration: none;
        font-weight: 500;
        transition: var(--transition-fast);
        border: 1px solid transparent;
      }
      .filter-nav a:hover {
        background-color: var(--bg-alt);
        border-color: var(--border);
      }
      .filter-nav a.active {
        background-color: var(--accent);
        color: #fff;
        border-color: var(--accent);
      }
      .sort-control select {
        padding: 10px 14px;
        border-radius: var(--radius-sm);
        border: 1px solid var(--border);
        background-color: var(--bg);
        font-size: 16px;
      }
    </style>
</head>
<body>
<?php include __DIR__ . '/partials/header.php'; ?>
<main id="main-content">
    <section class="section shop-section">
        <div class="section__header">
            <h1 class="section__title">Our Collection</h1>
            <p class="section__subtitle">Browse our artisanal fragrances or filter by category.</p>
        </div>

        <form method="get" action="/shop.php" id="shop-controls-form">
            <div class="shop-controls">
                <nav class="filter-nav" aria-label="Product Categories">
                    <a href="/shop.php?sort=<?php echo e($current_sort); ?>" class="<?php echo !$current_category_id ? 'active' : ''; ?>">All</a>
                    <?php foreach ($categories as $category): ?>
                        <a href="/shop.php?category=<?php echo (int)$category['id']; ?>&sort=<?php echo e($current_sort); ?>" class="<?php echo $current_category_id === (int)$category['id'] ? 'active' : ''; ?>">
                            <?php echo e($category['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
                <div class="sort-control">
                    <label for="sort-by" class="sr-only">Sort by</label>
                    <select name="sort" id="sort-by" onchange="document.getElementById('shop-controls-form').submit()">
                        <option value="default" <?php echo $current_sort === 'default' ? 'selected' : ''; ?>>Sort by: Default</option>
                        <option value="price_asc" <?php echo $current_sort === 'price_asc' ? 'selected' : ''; ?>>Sort by: Price Low to High</option>
                        <option value="price_desc" <?php echo $current_sort === 'price_desc' ? 'selected' : ''; ?>>Sort by: Price High to Low</option>
                        <option value="name_asc" <?php echo $current_sort === 'name_asc' ? 'selected' : ''; ?>>Sort by: Name A-Z</option>
                        <option value="name_desc" <?php echo $current_sort === 'name_desc' ? 'selected' : ''; ?>>Sort by: Name Z-A</option>
                    </select>
                    <!-- Hidden input to carry over category selection when sorting -->
                    <?php if ($current_category_id): ?>
                        <input type="hidden" name="category" value="<?php echo (int)$current_category_id; ?>">
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <?php if (empty($products)): ?>
            <p style="text-align: center; padding: 40px;">No products found matching your criteria.</p>
        <?php else: ?>
            <div class="grid product-grid">
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