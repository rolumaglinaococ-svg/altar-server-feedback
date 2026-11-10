<?php
/**
 * admin/authenticate.php
 * ---------------------------------------------------------------------
 * Receives the login form from login.php, checks the username and
 * password against the database, and starts a session if correct.
 *
 * How the password check works:
 *  - The database never stores the real password, only a bcrypt hash
 *    of it (created with password_hash() when the account was made).
 *  - password_verify($typedPassword, $storedHash) re-hashes the typed
 *    password behind the scenes and compares it safely to the stored
 *    hash. It returns true only if they match.
 * ---------------------------------------------------------------------
 */

session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($username === '' || $password === '') {
    header('Location: login.php?error=1');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id, username, password_hash FROM admin_users WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => $username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        // Regenerate the session ID on login to help prevent session fixation attacks.
        session_regenerate_id(true);

        $_SESSION['admin_id']       = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];

        header('Location: dashboard.php');
        exit;
    } else {
        header('Location: login.php?error=1');
        exit;
    }
} catch (PDOException $e) {
    if (APP_DEBUG) {
        die('Login error: ' . $e->getMessage());
    } else {
        header('Location: login.php?error=1');
        exit;
    }
}
