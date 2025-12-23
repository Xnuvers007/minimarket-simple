<?php
/**
 * File Functions - Helper Functions untuk Sistem Toko
 * Functions yang sudah ada di config.php tidak perlu didefinisikan ulang
 */

// Note: Function-function berikut sudah ada di config.php:
// - redirect($url)
// - checkRole($allowedRoles)
// - formatRupiah($amount)
// - isLoggedIn()
// - getUserRole()
// - getUserId()
// - getUsername()

// Fungsi tambahan untuk format tanggal (alias dari formatTanggalIndo di config.php)
function formatTanggal($date) {
    return formatTanggalIndo($date);
}

// Note: uploadImage() dan deleteImage() sudah ada di config.php
// Alias untuk backward compatibility
function uploadProductImage($file) {
    return uploadImage($file, 'products');
}

function deleteProductImage($filename) {
    return deleteImage($filename);
}

// Fungsi untuk generate kode transaksi
function generateTransactionCode() {
    return 'TRX-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

// Fungsi untuk generate kode order
function generateOrderCode() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

// Fungsi untuk get status badge
function getStatusBadge($status) {
    $badges = array(
        'pending' => '<span class="badge bg-warning">Pending</span>',
        'processing' => '<span class="badge bg-info">Diproses</span>',
        'shipped' => '<span class="badge bg-primary">Dikirim</span>',
        'completed' => '<span class="badge bg-success">Selesai</span>',
        'cancelled' => '<span class="badge bg-danger">Dibatalkan</span>',
        'paid' => '<span class="badge bg-success">Dibayar</span>',
        'unpaid' => '<span class="badge bg-danger">Belum Dibayar</span>'
    );
    
    return isset($badges[$status]) ? $badges[$status] : '<span class="badge bg-secondary">' . $status . '</span>';
}

// Fungsi untuk validasi email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Fungsi untuk hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Fungsi untuk verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Note: setFlashMessage() dan displayFlashMessage() sudah ada di config.php

// Fungsi untuk pagination
function paginate($total_items, $items_per_page, $current_page) {
    $total_pages = ceil($total_items / $items_per_page);
    $offset = ($current_page - 1) * $items_per_page;
    
    return array(
        'total_pages' => $total_pages,
        'offset' => $offset,
        'current_page' => $current_page
    );
}

// Fungsi untuk get user role name
function getRoleName($role) {
    $roles = array(
        'admin' => 'Administrator',
        'kasir' => 'Kasir',
        'customer' => 'Customer'
    );
    
    return isset($roles[$role]) ? $roles[$role] : $role;
}

// Note: logActivity() sudah ada di config.php dengan signature berbeda
// Gunakan: logActivity($activity, $description) dari config.php

// Fungsi untuk check stock availability
function checkStockAvailability($product_id, $quantity) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    
    if ($product && $product['stock'] >= $quantity) {
        return true;
    }
    return false;
}

// Fungsi untuk update stock
function updateStock($product_id, $quantity, $operation = 'decrease') {
    global $conn;
    
    if ($operation == 'decrease') {
        $sql = "UPDATE products SET stock = stock - ? WHERE id = ?";
    } else {
        $sql = "UPDATE products SET stock = stock + ? WHERE id = ?";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $quantity, $product_id);
    $success = $stmt->execute();
    $stmt->close();
    
    return $success;
}

// Fungsi untuk get cart total
function getCartTotal($user_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT SUM(c.quantity * p.price) as total 
                           FROM cart c 
                           JOIN products p ON c.product_id = p.id 
                           WHERE c.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row['total'] ?? 0;
}

// Fungsi untuk get cart items count
function getCartItemsCount($user_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row['count'] ?? 0;
}
?>