<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $message = 'Invalid request.';
    } else {
        $email = trim($_POST['email'] ?? '');
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $stmt = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $res = $stmt->get_result();
            $user = $res->fetch_assoc();
            $stmt->close();
            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expiresAt = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');
                $stmt2 = db()->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)');
                $stmt2->bind_param('iss', $user['id'], $token, $expiresAt);
                $stmt2->execute();
                $stmt2->close();
                $resetLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/public/password_reset.php?token=' . $token;
                // In real apps send email. Here we show the link for demo.
                $message = 'Password reset link: ' . h($resetLink);
            } else {
                $message = 'If the email exists, a reset link will be sent.';
            }
        } else {
            $message = 'Enter a valid email.';
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">Password Reset</div>
      <div class="card-body">
        <?php if ($message): ?><div class="alert alert-info"><?php echo $message; ?></div><?php endif; ?>
        <form method="post">
          <?php echo csrf_field(); ?>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" required>
          </div>
          <button class="btn btn-primary">Send Reset Link</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>


