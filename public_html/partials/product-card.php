<?php
/**
 * Product Card Template
 * Displays a single product in a card format
 */

// Ensure $product is set and has required fields
if (!isset($product) || empty($product['id']) || empty($product['name'])) {
    return;
}

// Set default values for optional fields
$product['slug'] = $product['slug'] ?? '';
$product['price'] = $product['price'] ?? 0;
$product['mrp'] = $product['mrp'] ?? 0;
$product['image'] = $product['image'] ?? 'default-product.jpg';
$product['scent_notes'] = $product['scent_notes'] ?? '';
$product['is_bestseller'] = $product['is_bestseller'] ?? 0;

// Calculate discount percentage if MRP is higher than price
$discount = 0;
if ($product['mrp'] > $product['price']) {
    $discount = round(($product['mrp'] - $product['price']) / $product['mrp'] * 100);
}

// Format price with Indian Rupee symbol
$formattedPrice = '₹' . number_format($product['price']);
$formattedMrp = '₹' . number_format($product['mrp']);

// Generate product URL
$productUrl = '/product.php?slug=' . urlencode($product['slug']);
?>

<div class="product-card" data-product-id="<?php echo (int)$product['id']; ?>">
    <?php if ($product['is_bestseller']): ?>
    <div class="product-card__badge">Bestseller</div>
    <?php endif; ?>
    
    <?php if ($discount > 0): ?>
    <div class="product-card__discount"><?php echo $discount; ?>% OFF</div>
    <?php endif; ?>
    
    <a href="<?php echo e($productUrl); ?>" class="product-card__img-link">
        <div class="product-card__img">
            <img src="/uploads/<?php echo e($product['image']); ?>" 
                 alt="<?php echo e($product['name']); ?>" 
                 loading="lazy"
                 width="300"
                 height="300">
        </div>
    </a>
    
    <div class="product-card__body">
        <h3 class="product-card__title">
            <a href="<?php echo e($productUrl); ?>"><?php echo e($product['name']); ?></a>
        </h3>
        
        <?php if (!empty($product['scent_notes'])): ?>
        <div class="product-card__notes"><?php echo e($product['scent_notes']); ?></div>
        <?php endif; ?>
        
        <div class="product-card__price">
            <span class="current"><?php echo $formattedPrice; ?></span>
            <?php if ($product['mrp'] > $product['price']): ?>
            <span class="mrp"><?php echo $formattedMrp; ?></span>
            <?php endif; ?>
        </div>
        
        <div class="product-card__cta">
            <form action="/cart.php" method="post" data-add-to-cart>
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                <input type="hidden" name="action" value="add">
                <button type="submit" class="btn">Add to Cart</button>
            </form>
        </div>
    </div>
</div>