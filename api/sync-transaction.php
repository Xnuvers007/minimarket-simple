<<<<<<< HEAD
<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

// Set header untuk JSON response
header('Content-Type: application/json');

// Cek apakah method adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Ambil data JSON dari request body
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validasi data
if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

try {
    // Mulai transaction
    $conn->begin_transaction();
    
    // Data transaksi yang diperlukan
    $customer_id = isset($data['customer_id']) ? intval($data['customer_id']) : null;
    $total_amount = isset($data['total_amount']) ? floatval($data['total_amount']) : 0;
    $payment_method = isset($data['payment_method']) ? $data['payment_method'] : 'cash';
    $payment_amount = isset($data['payment_amount']) ? floatval($data['payment_amount']) : 0;
    $change_amount = isset($data['change_amount']) ? floatval($data['change_amount']) : 0;
    $items = isset($data['items']) ? $data['items'] : [];
    $offline_timestamp = isset($data['offline_timestamp']) ? $data['offline_timestamp'] : date('Y-m-d H:i:s');
    
    // Validasi items
    if (empty($items)) {
        throw new Exception('No items in transaction');
    }
    
    // Insert transaksi
    $stmt = $conn->prepare("INSERT INTO transactions (customer_id, total_amount, payment_method, payment_amount, change_amount, status, transaction_date, notes) VALUES (?, ?, ?, ?, ?, 'completed', ?, ?)");
    $notes = "Synced from offline transaction (Original time: {$offline_timestamp})";
    $stmt->bind_param("idsddss", $customer_id, $total_amount, $payment_method, $payment_amount, $change_amount, $offline_timestamp, $notes);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to insert transaction: ' . $stmt->error);
    }
    
    $transaction_id = $conn->insert_id;
    $stmt->close();
    
    // Insert transaction items dan update stock
    foreach ($items as $item) {
        $product_id = intval($item['product_id']);
        $quantity = intval($item['quantity']);
        $price = floatval($item['price']);
        $subtotal = $quantity * $price;
        
        // Insert transaction item
        $stmt = $conn->prepare("INSERT INTO transaction_items (transaction_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiidd", $transaction_id, $product_id, $quantity, $price, $subtotal);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to insert transaction item: ' . $stmt->error);
        }
        $stmt->close();
        
        // Update stock produk
        $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt->bind_param("ii", $quantity, $product_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update product stock: ' . $stmt->error);
        }
        $stmt->close();
        
        // Cek jika stock rendah, bisa tambahkan notifikasi di sini
        $stmt = $conn->prepare("SELECT stock, min_stock FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
        
        if ($product && $product['stock'] <= $product['min_stock']) {
            // Log stock rendah (opsional)
            error_log("Low stock warning: Product ID {$product_id}, Stock: {$product['stock']}");
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    // Response sukses
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Transaction synced successfully',
        'transaction_id' => $transaction_id
    ]);
    
} catch (Exception $e) {
    // Rollback jika ada error
    $conn->rollback();
    
    // Log error
    error_log("Sync transaction error: " . $e->getMessage());
    
    // Response error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to sync transaction: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
=======
<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

// Set header untuk JSON response
header('Content-Type: application/json');

// Cek apakah method adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Ambil data JSON dari request body
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validasi data
if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

try {
    // Mulai transaction
    $conn->begin_transaction();
    
    // Data transaksi yang diperlukan
    $customer_id = isset($data['customer_id']) ? intval($data['customer_id']) : null;
    $total_amount = isset($data['total_amount']) ? floatval($data['total_amount']) : 0;
    $payment_method = isset($data['payment_method']) ? $data['payment_method'] : 'cash';
    $payment_amount = isset($data['payment_amount']) ? floatval($data['payment_amount']) : 0;
    $change_amount = isset($data['change_amount']) ? floatval($data['change_amount']) : 0;
    $items = isset($data['items']) ? $data['items'] : [];
    $offline_timestamp = isset($data['offline_timestamp']) ? $data['offline_timestamp'] : date('Y-m-d H:i:s');
    
    // Validasi items
    if (empty($items)) {
        throw new Exception('No items in transaction');
    }
    
    // Insert transaksi
    $stmt = $conn->prepare("INSERT INTO transactions (customer_id, total_amount, payment_method, payment_amount, change_amount, status, transaction_date, notes) VALUES (?, ?, ?, ?, ?, 'completed', ?, ?)");
    $notes = "Synced from offline transaction (Original time: {$offline_timestamp})";
    $stmt->bind_param("idsddss", $customer_id, $total_amount, $payment_method, $payment_amount, $change_amount, $offline_timestamp, $notes);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to insert transaction: ' . $stmt->error);
    }
    
    $transaction_id = $conn->insert_id;
    $stmt->close();
    
    // Insert transaction items dan update stock
    foreach ($items as $item) {
        $product_id = intval($item['product_id']);
        $quantity = intval($item['quantity']);
        $price = floatval($item['price']);
        $subtotal = $quantity * $price;
        
        // Insert transaction item
        $stmt = $conn->prepare("INSERT INTO transaction_items (transaction_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiidd", $transaction_id, $product_id, $quantity, $price, $subtotal);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to insert transaction item: ' . $stmt->error);
        }
        $stmt->close();
        
        // Update stock produk
        $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt->bind_param("ii", $quantity, $product_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update product stock: ' . $stmt->error);
        }
        $stmt->close();
        
        // Cek jika stock rendah, bisa tambahkan notifikasi di sini
        $stmt = $conn->prepare("SELECT stock, min_stock FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
        
        if ($product && $product['stock'] <= $product['min_stock']) {
            // Log stock rendah (opsional)
            error_log("Low stock warning: Product ID {$product_id}, Stock: {$product['stock']}");
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    // Response sukses
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Transaction synced successfully',
        'transaction_id' => $transaction_id
    ]);
    
} catch (Exception $e) {
    // Rollback jika ada error
    $conn->rollback();
    
    // Log error
    error_log("Sync transaction error: " . $e->getMessage());
    
    // Response error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to sync transaction: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
>>>>>>> 882da412c224f7c20dfb67829049d92fbad8991f
