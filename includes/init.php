<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Initialize session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set default timezone
date_default_timezone_set('Asia/Colombo');

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Initialize language preference
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

// Set error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Ensure upload directory exists with proper permissions
$uploadDir = __DIR__ . '/../assets/images/food';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Ensure logs directory exists with proper permissions
$logDir = __DIR__ . '/../logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0777, true);
}
?>