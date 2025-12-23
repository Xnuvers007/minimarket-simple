<<<<<<< HEAD
<?php
require_once '../config.php';
checkRole(['admin']);

// Get Statistics
$stats = [
    'total_products' => $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'],
    'total_users' => $conn->query("SELECT COUNT(*) as count FROM users WHERE role='customer'")->fetch_assoc()['count'],
    'total_transactions' => $conn->query("SELECT COUNT(*) as count FROM transactions WHERE DATE(transaction_date) = CURDATE()")->fetch_assoc()['count'],
    'today_revenue' => $conn->query("SELECT COALESCE(SUM(grand_total), 0) as total FROM transactions WHERE DATE(transaction_date) = CURDATE()")->fetch_assoc()['total'],
    'low_stock' => $conn->query("SELECT COUNT(*) as count FROM products WHERE stock <= min_stock")->fetch_assoc()['count']
];

// Recent Transactions
$recent_transactions = $conn->query("SELECT t.*, u.full_name FROM transactions t LEFT JOIN users u ON t.kasir_id = u.id ORDER BY transaction_date DESC LIMIT 10");

// Top Products
$top_products = $conn->query("SELECT p.product_name, SUM(td.quantity) as total_sold, SUM(td.subtotal) as revenue FROM transaction_details td JOIN products p ON td.product_id = p.id JOIN transactions t ON td.transaction_id = t.id WHERE DATE(t.transaction_date) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY p.id ORDER BY total_sold DESC LIMIT 5");

// Monthly Revenue
$monthly_revenue = $conn->query("SELECT DATE_FORMAT(transaction_date, '%Y-%m') as month, SUM(grand_total) as revenue FROM transactions WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY month ORDER BY month");

$page_title = 'Admin Dashboard';
$use_charts = true;
require_once '../includes/admin_header.php';
?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }
        
        .wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s;
        }
        
        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h2 {
            font-size: 24px;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            padding: 15px 25px;
            display: flex;
            align-items: center;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .menu-item:hover, .menu-item.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left: 4px solid #3498db;
        }
        
        .menu-item i {
            margin-right: 15px;
            font-size: 18px;
            width: 25px;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 30px;
        }
        
        .top-bar {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .welcome h1 {
            font-size: 28px;
            color: #2c3e50;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .logout-btn {
            padding: 10px 20px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
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
        .stat-card.red .stat-icon { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; }
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
        
        /* Cards */
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
        
        /* Table */
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
        
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        
        /* Chart */
        .chart-container {
            height: 300px;
            position: relative;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -260px;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-store" style="font-size: 40px; margin-bottom: 10px;"></i>
                <h2>Admin Panel</h2>
            </div>
            
            <div class="sidebar-menu">
                <a href="index.php" class="menu-item active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="products.php" class="menu-item">
                    <i class="fas fa-box"></i>
                    <span>Produk</span>
                </a>
                <a href="categories.php" class="menu-item">
                    <i class="fas fa-tags"></i>
                    <span>Kategori</span>
                </a>
                <a href="suppliers.php" class="menu-item">
                    <i class="fas fa-truck"></i>
                    <span>Supplier</span>
                </a>
                <a href="stock.php" class="menu-item">
                    <i class="fas fa-warehouse"></i>
                    <span>Stok Barang</span>
                </a>
                <a href="users.php" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span>Pengguna</span>
                </a>
                <a href="reports.php" class="menu-item">
                    <i class="fas fa-chart-line"></i>
                    <span>Laporan</span>
                </a>
                <a href="settings.php" class="menu-item">
                    <i class="fas fa-cog"></i>
                    <span>Pengaturan</span>
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="top-bar">
                <div class="welcome">
                    <h1>Selamat Datang, <?php echo $_SESSION['full_name']; ?>! 👋</h1>
                    <p style="color: #7f8c8d; margin-top: 5px;">Berikut ringkasan bisnis Anda hari ini</p>
                </div>
                
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['full_name'], 0, 2)); ?>
                    </div>
                    <a href="../logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['total_products']); ?></div>
                    <div class="stat-label">Total Produk</div>
                </div>
                
                <div class="stat-card green">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
                    <div class="stat-label">Total Customer</div>
                </div>
                
                <div class="stat-card orange">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['total_transactions']); ?></div>
                    <div class="stat-label">Transaksi Hari Ini</div>
                </div>
                
                <div class="stat-card red">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-value"><?php echo formatRupiah($stats['today_revenue']); ?></div>
                    <div class="stat-label">Pendapatan Hari Ini</div>
                </div>
                
                <div class="stat-card purple">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['low_stock']); ?></div>
                    <div class="stat-label">Stok Menipis</div>
                </div>
            </div>
            
            <!-- Recent Transactions -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><i class="fas fa-receipt"></i> Transaksi Terbaru</h2>
                    <a href="reports.php" style="color: #667eea; text-decoration: none;">Lihat Semua <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Tanggal</th>
                            <th>Kasir</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $recent_transactions->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo $row['invoice_number']; ?></strong></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['transaction_date'])); ?></td>
                            <td><?php echo $row['full_name'] ?? 'N/A'; ?></td>
                            <td><strong><?php echo formatRupiah($row['grand_total']); ?></strong></td>
                            <td>
                                <?php if($row['status'] == 'completed'): ?>
                                    <span class="badge badge-success">Selesai</span>
                                <?php elseif($row['status'] == 'pending'): ?>
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
            
            <!-- Top Products -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><i class="fas fa-star"></i> Produk Terlaris (7 Hari Terakhir)</h2>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Terjual</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $top_products->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo $row['product_name']; ?></strong></td>
                            <td><?php echo number_format($row['total_sold']); ?> unit</td>
                            <td><strong><?php echo formatRupiah($row['revenue']); ?></strong></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<script src="../assets/js/chart.min.js"></script>
