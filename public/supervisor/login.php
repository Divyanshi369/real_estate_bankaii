<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

if (current_user()) { role_redirect(current_user()['role']); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid request.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (attempt_login($email, $password)) {
            if (current_user()['role'] !== 'supervisor') {
                $_SESSION['user'] = null; session_destroy(); session_start();
                $error = 'This login is only for Supervisor accounts.';
            } else { redirect('dashboard.php'); }
        } else { $error = 'Invalid credentials.'; }
    }
}

include __DIR__ . '/../../includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-4">
    <div class="card">
      <div class="card-header">Supervisor Login</div>
      <div class="card-body">
        <?php if ($error): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endif; ?>
        <form method="post">
          <?php echo csrf_field(); ?>
          <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
          <button class="btn btn-primary w-100">Login</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>