<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

$result = null;
$submitted_answers = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['_csrf'] ?? '')) { http_response_code(400); exit('Invalid CSRF'); }

    $strength = sanitize_string($_POST['strength'] ?? '');
    $mood = sanitize_string($_POST['mood'] ?? '');
    $note = sanitize_string($_POST['note'] ?? '');

    $submitted_answers = ['strength' => $strength, 'mood' => $mood, 'note' => $note];
    $_SESSION['quiz'] = ['answers' => $submitted_answers, 'time' => time()];

    // Simple recommendation based on note
    $like = '';
    if ($note === 'oud') $like = 'Oud';
    elseif ($note === 'rose') $like = 'Rose';
    elseif ($note === 'musk') $like = 'Musk';
    elseif ($note === 'floral') $like = 'Floral';


    try {
        if ($like) {
            $stmt = $pdo->prepare("SELECT id, name, slug, price, mrp, image, scent_notes, is_bestseller FROM products WHERE name LIKE ? OR scent_notes LIKE ? ORDER BY is_bestseller DESC LIMIT 4");
            $stmt->execute(['%'.$like.'%', '%'.$like.'%']);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt = $pdo->query("SELECT id, name, slug, price, mrp, image, scent_notes, is_bestseller FROM products ORDER BY is_bestseller DESC, created_at DESC LIMIT 4");
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
    <div class="section__header">
        <h1 class="section__title">Find Your Signature Scent</h1>
        <p class="section__subtitle">Answer a few questions and we'll recommend the perfect fragrance for you.</p>
    </div>

    <form method="post" class="quiz-form">
        <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">

        <fieldset class="quiz-step">
            <legend class="quiz-step__legend">1. What's your preferred scent strength?</legend>
            <div class="quiz-options">
                <div class="quiz-option">
                    <input type="radio" id="strength-subtle" name="strength" value="subtle" required <?php echo (isset($submitted_answers['strength']) && $submitted_answers['strength'] === 'subtle') ? 'checked' : ''; ?>>
                    <label for="strength-subtle" class="quiz-option__label">
                        <span class="quiz-option__icon">🌬️</span>
                        <span class="quiz-option__title">Subtle & Light</span>
                    </label>
                </div>
                <div class="quiz-option">
                    <input type="radio" id="strength-balanced" name="strength" value="balanced" required <?php echo (isset($submitted_answers['strength']) && $submitted_answers['strength'] === 'balanced') ? 'checked' : ''; ?>>
                    <label for="strength-balanced" class="quiz-option__label">
                        <span class="quiz-option__icon">平衡</span>
                        <span class="quiz-option__title">Balanced</span>
                    </label>
                </div>
                <div class="quiz-option">
                    <input type="radio" id="strength-strong" name="strength" value="strong" required <?php echo (isset($submitted_answers['strength']) && $submitted_answers['strength'] === 'strong') ? 'checked' : ''; ?>>
                    <label for="strength-strong" class="quiz-option__label">
                        <span class="quiz-option__icon">💪</span>
                        <span class="quiz-option__title">Strong & Bold</span>
                    </label>
                </div>
            </div>
        </fieldset>

        <fieldset class="quiz-step">
            <legend class="quiz-step__legend">2. What mood are you going for?</legend>
            <div class="quiz-options">
                <div class="quiz-option">
                    <input type="radio" id="mood-fresh" name="mood" value="fresh" required <?php echo (isset($submitted_answers['mood']) && $submitted_answers['mood'] === 'fresh') ? 'checked' : ''; ?>>
                    <label for="mood-fresh" class="quiz-option__label">
                        <span class="quiz-option__icon">🌿</span>
                        <span class="quiz-option__title">Fresh & Clean</span>
                    </label>
                </div>
                <div class="quiz-option">
                    <input type="radio" id="mood-warm" name="mood" value="warm" required <?php echo (isset($submitted_answers['mood']) && $submitted_answers['mood'] === 'warm') ? 'checked' : ''; ?>>
                    <label for="mood-warm" class="quiz-option__label">
                        <span class="quiz-option__icon">🔥</span>
                        <span class="quiz-option__title">Warm & Spicy</span>
                    </label>
                </div>
                <div class="quiz-option">
                    <input type="radio" id="mood-romantic" name="mood" value="romantic" required <?php echo (isset($submitted_answers['mood']) && $submitted_answers['mood'] === 'romantic') ? 'checked' : ''; ?>>
                    <label for="mood-romantic" class="quiz-option__label">
                        <span class="quiz-option__icon">🌹</span>
                        <span class="quiz-option__title">Romantic</span>
                    </label>
                </div>
            </div>
        </fieldset>

        <fieldset class="quiz-step">
            <legend class="quiz-step__legend">3. Which is your favorite note?</legend>
            <div class="quiz-options">
                <div class="quiz-option">
                    <input type="radio" id="note-oud" name="note" value="oud" required <?php echo (isset($submitted_answers['note']) && $submitted_answers['note'] === 'oud') ? 'checked' : ''; ?>>
                    <label for="note-oud" class="quiz-option__label">
                        <span class="quiz-option__icon">🪵</span>
                        <span class="quiz-option__title">Oud</span>
                    </label>
                </div>
                <div class="quiz-option">
                    <input type="radio" id="note-rose" name="note" value="rose" required <?php echo (isset($submitted_answers['note']) && $submitted_answers['note'] === 'rose') ? 'checked' : ''; ?>>
                    <label for="note-rose" class="quiz-option__label">
                        <span class="quiz-option__icon">🌷</span>
                        <span class="quiz-option__title">Rose</span>
                    </label>
                </div>
                <div class="quiz-option">
                    <input type="radio" id="note-musk" name="note" value="musk" required <?php echo (isset($submitted_answers['note']) && $submitted_answers['note'] === 'musk') ? 'checked' : ''; ?>>
                    <label for="note-musk" class="quiz-option__label">
                        <span class="quiz-option__icon">✨</span>
                        <span class="quiz-option__title">Musk</span>
                    </label>
                </div>
                 <div class="quiz-option">
                    <input type="radio" id="note-floral" name="note" value="floral" required <?php echo (isset($submitted_answers['note']) && $submitted_answers['note'] === 'floral') ? 'checked' : ''; ?>>
                    <label for="note-floral" class="quiz-option__label">
                        <span class="quiz-option__icon">🌸</span>
                        <span class="quiz-option__title">Floral</span>
                    </label>
                </div>
            </div>
        </fieldset>

        <div class="quiz-submit-container">
            <button class="btn btn--primary btn--with-icon" type="submit">
                <span>Get My Recommendation</span>
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </button>
        </div>
    </form>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_array($result)): ?>
    <section class="quiz-results section">
        <div class="section__header">
            <h2 class="section__title">Recommended For You</h2>
            <p class="section__subtitle">Based on your preferences, we think you'll love these.</p>
        </div>
        <?php if (empty($result)): ?>
            <p style="text-align:center;">We couldn't find a perfect match right now, but please browse our full collection!</p>
        <?php else: ?>
            <div class="grid product-grid">
                <?php foreach ($result as $product) { include __DIR__ . '/partials/product-card.php'; } ?>
            </div>
        <?php endif; ?>
    </section>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