=======
<?php
require_once '../config.php';
checkRole(['admin']);

// Get Statistics
$stats = [
    'total_products' => $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'],
    'total_users' => $conn->query("SELECT COUNT(*) as count FROM users WHERE role='customer'")->fetch_assoc()['count'],
    'total_transactions' => $conn->query("SELECT COUNT(*) as count FROM transactions WHERE DATE(transaction_date) = CURDATE()")->fetch_assoc()['count'],
    'today_revenue' => $conn->query("SELECT COALESCE(SUM(grand_total), 0) as total FROM transactions WHERE DATE(transaction_date) = CURDATE()")->fetch_assoc()['total'],
    'low_stock' => $conn->query("SELECT COUNT(*) as count FROM products WHERE stock <= min_stock")->fetch_assoc()['count']
];

// Recent Transactions
$recent_transactions = $conn->query("SELECT t.*, u.full_name FROM transactions t LEFT JOIN users u ON t.kasir_id = u.id ORDER BY transaction_date DESC LIMIT 10");

// Top Products
$top_products = $conn->query("SELECT p.product_name, SUM(td.quantity) as total_sold, SUM(td.subtotal) as revenue FROM transaction_details td JOIN products p ON td.product_id = p.id JOIN transactions t ON td.transaction_id = t.id WHERE DATE(t.transaction_date) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY p.id ORDER BY total_sold DESC LIMIT 5");

// Monthly Revenue
$monthly_revenue = $conn->query("SELECT DATE_FORMAT(transaction_date, '%Y-%m') as month, SUM(grand_total) as revenue FROM transactions WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY month ORDER BY month");

