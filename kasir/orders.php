<<<<<<< HEAD
<?php
require_once '../config.php';
checkRole(['kasir', 'admin']);

$message = '';
$error = '';

// Handle payment confirmation
if (isset($_POST['confirm_payment'])) {
    $order_id = (int)$_POST['order_id'];
    $payment_method = clean_input($_POST['payment_method']);
    
    // Get order details
    $order = $conn->query("SELECT * FROM orders WHERE id=$order_id")->fetch_assoc();
    
    if ($order) {
        // Create invoice number
        $invoice_number = 'INV-' . date('YmdHis') . '-' . rand(100, 999);
        
        // Insert into transactions table
        $kasir_id = $_SESSION['user_id'];
        $total_amount = $order['total_amount'];
        $discount = 0;
        $tax = 0;
        $grand_total = $order['total_amount'];
        $payment_amount = $order['total_amount'];
        $change_amount = 0;
        
        $stmt = $conn->prepare("INSERT INTO transactions (invoice_number, kasir_id, total_amount, discount, tax, grand_total, payment_amount, change_amount, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed')");
        $stmt->bind_param("sidddddds", $invoice_number, $kasir_id, $total_amount, $discount, $tax, $grand_total, $payment_amount, $change_amount, $payment_method);
        
        if ($stmt->execute()) {
            $transaction_id = $conn->insert_id;
            
            // Get order items and insert into transaction_details
            $items = $conn->query("SELECT * FROM order_details WHERE order_id=$order_id");
            while($item = $items->fetch_assoc()) {
                $stmt2 = $conn->prepare("INSERT INTO transaction_details (transaction_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
                $stmt2->bind_param("iiidd", $transaction_id, $item['product_id'], $item['quantity'], $item['price'], $item['subtotal']);
                $stmt2->execute();
            }
            
            // Update order status
            $stmt3 = $conn->prepare("UPDATE orders SET status='completed', payment_status='paid', payment_method=? WHERE id=?");
            $stmt3->bind_param("si", $payment_method, $order_id);
            $stmt3->execute();
            
            // Log activity
            logActivity('confirm_payment', "Konfirmasi pembayaran order #$order_id, buat transaksi $invoice_number");
            $message = "Pembayaran berhasil dikonfirmasi dan transaksi telah dibuat!";
        } else {
            $error = "Gagal membuat transaksi!";
        }
    } else {
        $error = "Order tidak ditemukan!";
    }
}

// Handle order cancellation
if (isset($_POST['cancel_order'])) {
    $order_id = (int)$_POST['order_id'];
    $reason = clean_input($_POST['cancel_reason']);
    
    $stmt = $conn->prepare("UPDATE orders SET status='cancelled', notes=CONCAT(IFNULL(notes, ''), '\nDibatalkan: ', ?) WHERE id=?");
    $stmt->bind_param("si", $reason, $order_id);
    
    if ($stmt->execute()) {
        // Restore stock
        $items = $conn->query("SELECT product_id, quantity FROM order_details WHERE order_id=$order_id");
        while($item = $items->fetch_assoc()) {
            $conn->query("UPDATE products SET stock = stock + {$item['quantity']} WHERE id = {$item['product_id']}");
        }
        
        logActivity('cancel_order', "Batalkan order #$order_id: $reason");
        $message = "Order berhasil dibatalkan!";
    } else {
        $error = "Gagal membatalkan order!";
    }
}

// Get filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$payment_filter = isset($_GET['payment']) ? $_GET['payment'] : '';

// Build query
$where = "1=1";
if (!empty($status_filter)) {
    $where .= " AND o.status = '$status_filter'";
}
if (!empty($payment_filter)) {
    $where .= " AND o.payment_status = '$payment_filter'";
}

// Get orders
$orders = $conn->query("SELECT o.*, u.full_name, u.phone FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE $where ORDER BY o.order_date DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Customer - <?php echo SITE_NAME; ?></title>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
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
        
        .nav-menu {
            display: flex;
            gap: 20px;
        }
        
        .nav-link {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: 0.3s;
        }
        
        .nav-link:hover { background: rgba(255,255,255,0.2); }
        .nav-link.active { background: rgba(255,255,255,0.3); }
        
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 30px;
        }
        
        .header {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .filter-group label {
            font-size: 12px;
            color: #666;
            font-weight: 500;
        }
        
        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .orders-grid {
            display: grid;
            gap: 20px;
        }
        
        .order-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .order-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .order-number {
            font-size: 18px;
            font-weight: bold;
        }
        
        .order-date {
            font-size: 13px;
            opacity: 0.9;
        }
        
        .order-body {
            padding: 20px;
        }
        
        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .info-label {
            font-size: 12px;
            color: #666;
            font-weight: 500;
        }
        
        .info-value {
            font-size: 14px;
            font-weight: 600;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-processing { background: #cce5ff; color: #004085; }
        .badge-completed { background: #d4edda; color: #155724; }
        .badge-cancelled { background: #f8d7da; color: #721c24; }
        .badge-paid { background: #d4edda; color: #155724; }
        .badge-unpaid { background: #fff3cd; color: #856404; }
        
        .order-items {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .item-header {
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }
        
        .order-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-info {
            background: #17a2b8;
            color: white;
        }
        
        .btn-info:hover {
            background: #138496;
        }
        
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
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
        }
        
        .modal-header {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-cash-register"></i> <?php echo SITE_NAME; ?>
            </div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="orders.php" class="nav-link active">
                    <i class="fas fa-shopping-bag"></i> Order Customer
                </a>
                <a href="pos.php" class="nav-link">
                    <i class="fas fa-cash-register"></i> POS
                </a>
                <a href="transactions.php" class="nav-link">
                    <i class="fas fa-receipt"></i> Transaksi
                </a>
                <a href="../logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div>
                <h2><i class="fas fa-shopping-bag"></i> Order Customer</h2>
                <p style="color: #666; margin-top: 5px;">Kelola order dari customer online</p>
            </div>
        </div>

        <?php if($message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <?php if($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="filters">
            <div class="filter-group">
                <label>Status Order</label>
                <select onchange="window.location.href='?status='+this.value+'&payment=<?php echo $payment_filter; ?>'">
                    <option value="">Semua Status</option>
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="processing" <?php echo $status_filter == 'processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Status Pembayaran</label>
                <select onchange="window.location.href='?status=<?php echo $status_filter; ?>&payment='+this.value">
                    <option value="">Semua</option>
                    <option value="unpaid" <?php echo $payment_filter == 'unpaid' ? 'selected' : ''; ?>>Belum Bayar</option>
                    <option value="paid" <?php echo $payment_filter == 'paid' ? 'selected' : ''; ?>>Sudah Bayar</option>
                </select>
            </div>
        </div>

        <!-- Orders Grid -->
        <div class="orders-grid">
            <?php if($orders->num_rows > 0): ?>
                <?php while($order = $orders->fetch_assoc()): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-number">#<?php echo $order['order_number']; ?></div>
                            <div class="order-date"><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></div>
                        </div>
                        <div>
                            <span class="badge badge-<?php echo $order['status']; ?>">
                                <?php echo strtoupper($order['status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="order-body">
                        <div class="order-info">
                            <div class="info-item">
                                <span class="info-label">Customer</span>
                                <span class="info-value"><?php echo $order['full_name']; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Telepon</span>
                                <span class="info-value"><?php echo $order['phone']; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Total</span>
                                <span class="info-value" style="color: #667eea;"><?php echo formatRupiah($order['total_amount']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Pembayaran</span>
                                <span class="badge badge-<?php echo $order['payment_status']; ?>">
                                    <?php echo $order['payment_status'] == 'paid' ? 'LUNAS' : 'BELUM BAYAR'; ?>
                                </span>
                            </div>
                        </div>
                        
                        <?php if($order['shipping_address']): ?>
                        <div class="order-items">
                            <div class="item-header"><i class="fas fa-map-marker-alt"></i> Alamat Pengiriman</div>
                            <p style="margin: 0; color: #666;"><?php echo nl2br($order['shipping_address']); ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="order-actions">
                            <button class="btn btn-info" onclick="viewDetails(<?php echo $order['id']; ?>)">
                                <i class="fas fa-eye"></i> Detail
                            </button>
                            
                            <?php if($order['payment_status'] == 'unpaid' && $order['status'] != 'cancelled'): ?>
                            <button class="btn btn-success" onclick="showConfirmModal(<?php echo $order['id']; ?>, '<?php echo $order['order_number']; ?>')">
                                <i class="fas fa-check-circle"></i> Konfirmasi Bayar
                            </button>
                            
                            <button class="btn btn-danger" onclick="showCancelModal(<?php echo $order['id']; ?>, '<?php echo $order['order_number']; ?>')">
                                <i class="fas fa-times-circle"></i> Batalkan
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="order-card">
                    <div class="order-body" style="text-align: center; padding: 40px;">
                        <i class="fas fa-inbox" style="font-size: 48px; color: #ddd; margin-bottom: 15px;"></i>
                        <p style="color: #666;">Tidak ada order</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Confirm Payment Modal -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fas fa-check-circle"></i> Konfirmasi Pembayaran
            </div>
            <form method="POST">
                <input type="hidden" name="order_id" id="confirm_order_id">
                <p style="margin-bottom: 20px;">Konfirmasi pembayaran untuk order <strong id="confirm_order_number"></strong>?</p>
                
                <div class="form-group">
                    <label>Metode Pembayaran</label>
                    <select name="payment_method" required>
                        <option value="cod">Cash on Delivery (COD)</option>
                        <option value="transfer">Transfer Bank</option>
                        <option value="ewallet">E-Wallet</option>
                        <option value="qris">QRIS</option>
                        <option value="cash">Tunai</option>
                        <option value="debit">Debit</option>
                        <option value="credit">Kredit</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-danger" onclick="closeModal('confirmModal')">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" name="confirm_payment" class="btn btn-success">
                        <i class="fas fa-check"></i> Konfirmasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cancel Order Modal -->
    <div id="cancelModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fas fa-times-circle"></i> Batalkan Order
            </div>
            <form method="POST">
                <input type="hidden" name="order_id" id="cancel_order_id">
                <p style="margin-bottom: 20px;">Batalkan order <strong id="cancel_order_number"></strong>?</p>
                
                <div class="form-group">
                    <label>Alasan Pembatalan</label>
                    <textarea name="cancel_reason" rows="4" placeholder="Masukkan alasan pembatalan..." required></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-info" onclick="closeModal('cancelModal')">
                        <i class="fas fa-times"></i> Tutup
                    </button>
                    <button type="submit" name="cancel_order" class="btn btn-danger">
                        <i class="fas fa-check"></i> Ya, Batalkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="detailsModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <i class="fas fa-receipt"></i> Detail Order
            </div>
            <div id="detailsContent">
                <div style="text-align: center; padding: 40px;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 32px; color: #667eea;"></i>
                    <p style="margin-top: 15px;">Loading...</p>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-info" onclick="closeModal('detailsModal')">
                    <i class="fas fa-times"></i> Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        function showConfirmModal(orderId, orderNumber) {
            document.getElementById('confirm_order_id').value = orderId;
            document.getElementById('confirm_order_number').textContent = orderNumber;
            document.getElementById('confirmModal').classList.add('active');
        }

        function showCancelModal(orderId, orderNumber) {
            document.getElementById('cancel_order_id').value = orderId;
            document.getElementById('cancel_order_number').textContent = orderNumber;
            document.getElementById('cancelModal').classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        function viewDetails(orderId) {
            document.getElementById('detailsModal').classList.add('active');
            
            fetch('../customer/get_order_details.php?id=' + orderId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = '<div class="order-items">';
                        html += '<div class="item-header">Item Pesanan</div>';
                        
                        data.items.forEach(item => {
                            html += `
                                <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee;">
                                    <div>
                                        <strong>${item.product_name}</strong><br>
                                        <span style="font-size: 12px; color: #666;">${item.quantity} x ${formatRupiah(item.price)}</span>
                                    </div>
                                    <strong>${formatRupiah(item.subtotal)}</strong>
                                </div>
                            `;
                        });
                        
                        html += `
                            <div style="display: flex; justify-content: space-between; padding: 15px 0; margin-top: 10px; border-top: 2px solid #ddd;">
                                <strong>TOTAL</strong>
                                <strong style="color: #667eea; font-size: 18px;">${formatRupiah(data.order.total_amount)}</strong>
                            </div>
                        `;
                        html += '</div>';
                        
                        document.getElementById('detailsContent').innerHTML = html;
                    } else {
                        document.getElementById('detailsContent').innerHTML = '<p style="text-align: center; color: red;">Gagal memuat detail order</p>';
                    }
                })
                .catch(error => {
                    document.getElementById('detailsContent').innerHTML = '<p style="text-align: center; color: red;">Terjadi kesalahan</p>';
                });
        }

        function formatRupiah(amount) {
            return 'Rp ' + parseInt(amount).toLocaleString('id-ID');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        }
    </script>
</body>
</html>
=======
<?php
require_once '../config.php';
checkRole(['kasir', 'admin']);

$message = '';
$error = '';

// Handle payment confirmation
if (isset($_POST['confirm_payment'])) {
    $order_id = (int)$_POST['order_id'];
    $payment_method = clean_input($_POST['payment_method']);
    
    // Get order details
    $order = $conn->query("SELECT * FROM orders WHERE id=$order_id")->fetch_assoc();
    
    if ($order) {
        // Create invoice number
        $invoice_number = 'INV-' . date('YmdHis') . '-' . rand(100, 999);
        
        // Insert into transactions table
        $kasir_id = $_SESSION['user_id'];
        $total_amount = $order['total_amount'];
        $discount = 0;
        $tax = 0;
        $grand_total = $order['total_amount'];
        $payment_amount = $order['total_amount'];
        $change_amount = 0;
        
        $stmt = $conn->prepare("INSERT INTO transactions (invoice_number, kasir_id, total_amount, discount, tax, grand_total, payment_amount, change_amount, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed')");
        $stmt->bind_param("sidddddds", $invoice_number, $kasir_id, $total_amount, $discount, $tax, $grand_total, $payment_amount, $change_amount, $payment_method);
        
        if ($stmt->execute()) {
            $transaction_id = $conn->insert_id;
            
            // Get order items and insert into transaction_details
            $items = $conn->query("SELECT * FROM order_details WHERE order_id=$order_id");
            while($item = $items->fetch_assoc()) {
                $stmt2 = $conn->prepare("INSERT INTO transaction_details (transaction_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
                $stmt2->bind_param("iiidd", $transaction_id, $item['product_id'], $item['quantity'], $item['price'], $item['subtotal']);
                $stmt2->execute();
            }
            
            // Update order status
            $stmt3 = $conn->prepare("UPDATE orders SET status='completed', payment_status='paid', payment_method=? WHERE id=?");
            $stmt3->bind_param("si", $payment_method, $order_id);
            $stmt3->execute();
            
            // Log activity
            logActivity('confirm_payment', "Konfirmasi pembayaran order #$order_id, buat transaksi $invoice_number");
            $message = "Pembayaran berhasil dikonfirmasi dan transaksi telah dibuat!";
        } else {
            $error = "Gagal membuat transaksi!";
        }
    } else {
        $error = "Order tidak ditemukan!";
    }
}

// Handle order cancellation
if (isset($_POST['cancel_order'])) {
    $order_id = (int)$_POST['order_id'];
    $reason = clean_input($_POST['cancel_reason']);
    
    $stmt = $conn->prepare("UPDATE orders SET status='cancelled', notes=CONCAT(IFNULL(notes, ''), '\nDibatalkan: ', ?) WHERE id=?");
    $stmt->bind_param("si", $reason, $order_id);
    
    if ($stmt->execute()) {
        // Restore stock
        $items = $conn->query("SELECT product_id, quantity FROM order_details WHERE order_id=$order_id");
        while($item = $items->fetch_assoc()) {
            $conn->query("UPDATE products SET stock = stock + {$item['quantity']} WHERE id = {$item['product_id']}");
        }
        
        logActivity('cancel_order', "Batalkan order #$order_id: $reason");
        $message = "Order berhasil dibatalkan!";
    } else {
        $error = "Gagal membatalkan order!";
    }
}

// Get filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$payment_filter = isset($_GET['payment']) ? $_GET['payment'] : '';

// Build query
$where = "1=1";
if (!empty($status_filter)) {
    $where .= " AND o.status = '$status_filter'";
}
if (!empty($payment_filter)) {
    $where .= " AND o.payment_status = '$payment_filter'";
}

// Get orders
$orders = $conn->query("SELECT o.*, u.full_name, u.phone FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE $where ORDER BY o.order_date DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Customer - <?php echo SITE_NAME; ?></title>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
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
        
        .nav-menu {
            display: flex;
            gap: 20px;
        }
        
        .nav-link {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: 0.3s;
        }
        
        .nav-link:hover { background: rgba(255,255,255,0.2); }
        .nav-link.active { background: rgba(255,255,255,0.3); }
        
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 30px;
        }
        
        .header {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .filter-group label {
            font-size: 12px;
            color: #666;
            font-weight: 500;
        }
        
        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .orders-grid {
            display: grid;
            gap: 20px;
        }
        
        .order-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .order-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .order-number {
            font-size: 18px;
            font-weight: bold;
        }
        
        .order-date {
            font-size: 13px;
            opacity: 0.9;
        }
        
        .order-body {
            padding: 20px;
        }
        
        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .info-label {
            font-size: 12px;
            color: #666;
            font-weight: 500;
        }
        
        .info-value {
            font-size: 14px;
            font-weight: 600;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-processing { background: #cce5ff; color: #004085; }
        .badge-completed { background: #d4edda; color: #155724; }
        .badge-cancelled { background: #f8d7da; color: #721c24; }
        .badge-paid { background: #d4edda; color: #155724; }
        .badge-unpaid { background: #fff3cd; color: #856404; }
        
        .order-items {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .item-header {
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }
        
        .order-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-info {
            background: #17a2b8;
            color: white;
        }
        
        .btn-info:hover {
            background: #138496;
        }
        
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
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
        }
        
        .modal-header {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-cash-register"></i> <?php echo SITE_NAME; ?>
            </div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="orders.php" class="nav-link active">
                    <i class="fas fa-shopping-bag"></i> Order Customer
                </a>
                <a href="pos.php" class="nav-link">
                    <i class="fas fa-cash-register"></i> POS
                </a>
                <a href="transactions.php" class="nav-link">
                    <i class="fas fa-receipt"></i> Transaksi
                </a>
                <a href="../logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div>
                <h2><i class="fas fa-shopping-bag"></i> Order Customer</h2>
                <p style="color: #666; margin-top: 5px;">Kelola order dari customer online</p>
            </div>
        </div>

        <?php if($message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <?php if($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="filters">
            <div class="filter-group">
                <label>Status Order</label>
                <select onchange="window.location.href='?status='+this.value+'&payment=<?php echo $payment_filter; ?>'">
                    <option value="">Semua Status</option>
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="processing" <?php echo $status_filter == 'processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Status Pembayaran</label>
                <select onchange="window.location.href='?status=<?php echo $status_filter; ?>&payment='+this.value">
                    <option value="">Semua</option>
                    <option value="unpaid" <?php echo $payment_filter == 'unpaid' ? 'selected' : ''; ?>>Belum Bayar</option>
                    <option value="paid" <?php echo $payment_filter == 'paid' ? 'selected' : ''; ?>>Sudah Bayar</option>
                </select>
            </div>
        </div>

        <!-- Orders Grid -->
        <div class="orders-grid">
            <?php if($orders->num_rows > 0): ?>
                <?php while($order = $orders->fetch_assoc()): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-number">#<?php echo $order['order_number']; ?></div>
                            <div class="order-date"><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></div>
                        </div>
                        <div>
                            <span class="badge badge-<?php echo $order['status']; ?>">
                                <?php echo strtoupper($order['status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="order-body">
                        <div class="order-info">
                            <div class="info-item">
                                <span class="info-label">Customer</span>
                                <span class="info-value"><?php echo $order['full_name']; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Telepon</span>
                                <span class="info-value"><?php echo $order['phone']; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Total</span>
                                <span class="info-value" style="color: #667eea;"><?php echo formatRupiah($order['total_amount']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Pembayaran</span>
                                <span class="badge badge-<?php echo $order['payment_status']; ?>">
                                    <?php echo $order['payment_status'] == 'paid' ? 'LUNAS' : 'BELUM BAYAR'; ?>
                                </span>
                            </div>
                        </div>
                        
                        <?php if($order['shipping_address']): ?>
                        <div class="order-items">
                            <div class="item-header"><i class="fas fa-map-marker-alt"></i> Alamat Pengiriman</div>
                            <p style="margin: 0; color: #666;"><?php echo nl2br($order['shipping_address']); ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="order-actions">
                            <button class="btn btn-info" onclick="viewDetails(<?php echo $order['id']; ?>)">
                                <i class="fas fa-eye"></i> Detail
                            </button>
                            
                            <?php if($order['payment_status'] == 'unpaid' && $order['status'] != 'cancelled'): ?>
                            <button class="btn btn-success" onclick="showConfirmModal(<?php echo $order['id']; ?>, '<?php echo $order['order_number']; ?>')">
                                <i class="fas fa-check-circle"></i> Konfirmasi Bayar
                            </button>
                            
                            <button class="btn btn-danger" onclick="showCancelModal(<?php echo $order['id']; ?>, '<?php echo $order['order_number']; ?>')">
                                <i class="fas fa-times-circle"></i> Batalkan
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="order-card">
                    <div class="order-body" style="text-align: center; padding: 40px;">
                        <i class="fas fa-inbox" style="font-size: 48px; color: #ddd; margin-bottom: 15px;"></i>
                        <p style="color: #666;">Tidak ada order</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Confirm Payment Modal -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fas fa-check-circle"></i> Konfirmasi Pembayaran
            </div>
            <form method="POST">
                <input type="hidden" name="order_id" id="confirm_order_id">
                <p style="margin-bottom: 20px;">Konfirmasi pembayaran untuk order <strong id="confirm_order_number"></strong>?</p>
                
                <div class="form-group">
                    <label>Metode Pembayaran</label>
                    <select name="payment_method" required>
                        <option value="cod">Cash on Delivery (COD)</option>
                        <option value="transfer">Transfer Bank</option>
                        <option value="ewallet">E-Wallet</option>
                        <option value="qris">QRIS</option>
                        <option value="cash">Tunai</option>
                        <option value="debit">Debit</option>
                        <option value="credit">Kredit</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-danger" onclick="closeModal('confirmModal')">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" name="confirm_payment" class="btn btn-success">
                        <i class="fas fa-check"></i> Konfirmasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cancel Order Modal -->
    <div id="cancelModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fas fa-times-circle"></i> Batalkan Order
            </div>
            <form method="POST">
                <input type="hidden" name="order_id" id="cancel_order_id">
                <p style="margin-bottom: 20px;">Batalkan order <strong id="cancel_order_number"></strong>?</p>
                
                <div class="form-group">
                    <label>Alasan Pembatalan</label>
                    <textarea name="cancel_reason" rows="4" placeholder="Masukkan alasan pembatalan..." required></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-info" onclick="closeModal('cancelModal')">
                        <i class="fas fa-times"></i> Tutup
                    </button>
                    <button type="submit" name="cancel_order" class="btn btn-danger">
                        <i class="fas fa-check"></i> Ya, Batalkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="detailsModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <i class="fas fa-receipt"></i> Detail Order
            </div>
            <div id="detailsContent">
                <div style="text-align: center; padding: 40px;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 32px; color: #667eea;"></i>
                    <p style="margin-top: 15px;">Loading...</p>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-info" onclick="closeModal('detailsModal')">
                    <i class="fas fa-times"></i> Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        function showConfirmModal(orderId, orderNumber) {
            document.getElementById('confirm_order_id').value = orderId;
            document.getElementById('confirm_order_number').textContent = orderNumber;
            document.getElementById('confirmModal').classList.add('active');
        }

        function showCancelModal(orderId, orderNumber) {
            document.getElementById('cancel_order_id').value = orderId;
            document.getElementById('cancel_order_number').textContent = orderNumber;
            document.getElementById('cancelModal').classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        function viewDetails(orderId) {
            document.getElementById('detailsModal').classList.add('active');
            
            fetch('../customer/get_order_details.php?id=' + orderId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = '<div class="order-items">';
                        html += '<div class="item-header">Item Pesanan</div>';
                        
                        data.items.forEach(item => {
                            html += `
                                <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee;">
                                    <div>
                                        <strong>${item.product_name}</strong><br>
                                        <span style="font-size: 12px; color: #666;">${item.quantity} x ${formatRupiah(item.price)}</span>
                                    </div>
                                    <strong>${formatRupiah(item.subtotal)}</strong>
                                </div>
                            `;
                        });
                        
                        html += `
                            <div style="display: flex; justify-content: space-between; padding: 15px 0; margin-top: 10px; border-top: 2px solid #ddd;">
                                <strong>TOTAL</strong>
                                <strong style="color: #667eea; font-size: 18px;">${formatRupiah(data.order.total_amount)}</strong>
                            </div>
                        `;
                        html += '</div>';
                        
                        document.getElementById('detailsContent').innerHTML = html;
                    } else {
                        document.getElementById('detailsContent').innerHTML = '<p style="text-align: center; color: red;">Gagal memuat detail order</p>';
                    }
                })
                .catch(error => {
                    document.getElementById('detailsContent').innerHTML = '<p style="text-align: center; color: red;">Terjadi kesalahan</p>';
                });
        }

        function formatRupiah(amount) {
            return 'Rp ' + parseInt(amount).toLocaleString('id-ID');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        }
    </script>
</body>
</html>
>>>>>>> 882da412c224f7c20dfb67829049d92fbad8991f
