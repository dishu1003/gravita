<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

$slug = isset($_GET['slug']) ? sanitize_string($_GET['slug']) : '';
if ($slug === '') {
    http_response_code(404);
    exit('Product not found');
}

try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE slug = ? LIMIT 1");
    $stmt->execute([$slug]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        http_response_code(404);
        exit('Product not found');
    }

    $rel_stmt = $pdo->prepare("SELECT id, name, slug, price, mrp, image, scent_notes FROM products WHERE id <> ? AND category_id <=> ? ORDER BY is_bestseller DESC, RAND() LIMIT 4");
    $rel_stmt->execute([(int)$product['id'], $product['category_id']]);
    $related = $rel_stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <link rel="stylesheet" href="/assets/css/main.css?v=4">
</head>
<body>
<?php include __DIR__ . '/partials/header.php'; ?>

<main class="section">
    <div class="product-page-grid">
        <div class="product-gallery">
            <img src="/uploads/<?php echo e($product['image']); ?>" alt="<?php echo e($product['name']); ?>" loading="eager" class="product-gallery__image">
        </div>

        <div class="product-info">
            <h1 class="product-info__title"><?php echo e($product['name']); ?></h1>

            <div class="product-info__price">
                <span>₹<?php echo number_format((float)$product['price'], 2); ?></span>
                <?php if (!empty($product['mrp']) && $product['mrp'] > $product['price']): ?>
                    <span class="mrp">₹<?php echo number_format((float)$product['mrp'], 2); ?></span>
                <?php endif; ?>
            </div>

            <p class="product-info__description">
                <?php echo nl2br(e($product['description'] ?? '')); ?>
            </p>

            <form data-add-to-cart method="post" action="/cart.php" class="product-add-to-cart-form">
                <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">

                <div class="quantity-selector">
                    <button type="button" class="quantity-btn" data-action="decrease" aria-label="Decrease quantity">-</button>
                    <input id="qty" name="qty" type="number" min="1" value="1" class="quantity-input" aria-label="Quantity">
                    <button type="button" class="quantity-btn" data-action="increase" aria-label="Increase quantity">+</button>
                </div>

                <button class="btn btn--primary btn--with-icon" type="submit">
                    <span>Add to Cart</span>
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                </button>
            </form>

            <div class="product-accordion">
                <?php if (!empty($product['scent_notes'])): ?>
                <div class="faq__item">
                    <button class="faq__q" aria-expanded="true" aria-controls="acc-notes" data-accordion>
                        <span>Scent Notes</span>
                        <svg class="faq__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    </button>
                    <div id="acc-notes" class="faq__a">
                        <p><?php echo e($product['scent_notes']); ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (!empty($related)): ?>
    <section class="related-products">
        <div class="section__header">
            <h2 class="section__title">You May Also Like</h2>
        </div>
        <div class="grid product-grid">
            <?php foreach ($related as $product) { include __DIR__ . '/partials/product-card.php'; } ?>
        </div>
    </section>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
<script src="/assets/js/main.js" defer></script>
</body>
</html>
