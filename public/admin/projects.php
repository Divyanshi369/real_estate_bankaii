<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_role(['admin']);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $message = 'Invalid CSRF token. Please refresh and try again.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'create' || $action === 'update') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $status = $_POST['status'] ?? 'planned';
            $assigned_manager = (int)($_POST['assigned_manager'] ?? 0);

            if ($name !== '' && in_array($status, ['planned','active','completed','on_hold','cancelled'], true)) {
                if ($action === 'create') {
                    $stmt = db()->prepare('INSERT INTO projects (name, description, status, assigned_manager) VALUES (?, ?, ?, NULLIF(?,0))');
                    $stmt->bind_param('sssi', $name, $description, $status, $assigned_manager);
                    $stmt->execute();
                    if ($stmt->error) $message = 'Database Error: ' . $stmt->error;
                    $stmt->close();
                } else {
                    $id = (int)($_POST['id'] ?? 0);
                    $stmt = db()->prepare('UPDATE projects SET name = ?, description = ?, status = ?, assigned_manager = NULLIF(?,0) WHERE id = ?');
                    $stmt->bind_param('sssii', $name, $description, $status, $assigned_manager, $id);
                    $stmt->execute();
                    if ($stmt->error) $message = 'Database Error: ' . $stmt->error;
                    $stmt->close();
                }
            } else {
                $message = 'Invalid data provided.';
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $stmt = db()->prepare('DELETE FROM projects WHERE id = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                if ($stmt->error) $message = 'Database Error: ' . $stmt->error;
                $stmt->close();
            }
        }
    }
}

// Fetch managers
$managers = db()->query("SELECT id, name FROM users WHERE role = 'manager' ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

// Pagination
$resCount = db()->query('SELECT COUNT(*) AS c FROM projects');
$total = (int)($resCount->fetch_assoc()['c'] ?? 0);
$pageInfo = paginate($total, 10);

// Fetch projects in ascending order
$stmt = db()->prepare('SELECT p.id, p.name, p.description, p.status, p.assigned_manager, u.name AS manager_name 
                       FROM projects p 
                       LEFT JOIN users u ON u.id = p.assigned_manager 
                       ORDER BY p.id ASC LIMIT ? OFFSET ?');
$stmt->bind_param('ii', $pageInfo['perPage'], $pageInfo['offset']);
$stmt->execute();
$projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Projects</h3>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">Add Project</button>
</div>

<?php if ($message): ?>
  <div class="alert alert-warning"><?php echo h($message); ?></div>
<?php endif; ?>

<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr><th>ID</th><th>Name</th><th>Status</th><th>Manager</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($projects as $p): ?>
      <tr>
        <td><?php echo (int)$p['id']; ?></td>
        <td><?php echo h($p['name']); ?></td>
        <td><?php echo h($p['status']); ?></td>
        <td><?php echo h($p['manager_name'] ?? '—'); ?></td>
        <td>
          <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo (int)$p['id']; ?>">Edit</button>
          <form method="post" class="d-inline" onsubmit="return confirm('Delete project?');">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Pagination -->
<nav>
  <ul class="pagination justify-content-center">
    <?php for ($i=1; $i<=$pageInfo['totalPages']; $i++): ?>
      <li class="page-item <?php echo $i===$pageInfo['page']?'active':''; ?>">
        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
      </li>
    <?php endfor; ?>
  </ul>
</nav>


<!-- Create Project Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header">
          <h5 class="modal-title">Add Project</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="action" value="create">

          <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="planned">Planned</option>
              <option value="active">Active</option>
              <option value="completed">Completed</option>
              <option value="on_hold">On Hold</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Manager</label>
            <select name="assigned_manager" class="form-select">
              <option value="0">— Unassigned —</option>
              <?php foreach ($managers as $m): ?>
                <option value="<?php echo (int)$m['id']; ?>"><?php echo h($m['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Create</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Project Modals -->
<?php foreach ($projects as $p): ?>
<div class="modal fade" id="editModal<?php echo (int)$p['id']; ?>" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header">
          <h5 class="modal-title">Edit Project</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">

          <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="<?php echo h($p['name']); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"><?php echo h($p['description']); ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <?php foreach (['planned','active','completed','on_hold','cancelled'] as $s): ?>
                <option value="<?php echo $s; ?>" <?php echo $p['status']===$s?'selected':''; ?>>
                  <?php echo ucfirst(str_replace('_',' ', $s)); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Manager</label>
            <select name="assigned_manager" class="form-select">
              <option value="0">— Unassigned —</option>
              <?php foreach ($managers as $m): ?>
                <option value="<?php echo (int)$m['id']; ?>" <?php echo ((int)$p['assigned_manager'] === (int)$m['id'])?'selected':''; ?>>
                  <?php echo h($m['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<!-- Include Bootstrap JS for modals -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
