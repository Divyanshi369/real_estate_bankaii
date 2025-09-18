<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_role(['worker']);

$user = current_user();
$resCountStmt = db()->prepare('SELECT COUNT(*) AS c FROM tasks WHERE assigned_to = ?');
$resCountStmt->bind_param('i', $user['id']);
$resCountStmt->execute();
$total = (int)($resCountStmt->get_result()->fetch_assoc()['c'] ?? 0);
$resCountStmt->close();

$pageInfo = paginate($total, 10);
$stmt = db()->prepare('SELECT t.id, t.task_name, t.status, p.name AS project_name FROM tasks t JOIN projects p ON p.id = t.project_id WHERE t.assigned_to = ? ORDER BY t.id DESC LIMIT ? OFFSET ?');
$stmt->bind_param('iii', $user['id'], $pageInfo['perPage'], $pageInfo['offset']);
$stmt->execute();
$tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include __DIR__ . '/../../includes/header.php';
?>
<h3>My Tasks</h3>
<div class="table-responsive">
  <table class="table table-striped">
    <thead><tr><th>ID</th><th>Task</th><th>Project</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($tasks as $t): ?>
        <tr><td><?php echo (int)$t['id']; ?></td><td><?php echo h($t['task_name']); ?></td><td><?php echo h($t['project_name']); ?></td><td><?php echo h($t['status']); ?></td></tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<nav>
  <ul class="pagination justify-content-center">
    <?php for ($i=1; $i<=$pageInfo['totalPages']; $i++): ?>
      <li class="page-item <?php echo $i===$pageInfo['page']?'active':''; ?>">
        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
      </li>
    <?php endfor; ?>
  </ul>
</nav>
<?php include __DIR__ . '/../../includes/footer.php'; ?>


