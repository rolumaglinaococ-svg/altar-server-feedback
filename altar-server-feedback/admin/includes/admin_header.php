<?php
/**
 * admin/includes/admin_header.php
 * Shared top navigation bar for the admin area.
 * Include AFTER auth_check.php. Expects $activePage to be set
 * ('dashboard' or 'feedback') so the correct nav link is highlighted.
 */
if (!isset($activePage)) {
    $activePage = '';
}
?>
<div class="admin-topbar">
    <div class="brand">ALTAR SERVER MONTH 2026 &middot; Admin</div>
    <nav class="admin-nav">
        <a href="dashboard.php" class="<?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
        <a href="dashboard.php#feedback-table" class="<?php echo $activePage === 'feedback' ? 'active' : ''; ?>">Feedback</a>
        <a href="logout.php">Logout</a>
    </nav>
</div>
