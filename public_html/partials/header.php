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
      <button class="search-trigger" data-search-trigger aria-label="Search" title="Search (Ctrl+K)">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"></circle>
          <path d="m21 21-4.35-4.35"></path>
        </svg>
      </button>
      <a class="cart" href="/cart.php" aria-label="Cart">
        <span>Cart</span>
        <span class="badge" aria-live="polite" aria-atomic="true"><?php echo (int)$cartCount; ?></span>
      </a>
    </nav>
  </div>
</header>