<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_role(['admin']);

// CSV export for simple reports
if (isset($_GET['export']) && $_GET['export'] === 'projects') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="projects.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID','Name','Status','Manager']);
    $res = db()->query("SELECT p.id, p.name, p.status, COALESCE(u.name,'') AS manager FROM projects p LEFT JOIN users u ON u.id = p.assigned_manager ORDER BY p.id ASC");
    while ($row = $res->fetch_assoc()) { fputcsv($out, [$row['id'],$row['name'],$row['status'],$row['manager']]); }
    fclose($out);
    exit;
}

if (isset($_GET['export']) && $_GET['export'] === 'workers') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="workers.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['UserID','Name','Email','Attendance Days']);
    $sql = "SELECT u.id, u.name, u.email, COUNT(a.id) AS attendance_days FROM users u LEFT JOIN attendance a ON a.worker_id = u.id WHERE u.role = 'worker' GROUP BY u.id, u.name, u.email ORDER BY u.id";
    $res = db()->query($sql);
    while ($row = $res->fetch_assoc()) { fputcsv($out, [$row['id'],$row['name'],$row['email'],$row['attendance_days']]); }
    fclose($out);
    exit;
}

include __DIR__ . '/../../includes/header.php';
?>

<style>
/* Reports Page Styles */
.reports-container {
    max-width: 600px;
    margin: 10px auto;
    background-color: #007bff;
    color: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.reports-container h3 {
    margin-bottom: 20px;
    text-align: center;
}
.reports-container .list-group-item {
    background-color: #fff;
    color: #007bff;
    margin-bottom: 10px;
    font-weight: 500;
    border-radius: 8px;
    transition: all 0.2s ease-in-out;
}
.reports-container .list-group-item:hover {
    background-color: #0056b3;
    color: #fff;
    transform: translateY(-2px);
}
</style>

<div class="reports-container">
    <h3>Reports</h3>
    <div class="list-group">
        <a class="list-group-item list-group-item-action" href="?export=projects">
            Export Projects (CSV)
        </a>
        <a class="list-group-item list-group-item-action" href="?export=workers">
            Export Worker Attendance Summary (CSV)
        </a>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
