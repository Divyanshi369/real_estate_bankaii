<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_role(['manager']);

$user = current_user();

$stmt = db()->prepare("SELECT id, name, status FROM projects WHERE assigned_manager = ? ORDER BY id DESC");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include __DIR__ . '/../../includes/header.php';
?>
<h3>My Projects</h3>
<div class="table-responsive">
  <table class="table table-striped">
    <thead><tr><th>ID</th><th>Name</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($projects as $p): ?>
        <tr><td><?php echo (int)$p['id']; ?></td><td><?php echo h($p['name']); ?></td><td><?php echo h($p['status']); ?></td></tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>


