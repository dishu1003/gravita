<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

$pageTitle = 'My Wishlist - PerfumeStore';
$pageDescription = 'View and manage your favorite perfumes in your wishlist';
$pageCanonical = SITE_URL . '/wishlist.php';

// Check if user is logged in
$isAuthenticated = isset($_SESSION['user_id']);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?php echo e($pageTitle); ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="<?php echo e($pageDescription); ?>">
<link rel="canonical" href="<?php echo e($pageCanonical); ?>">
<link rel="manifest" href="manifest.json">
<link rel="stylesheet" href="/assets/css/main.css?v=9">
</head>
<body data-authenticated="<?php echo $isAuthenticated ? 'true' : 'false'; ?>">
<?php include __DIR__ . '/partials/header.php'; ?>

<main id="main-content">
    <section class="section">
        <div class="container">
            <div data-wishlist-container>
                <!-- Wishlist content will be dynamically loaded by JavaScript -->
                <div class="wishlist-loading">
                    <div class="loading-spinner"></div>
                    <span>Loading your wishlist...</span>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
<script src="/assets/js/main.js" defer></script>
<script src="/assets/js/wishlist.js" defer></script>
</body>
</html>