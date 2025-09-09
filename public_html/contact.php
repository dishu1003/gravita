<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

$sent = false;
$error = null;
$form_data = ['name' => '', 'email' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['_csrf'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $name = sanitize_string($_POST['name'] ?? '');
        $email = sanitize_string($_POST['email'] ?? '');
        $message = sanitize_string($_POST['message'] ?? '');
        $form_data = ['name' => $name, 'email' => $email, 'message' => $message];

        if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($message) < 10) {
            $error = 'Please fill out all fields correctly. Message must be at least 10 characters.';
        } else {
            $html = "<p>New contact message from your website:</p>
                     <ul>
                         <li><strong>Name:</strong> " . e($name) . "</li>
                         <li><strong>Email:</strong> " . e($email) . "</li>
                     </ul>
                     <p><strong>Message:</strong><br>" . nl2br(e($message)) . "</p>";

            $sent = send_email($pdo, ADMIN_EMAIL, 'New Contact Form Message', $html);

            if ($sent) {
                // Clear form data on success
                $form_data = ['name' => '', 'email' => '', 'message' => ''];
            } else {
                $error = 'Sorry, there was an issue sending your message. Please try again later.';
            }
        }
    }
}

$pageTitle = 'Contact Us — PerfumeStore';
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
        <h1 class="section__title">Get In Touch</h1>
        <p class="section__subtitle">Have a question or a comment? Drop us a line below. We'd love to hear from you.</p>
    </div>

    <div class="contact-form-container">
        <?php if ($sent): ?>
            <div class="alert alert--success" role="status">
                <strong>Thank you!</strong> Your message has been sent successfully. We’ll get back to you shortly.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert--error" role="alert">
                <strong>Oops!</strong> <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="form">
            <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">

            <div class="form-group">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" id="name" name="name" class="form-control" value="<?php echo e($form_data['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo e($form_data['email']); ?>" required>
            </div>

            <div class="form-group">
                <label for="message" class="form-label">Message</label>
                <textarea id="message" name="message" class="form-control" rows="6" required><?php echo e($form_data['message']); ?></textarea>
            </div>

            <button class="btn btn--primary btn--with-icon" type="submit">
                <span>Send Message</span>
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13"></path><path d="M22 2l-7 20-4-9-9-4 20-7z"></path></svg>
            </button>
        </form>
    </div>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
