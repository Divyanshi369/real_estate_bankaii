<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_role(['supervisor']);

$stats = [
  'workers' => 0,
  'tasks' => 0,
  'today_present' => 0,
];

$stats['workers'] = (int)(db()->query("SELECT COUNT(*) c FROM users WHERE role = 'worker'")->fetch_assoc()['c'] ?? 0);
$stats['tasks'] = (int)(db()->query('SELECT COUNT(*) c FROM tasks')->fetch_assoc()['c'] ?? 0);
$today = (new DateTime())->format('Y-m-d');
$stmt = db()->prepare("SELECT COUNT(*) c FROM attendance WHERE date = ? AND status = 'present'");
$stmt->bind_param('s', $today);
$stmt->execute();
$stats['today_present'] = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
$stmt->close();

include __DIR__ . '/../../includes/header.php';
?>
<div class="row g-3">
  <div class="col-md-4"><div class="card text-bg-primary"><div class="card-body"><h5>Workers</h5><p class="display-6"><?php echo $stats['workers']; ?></p></div></div></div>
  <div class="col-md-4"><div class="card text-bg-success"><div class="card-body"><h5>Tasks</h5><p class="display-6"><?php echo $stats['tasks']; ?></p></div></div></div>
  <div class="col-md-4"><div class="card text-bg-warning"><div class="card-body"><h5>Present Today</h5><p class="display-6"><?php echo $stats['today_present']; ?></p></div></div></div>
</div>
<!--<div class="mt-3">-->
<!--  <a class="btn btn-primary" href="<?php echo public_url('supervisor/stock_release.php'); ?>">Record Stock Release</a>-->
<!--</div>-->
<?php include __DIR__ . '/../../includes/footer.php'; ?>