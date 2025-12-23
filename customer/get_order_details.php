<?php
require_once '../config.php';
checkRole(['customer', 'kasir', 'admin']);

header('Content-Type: application/json');

$order_id = $_GET['id'] ?? $_GET['order_id'] ?? 0;
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Verify order belongs to user (for customer) or allow kasir/admin to view all
if ($role == 'customer') {
    $order = $conn->query("SELECT * FROM orders WHERE id=$order_id AND user_id=$user_id")->fetch_assoc();
} else {
    $order = $conn->query("SELECT * FROM orders WHERE id=$order_id")->fetch_assoc();
}

if (!$order) {
    echo json_encode(['success' => false, 'error' => 'Order not found']);
    exit;
}

// Get order details
$details = $conn->query("SELECT od.*, p.product_name FROM order_details od JOIN products p ON od.product_id = p.id WHERE od.order_id=$order_id");

$items = [];
while($item = $details->fetch_assoc()) {
    $items[] = $item;
}

echo json_encode([
    'success' => true,
    'order' => $order,
    'items' => $items,
    'total' => $order['total_amount']
]);
?>