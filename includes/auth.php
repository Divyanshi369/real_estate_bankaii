<?php
require_once dirname(__DIR__) . '/config/db.php';
require_once __DIR__ . '/helpers.php';

function attempt_login(string $email, string $password): bool {
    $sql = 'SELECT id, name, email, password_hash, role FROM users WHERE email = ? LIMIT 1';
    $stmt = db()->prepare($sql);
    if (!$stmt) { return false; }
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
        return true;
    }
    return false;
}

function require_login(): void {
    if (!current_user()) {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($uri, '/admin/') !== false) { redirect(public_url('admin/login.php')); }
        if (strpos($uri, '/manager/') !== false) { redirect(public_url('manager/login.php')); }
        if (strpos($uri, '/supervisor/') !== false) { redirect(public_url('supervisor/login.php')); }
        if (strpos($uri, '/worker/') !== false) { redirect(public_url('worker/login.php')); }
        redirect(public_url('index.php'));
    }
}

function require_role(array $roles): void {
    require_login();
    $user = current_user();
    if (!$user || !in_array($user['role'], $roles, true)) {
        if (count($roles) === 1) {
            $target = $roles[0];
            if (in_array($target, ['admin','manager','supervisor','worker'], true)) {
                redirect(public_url($target . '/login.php'));
            }
        }
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}

function logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function role_redirect(string $role): void {
    switch ($role) {
        case 'admin': redirect(public_url('admin/dashboard.php'));
        case 'manager': redirect(public_url('manager/dashboard.php'));
        case 'supervisor': redirect(public_url('supervisor/dashboard.php'));
        case 'worker': redirect(public_url('worker/dashboard.php'));
        default: redirect(public_url('index.php'));
    }
}

?>


