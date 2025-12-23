<<<<<<< HEAD
<?php
require_once '../config.php';
checkRole(['customer']);

$user_id = $_SESSION['user_id'];

// Get statistics
$stats = [
    'total_orders' => $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id=$user_id")->fetch_assoc()['count'],
    'pending_orders' => $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id=$user_id AND status='pending'")->fetch_assoc()['count'],
    'cart_items' => $conn->query("SELECT COUNT(*) as count FROM cart WHERE user_id=$user_id")->fetch_assoc()['count'],
    'total_spent' => $conn->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE user_id=$user_id AND payment_status='paid'")->fetch_assoc()['total']
];

// Recent orders
$recent_orders = $conn->query("SELECT * FROM orders WHERE user_id=$user_id ORDER BY order_date DESC LIMIT 5");

// Get user info
$user_info = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

$page_title = 'Customer Dashboard';
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
            position: sticky;
            top: 0;
            z-index: 100;
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
            gap: 25px;
            align-items: center;
        }
        
        .nav-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: opacity 0.3s;
        }
        
        .nav-link:hover { opacity: 0.8; }
        
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 30px;
        }
        
        .welcome-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }
        
        .welcome-banner h1 {
            font-size: 32px;
            margin-bottom: 10px;
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
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        
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
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #7f8c8d;
        }
        
        .empty-state i {
            font-size: 60px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-store"></i> Minimarket
            </div>
            
            <div class="nav-menu">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="shop.php" class="nav-link">
                    <i class="fas fa-shopping-bag"></i> Belanja
                </a>
                <a href="cart.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i> Keranjang
                </a>
                <a href="orders.php" class="nav-link">
                    <i class="fas fa-box"></i> Pesanan
                </a>
                <a href="profile.php" class="nav-link">
                    <i class="fas fa-user"></i> Profil
                </a>
                <a href="../logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="welcome-banner">
            <h1>Selamat Datang, <?php echo $user_info['full_name']; ?>! 👋</h1>
            <p style="font-size: 18px; opacity: 0.9;">Ayo mulai belanja kebutuhan Anda hari ini</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['total_orders']); ?></div>
                <div class="stat-label">Total Pesanan</div>
            </div>
            
            <div class="stat-card orange">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['pending_orders']); ?></div>
                <div class="stat-label">Pesanan Pending</div>
            </div>
            
            <div class="stat-card green">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['cart_items']); ?></div>
                <div class="stat-label">Item di Keranjang</div>
            </div>
            
            <div class="stat-card purple">
                <div class="stat-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stat-value"><?php echo formatRupiah($stats['total_spent']); ?></div>
                <div class="stat-label">Total Belanja</div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-history"></i> Pesanan Terbaru</h2>
                <a href="orders.php" class="btn btn-primary">
                    <i class="fas fa-eye"></i> Lihat Semua
                </a>
            </div>
            
            <?php if($recent_orders->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Pembayaran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = $recent_orders->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo $order['order_number']; ?></strong></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                        <td><strong><?php echo formatRupiah($order['total_amount']); ?></strong></td>
                        <td>
                            <?php 
                            $status_badges = [
                                'pending' => 'badge-warning',
                                'processing' => 'badge-info',
                                'ready' => 'badge-info',
                                'completed' => 'badge-success',
                                'cancelled' => 'badge-danger'
                            ];
                            $status_text = [
                                'pending' => 'Pending',
                                'processing' => 'Diproses',
                                'ready' => 'Siap Diambil',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan'
                            ];
                            ?>
                            <span class="badge <?php echo $status_badges[$order['status']]; ?>">
                                <?php echo $status_text[$order['status']]; ?>
                            </span>
                        </td>
                        <td>
                            <?php if($order['payment_status'] == 'paid'): ?>
                                <span class="badge badge-success">Lunas</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Belum Bayar</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>Belum ada pesanan</h3>
                <p>Mulai belanja sekarang!</p>
                <a href="shop.php" class="btn btn-primary" style="margin-top: 15px;">
                    <i class="fas fa-shopping-bag"></i> Mulai Belanja
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

=======
<?php
require_once '../config.php';
checkRole(['customer']);

$user_id = $_SESSION['user_id'];

// Get statistics
$stats = [
    'total_orders' => $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id=$user_id")->fetch_assoc()['count'],
    'pending_orders' => $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id=$user_id AND status='pending'")->fetch_assoc()['count'],
    'cart_items' => $conn->query("SELECT COUNT(*) as count FROM cart WHERE user_id=$user_id")->fetch_assoc()['count'],
    'total_spent' => $conn->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE user_id=$user_id AND payment_status='paid'")->fetch_assoc()['total']
];

// Recent orders
$recent_orders = $conn->query("SELECT * FROM orders WHERE user_id=$user_id ORDER BY order_date DESC LIMIT 5");

// Get user info
$user_info = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

$page_title = 'Customer Dashboard';
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
            position: sticky;
            top: 0;
            z-index: 100;
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
            gap: 25px;
            align-items: center;
        }
        
        .nav-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: opacity 0.3s;
        }
        
        .nav-link:hover { opacity: 0.8; }
        
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 30px;
        }
        
        .welcome-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }
        
        .welcome-banner h1 {
            font-size: 32px;
            margin-bottom: 10px;
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
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        
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
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #7f8c8d;
        }
        
        .empty-state i {
            font-size: 60px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-store"></i> Minimarket
            </div>
            
            <div class="nav-menu">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="shop.php" class="nav-link">
                    <i class="fas fa-shopping-bag"></i> Belanja
                </a>
                <a href="cart.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i> Keranjang
                </a>
                <a href="orders.php" class="nav-link">
                    <i class="fas fa-box"></i> Pesanan
                </a>
                <a href="profile.php" class="nav-link">
                    <i class="fas fa-user"></i> Profil
                </a>
                <a href="../logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="welcome-banner">
            <h1>Selamat Datang, <?php echo $user_info['full_name']; ?>! 👋</h1>
            <p style="font-size: 18px; opacity: 0.9;">Ayo mulai belanja kebutuhan Anda hari ini</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['total_orders']); ?></div>
                <div class="stat-label">Total Pesanan</div>
            </div>
            
            <div class="stat-card orange">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['pending_orders']); ?></div>
                <div class="stat-label">Pesanan Pending</div>
            </div>
            
            <div class="stat-card green">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-value"><?php echo number_format($stats['cart_items']); ?></div>
                <div class="stat-label">Item di Keranjang</div>
            </div>
            
            <div class="stat-card purple">
                <div class="stat-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stat-value"><?php echo formatRupiah($stats['total_spent']); ?></div>
                <div class="stat-label">Total Belanja</div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-history"></i> Pesanan Terbaru</h2>
                <a href="orders.php" class="btn btn-primary">
                    <i class="fas fa-eye"></i> Lihat Semua
                </a>
            </div>
            
            <?php if($recent_orders->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Pembayaran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = $recent_orders->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo $order['order_number']; ?></strong></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                        <td><strong><?php echo formatRupiah($order['total_amount']); ?></strong></td>
                        <td>
                            <?php 
                            $status_badges = [
                                'pending' => 'badge-warning',
                                'processing' => 'badge-info',
                                'ready' => 'badge-info',
                                'completed' => 'badge-success',
                                'cancelled' => 'badge-danger'
                            ];
                            $status_text = [
                                'pending' => 'Pending',
                                'processing' => 'Diproses',
                                'ready' => 'Siap Diambil',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan'
                            ];
                            ?>
                            <span class="badge <?php echo $status_badges[$order['status']]; ?>">
                                <?php echo $status_text[$order['status']]; ?>
                            </span>
                        </td>
                        <td>
                            <?php if($order['payment_status'] == 'paid'): ?>
                                <span class="badge badge-success">Lunas</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Belum Bayar</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>Belum ada pesanan</h3>
                <p>Mulai belanja sekarang!</p>
                <a href="shop.php" class="btn btn-primary" style="margin-top: 15px;">
                    <i class="fas fa-shopping-bag"></i> Mulai Belanja
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

>>>>>>> 882da412c224f7c20dfb67829049d92fbad8991f
<?php require_once '../includes/admin_footer.php'; ?>