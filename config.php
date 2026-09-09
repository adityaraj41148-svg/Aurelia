<?php

/**
 * Configuration File for AA Mart
 */

// Start output buffering and secure session if not already active
if (ob_get_level() === 0) {
    ob_start();
}
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Database Configuration Constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'aamart');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application Info & Base URL
define('APP_NAME', 'AA Mart');
define('BASE_URL', 'http://localhost/aamart/');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', BASE_URL . 'uploads/');

// Error Handling Settings
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Default Timezone
date_default_timezone_set('Asia/Kolkata');
