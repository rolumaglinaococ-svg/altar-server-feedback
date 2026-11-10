<?php
/**
 * admin/delete_feedback.php
 * ---------------------------------------------------------------------
 * Deletes one feedback row. This is a destructive action, so it:
 *   - requires the admin to be logged in (auth_check.php)
 *   - only accepts POST requests (never a GET link like ?id=5)
 *   - checks a CSRF token so another website can't trick the admin's
 *     browser into deleting feedback without them meaning to
 * ---------------------------------------------------------------------
 */
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$postedToken = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
if (!csrf_check($postedToken)) {
    http_response_code(403);
    die('Invalid request. Please go back and try again.');
}

$id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : false;

if ($id !== false && $id !== null) {
    $stmt = $pdo->prepare('DELETE FROM feedback WHERE id = :id');
    $stmt->execute([':id' => $id]);
}

header('Location: dashboard.php#feedback-table');
exit;
