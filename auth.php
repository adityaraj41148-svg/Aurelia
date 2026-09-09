<?php
/**
 * User & Admin Authentication Handler
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Check if user is logged in
function is_logged_in(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Get current logged in user details
function current_user(): ?array {
    if (!is_logged_in()) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, email, phone, role, status FROM users WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

// Check if admin is logged in
function is_admin_logged_in(): bool {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Require admin authentication or redirect
function require_admin(): void {
    if (!is_admin_logged_in()) {
        set_flash('error', 'Please login to access the admin panel.');
        redirect(BASE_URL . 'admin/login.php');
    }
}

// Require user authentication or redirect
function require_user(): void {
    if (!is_logged_in()) {
        set_flash('error', 'Please sign in to access this page.');
        redirect(BASE_URL . 'login.php');
    }
}

// User Login Function
function login_user(string $email, string $password): bool {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email AND status = 'active'");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        return true;
    }
    return false;
}

// Admin Login Function
function login_admin(string $email, string $password): bool {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM admin_users WHERE email = :email AND status = 'active'");
    $stmt->execute(['email' => $email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_role'] = $admin['role'];
        return true;
    }
    return false;
}

// User Logout
function logout_user(): void {
    unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);
    set_flash('success', 'You have been logged out.');
    redirect(BASE_URL . 'index.php');
}

// Admin Logout
function logout_admin(): void {
    unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_email'], $_SESSION['admin_role']);
    set_flash('success', 'Logged out from admin panel.');
    redirect(BASE_URL . 'admin/login.php');
}
