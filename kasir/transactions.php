<<<<<<< HEAD
<?php
require_once '../config.php';
checkRole(['kasir', 'admin']);

// Handle Delete Transaction
if (isset($_POST['delete_transaction'])) {
    header('Content-Type: application/json');
    $transaction_id = intval($_POST['transaction_id']);
    
    $conn->begin_transaction();
    try {
        // Get transaction details untuk restore stock
        $details = $conn->query("SELECT product_id, quantity FROM transaction_details WHERE transaction_id=$transaction_id");
        
        // Restore stock
        while($detail = $details->fetch_assoc()) {
            $conn->query("UPDATE products SET stock = stock + {$detail['quantity']} WHERE id = {$detail['product_id']}");
        }
        
        // Delete transaction details
        $conn->query("DELETE FROM transaction_details WHERE transaction_id=$transaction_id");
        
        // Delete stock history
        $conn->query("DELETE FROM stock_history WHERE reference IN (SELECT invoice_number FROM transactions WHERE id=$transaction_id)");
        
        // Delete transaction
        $conn->query("DELETE FROM transactions WHERE id=$transaction_id");
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Transaksi berhasil dihapus']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus transaksi: ' . $e->getMessage()]);
    }
    exit;
}

$kasir_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Filter - admin can see all, kasir only their own
if ($role == 'admin') {
    $where = "WHERE 1=1";
} else {
    $where = "WHERE kasir_id=$kasir_id";
}

$date_from = '';
$date_to = '';

if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
    $date_from = $_GET['date_from'];
    $where .= " AND DATE(transaction_date) >= '$date_from'";
}

if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
    $date_to = $_GET['date_to'];
    $where .= " AND DATE(transaction_date) <= '$date_to'";
}

// Get transactions
$transactions = $conn->query("SELECT * FROM transactions $where ORDER BY transaction_date DESC");

// Get summary
$summary = $conn->query("SELECT COUNT(*) as total_trans, COALESCE(SUM(grand_total), 0) as total_amount FROM transactions $where")->fetch_assoc();

