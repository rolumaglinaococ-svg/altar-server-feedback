<?php
/**
 * admin/logout.php
 * Destroys the admin's session and sends them back to the login page.
 */
session_start();

// Clear all session variables.
$_SESSION = [];

// Delete the session cookie itself, if one is being used.
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Finally, destroy the session data on the server.
session_destroy();

header('Location: login.php');
exit;
