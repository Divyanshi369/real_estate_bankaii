<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__DIR__) . '/config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

// If user already logged in, redirect to their dashboard
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

        // FIX: call the correct function attempt_login()
        if (attempt_login($email, $password)) {
            // automatically redirect based on role
            role_redirect(current_user()['role']);
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Real Estate Bankaii - Login</title>
<!-- Base URL ensures assets always load from root -->
<!--<base href="https://estate.bankaiicreations.com/">-->

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= public_url('../assets/css/style.css') ?>">

<!--<link rel="stylesheet" href="/assets/css/style.css">-->
</head>
<body class="login-page">

<div class="login-card">
<h2>Welcome Back</h2>

<?php if ($error): ?>
<div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST">
<input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
<input type="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
<input type="password" name="password" placeholder="Password" required>
<button type="submit">Login</button>
<a href="/public/forgot_password.php">Forgot password?</a>
</form>
</div>

</body>
</html>
