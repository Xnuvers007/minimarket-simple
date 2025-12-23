<?php
require_once '../config.php';
checkRole(['admin']);

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

// Get date range
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');

// Sales summary
$sales_summary = $conn->query("SELECT COUNT(*) as total_trans, COALESCE(SUM(grand_total), 0) as total_sales, COALESCE(AVG(grand_total), 0) as avg_sales FROM transactions WHERE DATE(transaction_date) BETWEEN '$date_from' AND '$date_to' AND status='completed'")->fetch_assoc();

// Top products
$top_products = $conn->query("SELECT p.product_name, SUM(td.quantity) as total_sold, SUM(td.subtotal) as revenue FROM transaction_details td JOIN products p ON td.product_id = p.id JOIN transactions t ON td.transaction_id = t.id WHERE DATE(t.transaction_date) BETWEEN '$date_from' AND '$date_to' AND t.status='completed' GROUP BY p.id ORDER BY total_sold DESC LIMIT 10");

// Sales by category
$sales_by_category = $conn->query("SELECT c.category_name, COUNT(DISTINCT td.transaction_id) as trans_count, SUM(td.subtotal) as revenue FROM transaction_details td JOIN products p ON td.product_id = p.id JOIN categories c ON p.category_id = c.id JOIN transactions t ON td.transaction_id = t.id WHERE DATE(t.transaction_date) BETWEEN '$date_from' AND '$date_to' AND t.status='completed' GROUP BY c.id ORDER BY revenue DESC");

// Daily sales
$daily_sales = $conn->query("SELECT DATE(transaction_date) as date, COUNT(*) as trans_count, SUM(grand_total) as revenue FROM transactions WHERE DATE(transaction_date) BETWEEN '$date_from' AND '$date_to' AND status='completed' GROUP BY DATE(transaction_date) ORDER BY date");

// Low stock products
$low_stock = $conn->query("SELECT * FROM products WHERE stock <= min_stock AND status='active' ORDER BY stock ASC LIMIT 10");

