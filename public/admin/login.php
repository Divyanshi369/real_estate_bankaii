<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

if (current_user()) {
    role_redirect(current_user()['role']);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (filter_var($email, FILTER_VALIDATE_EMAIL) && $password !== '') {
            if (attempt_login($email, $password)) {
                role_redirect(current_user()['role']);
            } else {
                $error = 'Invalid credentials';
            }
        } else {
            $error = 'Please provide a valid email and password';
        }
    }
}

include __DIR__ . '/../../includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-4">
    <div class="card">
      <div class="card-header">Login</div>
      <div class="card-body">
        <?php if ($error): ?>
          <div class="alert alert-danger"><?php echo h($error); ?></div>
        <?php endif; ?>
        <form method="post">
          <?php echo csrf_field(); ?>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        <div class="mt-3 text-center">
          <a href="/public/password_reset_request.php">Forgot password?</a>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4 mt-3 mt-md-0">
    <div class="card shadow-sm">
        <div class="card-header text-center fw-bold">Role Logins</div>
        <div class="list-group list-group-flush">
            <a class="list-group-item" href="<?php echo public_url('admin/login.php'); ?>">Admin Login</a>
            <a class="list-group-item" href="<?php echo public_url('manager/login.php'); ?>">Manager Login</a>
            <a class="list-group-item" href="<?php echo public_url('supervisor/login.php'); ?>">Supervisor Login</a>
            <a class="list-group-item" href="<?php echo public_url('worker/login.php'); ?>">Worker Login</a>
        </div>
    </div>
</div>

    </div>
  </div>
  
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>