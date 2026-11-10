<?php
/**
 * admin/includes/auth_check.php
 * ---------------------------------------------------------------------
 * Include this at the very TOP of every admin page that should be
 * protected (dashboard, view_feedback, delete_feedback). It checks
 * whether a valid admin session exists; if not, it sends the visitor
 * to the login page and stops the rest of the page from running.
 * ---------------------------------------------------------------------
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

/**
 * Generates (or reuses) a CSRF token stored in the session, used to
 * protect admin forms (like the delete form) against Cross-Site
 * Request Forgery -- a trick where another website tries to submit
 * a form to our site on the admin's behalf without them knowing.
 */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
}

function csrf_check($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string) $token);
}
