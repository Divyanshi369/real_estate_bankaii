<?php
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

header('Content-Type: text/html; charset=utf-8');

$checks = [];

// PHP version
$checks[] = ['PHP Version', PHP_VERSION];

// Session test
$_SESSION['health_test'] = 'ok';
$checks[] = ['Session write', isset($_SESSION['health_test']) ? 'ok' : 'failed'];

// DB connection
try {
    $ok = db()->ping();
    $checks[] = ['DB connection', $ok ? 'ok' : 'failed'];
} catch (Throwable $e) {
    $checks[] = ['DB connection', 'failed: ' . $e->getMessage()];
}

// Users and roles
try {
    $roles = ['admin','manager','supervisor','worker'];
    $roleCounts = [];
    foreach ($roles as $r) {
        $stmt = db()->prepare('SELECT COUNT(*) AS c FROM users WHERE role = ?');
        $stmt->bind_param('s', $r);
        $stmt->execute();
        $roleCounts[$r] = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
        $stmt->close();
    }
} catch (Throwable $e) {
    $roleCounts = ['error' => $e->getMessage()];
}

?>
<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Health Check</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body class="p-4">
    <h3>Health Check</h3>
    <table class="table table-bordered w-auto">
      <thead><tr><th>Check</th><th>Result</th></tr></thead>
      <tbody>
        <?php foreach ($checks as $c): ?>
          <tr><td><?php echo h($c[0]); ?></td><td><?php echo h($c[1]); ?></td></tr>
        <?php endforeach; ?>
        <tr><td>Role counts</td><td><?php echo h(json_encode($roleCounts)); ?></td></tr>
      </tbody>
    </table>
    <div class="mt-3">
      <a class="btn btn-secondary" href="index.php">Back to Login</a>
    </div>
  </body>
</html>