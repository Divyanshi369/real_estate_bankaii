<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_role(['admin']);

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $message = 'Invalid CSRF token';
    } else {
        $action = $_POST['action'] ?? '';

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'worker';
        if (!in_array($role, ['admin','manager','supervisor','worker'], true)) $role = 'worker';

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Invalid name or email';
        } else {
            if ($action === 'create') {
                $password = $_POST['password'] ?? '';
                if (strlen($password) < 6) {
                    $message = 'Password too short';
                } else {
                    // Check if email already exists
                    $stmtCheck = db()->prepare('SELECT id FROM users WHERE email = ?');
                    $stmtCheck->bind_param('s', $email);
                    $stmtCheck->execute();
                    $stmtCheck->store_result();
                    if ($stmtCheck->num_rows > 0) {
                        $message = 'Email already exists';
                    } else {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
                        if (!$stmt) { die('Prepare failed: ' . db()->error); }
                        $stmt->bind_param('ssss', $name, $email, $hash, $role);
                        if (!$stmt->execute()) { die('Execute failed: ' . $stmt->error); }
                        $stmt->close();
                        $message = 'User created successfully!';
                    }
                    $stmtCheck->close();
                }
            } elseif ($action === 'update') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) {
                    $message = 'Invalid user ID';
                } else {
                    // Optional: check if email exists for another user
                    $stmtCheck = db()->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
                    $stmtCheck->bind_param('si', $email, $id);
                    $stmtCheck->execute();
                    $stmtCheck->store_result();
                    if ($stmtCheck->num_rows > 0) {
                        $message = 'Email already used by another user';
                    } else {
                        $stmt = db()->prepare('UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?');
                        $stmt->bind_param('sssi', $name, $email, $role, $id);
                        if (!$stmt->execute()) { die('Execute failed: ' . $stmt->error); }
                        $stmt->close();
                        $message = 'User updated successfully!';
                    }
                    $stmtCheck->close();
                }
            } elseif ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $stmt = db()->prepare('DELETE FROM users WHERE id = ?');
                    $stmt->bind_param('i', $id);
                    $stmt->execute();
                    $stmt->close();
                    $message = 'User deleted successfully!';
                }
            }
        }
    }
}

// Pagination and fetching users
$resCount = db()->query('SELECT COUNT(*) AS c FROM users');
$total = (int)($resCount->fetch_assoc()['c'] ?? 0);
$pageInfo = paginate($total, 10);

$stmt = db()->prepare('SELECT id, name, email, role FROM users ORDER BY id ASC LIMIT ? OFFSET ?');
$stmt->bind_param('ii', $pageInfo['perPage'], $pageInfo['offset']);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Users</h3>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">Add User</button>
</div>

<?php if ($message): ?>
  <div class="alert alert-info"><?php echo h($message); ?></div>
<?php endif; ?>

<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
      <tr>
        <td><?php echo (int)$u['id']; ?></td>
        <td><?php echo h($u['name']); ?></td>
        <td><?php echo h($u['email']); ?></td>
        <td><?php echo h($u['role']); ?></td>
        <td>
          <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo (int)$u['id']; ?>">Edit</button>
          <form method="post" class="d-inline" onsubmit="return confirm('Delete user?');">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
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

<!-- CREATE USER MODAL -->
<div class="modal fade" id="createModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header">
          <h5 class="modal-title">Add User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="action" value="create">
          <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Role</label>
            <select name="role" class="form-select">
              <option value="worker">Worker</option>
              <option value="supervisor">Supervisor</option>
              <option value="manager">Manager</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Create</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- EDIT USER MODALS -->
<?php foreach ($users as $u): ?>
<div class="modal fade" id="editModal<?php echo (int)$u['id']; ?>" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header">
          <h5 class="modal-title">Edit User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
          <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="<?php echo h($u['name']); ?>" required></div>
          <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?php echo h($u['email']); ?>" required></div>
          <div class="mb-3"><label class="form-label">Role</label>
            <select name="role" class="form-select">
              <?php foreach (['admin','manager','supervisor','worker'] as $r): ?>
                <option value="<?php echo $r; ?>" <?php echo $u['role'] === $r ? 'selected' : ''; ?>><?php echo ucfirst($r); ?></option>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
