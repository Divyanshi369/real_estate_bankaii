<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_role(['manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) { die('Invalid CSRF'); }
    $worker_id = (int)($_POST['worker_id'] ?? 0);
    $date = $_POST['date'] ?? (new DateTime())->format('Y-m-d');
    $status = $_POST['status'] ?? 'present';
    if ($worker_id && in_array($status, ['present','absent','leave'], true) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $stmt = db()->prepare('INSERT INTO attendance (worker_id, date, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status)');
        $stmt->bind_param('iss', $worker_id, $date, $status);
        $stmt->execute();
        $stmt->close();
    }
}

$workers = db()->query("SELECT id, name FROM users WHERE role = 'worker' ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

$resCount = db()->query('SELECT COUNT(*) c FROM attendance');
$total = (int)($resCount->fetch_assoc()['c'] ?? 0);
$pageInfo = paginate($total, 10);
$stmt = db()->prepare('SELECT a.id, a.date, a.status, u.name as worker_name FROM attendance a JOIN users u ON u.id = a.worker_id ORDER BY a.date DESC, a.id DESC LIMIT ? OFFSET ?');
$stmt->bind_param('ii', $pageInfo['perPage'], $pageInfo['offset']);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include __DIR__ . '/../../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Attendance</h3>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#markModal">Mark Attendance</button>
e</div>
<div class="table-responsive">
  <table class="table table-striped">
    <thead><tr><th>Date</th><th>Worker</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr><td><?php echo h($r['date']); ?></td><td><?php echo h($r['worker_name']); ?></td><td><?php echo h($r['status']); ?></td></tr>
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


<div class="modal fade" id="markModal" tabindex="-1"><div class="modal-dialog">
  <div class="modal-content">
    <form method="post">
      <div class="modal-header"><h5 class="modal-title">Mark Attendance</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <?php echo csrf_field(); ?>
        <div class="mb-3"><label class="form-label">Worker</label>
          <select name="worker_id" class="form-select" required>
            <?php foreach ($workers as $w): ?><option value="<?php echo (int)$w['id']; ?>"><?php echo h($w['name']); ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3"><label class="form-label">Date</label><input type="date" name="date" class="form-control" value="<?php echo (new DateTime())->format('Y-m-d'); ?>" required></div>
        <div class="mb-3"><label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="present">Present</option>
            <option value="absent">Absent</option>
            <option value="leave">Leave</option>
          </select>
        </div>
      </div>
      <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button><button class="btn btn-primary">Save</button></div>
    </form>
  </div>
</div></div>
<!-- Include Bootstrap JS for modals -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<?php include __DIR__ . '/../../includes/footer.php'; ?>


