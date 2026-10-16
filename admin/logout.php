<?php
require __DIR__ . '/includes/require-login.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !adminCsrfTokenIsValid()) {
    http_response_code(400);
    exit('Invalid logout request.');
}

$_SESSION = [];
session_destroy();

if (ini_get('session.use_cookies')) {
    $cookie = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
}

header('Location: login.php');
exit;
