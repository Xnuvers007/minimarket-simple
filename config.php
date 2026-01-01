<?php
  
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
    
// Start session hanya jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =============================================
// DATABASE CONFIGURATION
// =============================================
define('DB_HOST', 'sql111.infinityfree.com');
define('DB_USER', 'if0_40803099');
define('DB_PASS', '74vRti0jMu5pgR');
define('DB_NAME', 'if0_40803099_minimarket_db');

// =============================================
// SITE CONFIGURATION
// =============================================
define('SITE_NAME', 'Minimarket Sejahtera');
// define('SITE_URL', 'http://localhost:8000');
// define('BASE_URL', 'http://localhost:8000/'); // Tambahkan trailing slash

// Deteksi Protokol (http atau https)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";

// Deteksi Host (localhost atau localhost:8000)
$host = $_SERVER['HTTP_HOST'];

// Deteksi Folder Otomatis
// Jika dijalankan di root (seperti php -S), path akan kosong
// Jika di XAMPP, path akan berisi '/sera'
$path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

// Gabungkan semuanya
define('SITE_URL', $protocol . "://" . $host . $path);
define('BASE_URL', SITE_URL . '/');

define('ADMIN_EMAIL', 'admin@minimarket.com');

// Path Configuration
define('ROOT_PATH', __DIR__);
define('UPLOAD_PATH', ROOT_PATH . '/assets/images/products/');
define('UPLOAD_URL', BASE_URL . 'assets/images/products/');

// =============================================
// CONNECT TO DATABASE
// =============================================
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

// =============================================
// TIMEZONE
// =============================================
date_default_timezone_set('Asia/Jakarta');

// =============================================
// HELPER FUNCTIONS
// =============================================

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user role
function getUserRole() {
    return $_SESSION['role'] ?? null;
}

// Get current user ID
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get current username
function getUsername() {
    return $_SESSION['username'] ?? null;
}

// Check user role
function checkRole($allowedRoles) {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "index.php");
        exit();
    }
    
    $userRole = getUserRole();
    if (!in_array($userRole, $allowedRoles)) {
        header("Location: " . BASE_URL . "index.php");
        exit();
    }
}

// Redirect helper
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Format Rupiah
function formatRupiah($amount) {
    return "Rp " . number_format($amount, 0, ',', '.');
}

// Format Tanggal Indonesia
function formatTanggalIndo($date) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $pecahkan = explode('-', date('Y-m-d', strtotime($date)));
    return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
}

// Generate Invoice Number
function generateInvoiceNumber() {
    return 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

// Generate Order Number
function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

// Generate SKU
function generateSKU($prefix = 'PRD') {
    return $prefix . '-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

// XSS Protection
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Sanitize string
function sanitize($string) {
    global $conn;
    return $conn->real_escape_string(trim($string));
}

// Flash Message
function setFlashMessage($type, $message) {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'];
        $message = $_SESSION['flash_message'];
        
        unset($_SESSION['flash_type']);
        unset($_SESSION['flash_message']);
        
        return [
            'type' => $type,
            'message' => $message
        ];
    }
    return null;
}

function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $alertClass = 'alert-info';
        switch ($flash['type']) {
            case 'success':
                $alertClass = 'alert-success';
                break;
            case 'error':
                $alertClass = 'alert-danger';
                break;
            case 'warning':
                $alertClass = 'alert-warning';
                break;
        }
        
        echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($flash['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
    }
}

// Get Setting Value
function getSetting($key, $default = '') {
    global $conn;
    
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['setting_value'];
    }
    
    return $default;
}

// Log Activity
function logActivity($activity, $description = '') {
    global $conn;
    
    $user_id = getUserId();
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, activity, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $activity, $description, $ip_address, $user_agent);
    $stmt->execute();
    $stmt->close();
}

// Upload Image
function uploadImage($file, $folder = 'products') {
    $target_dir = UPLOAD_PATH;
    
    // Create directory if not exists
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    // Validate file type
    $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_types)) {
        return array('success' => false, 'message' => 'Tipe file tidak diizinkan. Hanya JPG, JPEG, PNG, dan GIF.');
    }
    
    // Validate file size (max 5MB)
    if ($file['size'] > 5242880) {
        return array('success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 5MB.');
    }
    
    // Generate unique filename
    $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return array('success' => true, 'filename' => $new_filename);
    } else {
        return array('success' => false, 'message' => 'Gagal upload file.');
    }
}

// Delete Image
function deleteImage($filename) {
    if (!empty($filename)) {
        $file_path = UPLOAD_PATH . $filename;
        if (file_exists($file_path)) {
            unlink($file_path);
            return true;
        }
    }
    return false;
}
?>