<?php
require_once '../config.php';
checkRole(['customer']);

$user_id = $_SESSION['user_id'];

// Get orders
$orders = $conn->query("SELECT * FROM orders WHERE user_id=$user_id ORDER BY order_date DESC");

$page_title = 'Pesanan Saya';
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
        
        .container { max-width: 1200px; margin: 30px auto; padding: 0 30px; }
        
        .page-header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .page-header h1 { font-size: 32px; color: #2c3e50; }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        
        .order-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        
        .order-card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .order-number {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .order-date {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .order-badges {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }
        
        .badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-secondary { background: #e2e3e5; color: #383d41; }
        
        .order-body {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 20px;
            align-items: end;
        }
        
        .order-info {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            color: #7f8c8d;
            font-size: 13px;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .order-total {
            font-size: 28px;
            font-weight: bold;
            color: #27ae60;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-info { background: #3498db; color: white; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        
        .empty-state {
            background: white;
            border-radius: 10px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .empty-state i { font-size: 80px; color: #ddd; margin-bottom: 20px; }
        .empty-state h2 { color: #666; margin-bottom: 10px; }
        
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
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        @media (max-width: 768px) {
            .order-info { grid-template-columns: 1fr; }
            .order-body { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand"><i class="fas fa-store"></i> Minimarket</div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link"><i class="fas fa-home"></i> Dashboard</a>
                <a href="shop.php" class="nav-link"><i class="fas fa-shopping-bag"></i> Belanja</a>
                <a href="cart.php" class="nav-link"><i class="fas fa-shopping-cart"></i> Keranjang</a>
                <a href="orders.php" class="nav-link"><i class="fas fa-box"></i> Pesanan</a>
                <a href="profile.php" class="nav-link"><i class="fas fa-user"></i> Profil</a>
                <a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Pesanan berhasil dibuat! Silakan lakukan pembayaran.
            </div>
        <?php endif; ?>
        
        <div class="page-header">
            <h1><i class="fas fa-box"></i> Pesanan Saya</h1>
            <p style="color: #7f8c8d; margin-top: 5px;">Riwayat dan status pesanan Anda</p>
        </div>
        
        <?php if($orders->num_rows > 0): ?>
            <?php while($order = $orders->fetch_assoc()): 
                $status_badges = [
                    'pending' => ['class' => 'badge-warning', 'text' => 'Menunggu Pembayaran'],
                    'processing' => ['class' => 'badge-info', 'text' => 'Diproses'],
                    'ready' => ['class' => 'badge-info', 'text' => 'Siap Diambil'],
                    'completed' => ['class' => 'badge-success', 'text' => 'Selesai'],
                    'cancelled' => ['class' => 'badge-danger', 'text' => 'Dibatalkan']
                ];
                
                $payment_badges = [
                    'unpaid' => ['class' => 'badge-warning', 'text' => 'Belum Dibayar'],
                    'paid' => ['class' => 'badge-success', 'text' => 'Sudah Dibayar']
                ];
            ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <div class="order-number"><?php echo $order['order_number']; ?></div>
                        <div class="order-date">
                            <i class="fas fa-calendar"></i> <?php echo date('d F Y, H:i', strtotime($order['order_date'])); ?>
                        </div>
                    </div>
                    <div class="order-badges">
                        <span class="badge <?php echo $status_badges[$order['status']]['class']; ?>">
                            <?php echo $status_badges[$order['status']]['text']; ?>
                        </span>
                        <span class="badge <?php echo $payment_badges[$order['payment_status']]['class']; ?>">
                            <?php echo $payment_badges[$order['payment_status']]['text']; ?>
                        </span>
                    </div>
                </div>
                
                <div class="order-body">
                    <div class="order-info">
                        <div class="info-item">
                            <div class="info-label">Alamat Pengiriman</div>
                            <div class="info-value"><?php echo substr($order['shipping_address'], 0, 50); ?>...</div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Catatan</div>
                            <div class="info-value"><?php echo $order['notes'] ?: '-'; ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Total Pembayaran</div>
                            <div class="order-total"><?php echo formatRupiah($order['total_amount']); ?></div>
                        </div>
                    </div>
                    
                    <button class="btn btn-info" onclick="viewDetails(<?php echo $order['id']; ?>)">
                        <i class="fas fa-eye"></i> Detail
                    </button>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h2>Belum Ada Pesanan</h2>
            <p style="color: #999; margin-bottom: 20px;">Anda belum melakukan pemesanan. Yuk mulai belanja!</p>
            <a href="shop.php" class="btn btn-primary" style="font-size: 18px; padding: 15px 30px;">
                <i class="fas fa-shopping-bag"></i> Mulai Belanja
            </a>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="modal" id="detailModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Detail Pesanan</h2>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            <div id="modalBody"></div>
        </div>
    </div>
    
    <script>
        function viewDetails(orderId) {
            fetch('get_order_details.php?order_id=' + orderId)
                .then(response => response.json())
                .then(data => {
                    let html = '<div style="max-height: 400px; overflow-y: auto;">';
                    
                    data.items.forEach(item => {
                        html += `
                            <div class="detail-item">
                                <div>
                                    <strong>${item.product_name}</strong><br>
                                    <span style="color: #7f8c8d; font-size: 14px;">
                                        ${item.quantity} x ${formatRupiah(item.price)}
                                    </span>
                                </div>
                                <strong style="color: #27ae60;">${formatRupiah(item.subtotal)}</strong>
                            </div>
                        `;
                    });
                    
                    html += '</div>';
                    html += '<div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #f0f0f0;">';
                    html += '<div style="display: flex; justify-content: space-between; font-size: 20px; font-weight: bold;">';
                    html += '<span>Total:</span>';
                    const total = data.total || data.order.total_amount || data.items.reduce((sum, item) => sum + parseFloat(item.subtotal || 0), 0);
                    html += `<span style="color: #27ae60;">${formatRupiah(total)}</span>`;
                    html += '</div></div>';
                    
                    document.getElementById('modalBody').innerHTML = html;
                    document.getElementById('detailModal').classList.add('active');
                });
        }
        
        function closeModal() {
            document.getElementById('detailModal').classList.remove('active');
        }
        
        function formatRupiah(amount) {
            return 'Rp ' + parseFloat(amount).toLocaleString('id-ID');
        }
    </script>

<?php require_once '../includes/admin_footer.php'; ?>