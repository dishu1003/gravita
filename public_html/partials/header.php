<?php
$cartCount = get_cart_count($pdo);
$siteTitle = get_setting($pdo, 'site_title', 'PerfumeStore');
?>
<header class="header" role="banner">
  <div class="header__wrap">
    <a class="logo" href="/" aria-label="Home">
      <img src="/assets/images/logo.svg" alt="PerfumeStore" width="140" height="32" loading="eager">
    </a>
    <nav class="nav" aria-label="Primary">
      <a href="/shop.php">Shop</a>
      <a href="/quiz.php">Quiz</a>
      <a href="/contact.php">Contact</a>
      <a href="/account/orders.php">My Orders</a>
      <a class="cart" href="/cart.php" aria-label="Cart">
        <span>Cart</span>
        <span class="badge" aria-live="polite" aria-atomic="true"><?php echo (int)$cartCount; ?></span>
      </a>
    </nav>
  </div>
</header>