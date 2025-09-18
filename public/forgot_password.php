<?php
// Enable full error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

$message = '';

// Check if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF check (optional, remove if not implemented)
    if (function_exists('verify_csrf') && !verify_csrf()) {
        $message = 'Invalid request. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Please enter a valid email address.";
        } else {
            // Check if email exists
            $stmt = db()->prepare('SELECT id, role FROM users WHERE email = ? LIMIT 1');
            if ($stmt === false) {
                die('Database prepare error: ' . db()->error);
            }
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $res = $stmt->get_result();
            $user = $res->fetch_assoc();
            $stmt->close();

            if ($user) {
                // Generate secure token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Save token in password_resets table
                $stmt = db()->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)');
                if ($stmt === false) {
                    die('Database prepare error: ' . db()->error);
                }
                $stmt->bind_param('iss', $user['id'], $token, $expires);
                $stmt->execute();
                $stmt->close();

                // Create reset link
                $resetLink = "https://estate.bankaiicreations.com/public/password_reset_request.php?token=$token";

                // Send email
                $subject = "Reset Your Password";
                $body = "Hello,\n\nWe received a request to reset your password. Click the link below to reset it:\n\n$resetLink\n\nThis link expires in 1 hour.\n\nIf you did not request this, please ignore this email.";
                $headers = "From: noreply@yourdomain.com\r\n";

                // Use mail() if server supports it
                if (!@mail($email, $subject, $body, $headers)) {
                    // Log error but do not show to user
                    error_log("Failed to send password reset email to $email");
                }
            }

            // Always show same message to prevent email enumeration
            $message = "If this email exists in our system, a reset link has been sent.";
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Forgot Password - Real Estate Bankaii</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/public/assets/css/style.css">
<style>
/* Keep styling similar to login page */
body.forgot-page { display: flex; align-items: center; justify-content: center; min-height: 100vh; font-family: 'Inter', sans-serif; margin: 0; position: relative; overflow: hidden; background: url('../../bg-login.png') center/cover no-repeat fixed; }
body.forgot-page::before { content: ""; position: absolute; inset: 0; background: rgba(0,0,0,0.55); z-index: -1; }
.forgot-card { background: rgba(255,255,255,0.12); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); padding: 3rem 2rem; border-radius: 16px; box-shadow: 0 15px 50px rgba(0,0,0,0.45); width: 100%; max-width: 420px; text-align: center; position: relative; border: 1px solid rgba(255,255,255,0.25); color: #fff; }
.forgot-card h2 { margin-bottom: 1.5rem; font-weight: 700; font-size: 2rem; }
.forgot-card form { display: flex; flex-direction: column; gap: 1rem; }
.forgot-card input { width: 100%; padding: 0.85rem 1rem; border-radius: 10px; border: 1px solid rgba(255,255,255,0.35); background: rgba(255,255,255,0.08); color: #fff; font-size: 1rem; }
.forgot-card input:focus { outline: none; border: 1px solid #6366f1; background: rgba(255,255,255,0.15); }
.forgot-card button { padding: 0.85rem; border: none; border-radius: 10px; background: linear-gradient(135deg, #6366f1, #4338ca); color: #fff; font-weight: 600; cursor: pointer; }
.forgot-card button:hover { background: linear-gradient(135deg, #4338ca, #312e81); }
.forgot-card .message { background: rgba(59,130,246,0.85); color: #fff; padding: 0.75rem; border-radius: 8px; font-size: 0.9rem; text-align: center; margin-bottom: 1rem; }
</style>
</head>
<body class="forgot-page">

<div class="forgot-card">
<h2>Forgot Password</h2>

<?php if ($message): ?><div class="message"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<form method="post">
<input type="hidden" name="csrf_token" value="<?= function_exists('csrf_token') ? csrf_token() : '' ?>">
<input type="email" name="email" placeholder="Enter your registered email" required>
<button type="submit">Send Reset Link</button>
<a href="/public/index.php" style="color:#c7d2fe;margin-top:1rem;display:inline-block;">Back to Login</a>
</form>
</div>

</body>
</html>
