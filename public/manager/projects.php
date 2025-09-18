<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_role(['manager']);

$user = current_user();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) { die('Invalid CSRF'); }
    $id = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? 'planned';
    if ($id && in_array($status, ['planned','active','completed','on_hold','cancelled'], true)) {
        $stmt = db()->prepare('UPDATE projects SET status = ? WHERE id = ? AND assigned_manager = ?');
        $stmt->bind_param('sii', $status, $id, $user['id']);
        $stmt->execute();
        $stmt->close();
    } else {
        $message = 'Invalid input';
    }
}

$resCountStmt = db()->prepare('SELECT COUNT(*) AS c FROM projects WHERE assigned_manager = ?');
$resCountStmt->bind_param('i', $user['id']);
$resCountStmt->execute();
$total = (int)($resCountStmt->get_result()->fetch_assoc()['c'] ?? 0);
$resCountStmt->close();

$pageInfo = paginate($total, 10);
$stmt = db()->prepare('SELECT id, name, status FROM projects WHERE assigned_manager = ? ORDER BY id DESC LIMIT ? OFFSET ?');
$stmt->bind_param('iii', $user['id'], $pageInfo['perPage'], $pageInfo['offset']);
$stmt->execute();
$projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include __DIR__ . '/../../includes/header.php';
?>
<h3>My Projects</h3>
<?php if ($message): ?><div class="alert alert-warning"><?php echo h($message); ?></div><?php endif; ?>
<div class="table-responsive">
  <table class="table table-striped">
    <thead><tr><th>ID</th><th>Name</th><th>Status</th><th>Update</th></tr></thead>
    <tbody>
      <?php foreach ($projects as $p): ?>
        <tr>
          <td><?php echo (int)$p['id']; ?></td>
          <td><?php echo h($p['name']); ?></td>
          <td><?php echo h($p['status']); ?></td>
          <td>
            <form method="post" class="d-flex gap-2">
              <?php echo csrf_field(); ?>
              <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
              <select name="status" class="form-select form-select-sm w-auto">
                <?php foreach (['planned','active','completed','on_hold','cancelled'] as $s): ?>
                  <option value="<?php echo $s; ?>" <?php echo $p['status']===$s?'selected':''; ?>><?php echo ucfirst(str_replace('_',' ',$s)); ?></option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn-sm btn-primary">Save</button>
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

<?php include __DIR__ . '/../../includes/footer.php'; ?>


