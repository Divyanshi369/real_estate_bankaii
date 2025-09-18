<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_role(['manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) { die('Invalid CSRF'); }
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $project_id = (int)($_POST['project_id'] ?? 0);
        $assigned_to = (int)($_POST['assigned_to'] ?? 0);
        $task_name = trim($_POST['task_name'] ?? '');
        if ($project_id && $assigned_to && $task_name !== '') {
            $stmt = db()->prepare('INSERT INTO tasks (project_id, assigned_to, task_name) VALUES (?, ?, ?)');
            $stmt->bind_param('iis', $project_id, $assigned_to, $task_name);
            $stmt->execute();
            $stmt->close();
        }
    } elseif ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'assigned';
        if ($id && in_array($status, ['assigned','in_progress','done','blocked'], true)) {
            $stmt = db()->prepare('UPDATE tasks SET status = ? WHERE id = ?');
            $stmt->bind_param('si', $status, $id);
            $stmt->execute();
            $stmt->close();
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $stmt = db()->prepare('DELETE FROM tasks WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

$projects = db()->query('SELECT id, name FROM projects ORDER BY name ASC')->fetch_all(MYSQLI_ASSOC);
$workers = db()->query("SELECT id, name FROM users WHERE role = 'worker' ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

$resCount = db()->query('SELECT COUNT(*) AS c FROM tasks');
$total = (int)($resCount->fetch_assoc()['c'] ?? 0);
$pageInfo = paginate($total, 10);
$stmt = db()->prepare('SELECT t.id, t.task_name, t.status, p.name AS project_name, u.name AS worker_name FROM tasks t JOIN projects p ON p.id = t.project_id JOIN users u ON u.id = t.assigned_to ORDER BY t.id DESC LIMIT ? OFFSET ?');
$stmt->bind_param('ii', $pageInfo['perPage'], $pageInfo['offset']);
$stmt->execute();
$tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include __DIR__ . '/../../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Tasks</h3>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">Assign Task</button>
</div>
<div class="table-responsive">
  <table class="table table-striped">
    <thead><tr><th>ID</th><th>Task</th><th>Project</th><th>Worker</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($tasks as $t): ?>
        <tr>
          <td><?php echo (int)$t['id']; ?></td>
          <td><?php echo h($t['task_name']); ?></td>
          <td><?php echo h($t['project_name']); ?></td>
          <td><?php echo h($t['worker_name']); ?></td>
          <td><?php echo h($t['status']); ?></td>
          <td>
            <form method="post" class="d-inline">
              <?php echo csrf_field(); ?>
              <input type="hidden" name="action" value="update">
              <input type="hidden" name="id" value="<?php echo (int)$t['id']; ?>">
              <select name="status" class="form-select form-select-sm d-inline w-auto">
                <?php foreach (['assigned','in_progress','done','blocked'] as $s): ?>
                  <option value="<?php echo $s; ?>" <?php echo $t['status']===$s?'selected':''; ?>><?php echo ucfirst(str_replace('_',' ',$s)); ?></option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn-sm btn-primary">Update</button>
            </form>
            <form method="post" class="d-inline" onsubmit="return confirm('Delete task?');">
              <?php echo csrf_field(); ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?php echo (int)$t['id']; ?>">
              <button class="btn btn-sm btn-danger">Delete</button>
            </form>
          </td>
        </tr>
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


<div class="modal fade" id="createModal" tabindex="-1"><div class="modal-dialog">
  <div class="modal-content">
    <form method="post">
      <div class="modal-header"><h5 class="modal-title">Assign Task</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="create">
        <div class="mb-3"><label class="form-label">Project</label>
          <select name="project_id" class="form-select" required>
            <?php foreach ($projects as $p): ?><option value="<?php echo (int)$p['id']; ?>"><?php echo h($p['name']); ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3"><label class="form-label">Worker</label>
          <select name="assigned_to" class="form-select" required>
            <?php foreach ($workers as $w): ?><option value="<?php echo (int)$w['id']; ?>"><?php echo h($w['name']); ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3"><label class="form-label">Task Name</label><input name="task_name" class="form-control" required></div>
      </div>
      <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button><button class="btn btn-primary">Create</button></div>
    </form>
  </div>
</div></div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>


