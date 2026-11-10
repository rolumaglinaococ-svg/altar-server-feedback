<?php
/**
 * admin/login.php
 * Simple login form for the administrator. Submits to authenticate.php.
 */
session_start();

// If already logged in, skip straight to the dashboard.
if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Altar Server Month 2026</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<style>
    body.admin-body {
        background-image: url('../assets/images/stst.jpg') !important;
        background-size: cover !important;
        background-position: center !important;
        background-repeat: no-repeat !important;
        background-attachment: fixed !important;
    }
</style>

<body class="admin-body">
    <div class="login-wrap">
        <div class="login-card">
            <div class="login-badge">🔐</div>
            <h1>Admin Login</h1>
            <p class="sub">Altar Server Month 2026</p>

            <?php if ($error === '1'): ?>
                <div class="alert alert-error">Invalid username or password. Please try again.</div>
            <?php endif; ?>

            <form action="authenticate.php" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required maxlength="50" autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required maxlength="255">
                </div>
                <button type="submit" class="btn btn-primary btn-block">LOGIN</button>
            </form>
        </div>
    </div>
</body>
</html>
