<?php
/**
 * AA Mart - Database Configuration & Connection Entry Point
 */

require_once __DIR__ . '/../includes/db.php';

if (!function_exists('get_db_connection')) {
    function get_db_connection(): PDO {
        return getDB();
    }
}
