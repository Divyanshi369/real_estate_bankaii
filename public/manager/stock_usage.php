<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_role(['manager']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) { die('Invalid CSRF'); }
    $id = (int)($_POST['id'] ?? 0);
    $use = max(0, (int)($_POST['use'] ?? 0));
    if ($id && $use > 0) {
        $stmt = db()->prepare('UPDATE stock SET used_quantity = LEAST(quantity, used_quantity + ?) WHERE id = ?');
        $stmt->bind_param('ii', $use, $id);
        $stmt->execute();
        $stmt->close();
    }
}

$items = db()->query('SELECT id, item_name, quantity, used_quantity FROM stock ORDER BY item_name ASC')->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>
<h3>Stock Usage</h3>
<div class="table-responsive">
  <table class="table table-striped">
    <thead><tr><th>Item</th><th>Total Qty</th><th>Used</th><th>Available</th><th>Use</th></tr></thead>
    <tbody>
      <?php foreach ($items as $it): $available = (int)$it['quantity'] - (int)$it['used_quantity']; ?>
        <tr>
          <td><?php echo h($it['item_name']); ?></td>
          <td><?php echo (int)$it['quantity']; ?></td>
          <td><?php echo (int)$it['used_quantity']; ?></td>
          <td><?php echo max(0, $available); ?></td>
          <td>
            <form method="post" class="d-flex gap-2">
              <?php echo csrf_field(); ?>
              <input type="hidden" name="id" value="<?php echo (int)$it['id']; ?>">
              <input type="number" name="use" class="form-control form-control-sm" min="1" max="<?php echo max(0, $available); ?>" value="1" <?php echo $available<=0?'disabled':''; ?>>
              <button class="btn btn-sm btn-primary" <?php echo $available<=0?'disabled':''; ?>>Apply</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>


