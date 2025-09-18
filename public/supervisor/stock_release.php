<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_role(['supervisor']);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) { die('Invalid CSRF'); }
    $stock_id = (int)($_POST['stock_id'] ?? 0);
    $quantity = max(1, (int)($_POST['quantity'] ?? 0));
    $worker_name = trim($_POST['worker_name'] ?? '');
    $image_path = null;

    if ($stock_id && $worker_name !== '' && $quantity > 0) {
        if (!empty($_FILES['image']['name'])) {
            $dir = __DIR__ . '/../../uploads/releases';
            if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $safe = bin2hex(random_bytes(8)) . '.' . strtolower($ext);
            $dest = $dir . '/' . $safe;
            if (is_uploaded_file($_FILES['image']['tmp_name'])) {
                move_uploaded_file($_FILES['image']['tmp_name'], $dest);
                $image_path = 'uploads/releases/' . $safe;
            }
        }

        $userId = current_user()['id'];

        // update stock usage
        $stmt1 = db()->prepare('UPDATE stock SET used_quantity = LEAST(quantity, used_quantity + ?) WHERE id = ?');
        $stmt1->bind_param('ii', $quantity, $stock_id);
        $stmt1->execute();
        $stmt1->close();

        // insert release record
        $stmt2 = db()->prepare('INSERT INTO stock_releases (stock_id, quantity, worker_name, image_path, released_by) VALUES (?, ?, ?, ?, ?)');
        $stmt2->bind_param('iissi', $stock_id, $quantity, $worker_name, $image_path, $userId);
        $stmt2->execute();
        $stmt2->close();

        // redirect back to avoid resubmission
        header("Location: stock_release.php?success=1");
        exit;
    } else {
        $message = 'Please fill all fields correctly.';
    }
}

$stocks = db()->query('SELECT id, item_name, quantity, used_quantity FROM stock ORDER BY item_name ASC')->fetch_all(MYSQLI_ASSOC);

// fetch release history with stock name
$releases = db()->query("
    SELECT r.id, r.quantity, r.worker_name, r.image_path, r.created_at, s.item_name, u.name AS released_by
    FROM stock_releases r
    JOIN stock s ON r.stock_id = s.id
    JOIN users u ON r.released_by = u.id
    ORDER BY r.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>

<h3>New Stock Release</h3>
<?php if ($message): ?><div class="alert alert-warning"><?php echo h($message); ?></div><?php endif; ?>
<?php if (isset($_GET['success'])): ?><div class="alert alert-success">Release recorded successfully.</div><?php endif; ?>

<form method="post" enctype="multipart/form-data" class="card p-3 mb-4">
  <?php echo csrf_field(); ?>
  <div class="row g-3">
    <div class="col-md-4">
      <label class="form-label">Item</label>
      <select name="stock_id" class="form-select" required>
        <?php foreach ($stocks as $s): $avail = (int)$s['quantity'] - (int)$s['used_quantity']; ?>
          <option value="<?php echo (int)$s['id']; ?>">
            <?php echo h($s['item_name']); ?> (Available: <?php echo max(0, $avail); ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label">Quantity</label>
      <input type="number" name="quantity" class="form-control" min="1" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Released To (Worker Name)</label>
      <input name="worker_name" class="form-control" placeholder="e.g., John Doe" required>
    </div>
    <div class="col-md-2">
      <label class="form-label">Photo (optional)</label>
      <input type="file" name="image" class="form-control" accept="image/*">
    </div>
  </div>
  <div class="mt-3">
    <button class="btn btn-primary">Save Release</button>
  </div>
</form>

<h3>Release History</h3>
<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>Item</th>
        <th>Quantity</th>
        <th>Worker</th>
        <th>Released By</th>
        <th>Date</th>
        <th>Photo</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($releases as $r): ?>
        <tr>
          <td><?php echo (int)$r['id']; ?></td>
          <td><?php echo h($r['item_name']); ?></td>
          <td><?php echo (int)$r['quantity']; ?></td>
          <td><?php echo h($r['worker_name']); ?></td>
          <td><?php echo h($r['released_by']); ?></td>
          <td><?php echo h($r['created_at']); ?></td>
          <td>
            <?php if ($r['image_path']): ?>
              <a href="<?php echo h($r['image_path']); ?>" target="_blank">View</a>
            <?php else: ?>
              -
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
