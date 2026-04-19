<?php
/**
 * Sign out the current authenticated user.
 *
 * This removes the session payload and expires the session cookie.
 */
require_once(__DIR__ . '/auth.php');

ensureSessionStarted();

$_SESSION = [];

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

session_destroy();

header('Location: ../index.php');
exit;