$page_title = 'Riwayat Transaksi';
require_once '../includes/admin_header.php';
?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .nav-brand { font-size: 24px; font-weight: bold; }
        .nav-menu { display: flex; gap: 25px; align-items: center; }
        .nav-link { color: white; text-decoration: none; display: flex; align-items: center; gap: 8px; transition: opacity 0.3s; }
        .nav-link:hover { opacity: 0.8; }
        
        .container { max-width: 1400px; margin: 30px auto; padding: 0 30px; }
        
        .page-header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .page-header h1 { font-size: 32px; color: #2c3e50; }
        
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .summary-label {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .summary-value {
            font-size: 32px;
            font-weight: bold;
            color: #27ae60;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f5f7fa;
        }
        
        .filter-form {
            display: flex;
            gap: 15px;
            align-items: end;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .form-group {
            flex: 1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-secondary { background: #95a5a6; color: white; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; padding: 12px; text-align: left; font-weight: 600; color: #2c3e50; }
        td { padding: 12px; border-bottom: 1px solid #f0f0f0; }
        tr:hover { background: #f8f9fa; }
        
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active { display: flex; }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .close-modal { font-size: 24px; cursor: pointer; color: #999; }
        .close-modal:hover { color: #333; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand"><i class="fas fa-cash-register"></i> Kasir System</div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link"><i class="fas fa-home"></i> Dashboard</a>
                <a href="orders.php" class="nav-link"><i class="fas fa-shopping-bag"></i> Order Customer</a>
                <a href="pos.php" class="nav-link"><i class="fas fa-cash-register"></i> POS</a>
                <a href="transactions.php" class="nav-link"><i class="fas fa-history"></i> Transaksi</a>
                <a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-history"></i> Riwayat Transaksi</h1>
            <p style="color: #7f8c8d; margin-top: 5px;">Lihat dan kelola transaksi Anda</p>
        </div>
        
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-label"><i class="fas fa-receipt"></i> Total Transaksi</div>
                <div class="summary-value" style="color: #667eea;"><?php echo number_format($summary['total_trans']); ?></div>
            </div>
            
            <div class="summary-card">
                <div class="summary-label"><i class="fas fa-money-bill-wave"></i> Total Penjualan</div>
                <div class="summary-value"><?php echo formatRupiah($summary['total_amount']); ?></div>
            </div>
        </div>
        
        <div class="card">
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label>Dari Tanggal</label>
                    <input type="date" name="date_from" value="<?php echo $date_from; ?>">
                </div>
                
                <div class="form-group">
                    <label>Sampai Tanggal</label>
                    <input type="date" name="date_to" value="<?php echo $date_to; ?>">
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                
                <a href="transactions.php" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </form>
            
            <table>
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Diskon</th>
                        <th>Grand Total</th>
                        <th>Pembayaran</th>
                        <th>Kembalian</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($trans = $transactions->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo $trans['invoice_number']; ?></strong></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($trans['transaction_date'])); ?></td>
                        <td><?php echo formatRupiah($trans['total_amount']); ?></td>
                        <td><?php echo formatRupiah($trans['discount']); ?></td>
                        <td><strong><?php echo formatRupiah($trans['grand_total']); ?></strong></td>
                        <td>
                            <?php 
                            $payment_methods = [
                                'cod' => 'Cash on Delivery',
                                'transfer' => 'Transfer Bank',
                                'cash' => 'Tunai',
                                'debit' => 'Debit',
                                'credit' => 'Kredit',
                                'ewallet' => 'E-Wallet',
                                'qris' => 'QRIS',
                                'other' => 'Lainnya'
                            ];
                            if (!empty($trans['payment_method']) && isset($payment_methods[$trans['payment_method']])) {
                                echo $payment_methods[$trans['payment_method']];
                            } elseif (!empty($trans['payment_method'])) {
                                echo ucfirst($trans['payment_method']);
                            } else {
                                echo '<span class="text-muted">-</span>';
                            }
                            ?>
                        </td>
                        <td><?php echo formatRupiah($trans['change_amount']); ?></td>
                        <td>
                            <?php if($trans['status'] == 'completed'): ?>
                                <span class="badge badge-success">Selesai</span>
                            <?php elseif($trans['status'] == 'pending'): ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Batal</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-primary" style="padding: 5px 10px; font-size: 12px;" onclick="viewDetail(<?php echo $trans['id']; ?>)">
                                <i class="fas fa-eye"></i> Detail
                            </button>
                            <button class="btn btn-danger" style="padding: 5px 10px; font-size: 12px; margin-left: 5px;" onclick="deleteTransaction(<?php echo $trans['id']; ?>, '<?php echo $trans['invoice_number']; ?>')">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="modal" id="detailModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Detail Transaksi</h2>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            <div id="modalBody"></div>
        </div>
    </div>
    
    <script>
        function viewDetail(transId) {
            fetch('get_transaction_detail.php?id=' + transId)
                .then(res => res.json())
                .then(data => {
                    let html = '<table style="width: 100%;">';
                    html += '<thead><tr><th>Produk</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr></thead>';
                    html += '<tbody>';
                    
                    data.items.forEach(item => {
                        html += '<tr>';
                        html += '<td>' + item.product_name + '</td>';
                        html += '<td>' + item.quantity + '</td>';
                        html += '<td>' + formatRupiah(item.price) + '</td>';
                        html += '<td><strong>' + formatRupiah(item.subtotal) + '</strong></td>';
                        html += '</tr>';
                    });
                    
                    html += '</tbody></table>';
                    html += '<div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #f0f0f0;">';
                    html += '<div style="display: flex; justify-content: space-between; font-size: 18px; font-weight: bold;">';
                    html += '<span>Grand Total:</span>';
                    html += '<span style="color: #27ae60;">' + formatRupiah(data.grand_total) + '</span>';
                    html += '</div></div>';
                    
                    document.getElementById('modalBody').innerHTML = html;
                    document.getElementById('detailModal').classList.add('active');
                });
        }
        
        function closeModal() {
            document.getElementById('detailModal').classList.remove('active');
        }
        
        function deleteTransaction(transId, invoice) {
            if (!confirm('Hapus transaksi ' + invoice + '?\nStok produk akan dikembalikan.')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('delete_transaction', '1');
            formData.append('transaction_id', transId);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Transaksi berhasil dihapus');
                    location.reload();
                } else {
                    alert('Gagal: ' + data.message);
                }
            })
            .catch(err => {
                alert('Error: ' + err.message);
            });
        }
        
        function formatRupiah(amount) {
            return 'Rp ' + parseFloat(amount).toLocaleString('id-ID');
        }
    </script>

=======
<?php
require_once '../config.php';
checkRole(['kasir', 'admin']);

// Handle Delete Transaction
if (isset($_POST['delete_transaction'])) {
    header('Content-Type: application/json');
    $transaction_id = intval($_POST['transaction_id']);
    
    $conn->begin_transaction();
    try {
        // Get transaction details untuk restore stock
        $details = $conn->query("SELECT product_id, quantity FROM transaction_details WHERE transaction_id=$transaction_id");
        
        // Restore stock
        while($detail = $details->fetch_assoc()) {
            $conn->query("UPDATE products SET stock = stock + {$detail['quantity']} WHERE id = {$detail['product_id']}");
        }
        
        // Delete transaction details
        $conn->query("DELETE FROM transaction_details WHERE transaction_id=$transaction_id");
        
        // Delete stock history
        $conn->query("DELETE FROM stock_history WHERE reference IN (SELECT invoice_number FROM transactions WHERE id=$transaction_id)");
        
        // Delete transaction
        $conn->query("DELETE FROM transactions WHERE id=$transaction_id");
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Transaksi berhasil dihapus']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus transaksi: ' . $e->getMessage()]);
    }
    exit;
}

$kasir_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Filter - admin can see all, kasir only their own
if ($role == 'admin') {
    $where = "WHERE 1=1";
} else {
    $where = "WHERE kasir_id=$kasir_id";
}

$date_from = '';
$date_to = '';

if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
    $date_from = $_GET['date_from'];
    $where .= " AND DATE(transaction_date) >= '$date_from'";
}

if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
    $date_to = $_GET['date_to'];
    $where .= " AND DATE(transaction_date) <= '$date_to'";
}

// Get transactions
$transactions = $conn->query("SELECT * FROM transactions $where ORDER BY transaction_date DESC");

// Get summary
$summary = $conn->query("SELECT COUNT(*) as total_trans, COALESCE(SUM(grand_total), 0) as total_amount FROM transactions $where")->fetch_assoc();

$page_title = 'Riwayat Transaksi';
require_once '../includes/admin_header.php';
?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .nav-brand { font-size: 24px; font-weight: bold; }
        .nav-menu { display: flex; gap: 25px; align-items: center; }
        .nav-link { color: white; text-decoration: none; display: flex; align-items: center; gap: 8px; transition: opacity 0.3s; }
        .nav-link:hover { opacity: 0.8; }
        
        .container { max-width: 1400px; margin: 30px auto; padding: 0 30px; }
        
        .page-header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .page-header h1 { font-size: 32px; color: #2c3e50; }
        
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .summary-label {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .summary-value {
            font-size: 32px;
            font-weight: bold;
            color: #27ae60;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f5f7fa;
        }
        
        .filter-form {
            display: flex;
            gap: 15px;
            align-items: end;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .form-group {
            flex: 1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-secondary { background: #95a5a6; color: white; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; padding: 12px; text-align: left; font-weight: 600; color: #2c3e50; }
        td { padding: 12px; border-bottom: 1px solid #f0f0f0; }
        tr:hover { background: #f8f9fa; }
        
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active { display: flex; }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .close-modal { font-size: 24px; cursor: pointer; color: #999; }
        .close-modal:hover { color: #333; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand"><i class="fas fa-cash-register"></i> Kasir System</div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link"><i class="fas fa-home"></i> Dashboard</a>
                <a href="orders.php" class="nav-link"><i class="fas fa-shopping-bag"></i> Order Customer</a>
                <a href="pos.php" class="nav-link"><i class="fas fa-cash-register"></i> POS</a>
                <a href="transactions.php" class="nav-link"><i class="fas fa-history"></i> Transaksi</a>
                <a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-history"></i> Riwayat Transaksi</h1>
            <p style="color: #7f8c8d; margin-top: 5px;">Lihat dan kelola transaksi Anda</p>
        </div>
        
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-label"><i class="fas fa-receipt"></i> Total Transaksi</div>
                <div class="summary-value" style="color: #667eea;"><?php echo number_format($summary['total_trans']); ?></div>
            </div>
            
            <div class="summary-card">
                <div class="summary-label"><i class="fas fa-money-bill-wave"></i> Total Penjualan</div>
                <div class="summary-value"><?php echo formatRupiah($summary['total_amount']); ?></div>
            </div>
        </div>
        
        <div class="card">
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label>Dari Tanggal</label>
                    <input type="date" name="date_from" value="<?php echo $date_from; ?>">
                </div>
                
                <div class="form-group">
                    <label>Sampai Tanggal</label>
                    <input type="date" name="date_to" value="<?php echo $date_to; ?>">
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                
                <a href="transactions.php" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </form>
            
            <table>
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Diskon</th>
                        <th>Grand Total</th>
                        <th>Pembayaran</th>
                        <th>Kembalian</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($trans = $transactions->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo $trans['invoice_number']; ?></strong></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($trans['transaction_date'])); ?></td>
                        <td><?php echo formatRupiah($trans['total_amount']); ?></td>
                        <td><?php echo formatRupiah($trans['discount']); ?></td>
                        <td><strong><?php echo formatRupiah($trans['grand_total']); ?></strong></td>
                        <td>
                            <?php 
                            $payment_methods = [
                                'cod' => 'Cash on Delivery',
                                'transfer' => 'Transfer Bank',
                                'cash' => 'Tunai',
                                'debit' => 'Debit',
                                'credit' => 'Kredit',
                                'ewallet' => 'E-Wallet',
                                'qris' => 'QRIS',
                                'other' => 'Lainnya'
                            ];
                            if (!empty($trans['payment_method']) && isset($payment_methods[$trans['payment_method']])) {
                                echo $payment_methods[$trans['payment_method']];
                            } elseif (!empty($trans['payment_method'])) {
                                echo ucfirst($trans['payment_method']);
                            } else {
                                echo '<span class="text-muted">-</span>';
                            }
                            ?>
                        </td>
                        <td><?php echo formatRupiah($trans['change_amount']); ?></td>
                        <td>
                            <?php if($trans['status'] == 'completed'): ?>
                                <span class="badge badge-success">Selesai</span>
                            <?php elseif($trans['status'] == 'pending'): ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Batal</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-primary" style="padding: 5px 10px; font-size: 12px;" onclick="viewDetail(<?php echo $trans['id']; ?>)">
                                <i class="fas fa-eye"></i> Detail
                            </button>
                            <button class="btn btn-danger" style="padding: 5px 10px; font-size: 12px; margin-left: 5px;" onclick="deleteTransaction(<?php echo $trans['id']; ?>, '<?php echo $trans['invoice_number']; ?>')">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="modal" id="detailModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Detail Transaksi</h2>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            <div id="modalBody"></div>
        </div>
    </div>
    
    <script>
        function viewDetail(transId) {
            fetch('get_transaction_detail.php?id=' + transId)
                .then(res => res.json())
                .then(data => {
                    let html = '<table style="width: 100%;">';
                    html += '<thead><tr><th>Produk</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr></thead>';
                    html += '<tbody>';
                    
                    data.items.forEach(item => {
                        html += '<tr>';
                        html += '<td>' + item.product_name + '</td>';
                        html += '<td>' + item.quantity + '</td>';
                        html += '<td>' + formatRupiah(item.price) + '</td>';
                        html += '<td><strong>' + formatRupiah(item.subtotal) + '</strong></td>';
                        html += '</tr>';
                    });
                    
                    html += '</tbody></table>';
                    html += '<div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #f0f0f0;">';
                    html += '<div style="display: flex; justify-content: space-between; font-size: 18px; font-weight: bold;">';
                    html += '<span>Grand Total:</span>';
                    html += '<span style="color: #27ae60;">' + formatRupiah(data.grand_total) + '</span>';
                    html += '</div></div>';
                    
                    document.getElementById('modalBody').innerHTML = html;
                    document.getElementById('detailModal').classList.add('active');
                });
        }
        
        function closeModal() {
            document.getElementById('detailModal').classList.remove('active');
        }
        
        function deleteTransaction(transId, invoice) {
            if (!confirm('Hapus transaksi ' + invoice + '?\nStok produk akan dikembalikan.')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('delete_transaction', '1');
            formData.append('transaction_id', transId);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Transaksi berhasil dihapus');
                    location.reload();
                } else {
                    alert('Gagal: ' + data.message);
                }
            })
            .catch(err => {
                alert('Error: ' + err.message);
            });
        }
        
        function formatRupiah(amount) {
            return 'Rp ' + parseFloat(amount).toLocaleString('id-ID');
        }
    </script>

>>>>>>> 882da412c224f7c20dfb67829049d92fbad8991f
<?php require_once '../includes/admin_footer.php'; ?>