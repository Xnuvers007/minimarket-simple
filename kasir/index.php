<<<<<<< HEAD
<?php
require_once '../config.php';
checkRole(['kasir', 'admin']);

$kasir_id = $_SESSION['user_id'];

// Get statistics
$stats = [
    'today_transactions' => $conn->query("SELECT COUNT(*) as count FROM transactions WHERE kasir_id=$kasir_id AND DATE(transaction_date) = CURDATE()")->fetch_assoc()['count'],
    'today_revenue' => $conn->query("SELECT COALESCE(SUM(grand_total), 0) as total FROM transactions WHERE kasir_id=$kasir_id AND DATE(transaction_date) = CURDATE()")->fetch_assoc()['total'],
    'today_items_sold' => $conn->query("SELECT COALESCE(SUM(td.quantity), 0) as total FROM transaction_details td JOIN transactions t ON td.transaction_id = t.id WHERE t.kasir_id=$kasir_id AND DATE(t.transaction_date) = CURDATE()")->fetch_assoc()['total'],
    'avg_transaction' => $conn->query("SELECT COALESCE(AVG(grand_total), 0) as avg FROM transactions WHERE kasir_id=$kasir_id AND DATE(transaction_date) = CURDATE()")->fetch_assoc()['avg']
];

// Recent transactions
$recent_transactions = $conn->query("SELECT * FROM transactions WHERE kasir_id=$kasir_id ORDER BY transaction_date DESC LIMIT 10");

$page_title = 'Dashboard Kasir';
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
        
        .welcome-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }
        
        .welcome-banner h1 { font-size: 32px; margin-bottom: 10px; }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .quick-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .quick-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 15px;
        }
        
        .stat-card.blue .stat-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .stat-card.green .stat-icon { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; }
        .stat-card.orange .stat-icon { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
        .stat-card.purple .stat-icon { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333; }
        
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 14px;
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
        
        .card-title {
            font-size: 20px;
            font-weight: 600;
            color: #2c3e50;
        }
        
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
        <div class="welcome-banner">
            <h1>Selamat Datang, <?php echo $_SESSION['full_name']; ?>! 👋</h1>
            <p style="font-size: 18px; opacity: 0.9;">Ringkasan penjualan Anda hari ini</p>
            
            <div class="quick-actions">
                <a href="pos.php" class="quick-btn">
                    <i class="fas fa-plus-circle" style="font-size: 24px;"></i>
                    <span>Transaksi Baru</span>
                </a>
                <a href="transactions.php" class="quick-btn">
                    <i class="fas fa-list" style="font-size: 24px;"></i>
                    <span>Riwayat Transaksi</span>
                </a>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['today_transactions']); ?></div>
                <div class="stat-label">Transaksi Hari Ini</div>
            </div>
            
            <div class="stat-card green">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-value"><?php echo formatRupiah($stats['today_revenue']); ?></div>
                <div class="stat-label">Total Penjualan</div>
            </div>
            
            <div class="stat-card orange">
                <div class="stat-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['today_items_sold']); ?></div>
                <div class="stat-label">Item Terjual</div>
            </div>
            
            <div class="stat-card purple">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-value"><?php echo formatRupiah($stats['avg_transaction']); ?></div>
                <div class="stat-label">Rata-rata Transaksi</div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-history"></i> Transaksi Terbaru</h2>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($trans = $recent_transactions->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo $trans['invoice_number']; ?></strong></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($trans['transaction_date'])); ?></td>
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
                        <td>
                            <?php if($trans['status'] == 'completed'): ?>
                                <span class="badge badge-success">Selesai</span>
                            <?php elseif($trans['status'] == 'pending'): ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Batal</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

=======
<?php
require_once '../config.php';
checkRole(['kasir', 'admin']);

$kasir_id = $_SESSION['user_id'];

// Get statistics
$stats = [
    'today_transactions' => $conn->query("SELECT COUNT(*) as count FROM transactions WHERE kasir_id=$kasir_id AND DATE(transaction_date) = CURDATE()")->fetch_assoc()['count'],
    'today_revenue' => $conn->query("SELECT COALESCE(SUM(grand_total), 0) as total FROM transactions WHERE kasir_id=$kasir_id AND DATE(transaction_date) = CURDATE()")->fetch_assoc()['total'],
    'today_items_sold' => $conn->query("SELECT COALESCE(SUM(td.quantity), 0) as total FROM transaction_details td JOIN transactions t ON td.transaction_id = t.id WHERE t.kasir_id=$kasir_id AND DATE(t.transaction_date) = CURDATE()")->fetch_assoc()['total'],
    'avg_transaction' => $conn->query("SELECT COALESCE(AVG(grand_total), 0) as avg FROM transactions WHERE kasir_id=$kasir_id AND DATE(transaction_date) = CURDATE()")->fetch_assoc()['avg']
];

// Recent transactions
$recent_transactions = $conn->query("SELECT * FROM transactions WHERE kasir_id=$kasir_id ORDER BY transaction_date DESC LIMIT 10");

$page_title = 'Dashboard Kasir';
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
        
        .welcome-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }
        
        .welcome-banner h1 { font-size: 32px; margin-bottom: 10px; }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .quick-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .quick-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 15px;
        }
        
        .stat-card.blue .stat-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .stat-card.green .stat-icon { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; }
        .stat-card.orange .stat-icon { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
        .stat-card.purple .stat-icon { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333; }
        
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 14px;
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
        
        .card-title {
            font-size: 20px;
            font-weight: 600;
            color: #2c3e50;
        }
        
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
        <div class="welcome-banner">
            <h1>Selamat Datang, <?php echo $_SESSION['full_name']; ?>! 👋</h1>
            <p style="font-size: 18px; opacity: 0.9;">Ringkasan penjualan Anda hari ini</p>
            
            <div class="quick-actions">
                <a href="pos.php" class="quick-btn">
                    <i class="fas fa-plus-circle" style="font-size: 24px;"></i>
                    <span>Transaksi Baru</span>
                </a>
                <a href="transactions.php" class="quick-btn">
                    <i class="fas fa-list" style="font-size: 24px;"></i>
                    <span>Riwayat Transaksi</span>
                </a>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['today_transactions']); ?></div>
                <div class="stat-label">Transaksi Hari Ini</div>
            </div>
            
            <div class="stat-card green">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-value"><?php echo formatRupiah($stats['today_revenue']); ?></div>
                <div class="stat-label">Total Penjualan</div>
            </div>
            
            <div class="stat-card orange">
                <div class="stat-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['today_items_sold']); ?></div>
                <div class="stat-label">Item Terjual</div>
            </div>
            
            <div class="stat-card purple">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-value"><?php echo formatRupiah($stats['avg_transaction']); ?></div>
                <div class="stat-label">Rata-rata Transaksi</div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-history"></i> Transaksi Terbaru</h2>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($trans = $recent_transactions->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo $trans['invoice_number']; ?></strong></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($trans['transaction_date'])); ?></td>
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
                        <td>
                            <?php if($trans['status'] == 'completed'): ?>
                                <span class="badge badge-success">Selesai</span>
                            <?php elseif($trans['status'] == 'pending'): ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Batal</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

>>>>>>> 882da412c224f7c20dfb67829049d92fbad8991f
<?php require_once '../includes/admin_footer.php'; ?>