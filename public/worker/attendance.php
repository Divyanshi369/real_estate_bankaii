<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_role(['worker']);

$user = current_user();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) { die('Invalid CSRF'); }
    $date = (new DateTime())->format('Y-m-d');
    $status = 'present';
    $stmt = db()->prepare('INSERT INTO attendance (worker_id, date, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status)');
    $stmt->bind_param('iss', $user['id'], $date, $status);
    $stmt->execute();
    $stmt->close();
    $message = 'Attendance marked for ' . $date;
}

$stmt = db()->prepare('SELECT date, status FROM attendance WHERE worker_id = ? ORDER BY date DESC LIMIT 30');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include __DIR__ . '/../../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Attendance</h3>
  <form method="post">
    <?php echo csrf_field(); ?>
    <button class="btn btn-primary">Mark Present Today</button>
  </form>
</div>
<?php if ($message): ?><div class="alert alert-success"><?php echo h($message); ?></div><?php endif; ?>
<div class="table-responsive">
  <table class="table table-striped">
    <thead><tr><th>Date</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr><td><?php echo h($r['date']); ?></td><td><?php echo h($r['status']); ?></td></tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>


