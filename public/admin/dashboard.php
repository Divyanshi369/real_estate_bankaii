<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_role(['admin']);

$stats = [
    'users' => (int) (db()->query('SELECT COUNT(*) AS c FROM users')->fetch_assoc()['c'] ?? 0),
    'projects' => (int) (db()->query('SELECT COUNT(*) AS c FROM projects')->fetch_assoc()['c'] ?? 0),
    'stock_items' => (int) (db()->query('SELECT COUNT(*) AS c FROM stock')->fetch_assoc()['c'] ?? 0),
    'tasks' => (int) (db()->query('SELECT COUNT(*) AS c FROM tasks')->fetch_assoc()['c'] ?? 0),
];

include __DIR__ . '/../../includes/header.php';
?>

<div class="row g-3 mt-3">
  <div class="col-md-3"><div class="card users"><div class="card-body"><h5>Users</h5><p class="display-6"><?php echo $stats['users']; ?></p></div></div></div>
  <div class="col-md-3"><div class="card projects"><div class="card-body"><h5>Projects</h5><p class="display-6"><?php echo $stats['projects']; ?></p></div></div></div>
  <div class="col-md-3"><div class="card stock_items"><div class="card-body"><h5>Stock Items</h5><p class="display-6"><?php echo $stats['stock_items']; ?></p></div></div></div>
  <div class="col-md-3"><div class="card tasks"><div class="card-body"><h5>Tasks</h5><p class="display-6"><?php echo $stats['tasks']; ?></p></div></div></div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
