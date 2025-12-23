<?php
require_once '../config.php';

// Check if user is logged in and is kasir
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kasir') {
    header("Location: ../index.php");
    exit;
}

// Get transaction ID
if (!isset($_GET['id'])) {
    header("Location: transactions.php");
    exit;
}

$transaction_id = (int)$_GET['id'];

// Get transaction data
$transaction = $conn->query("SELECT t.*, u.full_name as kasir_name FROM transactions t LEFT JOIN users u ON t.kasir_id = u.id WHERE t.id = $transaction_id")->fetch_assoc();

if (!$transaction) {
    header("Location: transactions.php");
    exit;
}

// Get transaction details
$details = $conn->query("SELECT td.*, p.product_name FROM transaction_details td JOIN products p ON td.product_id = p.id WHERE td.transaction_id = $transaction_id");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #<?php echo $transaction['invoice_number']; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            padding: 20px;
            background: #f5f5f5;
        }
        
        .receipt {
            width: 80mm;
            max-width: 300px;
            margin: 0 auto;
            background: white;
            padding: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            border-bottom: 2px dashed #333;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        
        .store-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .store-info {
            font-size: 10px;
            line-height: 1.3;
        }
        
        .transaction-info {
            margin-bottom: 10px;
            font-size: 11px;
            border-bottom: 1px dashed #333;
            padding-bottom: 10px;
        }
        
        .transaction-info div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        
        .items {
            margin-bottom: 10px;
        }
        
        .item {
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px dotted #ccc;
        }
        
        .item-name {
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .item-details {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
        }
        
        .totals {
            border-top: 2px dashed #333;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 11px;
        }
        
        .total-row.grand {
            font-size: 14px;
            font-weight: bold;
            border-top: 1px solid #333;
            border-bottom: 1px solid #333;
            padding: 8px 0;
            margin-top: 8px;
        }
        
        .payment {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #333;
        }
        
        .footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px dashed #333;
            font-size: 10px;
        }
        
        .footer-message {
            margin-top: 10px;
            font-style: italic;
        }
        
        .no-print {
            text-align: center;
            margin-top: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            margin: 5px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .receipt {
                width: 80mm;
                max-width: none;
                box-shadow: none;
                padding: 5px;
            }
            
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="store-name"><?php echo SITE_NAME; ?></div>
            <div class="store-info">
                Jl. Contoh No. 123, Jakarta<br>
                Telp: (021) 1234-5678<br>
                Email: info@minimarket.com
            </div>
        </div>
        
        <!-- Transaction Info -->
        <div class="transaction-info">
            <div>
                <span>No. Transaksi</span>
                <span><?php echo $transaction['invoice_number']; ?></span>
            </div>
            <div>
                <span>Tanggal</span>
                <span><?php echo date('d/m/Y H:i', strtotime($transaction['transaction_date'])); ?></span>
            </div>
            <div>
                <span>Kasir</span>
                <span><?php echo $transaction['kasir_name']; ?></span>
            </div>
        </div>
        
        <!-- Items -->
        <div class="items">
            <?php while($item = $details->fetch_assoc()): ?>
            <div class="item">
                <div class="item-name"><?php echo $item['product_name']; ?></div>
                <div class="item-details">
                    <span><?php echo number_format($item['quantity']); ?> x <?php echo formatRupiah($item['price']); ?></span>
                    <span><?php echo formatRupiah($item['subtotal']); ?></span>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        
        <!-- Totals -->
        <div class="totals">
            <div class="total-row">
                <span>Subtotal</span>
                <span><?php echo formatRupiah($transaction['subtotal']); ?></span>
            </div>
            
            <?php if($transaction['discount'] > 0): ?>
            <div class="total-row">
                <span>Diskon</span>
                <span>-<?php echo formatRupiah($transaction['discount']); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if($transaction['tax'] > 0): ?>
            <div class="total-row">
                <span>Pajak</span>
                <span><?php echo formatRupiah($transaction['tax']); ?></span>
            </div>
            <?php endif; ?>
            
            <div class="total-row grand">
                <span>TOTAL</span>
                <span><?php echo formatRupiah($transaction['grand_total']); ?></span>
            </div>
        </div>
        
        <!-- Payment -->
        <div class="payment">
            <div class="total-row">
                <span>Tunai</span>
                <span><?php echo formatRupiah($transaction['payment_amount']); ?></span>
            </div>
            <div class="total-row">
                <span>Kembalian</span>
                <span><?php echo formatRupiah($transaction['change_amount']); ?></span>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div>*** TERIMA KASIH ***</div>
            <div class="footer-message">
                Barang yang sudah dibeli<br>
                tidak dapat ditukar/dikembalikan
            </div>
            <div style="margin-top: 10px;">
                <?php echo date('d/m/Y H:i:s'); ?>
            </div>
        </div>
    </div>
    
    <!-- Print Buttons -->
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-primary">
            🖨️ Print Struk
        </button>
        <a href="transactions.php" class="btn btn-secondary">
            ← Kembali ke Transaksi
        </a>
    </div>
    
    <script>
        // Auto print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // }
        
        // Close window after print (optional)
        window.onafterprint = function() {
            // window.close();
        }
    </script>
</body>
</html>
