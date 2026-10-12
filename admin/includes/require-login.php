<?php
/**
 * require-login.php — include this as the very first line of any admin
 * page that should be protected. It starts the session (if not already
 * started) and bounces anyone not logged in back to the login page.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
