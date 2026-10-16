<?php
/**
 * require-login.php — include this as the very first line of any admin
 * page that should be protected. It starts the session (if not already
 * started) and bounces anyone not logged in back to the login page.
 */
require_once __DIR__ . '/session.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

function adminCsrfToken(): string
{
    if (!isset($_SESSION['admin_csrf_token']) || !is_string($_SESSION['admin_csrf_token'])) {
        $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['admin_csrf_token'];
}

function adminCsrfTokenIsValid(): bool
{
    $submittedToken = $_POST['admin_csrf_token'] ?? '';
    return is_string($submittedToken) && hash_equals(adminCsrfToken(), $submittedToken);
}
