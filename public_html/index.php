<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

// Basic SEO variables
$pageTitle = 'PerfumeStore — Luxury Solid Perfume & Attar in India';
$pageDescription = 'Discover luxury solid perfumes and attars crafted in India. Shop Oud, Rose Saffron, Musk, and travel-friendly solid jars. Free shipping offers available.';
$pageCanonical = SITE_URL . '/';
$featuredProducts = [];

// Database connection fallback
$pdo = null;
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    $stmt = $pdo->prepare("SELECT id, name, slug, price, mrp, image, scent_notes, is_bestseller FROM products ORDER BY is_bestseller DESC, created_at DESC LIMIT 8");
    $stmt->execute();
    $featuredProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    // Silently handle database errors for demo purposes
    // log_error('index_products_fetch', $e);
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
<meta property="og:title" content="<?php echo e($pageTitle); ?>">
<meta property="og:description" content="<?php echo e($pageDescription); ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?php echo e($pageCanonical); ?>">
<meta property="og:image" content="<?php echo e(SITE_URL); ?>/assets/images/og-hero.jpg">
<link rel="manifest" href="manifest.json">
<link rel="preload" as="image" href="assets/images/hero-fallback.jpg" imagesrcset="assets/images/hero-fallback.jpg 1x">
<link rel="stylesheet" href="assets/css/main.css">
<style>
/* Critical CSS for above-the-fold */
.header { position: sticky; top: 0; background:#fff; z-index: 1000; box-shadow: 0 1px 8px rgba(0,0,0,.06); }
.header__wrap { display:flex; align-items:center; justify-content:space-between; max-width:1200px; margin:0 auto; padding:12px 16px; }
.hero { position:relative; display:grid; align-items:center; min-height:62vh; }
.hero__media { position:absolute; inset:0; overflow:hidden; z-index:-1; }
.hero__media video, .hero__media img { width:100%; height:100%; object-fit:cover; }
.hero__content { max-width:1200px; margin:0 auto; padding:48px 16px; color:#0b0b0b; }
.hero__title { font-size:clamp(24px,5vw,44px); margin:0 0 8px; }
.hero__subtitle { font-size:clamp(14px,2.5vw,18px); margin:0 0 20px; color:#333; }
.btn { display:inline-block; padding:12px 20px; border-radius:8px; text-decoration:none; font-weight:600; }
.btn--primary { background:#111; color:#fff; }
.btn--ghost { background:transparent; border:2px solid #111; color:#111; }
.grid { display:grid; gap:16px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
@media(min-width: 720px) { .grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
.section { padding:40px 16px; max-width:1200px; margin:0 auto; }
</style>
<script type="application/ld+json">
<?php
// JSON-LD for a simple Product list
$items = [];
foreach ($featuredProducts as $p) {
    $items[] = [
        '@type' => 'Product',
        'name' => $p['name'],
        'image' => SITE_URL . '/uploads/' . $p['image'],
        'description' => 'Luxury perfume from PerfumeStore',
        'sku' => isset($p['sku']) ? $p['sku'] : ('SKU-' . $p['id']),
        'brand' => ['@type' => 'Brand', 'name' => 'PerfumeStore'],
        'offers' => [
            '@type' => 'Offer',
            'priceCurrency' => 'INR',
            'price' => number_format((float)$p['price'], 2, '.', ''),
            'availability' => 'http://schema.org/InStock',
            'url' => SITE_URL . '/product.php?slug=' . urlencode($p['slug']),
        ]
    ];
}
echo json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'itemListElement' => $items
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>
</script>
</head>
<body>
<?php include __DIR__ . '/partials/header.php'; ?>

<main id="main-content" class="will-appear">
  <section class="hero" role="region" aria-label="PerfumeStore hero">
    <div class="hero__media" aria-hidden="true">
      <video autoplay muted loop playsinline poster="assets/images/hero-fallback.jpg">
        <source src="assets/images/hero.mp4" type="video/mp4">
        <img src="assets/images/hero-fallback.jpg" alt="" loading="eager">
      </video>
      <div class="hero__overlay"></div>
    </div>
    <div class="hero__content">
      <div class="hero__badge will-appear">Premium Collection</div>
      <h1 class="hero__title will-appear">Luxury Solid Perfumes & Attars</h1>
      <p class="hero__subtitle will-appear">Handcrafted in India with rare Oud, Rose Saffron, and Musk notes. Long-lasting, alcohol-free options, travel-friendly solid jars.</p>
      <div role="group" aria-label="Primary actions" class="hero__cta will-appear">
        <a class="btn btn--primary btn--with-icon" href="/shop.php">
          <span>Shop Now</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </a>
        <a class="btn btn--ghost btn--with-icon" href="/quiz.php">
          <span>Find Your Signature Scent</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        </a>
      </div>
      
      <div class="hero__scroll-indicator will-appear">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"></path><path d="m19 12-7 7-7-7"></path></svg>
      </div>
      </div>
    </div>
    <div class="hero__scroll-indicator">
      <span>Scroll</span>
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"></path><path d="M19 12l-7 7-7-7"></path></svg>
    </div>
  </section>

  <section class="section featured-section will-appear" aria-labelledby="featured-heading">
    <div class="section__header">
      <h2 id="featured-heading" class="section__title">Featured Collection</h2>
      <p class="section__subtitle">Our most popular and bestselling fragrances</p>
    </div>
    <div class="grid product-grid">
      <?php foreach ($featuredProducts as $product) { include __DIR__ . '/partials/product-card.php'; } ?>
    </div>
    <div class="section__footer">
      <a href="/shop.php" class="btn btn--outline btn--with-icon">
        <span>View All Products</span>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
      </a>
    </div>
  </section>

  <section class="section testimonials-section will-appear" aria-labelledby="testimonials-heading">
    <div class="section__header">
      <h2 id="testimonials-heading" class="section__title">What Our Customers Say</h2>
      <p class="section__subtitle">Real experiences from our fragrance community</p>
    </div>
    <div class="carousel testimonial-carousel" role="region" aria-label="Testimonials" aria-live="polite" data-carousel>
      <div class="carousel__container">
        <?php
        try {
            $tstmt = $pdo->prepare("SELECT id, name, content, rating FROM testimonials WHERE is_approved = 1 ORDER BY created_at DESC LIMIT 6");
            $tstmt->execute();
            $testimonials = $tstmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) { $testimonials = []; log_error('index_testimonials_fetch', $e); }
        foreach ($testimonials as $t) { include __DIR__ . '/partials/testimonial-card.php'; }
        ?>
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

  <section class="section faq-section will-appear" aria-labelledby="faq-heading">
    <div class="section__header">
      <h2 id="faq-heading" class="section__title">Frequently Asked Questions</h2>
      <p class="section__subtitle">Everything you need to know about our products</p>
    </div>
    <div class="faq">
      <div class="faq__item">
        <button class="faq__q" aria-expanded="false" aria-controls="faq1" data-accordion>
          <span>What makes PerfumeStore special?</span>
          <svg class="faq__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        </button>
        <div id="faq1" class="faq__a" hidden>
          <p>We handcraft luxury solid perfumes and attars with high-quality oils, designed for Indian climate and long-lasting performance. Our unique blends are created by master perfumers with decades of experience in traditional Indian perfumery.</p>
        </div>
      </div>

      <div class="faq__item">
        <button class="faq__q" aria-expanded="false" aria-controls="faq2" data-accordion>
          <span>Do you offer alcohol-free options?</span>
          <svg class="faq__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        </button>
        <div id="faq2" class="faq__a" hidden>
          <p>Yes, our attars are alcohol-free and skin-friendly. They are perfect for those who prefer natural fragrances or have sensitivities to alcohol-based perfumes.</p>
        </div>
      </div>

      <div class="faq__item">
        <button class="faq__q" aria-expanded="false" aria-controls="faq3" data-accordion>
          <span>Shipping and returns?</span>
          <svg class="faq__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        </button>
        <div id="faq3" class="faq__a" hidden>
          <p>We ship pan-India with free shipping on orders above ₹999. Returns are accepted on unopened products within 7 days of delivery. Please see our <a href="/shipping-policy.php">shipping policy</a> and <a href="/returns-policy.php">returns policy</a> pages for complete details.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="section newsletter-section will-appear" aria-labelledby="newsletter-heading">
    <div class="newsletter-container">
      <div class="newsletter-content">
        <h2 id="newsletter-heading" class="section__title">Stay Updated</h2>
        <p class="section__subtitle">Join our newsletter for exclusive offers, new releases, and fragrance tips</p>
        <form action="subscribe.php" method="post" class="newsletter-form" novalidate>
          <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
          <div class="form-group">
            <label for="newsletter_email" class="sr-only">Email</label>
            <div class="input-with-button">
              <input id="newsletter_email" name="email" type="email" required 
                     placeholder="Enter your email address" 
                     class="newsletter-input">
              <button class="btn btn--primary btn--with-icon" type="submit">
                <span>Subscribe</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13"></path><path d="M22 2l-7 20-4-9-9-4 20-7z"></path></svg>
              </button>
            </div>
          </div>
          <p class="newsletter-disclaimer">By subscribing, you agree to our <a href="privacy-policy.php">Privacy Policy</a>. We respect your privacy and will never share your information.</p>
        </form>
      </div>
      <div class="newsletter-decoration">
        <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="newsletter-icon"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
<script src="assets/js/main.js" defer></script>
<script src="assets/js/carousel.js" defer></script>
</body>
</html>