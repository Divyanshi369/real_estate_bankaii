<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) { die('Invalid CSRF'); }
    $action = $_POST['action'] ?? '';
    $userId = current_user()['id'];

    if ($action === 'create') {
        $item_name = trim($_POST['item_name'] ?? '');
        $quantity = max(0, (int)($_POST['quantity'] ?? 0));
        if ($item_name !== '') {
            $stmt = db()->prepare('INSERT INTO stock (item_name, quantity, used_quantity, updated_by) VALUES (?, ?, 0, ?)');
            $stmt->bind_param('sii', $item_name, $quantity, $userId);
            $stmt->execute();
            $stmt->close();
        }
    } elseif ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $item_name = trim($_POST['item_name'] ?? '');
        $quantity = max(0, (int)($_POST['quantity'] ?? 0));
        $used_quantity = max(0, (int)($_POST['used_quantity'] ?? 0));
        if ($id && $item_name !== '' && $used_quantity <= $quantity) {
            $stmt = db()->prepare('UPDATE stock SET item_name = ?, quantity = ?, used_quantity = ?, updated_by = ? WHERE id = ?');
            $stmt->bind_param('siiii', $item_name, $quantity, $used_quantity, $userId, $id);
            $stmt->execute();
            $stmt->close();
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $stmt = db()->prepare('DELETE FROM stock WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Pagination
$resCount = db()->query('SELECT COUNT(*) AS c FROM stock');
$total = (int)($resCount->fetch_assoc()['c'] ?? 0);
$pageInfo = paginate($total, 10);

// Fetch stock items in ascending order
$stmt = db()->prepare('SELECT s.id, s.item_name, s.quantity, s.used_quantity, s.updated_at, u.name AS updated_by_name 
                       FROM stock s 
                       LEFT JOIN users u ON u.id = s.updated_by 
                       ORDER BY s.id ASC 
                       LIMIT ? OFFSET ?');
$stmt->bind_param('ii', $pageInfo['perPage'], $pageInfo['offset']);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Stock</h3>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">Add Item</button>
</div>

<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th>ID</th><th>Item</th><th>Qty</th><th>Used</th><th>Updated By</th><th>Updated At</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $it): ?>
      <tr>
        <td><?php echo (int)$it['id']; ?></td>
        <td><?php echo h($it['item_name']); ?></td>
        <td><?php echo (int)$it['quantity']; ?></td>
        <td><?php echo (int)$it['used_quantity']; ?></td>
        <td><?php echo h($it['updated_by_name'] ?? '—'); ?></td>
        <td><?php echo h($it['updated_at']); ?></td>
        <td>
          <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo (int)$it['id']; ?>">Edit</button>
          <form method="post" class="d-inline" onsubmit="return confirm('Delete item?');">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?php echo (int)$it['id']; ?>">
            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
          </form>
        </td>
      </tr>

      <!-- Edit Modal -->
      <div class="modal fade" id="editModal<?php echo (int)$it['id']; ?>" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="post">
              <div class="modal-header">
                <h5 class="modal-title">Edit Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?php echo (int)$it['id']; ?>">
                <div class="mb-3">
                  <label class="form-label">Item Name</label>
                  <input name="item_name" class="form-control" value="<?php echo h($it['item_name']); ?>" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Quantity</label>
                  <input type="number" name="quantity" class="form-control" value="<?php echo (int)$it['quantity']; ?>" min="0" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Used Quantity</label>
                  <input type="number" name="used_quantity" class="form-control" value="<?php echo (int)$it['used_quantity']; ?>" min="0" required>
                </div>
              </div>
              <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button>
                <button class="btn btn-primary">Save</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Pagination -->
<nav>
  <ul class="pagination justify-content-center">
    <?php for ($i=1; $i<=$pageInfo['totalPages']; $i++): ?>
      <li class="page-item <?php echo $i===$pageInfo['page']?'active':''; ?>">
        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
      </li>
    <?php endfor; ?>
  </ul>
</nav>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header">
          <h5 class="modal-title">Add Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="action" value="create">
          <div class="mb-3">
            <label class="form-label">Item Name</label>
            <input name="item_name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" name="quantity" class="form-control" min="0" required>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button>
          <button type="submit" class="btn btn-primary">Create</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<!-- Include Bootstrap JS for modals -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
