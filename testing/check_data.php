<<<<<<< HEAD
<?php
require_once '../config.php';

echo "=== CHECKING TRANSACTIONS ===\n\n";

// Check total transactions
$result = $conn->query('SELECT COUNT(*) as count FROM transactions');
$row = $result->fetch_assoc();
echo "Total transactions: " . $row['count'] . "\n\n";

// Check recent transactions
echo "Recent transactions:\n";
$trans = $conn->query('SELECT id, invoice_number, grand_total, total_amount, payment_amount FROM transactions ORDER BY id DESC LIMIT 5');
if ($trans->num_rows > 0) {
    while($t = $trans->fetch_assoc()) {
        echo "ID: {$t['id']} | Invoice: {$t['invoice_number']} | Total: {$t['total_amount']} | Grand Total: {$t['grand_total']} | Payment: {$t['payment_amount']}\n";
    }
} else {
    echo "No transactions found!\n";
}

echo "\n=== CHECKING TRANSACTION DETAILS ===\n\n";
$details = $conn->query('SELECT COUNT(*) as count FROM transaction_details');
$row = $details->fetch_assoc();
echo "Total transaction details: " . $row['count'] . "\n";

echo "\n=== CHECKING ORDERS ===\n\n";
$orders = $conn->query('SELECT COUNT(*) as count FROM orders');
$row = $orders->fetch_assoc();
echo "Total orders: " . $row['count'] . "\n\n";

$orders_list = $conn->query('SELECT id, order_number, total_amount, status, payment_status FROM orders ORDER BY id DESC LIMIT 5');
if ($orders_list->num_rows > 0) {
    echo "Recent orders:\n";
    while($o = $orders_list->fetch_assoc()) {
        echo "ID: {$o['id']} | Order: {$o['order_number']} | Total: {$o['total_amount']} | Status: {$o['status']} | Payment: {$o['payment_status']}\n";
    }
} else {
    echo "No orders found!\n";
}
?>
=======
<?php
require_once '../config.php';

echo "=== CHECKING TRANSACTIONS ===\n\n";

// Check total transactions
$result = $conn->query('SELECT COUNT(*) as count FROM transactions');
$row = $result->fetch_assoc();
echo "Total transactions: " . $row['count'] . "\n\n";

// Check recent transactions
echo "Recent transactions:\n";
$trans = $conn->query('SELECT id, invoice_number, grand_total, total_amount, payment_amount FROM transactions ORDER BY id DESC LIMIT 5');
if ($trans->num_rows > 0) {
    while($t = $trans->fetch_assoc()) {
        echo "ID: {$t['id']} | Invoice: {$t['invoice_number']} | Total: {$t['total_amount']} | Grand Total: {$t['grand_total']} | Payment: {$t['payment_amount']}\n";
    }
} else {
    echo "No transactions found!\n";
}

echo "\n=== CHECKING TRANSACTION DETAILS ===\n\n";
$details = $conn->query('SELECT COUNT(*) as count FROM transaction_details');
$row = $details->fetch_assoc();
echo "Total transaction details: " . $row['count'] . "\n";

echo "\n=== CHECKING ORDERS ===\n\n";
$orders = $conn->query('SELECT COUNT(*) as count FROM orders');
$row = $orders->fetch_assoc();
echo "Total orders: " . $row['count'] . "\n\n";

$orders_list = $conn->query('SELECT id, order_number, total_amount, status, payment_status FROM orders ORDER BY id DESC LIMIT 5');
if ($orders_list->num_rows > 0) {
    echo "Recent orders:\n";
    while($o = $orders_list->fetch_assoc()) {
        echo "ID: {$o['id']} | Order: {$o['order_number']} | Total: {$o['total_amount']} | Status: {$o['status']} | Payment: {$o['payment_status']}\n";
    }
} else {
    echo "No orders found!\n";
}
?>
>>>>>>> 882da412c224f7c20dfb67829049d92fbad8991f
