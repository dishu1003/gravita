<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

// --- Data Fetching ---
$bestsellers = [];
$newArrivals = [];
$testimonials = [];

try {
    // Fetch Bestsellers
    $stmt_bestsellers = $pdo->prepare("SELECT * FROM products WHERE is_bestseller = 1 ORDER BY created_at DESC LIMIT 8");
    $stmt_bestsellers->execute();
    $bestsellers = $stmt_bestsellers->fetchAll(PDO::FETCH_ASSOC);

    // Fetch New Arrivals
    $stmt_new = $pdo->prepare("SELECT * FROM products ORDER BY created_at DESC LIMIT 4");
    $stmt_new->execute();
    $newArrivals = $stmt_new->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Testimonials
    $stmt_testimonials = $pdo->prepare("SELECT * FROM testimonials WHERE is_approved = 1 ORDER BY created_at DESC LIMIT 6");
    $stmt_testimonials->execute();
    $testimonials = $stmt_testimonials->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $e) {
    log_error('index_page_fetch', $e);
}
// --- End Data Fetching ---

$pageTitle = 'PerfumeStore — Luxury Solid Perfume & Attar in India';
$pageDescription = 'Discover luxury solid perfumes and attars crafted in India. Shop Oud, Rose Saffron, Musk, and travel-friendly solid jars. Free shipping offers available.';
$pageCanonical = SITE_URL . '/';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?php echo e($pageTitle); ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="<?php echo e($pageDescription); ?>">
<link rel="canonical" href="<?php echo e($pageCanonical); ?>">
<meta property="og:title" content="<?php echo e($pageTitle); ?>">
<meta property="og:description" content="<?php echo e($pageDescription); ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?php echo e($pageCanonical); ?>">
<meta property="og:image" content="<?php echo e(SITE_URL); ?>/assets/images/og-hero.jpg">
<link rel="manifest" href="manifest.json">
<link rel="stylesheet" href="/assets/css/main.css?v=10">
<!-- GSAP for advanced animations -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.4/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.4/ScrollTrigger.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.4/TextPlugin.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.4/CustomEase.min.js"></script>
</head>
<body>
<progress value="0" max="100"></progress>
<?php include __DIR__ . '/partials/header.php'; ?>

<main id="main-content">
  <section class="hero" role="region" aria-label="PerfumeStore hero">
    <div class="hero__media" aria-hidden="true">
      <video autoplay muted loop playsinline poster="/assets/images/hero-fallback.jpg">
        <source src="/assets/videos/hero-video.mp4" type="video/mp4">
      </video>
      <div class="hero__overlay"></div>
    </div>
    <div class="hero__content">
      <h1 class="hero__title will-appear">Artisanal Scents, Redefined.</h1>
      <p class="hero__subtitle will-appear">Discover our collection of handcrafted perfumes and attars, made with the finest natural ingredients.</p>
      <div class="hero__cta will-appear">
        <a href="/shop.php" class="btn btn--accent">Explore the Collection</a>
        <a href="/quiz.php" class="btn btn--outline">Find Your Scent</a>
      </div>
    </div>
  </section>

  <div class="section">
    <div class="feature-callouts">
        <div class="callout">
            <div class="callout__icon">🌿</div>
            <h3 class="callout__title">Alcohol-Free</h3>
            <p class="callout__text">Natural and skin-friendly options available.</p>
        </div>
        <div class="callout">
            <div class="callout__icon">🇮🇳</div>
            <h3 class="callout__title">Handcrafted in India</h3>
            <p class="callout__text">Made with passion by local artisans.</p>
        </div>
        <div class="callout">
            <div class="callout__icon">✨</div>
            <h3 class="callout__title">Long-Lasting</h3>
            <p class="callout__text">High-quality scents that endure.</p>
        </div>
        <div class="callout">
            <div class="callout__icon">🚚</div>
            <h3 class="callout__title">Free Shipping</h3>
            <p class="callout__text">Available on all orders over ₹999.</p>
        </div>
    </div>
  </div>

  <section class="section will-appear" aria-labelledby="bestsellers-heading">
    <div class="section__header">
      <h2 id="bestsellers-heading" class="section__title">Our Bestsellers</h2>
      <p class="section__subtitle">Loved by our customers, curated for you.</p>
    </div>
    <div class="grid product-grid">
      <?php foreach ($bestsellers as $product) { include __DIR__ . '/partials/product-card.php'; } ?>
    </div>
  </section>

  <section class="section will-appear" aria-labelledby="new-arrivals-heading">
    <div class="section__header">
      <h2 id="new-arrivals-heading" class="section__title">New Arrivals</h2>
      <p class="section__subtitle">Discover our latest handcrafted fragrances.</p>
    </div>
    <div class="grid product-grid">
      <?php foreach ($newArrivals as $product) { include __DIR__ . '/partials/product-card.php'; } ?>
    </div>
    <div class="section__footer">
      <a href="/shop.php" class="btn btn--outline btn--with-icon">
        <span>View All Products</span>
        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
      </a>
    </div>
  </section>

  <?php if (!empty($testimonials)): ?>
  <section class="section testimonials-section will-appear" aria-labelledby="testimonials-heading">
    <div class="section__header">
      <h2 id="testimonials-heading" class="section__title">What Our Customers Say</h2>
    </div>
    <div class="carousel testimonial-carousel" role="region" aria-label="Testimonials" data-carousel>
      <div class="carousel__container">
        <?php foreach ($testimonials as $t): ?>
            <div class="carousel__item">
                <?php
                    $testimonial = $t;
                    include __DIR__ . '/partials/testimonial-card.php';
                ?>
            </div>
        <?php endforeach; ?>
      </div>
      <div class="carousel__controls">
        <button class="carousel__btn prev" aria-label="Previous testimonial" data-carousel-prev>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
        </button>
        <div class="carousel__pagination" data-carousel-pagination></div>
        <button class="carousel__btn next" aria-label="Next testimonial" data-carousel-next>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
        </button>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <section class="section will-appear">
    <div class="trust-badges">
      <div class="trust-badge">
        <div class="trust-badge-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
          </svg>
        </div>
        <span class="trust-badge-text">4.9/5 Rating</span>
      </div>
      <div class="trust-badge">
        <div class="trust-badge-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 11H3v10h6M12 2v10m5-10v10h6v10"></path>
          </svg>
        </div>
        <span class="trust-badge-text">10,000+ Happy Customers</span>
      </div>
      <div class="trust-badge">
        <div class="trust-badge-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
            <line x1="9" y1="9" x2="9.01" y2="9"></line>
            <line x1="15" y1="9" x2="15.01" y2="9"></line>
          </svg>
        </div>
        <span class="trust-badge-text">100% Authentic</span>
      </div>
      <div class="trust-badge">
        <div class="trust-badge-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
          </svg>
        </div>
        <span class="trust-badge-text">Secure Payment</span>
      </div>
    </div>
  </section>

</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
<script src="/assets/js/main.js" defer></script>
<script src="/assets/js/carousel.js" defer></script>
<script src="/assets/js/advanced-search.js" defer></script>
<script src="/assets/js/wishlist.js" defer></script>
<script src="/assets/js/animations.js" defer></script>
<script src="/assets/js/social-proof.js" defer></script>
</body>
</html>
