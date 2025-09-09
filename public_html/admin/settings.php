<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';
require_admin();

$pageTitle = 'Settings';
$msg = null;
$err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['_csrf'] ?? '')) { $err = 'Invalid CSRF token.'; }
    else {
        $settings_to_update = $_POST['settings'] ?? [];
        try {
            $stmt = $pdo->prepare("UPDATE settings SET `value` = ? WHERE `key` = ?");
            foreach ($settings_to_update as $key => $value) {
                $stmt->execute([sanitize_string($value), $key]);
            }
            $msg = 'Settings updated successfully.';
        } catch (Throwable $e) {
            $err = 'Failed to update settings.';
            log_error('admin_settings_update', $e);
        }
    }
}

$settings = get_settings($pdo);

?>
<?php include __DIR__ . '/partials/header.php'; ?>

<?php if ($msg): ?><div class="alert alert--success"><?php echo e($msg); ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert--error"><?php echo e($err); ?></div><?php endif; ?>

<div class="admin-card">
    <h2>Site Settings</h2>
    <form method="post" class="form">
        <input type="hidden" name="_csrf" value="<?php echo e(csrf_token()); ?>">

        <div class="form-group">
            <label for="setting-site_title" class="form-label">Site Title</label>
            <input type="text" id="setting-site_title" name="settings[site_title]" class="form-control" value="<?php echo e($settings['site_title'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="setting-tax_percent" class="form-label">Tax Percent (%)</label>
            <input type="number" step="0.01" id="setting-tax_percent" name="settings[tax_percent]" class="form-control" value="<?php echo e($settings['tax_percent'] ?? '0'); ?>">
        </div>

        <div class="form-group">
            <label for="setting-shipping_flat" class="form-label">Shipping Flat Rate (INR)</label>
            <input type="number" step="0.01" id="setting-shipping_flat" name="settings[shipping_flat]" class="form-control" value="<?php echo e($settings['shipping_flat'] ?? '0'); ?>">
        </div>

        <hr style="margin: 24px 0;">
        <h3>Payment Gateway (Razorpay)</h3>

        <div class="form-group">
            <label for="setting-razorpay_key_id" class="form-label">Key ID</label>
            <input type="text" id="setting-razorpay_key_id" name="settings[razorpay_key_id]" class="form-control" value="<?php echo e($settings['razorpay_key_id'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="setting-razorpay_key_secret" class="form-label">Key Secret</label>
            <input type="password" id="setting-razorpay_key_secret" name="settings[razorpay_key_secret]" class="form-control" value="<?php echo e($settings['razorpay_key_secret'] ?? ''); ?>">
        </div>

        <hr style="margin: 24px 0;">
        <h3>Email (SMTP)</h3>

        <div class="form-group">
            <label for="setting-smtp_host" class="form-label">SMTP Host</label>
            <input type="text" id="setting-smtp_host" name="settings[smtp_host]" class="form-control" value="<?php echo e($settings['smtp_host'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="setting-smtp_port" class="form-label">SMTP Port</label>
            <input type="text" id="setting-smtp_port" name="settings[smtp_port]" class="form-control" value="<?php echo e($settings['smtp_port'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="setting-smtp_user" class="form-label">SMTP User</label>
            <input type="text" id="setting-smtp_user" name="settings[smtp_user]" class="form-control" value="<?php echo e($settings['smtp_user'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="setting-smtp_pass" class="form-label">SMTP Password</label>
            <input type="password" id="setting-smtp_pass" name="settings[smtp_pass]" class="form-control" value="<?php echo e($settings['smtp_pass'] ?? ''); ?>">
        </div>

        <button class="btn btn--primary" type="submit">Save Settings</button>
    </form>
</div>


<?php include __DIR__ . '/partials/footer.php'; ?>