$page_title = 'Laporan & Analytics';
$use_charts = true;
require_once '../includes/admin_header.php';
?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        .wrapper { display: flex; min-height: 100vh; }
        
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-header { padding: 30px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .menu-item { padding: 15px 25px; display: flex; align-items: center; color: rgba(255,255,255,0.8); text-decoration: none; transition: all 0.3s; }
        .menu-item:hover, .menu-item.active { background: rgba(255,255,255,0.1); color: white; border-left: 4px solid #3498db; }
        .menu-item i { margin-right: 15px; font-size: 18px; width: 25px; }
        
        .main-content { margin-left: 260px; flex: 1; padding: 30px; }
        
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
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        
        .filter-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .filter-form {
            display: flex;
            gap: 15px;
            align-items: end;
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
        
        .card-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; padding: 12px; text-align: left; font-weight: 600; color: #2c3e50; }
        td { padding: 12px; border-bottom: 1px solid #f0f0f0; }
        tr:hover { background: #f8f9fa; }
        
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        @media print {
            .sidebar, .top-bar, .filter-card, .no-print { display: none !important; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-store" style="font-size: 40px; margin-bottom: 10px;"></i>
                <h2>Admin Panel</h2>
            </div>
            
            <div class="sidebar-menu">
                <a href="index.php" class="menu-item"><i class="fas fa-home"></i><span>Dashboard</span></a>
                <a href="products.php" class="menu-item"><i class="fas fa-box"></i><span>Produk</span></a>
                <a href="categories.php" class="menu-item"><i class="fas fa-tags"></i><span>Kategori</span></a>
                <a href="suppliers.php" class="menu-item"><i class="fas fa-truck"></i><span>Supplier</span></a>
                <a href="stock.php" class="menu-item"><i class="fas fa-warehouse"></i><span>Stok Barang</span></a>
                <a href="users.php" class="menu-item"><i class="fas fa-users"></i><span>Pengguna</span></a>
                <a href="reports.php" class="menu-item active"><i class="fas fa-chart-line"></i><span>Laporan</span></a>
                <a href="settings.php" class="menu-item"><i class="fas fa-cog"></i><span>Pengaturan</span></a>
            </div>
        </div>
        
        <div class="main-content">
            <div class="top-bar no-print">
                <div>
                    <h1 style="font-size: 28px; color: #2c3e50;"><i class="fas fa-chart-line"></i> Laporan Penjualan</h1>
                    <p style="color: #7f8c8d; margin-top: 5px;">Analisis dan statistik penjualan</p>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button class="btn btn-success" onclick="window.print()">
                        <i class="fas fa-print"></i> Cetak
                    </button>
                    <a href="index.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Kembali</a>
                    <a href="../logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            
            <div class="filter-card no-print">
                <form method="GET" class="filter-form">
                    <div class="form-group">
                        <label>Dari Tanggal</label>
                        <input type="date" name="date_from" value="<?php echo $date_from; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Sampai Tanggal</label>
                        <input type="date" name="date_to" value="<?php echo $date_to; ?>" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </form>
            </div>
            
            <div style="text-align: center; margin-bottom: 20px;">
                <h2>Periode: <?php echo date('d F Y', strtotime($date_from)); ?> - <?php echo date('d F Y', strtotime($date_to)); ?></h2>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-icon"><i class="fas fa-receipt"></i></div>
                    <div class="stat-value"><?php echo number_format($sales_summary['total_trans']); ?></div>
                    <div class="stat-label">Total Transaksi</div>
                </div>
                
                <div class="stat-card green">
                    <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                    <div class="stat-value"><?php echo formatRupiah($sales_summary['total_sales']); ?></div>
                    <div class="stat-label">Total Penjualan</div>
                </div>
                
                <div class="stat-card orange">
                    <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="stat-value"><?php echo formatRupiah($sales_summary['avg_sales']); ?></div>
                    <div class="stat-label">Rata-rata Transaksi</div>
                </div>
            </div>
            
            <div class="grid-2">
                <div class="card">
                    <h3 class="card-title"><i class="fas fa-star"></i> Top 10 Produk Terlaris</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Terjual</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($prod = $top_products->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo $prod['product_name']; ?></strong></td>
                                <td><?php echo number_format($prod['total_sold']); ?> unit</td>
                                <td><strong><?php echo formatRupiah($prod['revenue']); ?></strong></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="card">
                    <h3 class="card-title"><i class="fas fa-tags"></i> Penjualan per Kategori</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Transaksi</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($cat = $sales_by_category->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo $cat['category_name']; ?></strong></td>
                                <td><?php echo number_format($cat['trans_count']); ?></td>
                                <td><strong><?php echo formatRupiah($cat['revenue']); ?></strong></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card">
                <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Penjualan Harian</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jumlah Transaksi</th>
                            <th>Total Penjualan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($daily = $daily_sales->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo date('d F Y', strtotime($daily['date'])); ?></strong></td>
                            <td><?php echo number_format($daily['trans_count']); ?> transaksi</td>
                            <td><strong><?php echo formatRupiah($daily['revenue']); ?></strong></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="card">
                <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Produk Stok Menipis</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Stok Saat Ini</th>
                            <th>Min. Stok</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($prod = $low_stock->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo $prod['product_name']; ?></strong></td>
                            <td><?php echo $prod['stock']; ?> <?php echo $prod['unit']; ?></td>
                            <td><?php echo $prod['min_stock']; ?> <?php echo $prod['unit']; ?></td>
                            <td>
                                <?php if($prod['stock'] == 0): ?>
                                    <strong style="color: #e74c3c;">Habis</strong>
                                <?php else: ?>
                                    <strong style="color: #f39c12;">Menipis</strong>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="card">
                <h3 class="card-title"><i class="fas fa-list"></i> Daftar Transaksi</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Tanggal</th>
                            <th>Kasir</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $transactions = $conn->query("SELECT t.*, u.full_name as kasir_name FROM transactions t LEFT JOIN users u ON t.kasir_id = u.id WHERE DATE(t.transaction_date) BETWEEN '$date_from' AND '$date_to' ORDER BY t.transaction_date DESC");
                        while($trans = $transactions->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><strong><?php echo $trans['invoice_number']; ?></strong></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($trans['transaction_date'])); ?></td>
                            <td><?php echo $trans['kasir_name'] ?? 'N/A'; ?></td>
                            <td><strong><?php echo formatRupiah($trans['grand_total']); ?></strong></td>
                            <td>
                                <?php if($trans['status'] == 'completed'): ?>
                                    <strong style="color: #27ae60;">Selesai</strong>
                                <?php else: ?>
                                    <strong style="color: #95a5a6;">Pending</strong>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button onclick="deleteTransaction(<?php echo $trans['id']; ?>, '<?php echo $trans['invoice_number']; ?>')" style="padding: 5px 10px; background: #e74c3c; color: white; border: none; border-radius: 5px; cursor: pointer;">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
        function deleteTransaction(transId, invoice) {
            if (!confirm('Hapus transaksi ' + invoice + '?\\nStok produk akan dikembalikan.')) {
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
    </script>

<script src="../assets/js/chart.min.js"></script>
<?php require_once '../includes/admin_footer.php'; ?>