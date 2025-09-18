<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_role(['supervisor']);

// Supervisor adds worker accounts (basic create)
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) { die('Invalid CSRF'); }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($password) >= 6) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'worker';
        $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $name, $email, $hash, $role);
        $stmt->execute();
        $stmt->close();

        // ✅ Redirect back to workers.php (prevents blank screen)
        header("Location: workers.php");
        exit;
    } else {
        $message = 'Invalid input';
    }
}


$resCount = db()->query("SELECT COUNT(*) c FROM users WHERE role = 'worker'");
$total = (int)($resCount->fetch_assoc()['c'] ?? 0);
$pageInfo = paginate($total, 10);
$stmt = db()->prepare("SELECT id, name, email FROM users WHERE role = 'worker' ORDER BY id DESC LIMIT ? OFFSET ?");
$stmt->bind_param('ii', $pageInfo['perPage'], $pageInfo['offset']);
$stmt->execute();
$workers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include __DIR__ . '/../../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Workers</h3>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">Add Worker</button>
</div>
<?php if ($message): ?><div class="alert alert-warning"><?php echo h($message); ?></div><?php endif; ?>
<div class="table-responsive">
  <table class="table table-striped">
    <thead><tr><th>ID</th><th>Name</th><th>Email</th></tr></thead>
    <tbody>
      <?php foreach ($workers as $w): ?>
        <tr><td><?php echo (int)$w['id']; ?></td><td><?php echo h($w['name']); ?></td><td><?php echo h($w['email']); ?></td></tr>
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
      <div class="modal-header"><h5 class="modal-title">Add Worker</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <?php echo csrf_field(); ?>
        <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
      </div>
      <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button><button class="btn btn-primary">Create</button></div>
    </form>
  </div>
</div></div>
<!-- Bootstrap JS (needed for modal to work) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>


