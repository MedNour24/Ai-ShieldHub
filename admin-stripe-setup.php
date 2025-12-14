<?php
// Admin page to update Stripe API keys

$config_path = __DIR__ . '/config/StripeConfig.php';
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $publishable_key = isset($_POST['publishable_key']) ? trim($_POST['publishable_key']) : '';
    $secret_key = isset($_POST['secret_key']) ? trim($_POST['secret_key']) : '';
    $webhook_secret = isset($_POST['webhook_secret']) ? trim($_POST['webhook_secret']) : '';

    // Validate keys format (basic check)
    $errors = [];
    if (!$publishable_key) $errors[] = 'Publishable key is required';
    if (!preg_match('/^pk_test_|^pk_live_/', $publishable_key)) $errors[] = 'Invalid publishable key format (should start with pk_test_ or pk_live_)';
    
    if (!$secret_key) $errors[] = 'Secret key is required';
    if (!preg_match('/^sk_test_|^sk_live_/', $secret_key)) $errors[] = 'Invalid secret key format (should start with sk_test_ or sk_live_)';

    if (!$webhook_secret) $errors[] = 'Webhook secret is required';
    if (!preg_match('/^whsec_/', $webhook_secret)) $errors[] = 'Invalid webhook secret format (should start with whsec_)';

    if (empty($errors)) {
        // Read current config file
        $config_content = file_get_contents($config_path);

        // Update keys
        $config_content = preg_replace(
            "/private static \\\$publishableKey = '[^']*';/",
            "private static \$publishableKey = '" . addslashes($publishable_key) . "';",
            $config_content
        );
        $config_content = preg_replace(
            "/private static \\\$secretKey = '[^']*';/",
            "private static \$secretKey = '" . addslashes($secret_key) . "';",
            $config_content
        );
        $config_content = preg_replace(
            "/private static \\\$webhookSecret = '[^']*';/",
            "private static \$webhookSecret = '" . addslashes($webhook_secret) . "';",
            $config_content
        );

        if (file_put_contents($config_path, $config_content)) {
            $message = 'Stripe keys updated successfully!';
            $message_type = 'success';
        } else {
            $message = 'Failed to write config file. Check file permissions.';
            $message_type = 'error';
        }
    } else {
        $message = 'Validation errors: ' . implode('; ', $errors);
        $message_type = 'error';
    }
}

// Read current keys (masked for display)
include_once 'config/StripeConfig.php';
$pub = StripeConfig::getPublishableKey();
$sec = StripeConfig::getSecretKey();
$web = StripeConfig::getWebhookSecret();

$pub_display = $pub !== 'pk_test_your_publishable_key_here' ? substr($pub, 0, 10) . '...' . substr($pub, -4) : '';
$sec_display = $sec !== 'sk_test_your_secret_key_here' ? substr($sec, 0, 10) . '...' . substr($sec, -4) : '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Setup — Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0b1020; color: #e6eef8; font-family: Segoe UI, Arial; padding: 30px; }
        .setup-card { background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.3); border-radius: 12px; padding: 30px; max-width: 500px; margin: 0 auto; }
        .form-label { color: #e6eef8; font-weight: 500; }
        .form-control { background: rgba(255,255,255,0.05); border: 1px solid rgba(99,102,241,0.3); color: #e6eef8; }
        .form-control:focus { background: rgba(255,255,255,0.08); border-color: #6366f1; color: #e6eef8; }
        .btn-primary { background: #6366f1; border: none; }
        .btn-primary:hover { background: #4f46e5; }
        .alert { margin-bottom: 20px; }
        .current-keys { font-size: 12px; color: #9fb3db; margin-top: 8px; }
    </style>
</head>
<body>
    <div class="setup-card">
        <h2 class="mb-4">Stripe API Keys Setup</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?>" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="publishable_key" class="form-label">Publishable Key (pk_test_... or pk_live_...)</label>
                <input type="password" class="form-control" id="publishable_key" name="publishable_key" required>
                <?php if ($pub_display): ?>
                    <div class="current-keys">Current: <?php echo htmlspecialchars($pub_display); ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="secret_key" class="form-label">Secret Key (sk_test_... or sk_live_...)</label>
                <input type="password" class="form-control" id="secret_key" name="secret_key" required>
                <?php if ($sec_display): ?>
                    <div class="current-keys">Current: <?php echo htmlspecialchars($sec_display); ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="webhook_secret" class="form-label">Webhook Secret (whsec_...)</label>
                <input type="password" class="form-control" id="webhook_secret" name="webhook_secret" required>
                <div class="current-keys">Get this from Stripe Dashboard → Webhooks</div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Save Stripe Keys</button>
        </form>

        <div class="alert alert-info mt-4">
            <strong>How to get your keys:</strong>
            <ol style="margin-bottom: 0; font-size: 13px;">
                <li>Log in to <a href="https://dashboard.stripe.com" target="_blank" style="color: #6366f1;">Stripe Dashboard</a></li>
                <li>Go to Developers → API keys</li>
                <li>Copy your publishable key (starts with pk_test_ or pk_live_)</li>
                <li>Copy your secret key (starts with sk_test_ or sk_live_)</li>
                <li>For webhook secret, go to Webhooks and reveal the signing secret</li>
                <li>Paste all three keys above and click Save</li>
            </ol>
        </div>

        <a href="course-details.php?id=1" class="btn btn-sm btn-outline-light mt-3">← Back to Courses</a>
    </div>
</body>
</html>
