<?php
require_once __DIR__ . '/helpers.php';
$user = current_user();
$isLoginPage = isset($HIDE_SIDEBAR) && $HIDE_SIDEBAR === true;
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Real Estate Bankaii</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!--<link href="<?php echo public_url('assets/css/style.css'); ?>" rel="stylesheet">-->
    <link rel="stylesheet" href="<?= public_url('../assets/css/style.css') ?>">
  </head>
  <body class="<?php echo $isLoginPage ? 'login-page' : 'dashboard'; ?>">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <?php if (!$isLoginPage): ?>
<button class="btn btn-outline-light d-lg-none me-2" id="sidebarToggle">☰</button>
      <?php endif; ?>
<a class="navbar-brand text-light fw-bold ms-4 ms-lg-5" href="#">🏗 REM</a>
      <div class="d-flex align-items-center">
        <?php if ($user): ?>
          <span class="text-light me-3">
            👋 Hello <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
          </span>
          <a class="btn btn-sm btn-outline-light" href="<?php echo public_url('logout.php'); ?>">Logout</a>
        <?php else: ?>
          <a class="btn btn-sm btn-light" href="<?php echo public_url('index.php'); ?>">Login</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <div class="layout">
    <?php if (!$isLoginPage): ?>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
      <nav class="nav flex-column px-2">
        <?php if ($user): ?>
          <?php
          // Helper function for active state
          function nav_item($url, $label, $file) {
              global $currentPage;
              $active = ($currentPage === $file) ? 'active' : '';
              echo "<a class='nav-link $active' href='".public_url($url)."'>$label</a>";
          }
          ?>
          <?php if ($user['role'] === 'admin'): ?>
            <?php nav_item('admin/dashboard.php', 'Dashboard', 'dashboard.php'); ?>
            <?php nav_item('admin/users.php', 'Users', 'users.php'); ?>
            <?php nav_item('admin/projects.php', 'Projects', 'projects.php'); ?>
            <?php nav_item('admin/stock.php', 'Stock', 'stock.php'); ?>
            <?php nav_item('admin/reports.php', 'Reports', 'reports.php'); ?>
            <?php nav_item('admin/releases.php', 'Releases', 'releases.php'); ?>
          <?php elseif ($user['role'] === 'manager'): ?>
            <?php nav_item('manager/dashboard.php', 'Dashboard', 'dashboard.php'); ?>
            <?php nav_item('manager/projects.php', 'Projects', 'projects.php'); ?>
            <?php nav_item('manager/workers.php', 'Workers', 'workers.php'); ?>
            <?php nav_item('manager/tasks.php', 'Tasks', 'tasks.php'); ?>
            <?php nav_item('manager/attendance.php', 'Attendance', 'attendance.php'); ?>
            <?php nav_item('manager/stock_usage.php', 'Stock Usage', 'stock_usage.php'); ?>
          <?php elseif ($user['role'] === 'supervisor'): ?>
            <?php nav_item('supervisor/dashboard.php', 'Dashboard', 'dashboard.php'); ?>
            <?php nav_item('supervisor/workers.php', 'Workers', 'workers.php'); ?>
            <?php nav_item('supervisor/tasks.php', 'Tasks', 'tasks.php'); ?>
            <?php nav_item('supervisor/attendance.php', 'Attendance', 'attendance.php'); ?>
            <?php nav_item('supervisor/stock_usage.php', 'Stock Usage', 'stock_usage.php'); ?>
            <?php nav_item('supervisor/stock_release.php', 'Record Release', 'stock_release.php'); ?>
          <?php elseif ($user['role'] === 'worker'): ?>
            <?php nav_item('worker/dashboard.php', 'Dashboard', 'dashboard.php'); ?>
            <?php nav_item('worker/tasks.php', 'Tasks', 'tasks.php'); ?>
            <?php nav_item('worker/attendance.php', 'Attendance', 'attendance.php'); ?>
          <?php endif; ?>
        <?php endif; ?>
      </nav>
    </aside>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="main">
