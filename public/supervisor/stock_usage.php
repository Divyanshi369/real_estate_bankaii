<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_role(['supervisor']);

// Fetch available stock
$stocks = db()->query('
    SELECT id, item_name, quantity, used_quantity, (quantity - used_quantity) AS available 
    FROM stock 
    WHERE (quantity - used_quantity) > 0
    ORDER BY item_name ASC
')->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/../../includes/header.php';
?>

<h3>Available Stock</h3>

<table class="table table-bordered table-striped">
  <thead>
    <tr>
      <th>Item Name</th>
      <th>Total Quantity</th>
      <th>Used Quantity</th>
      <th>Available</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($stocks as $s): ?>
      <tr>
        <td><?php echo h($s['item_name']); ?></td>
        <td><?php echo (int)$s['quantity']; ?></td>
        <td><?php echo (int)$s['used_quantity']; ?></td>
        <td><?php echo (int)$s['available']; ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
