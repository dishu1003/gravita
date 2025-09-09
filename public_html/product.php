<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

$slug = isset($_GET['slug']) ? sanitize_string($_GET['slug']) : '';
if ($slug === '') { http_response_code(404); exit('Product not found'); }

try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE slug = ? LIMIT 1");
    $stmt->execute([$slug]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) { http_response_code(404); exit('Product not found'); }

    $rel = $pdo->prepare("SELECT id, name, slug, price, mrp, image, scent_notes FROM products WHERE id <> ? AND category_id <=> ? ORDER BY created_at DESC LIMIT 4");
    $rel->execute([(int)$product['id'], $product['category_id']]);
    $related = $rel->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    log_error('product_fetch', $e);
    http_response_code(500);
    exit('Error loading product');
}

$pageTitle = e($product['name']) . ' — PerfumeStore';
$pageDescription = substr(strip_tags($product['description'] ?? ''), 0, 150);
$pageCanonical = SITE_URL . '/product.php?slug=' . urlencode($slug);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?php echo e($pageTitle); ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="<?php echo e($pageDescription); ?>">
<link rel="canonical" href="<?php echo e($pageCanonical); ?>">
<meta property="og:type" content="product">
<meta property="og:title" content="<?php echo e($product['name']); ?>">
<meta property="og:description" content="<?php echo e($pageDescription); ?>">
<meta property="og:image" content="<?php echo e(SITE_URL); ?>/uploads/<?php echo e($product['image']); ?>">
<link rel="stylesheet" href="/assets/css/main.css">
<script type="application/ld+json">
<?php
echo json_encode([
  '@context'=>'https://schema.org',
  '@type'=>'Product',
  'name'=>$product['name'],
  'image'=>[SITE_URL.'/uploads/'.$product['image']],
  'description'=>$pageDescription,
  'sku'=>$product['sku'] ?: ('SKU-'.$product['id']),
  'brand'=>['@type'=>'Brand','name'=>'PerfumeStore'],
  'offers'=>[
    '@type'=>'Offer',
    'priceCurrency'=>'INR',
    'price'=>number_format((float)$product['price'],2,'.',''),
    'availability'=>'http://schema.org/InStock',
    'url'=>$pageCanonical
  ],
  'aggregateRating'=>[
    '@type'=>'AggregateRating','ratingValue'=>'4.7','reviewCount'=>'27'
  ]
], JSON_UNESCAPED_SLASHES);
?>
</script>
</head>
<body>
<?php include __DIR__ . '/partials/header.php'; ?>
<main class="section">
  <div style="display:grid;grid-template-columns:1fr;gap:24px;">
    <div class="product__media">
      <img src="/uploads/<?php echo e($product['image']); ?>" alt="<?php echo e($product['name']); ?>" loading="eager" style="width:100%;border-radius:12px;">
    </div>
    <div class="product__info">
      <h1 style="font-size:28px;margin:0 0 8px;"><?php echo e($product['name']); ?></h1>
      <div class="product-card__price">
        <strong>₹<?php echo number_format((float)$product['price'], 2); ?></strong>
        <?php if (!empty($product['mrp'])): ?>
          <span class="mrp">₹<?php echo number_format((float)$product['mrp'], 2); ?></span>
        <?php endif; ?>
      </div>
      <?php if (!empty($product['scent_notes'])): ?>
      <p><strong>Scent notes:</strong> <?php echo e($product['scent_notes']); ?></p>
      <?php endif; ?>
      <div><?php echo nl2br(e($product['description'] ?? '')); ?></div>
      <form data-add-to-cart method="post" action="/cart.php" style="margin-top:16px;">
        <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
        <label for="qty" class="sr-only">Quantity</label>
        <input id="qty" name="qty" type="number" min="1" value="1" style="width:80px;padding:10px;border:1px solid #ddd;border-radius:8px;">
        <button class="btn btn--primary" type="submit" style="margin-left:8px;">Add to cart</button>
      </form>
    </div>
  </div>

  <?php if (!empty($related)): ?>
  <section style="margin-top:32px;">
    <h2 style="font-size:20px;margin-bottom:12px;">You may also like</h2>
    <div class="grid">
      <?php foreach ($related as $product) { include __DIR__ . '/partials/product-card.php'; } ?>
    </div>
  </section>
  <?php endif; ?>
</main>
<?php include __DIR__ . '/partials/footer.php'; ?>
<script src="/assets/js/main.js" defer></script>
</body>
</html>
