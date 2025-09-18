<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_role(['worker']);

$user = current_user();
$stmt = db()->prepare('SELECT t.task_name, t.status, p.name AS project_name FROM tasks t JOIN projects p ON p.id = t.project_id WHERE t.assigned_to = ? ORDER BY t.id DESC LIMIT 10');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include __DIR__ . '/../../includes/header.php';
?>
<h3>My Recent Tasks</h3>
<div class="table-responsive">
  <table class="table table-striped">
    <thead><tr><th>Task</th><th>Project</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($tasks as $t): ?>
        <tr><td><?php echo h($t['task_name']); ?></td><td><?php echo h($t['project_name']); ?></td><td><?php echo h($t['status']); ?></td></tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>


