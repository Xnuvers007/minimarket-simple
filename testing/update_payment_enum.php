<?php
require_once '../config.php';

// Update payment_method ENUM to include 'cod' and 'transfer'
$sql = "ALTER TABLE transactions MODIFY payment_method ENUM('cash', 'debit', 'credit', 'ewallet', 'qris', 'cod', 'transfer','other') NOT NULL DEFAULT 'cash'";

if ($conn->query($sql)) {
    echo "✓ Database berhasil diupdate!<br>";
    echo "Kolom payment_method sekarang mendukung: cash, debit, credit, ewallet, qris, cod, transfer, other.";
} else {
    echo "✗ Error: " . $conn->error;
}

$conn->close();
?>
