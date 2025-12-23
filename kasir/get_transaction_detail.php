<?php
require_once '../config.php';
checkRole(['kasir', 'admin']);

header('Content-Type: application/json');

$transaction_id = $_GET['id'] ?? 0;

// Get transaction
$trans = $conn->query("SELECT * FROM transactions WHERE id=$transaction_id")->fetch_assoc();

if (!$trans) {
    echo json_encode(['error' => 'Transaction not found']);
    exit;
}

// Get transaction details
$details = $conn->query("SELECT td.*, p.product_name FROM transaction_details td JOIN products p ON td.product_id = p.id WHERE td.transaction_id=$transaction_id");

$items = [];
while($item = $details->fetch_assoc()) {
    $items[] = $item;
}

echo json_encode([
    'items' => $items,
    'grand_total' => $trans['grand_total']
]);
?>