$page_title = 'Admin Dashboard';
$use_charts = true;
require_once '../includes/admin_header.php';
?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }
        
        .wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s;
        }
        
        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h2 {
            font-size: 24px;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            padding: 15px 25px;
            display: flex;
            align-items: center;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .menu-item:hover, .menu-item.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left: 4px solid #3498db;
        }
        
        .menu-item i {
            margin-right: 15px;
            font-size: 18px;
            width: 25px;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 30px;
        }
        
        .top-bar {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .welcome h1 {
            font-size: 28px;
            color: #2c3e50;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .logout-btn {
            padding: 10px 20px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
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
        .stat-card.red .stat-icon { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; }
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
        
        /* Cards */
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
        
        /* Table */
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
        
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        
        /* Chart */
        .chart-container {
            height: 300px;
            position: relative;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -260px;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-store" style="font-size: 40px; margin-bottom: 10px;"></i>
                <h2>Admin Panel</h2>
            </div>
            
            <div class="sidebar-menu">
                <a href="index.php" class="menu-item active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="products.php" class="menu-item">
                    <i class="fas fa-box"></i>
                    <span>Produk</span>
                </a>
                <a href="categories.php" class="menu-item">
                    <i class="fas fa-tags"></i>
                    <span>Kategori</span>
                </a>
                <a href="suppliers.php" class="menu-item">
                    <i class="fas fa-truck"></i>
                    <span>Supplier</span>
                </a>
                <a href="stock.php" class="menu-item">
                    <i class="fas fa-warehouse"></i>
                    <span>Stok Barang</span>
                </a>
                <a href="users.php" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span>Pengguna</span>
                </a>
                <a href="reports.php" class="menu-item">
                    <i class="fas fa-chart-line"></i>
                    <span>Laporan</span>
                </a>
                <a href="settings.php" class="menu-item">
                    <i class="fas fa-cog"></i>
                    <span>Pengaturan</span>
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="top-bar">
                <div class="welcome">
                    <h1>Selamat Datang, <?php echo $_SESSION['full_name']; ?>! 👋</h1>
                    <p style="color: #7f8c8d; margin-top: 5px;">Berikut ringkasan bisnis Anda hari ini</p>
                </div>
                
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['full_name'], 0, 2)); ?>
                    </div>
                    <a href="../logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['total_products']); ?></div>
                    <div class="stat-label">Total Produk</div>
                </div>
                
                <div class="stat-card green">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
                    <div class="stat-label">Total Customer</div>
                </div>
                
                <div class="stat-card orange">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['total_transactions']); ?></div>
                    <div class="stat-label">Transaksi Hari Ini</div>
                </div>
                
                <div class="stat-card red">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-value"><?php echo formatRupiah($stats['today_revenue']); ?></div>
                    <div class="stat-label">Pendapatan Hari Ini</div>
                </div>
                
                <div class="stat-card purple">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($stats['low_stock']); ?></div>
                    <div class="stat-label">Stok Menipis</div>
                </div>
            </div>
            
            <!-- Recent Transactions -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><i class="fas fa-receipt"></i> Transaksi Terbaru</h2>
                    <a href="reports.php" style="color: #667eea; text-decoration: none;">Lihat Semua <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Tanggal</th>
                            <th>Kasir</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $recent_transactions->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo $row['invoice_number']; ?></strong></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['transaction_date'])); ?></td>
                            <td><?php echo $row['full_name'] ?? 'N/A'; ?></td>
                            <td><strong><?php echo formatRupiah($row['grand_total']); ?></strong></td>
                            <td>
                                <?php if($row['status'] == 'completed'): ?>
                                    <span class="badge badge-success">Selesai</span>
                                <?php elseif($row['status'] == 'pending'): ?>
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
            
            <!-- Top Products -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><i class="fas fa-star"></i> Produk Terlaris (7 Hari Terakhir)</h2>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Terjual</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $top_products->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo $row['product_name']; ?></strong></td>
                            <td><?php echo number_format($row['total_sold']); ?> unit</td>
                            <td><strong><?php echo formatRupiah($row['revenue']); ?></strong></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<script src="../assets/js/chart.min.js"></script>
>>>>>>> 882da412c224f7c20dfb67829049d92fbad8991f
<?php require_once '../includes/admin_footer.php'; ?>