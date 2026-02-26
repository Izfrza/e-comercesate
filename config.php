<?php
// Database Configuration
// Ganti dengan data dari hosting Anda
define('DB_HOST', 'localhost'); // biasanya localhost atau server hostname
define('DB_USER', 'username_anda');
define('DB_PASS', 'password_anda');
define('DB_NAME', 'sate_madura_db');

// WhatsApp Configuration
define('WA_BUSINESS_NUMBER', '0895402357182');

// Start session
session_start();

// Database connection function
function getConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        die("Database connection error: " . $e->getMessage());
    }
}

// Helper function to sanitize input
function sanitize($data) {
    if ($data === null || $data === '') {
        return '';
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Helper function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Generate order number
function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
}

// Format number (thousand separator)
function formatNumber($amount) {
    return number_format($amount, 0, ',', '.');
}

// Format currency
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}
?>
