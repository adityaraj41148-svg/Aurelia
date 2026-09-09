<?php
/**
 * AURELIA Admin Logout Handler
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset admin session variables
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_email']);
unset($_SESSION['admin_role']);

$_SESSION['admin_flash_success'] = 'You have been logged out successfully.';
header('Location: admin-login.php');
exit();
