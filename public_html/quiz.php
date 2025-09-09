<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['_csrf'] ?? '')) { http_response_code(400); exit('Invalid CSRF'); }
    $strength = sanitize_string($_POST['strength'] ?? '');
    $mood = sanitize_string($_POST['mood'] ?? '');
    $note = sanitize_string($_POST['note'] ?? '');

    $_SESSION['quiz'] = ['strength'=>$strength, 'mood'=>$mood, 'note'=>$note, 'time'=>time()];

    // Simple recommendation based on note
    $like = '';
    if ($note === 'oud') $like = 'Oud';
    elseif ($note === 'rose') $like = 'Rose';
    elseif ($note === 'musk') $like = 'Musk';

    try {
        if ($like) {
            $stmt = $pdo->prepare("SELECT id, name, slug, price, mrp, image, scent_notes FROM products WHERE name LIKE ? OR scent_notes LIKE ? ORDER BY is_bestseller DESC LIMIT 4");
            $stmt->execute(['%'.$like.'%', '%'.$like.'%']);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt = $pdo->query("SELECT id, name, slug, price, mrp, image, scent_notes FROM products ORDER BY is_bestseller DESC, created_at DESC LIMIT 4");
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Throwable $e) {
        log_error('quiz_recommend', $e);
        $result = [];
    }
}

$pageTitle = 'Find Your Signature Scent — PerfumeStore';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?php echo e($pageTitle); ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
<?php include __DIR__ . '/partials/header.php'; ?>
<main class="section">
  <h1 style="font-size:24px;margin-bottom:12px;">Scent Quiz</h1>
  <form method="post" class="form">
    <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">
    <div>
      <label>Preferred strength</label><br>
      <select name="strength" required>
        <option value="subtle">Subtle</option>
        <option value="balanced">Balanced</option>
        <option value="strong">Strong</option>
      </select>
    </div>
    <div>
      <label>Mood</label><br>
      <select name="mood" required>
        <option value="fresh">Fresh</option>
        <option value="warm">Warm</option>
        <option value="romantic">Romantic</option>
      </select>
    </div>
    <div>
      <label>Favorite note</label><br>
      <select name="note" required>
        <option value="oud">Oud</option>
        <option value="rose">Rose</option>
        <option value="musk">Musk</option>
      </select>
    </div>
    <button class="btn btn--primary" type="submit">Get Recommendation</button>
  </form>

  <?php if (is_array($result)): ?>
  <section style="margin-top:24px;">
    <h2 style="font-size:20px;margin-bottom:12px;">Recommended for you</h2>
    <div class="grid">
      <?php foreach ($result as $product) { include __DIR__ . '/partials/product-card.php'; } ?>
    </div>
  </section>
  <?php endif; ?>
</main>
<?php include __DIR__ . '/partials/footer.php'; ?>
<script src="/assets/js/main.js" defer></script>
</body>
</html>
