<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

$isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['_csrf'] ?? '')) {
        if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['success'=>false,'error'=>'Invalid CSRF']); exit; }
        http_response_code(400); exit('Invalid CSRF');
    }

    $action = sanitize_string($_POST['action'] ?? '');
    $pid = (int)($_POST['product_id'] ?? 0);

    if ($action === 'add') {
        $qty = max(1, (int)($_POST['qty'] ?? 1));
        cart_add($pdo, $pid, $qty);
    } elseif ($action === 'update') {
        $qty = max(0, (int)($_POST['qty'] ?? 0));
        cart_set($pdo, $pid, $qty);
    } elseif ($action === 'remove') {
        cart_set($pdo, $pid, 0);
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'cartCount' => get_cart_count($pdo),
            'totals' => cart_totals($pdo),
            'items' => cart_items($pdo) // Send back updated items
        ]);
        exit;
    }
    header('Location: /cart.php');
    exit;
}

$items = cart_items($pdo);
$totals = cart_totals($pdo);
$pageTitle = 'Your Shopping Cart — PerfumeStore';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?php echo e($pageTitle); ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/assets/css/main.css?v=3">
</head>
<body>
<?php include __DIR__ . '/partials/header.php'; ?>
<main class="section">
    <div class="section__header">
        <h1 class="section__title">Your Cart</h1>
    </div>

    <div id="cart-container" class="cart-container">
        <?php if (empty($items)): ?>
            <div class="cart-empty">
                <div class="cart-empty__icon">🛒</div>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added any fragrances yet.</p>
                <a href="/shop.php" class="btn btn--primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($items as $item): ?>
                    <div class="cart-item" data-product-id="<?php echo (int)$item['id']; ?>">
                        <a href="/product.php?slug=<?php echo e($item['slug']); ?>" class="cart-item__img">
                            <img src="/uploads/<?php echo e($item['image']); ?>" alt="<?php echo e($item['name']); ?>" loading="lazy" width="100" height="100">
                        </a>
                        <div class="cart-item__info">
                            <a href="/product.php?slug=<?php echo e($item['slug']); ?>"><?php echo e($item['name']); ?></a>
                            <div class="cart-item__price">₹<?php echo number_format((float)$item['price'], 2); ?></div>
                        </div>
                        <div class="cart-item__actions">
                            <div class="quantity-selector">
                                <button class="quantity-btn" data-action="decrease" aria-label="Decrease quantity">-</button>
                                <input type="number" class="quantity-input" value="<?php echo (int)$item['qty']; ?>" min="1" aria-label="Quantity">
                                <button class="quantity-btn" data-action="increase" aria-label="Increase quantity">+</button>
                            </div>
                             <button class="cart-item__remove" data-action="remove">Remove</button>
                        </div>
                        <div class="cart-item__subtotal" style="text-align:right;font-weight:bold;">
                            ₹<?php echo number_format((float)$item['subtotal'], 2); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <aside class="cart-summary">
                <h2 class="cart-summary__title">Order Summary</h2>
                <div class="cart-summary__row">
                    <span>Subtotal</span>
                    <span id="summary-subtotal">₹<?php echo number_format($totals['subtotal'], 2); ?></span>
                </div>
                <div class="cart-summary__row">
                    <span>Tax</span>
                    <span id="summary-tax">₹<?php echo number_format($totals['tax'], 2); ?></span>
                </div>
                <div class="cart-summary__row">
                    <span>Shipping</span>
                    <span id="summary-shipping">₹<?php echo number_format($totals['shipping'], 2); ?></span>
                </div>
                <div class="cart-summary__row cart-summary__total">
                    <span>Total</span>
                    <span id="summary-total">₹<?php echo number_format($totals['total'], 2); ?></span>
                </div>
                <a href="/checkout.php" class="btn btn--primary">Proceed to Checkout</a>
            </aside>
        <?php endif; ?>
    </div>
</main>
<?php include __DIR__ . '/partials/footer.php'; ?>
<script src="/assets/js/cart.js" defer></script>
</body>
</html>
