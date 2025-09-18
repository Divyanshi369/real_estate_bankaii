<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_role(['admin','manager']);

// --- Pagination helper fallback ---
if (!function_exists('paginate')) {
    function paginate($total, $perPage = 10) {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $totalPages = max(1, (int)ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;
        return [
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'offset' => $offset,
        ];
    }
}

// --- Count total releases (safe) ---
$total = 0;
try {
    $resCount = db()->query('SELECT COUNT(*) c FROM stock_releases');
    $total = (int)($resCount->fetch_assoc()['c'] ?? 0);
} catch (Throwable $e) {
    // If table missing, fallback
    $total = 0;
}

// --- Pagination info ---
$pageInfo = paginate($total, 10);

// --- Fetch releases safely ---
$rows = [];
try {
    $stmt = db()->prepare(
        'SELECT r.id, r.quantity, r.worker_name, r.image_path, r.created_at,
                s.item_name, u.name AS released_by_name
         FROM stock_releases r
         LEFT JOIN stock s ON s.id = r.stock_id
         LEFT JOIN users u ON u.id = r.released_by
         ORDER BY r.id DESC
         LIMIT ? OFFSET ?'
    );
    $stmt->bind_param('ii', $pageInfo['perPage'], $pageInfo['offset']);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Throwable $e) {
    $rows = [];
    error_log("Stock release query failed: " . $e->getMessage());
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Stock Releases</h3>
</div>

<?php if (empty($rows)): ?>
  <div class="alert alert-warning">No stock releases found.</div>
<?php else: ?>
<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead>
      <tr>
        <th>ID</th><th>Item</th><th>Qty</th><th>To Worker</th>
        <th>Photo</th><th>By</th><th>At</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= (int)$r['id']; ?></td>
          <td><?= h($r['item_name'] ?? '—'); ?></td>
          <td><?= (int)($r['quantity'] ?? 0); ?></td>
          <td><?= h($r['worker_name'] ?? '—'); ?></td>
          <td>
            <?php if (!empty($r['image_path'])): ?>
              <a href="<?= public_url($r['image_path']); ?>" target="_blank">
                <img src="<?= public_url($r['image_path']); ?>" alt="photo"
                     style="height:48px;width:auto;border-radius:6px;object-fit:cover;">
              </a>
            <?php else: ?>—<?php endif; ?>
          </td>
          <td><?= h($r['released_by_name'] ?? '—'); ?></td>
          <td><?= h($r['created_at'] ?? '—'); ?></td>
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

<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
