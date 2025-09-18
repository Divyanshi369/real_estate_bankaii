<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): void {
    header('Location: ' . $path);
    exit;
}

function public_url(string $path = ''): string {
    $script = $_SERVER['SCRIPT_NAME'] ?? '/';
    $pos = strpos($script, '/public/');
    $base = '/';
    if ($pos !== false) {
        $base = substr($script, 0, $pos + 8);
    }
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function verify_csrf(): bool {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
    return true;
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function is_role(string $role): bool {
    $user = current_user();
    return $user && ($user['role'] === $role);
}

function paginate(int $totalRows, int $perPage = 10): array {
    $page = max(1, intval($_GET['page'] ?? 1));
    $totalPages = max(1, (int)ceil($totalRows / $perPage));
    $offset = ($page - 1) * $perPage;
    return ['page' => $page, 'totalPages' => $totalPages, 'perPage' => $perPage, 'offset' => $offset];
}

?>


