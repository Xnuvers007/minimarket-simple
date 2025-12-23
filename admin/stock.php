<<<<<<< HEAD
<?php
require_once '../config.php';
checkRole(['admin']);

$message = '';
$error = '';

// Handle Stock Adjustment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['adjust_stock'])) {
    $product_id = $_POST['id'];
    $type = $_POST['type']; // in, out, adjustment
    $quantity = intval($_POST['quantity']);
    $reference = clean_input($_POST['reference']);
    $notes = clean_input($_POST['notes']);
    $created_by = $_SESSION['user_id'];
    
    $conn->begin_transaction();
    
    try {
        // Insert stock history
        $stmt = $conn->prepare("INSERT INTO stock_history (product_id, quantity_change, type, reference, notes, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $qty_change = ($type == 'out') ? -$quantity : $quantity;
        $stmt->bind_param("iisssi", $product_id, $qty_change, $type, $reference, $notes, $created_by);
        $stmt->execute();
        
        // Update product stock
        $stmt = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
        $stmt->bind_param("ii", $qty_change, $product_id);
        $stmt->execute();
        
        $conn->commit();
        $message = "Stok berhasil diupdate!";
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Gagal update stok: " . $e->getMessage();
    }
}

// Get products with low stock
$low_stock_products = $conn->query("SELECT * FROM products WHERE stock <= min_stock AND status='active' ORDER BY stock ASC");

// Get all products for adjustment
$all_products = $conn->query("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status='active' ORDER BY p.product_name");

// Get stock history
$stock_history = $conn->query("SELECT sh.*, p.product_name, u.full_name FROM stock_history sh JOIN products p ON sh.product_id = p.id LEFT JOIN users u ON sh.created_by = u.id ORDER BY sh.created_at DESC LIMIT 50");

$page_title = 'Manajemen Stok';
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
        .btn-success { background: #27ae60; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        
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
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        
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
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        
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
        
        .form-group { margin-bottom: 20px; }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
        }
        
        .form-group textarea { resize: vertical; min-height: 80px; }
        
        .stock-alert {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
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
                <a href="stock.php" class="menu-item active"><i class="fas fa-warehouse"></i><span>Stok Barang</span></a>
                <a href="users.php" class="menu-item"><i class="fas fa-users"></i><span>Pengguna</span></a>
                <a href="reports.php" class="menu-item"><i class="fas fa-chart-line"></i><span>Laporan</span></a>
                <a href="settings.php" class="menu-item"><i class="fas fa-cog"></i><span>Pengaturan</span></a>
            </div>
        </div>
        
        <div class="main-content">
            <div class="top-bar">
                <div>
                    <h1 style="font-size: 28px; color: #2c3e50;"><i class="fas fa-warehouse"></i> Manajemen Stok</h1>
                    <p style="color: #7f8c8d; margin-top: 5px;">Kelola stok masuk, keluar, dan penyesuaian</p>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <a href="index.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Kembali</a>
                    <a href="../logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            
            <?php if($message): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($low_stock_products->num_rows > 0): ?>
            <div class="stock-alert">
                <h3 style="margin-bottom: 10px;"><i class="fas fa-exclamation-triangle"></i> Peringatan Stok Menipis!</h3>
                <p>Ada <?php echo $low_stock_products->num_rows; ?> produk dengan stok di bawah minimum. Segera lakukan restock!</p>
            </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2>Produk Stok Menipis</h2>
                    <button class="btn btn-success" onclick="openModal()">
                        <i class="fas fa-plus"></i> Tambah Stok
                    </button>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Stok Saat Ini</th>
                            <th>Min. Stok</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $low_stock_products->data_seek(0);
                        while($product = $low_stock_products->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><strong><?php echo $product['product_name']; ?></strong></td>
                            <td><span class="badge badge-danger"><?php echo $product['stock']; ?> <?php echo $product['unit']; ?></span></td>
                            <td><?php echo $product['min_stock']; ?> <?php echo $product['unit']; ?></td>
                            <td>
                                <?php if($product['stock'] == 0): ?>
                                    <span class="badge badge-danger">Habis</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Menipis</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-success" style="padding: 5px 10px; font-size: 12px;" onclick="quickAdjust(<?php echo $product['id']; ?>, '<?php echo addslashes($product['product_name']); ?>')">
                                    <i class="fas fa-plus"></i> Restock
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Riwayat Pergerakan Stok</h2>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Produk</th>
                            <th>Tipe</th>
                            <th>Jumlah</th>
                            <th>Referensi</th>
                            <th>Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($history = $stock_history->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($history['created_at'])); ?></td>
                            <td><?php echo $history['product_name']; ?></td>
                            <td>
                                <?php if($history['type'] == 'in'): ?>
                                    <span class="badge badge-success">Masuk</span>
                                <?php elseif($history['type'] == 'out'): ?>
                                    <span class="badge badge-danger">Keluar</span>
                                <?php else: ?>
                                    <span class="badge badge-info">Penyesuaian</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="color: <?php echo $history['quantity_change'] > 0 ? '#27ae60' : '#e74c3c'; ?>">
                                    <?php echo $history['quantity_change'] > 0 ? '+' : ''; ?><?php echo $history['quantity_change']; ?>
                                </strong>
                            </td>
                            <td><?php echo $history['reference'] ?: '-'; ?></td>
                            <td><?php echo $history['full_name']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="modal" id="stockModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Penyesuaian Stok</h2>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label>Produk *</label>
                    <select name="id" id="productSelect" required>
                        <option value="">Pilih Produk</option>
                        <?php 
                        $all_products->data_seek(0);
                        while($prod = $all_products->fetch_assoc()): 
                        ?>
                        <option value="<?php echo $prod['id']; ?>">
                            <?php echo $prod['product_name']; ?> (Stok: <?php echo $prod['stock']; ?>)
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Tipe Pergerakan *</label>
                    <select name="type" required>
                        <option value="in">Stok Masuk</option>
                        <option value="out">Stok Keluar</option>
                        <option value="adjustment">Penyesuaian</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Jumlah *</label>
                    <input type="number" name="quantity" min="1" required>
                </div>
                
                <div class="form-group">
                    <label>Referensi</label>
                    <input type="text" name="reference" placeholder="No. PO, Invoice, dll">
                </div>
                
                <div class="form-group">
                    <label>Catatan</label>
                    <textarea name="notes" placeholder="Catatan tambahan"></textarea>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" name="adjust_stock" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <button type="button" class="btn btn-danger" onclick="closeModal()">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openModal() {
            document.getElementById('stockModal').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('stockModal').classList.remove('active');
        }
        
        function quickAdjust(productId, productName) {
            openModal();
            document.getElementById('productSelect').value = productId;
        }
    </script>

=======
<?php
require_once '../config.php';
checkRole(['admin']);

$message = '';
$error = '';

// Handle Stock Adjustment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['adjust_stock'])) {
    $product_id = $_POST['id'];
    $type = $_POST['type']; // in, out, adjustment
    $quantity = intval($_POST['quantity']);
    $reference = clean_input($_POST['reference']);
    $notes = clean_input($_POST['notes']);
    $created_by = $_SESSION['user_id'];
    
    $conn->begin_transaction();
    
    try {
        // Insert stock history
        $stmt = $conn->prepare("INSERT INTO stock_history (product_id, quantity_change, type, reference, notes, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $qty_change = ($type == 'out') ? -$quantity : $quantity;
        $stmt->bind_param("iisssi", $product_id, $qty_change, $type, $reference, $notes, $created_by);
        $stmt->execute();
        
        // Update product stock
        $stmt = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
        $stmt->bind_param("ii", $qty_change, $product_id);
        $stmt->execute();
        
        $conn->commit();
        $message = "Stok berhasil diupdate!";
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Gagal update stok: " . $e->getMessage();
    }
}

// Get products with low stock
$low_stock_products = $conn->query("SELECT * FROM products WHERE stock <= min_stock AND status='active' ORDER BY stock ASC");

// Get all products for adjustment
$all_products = $conn->query("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status='active' ORDER BY p.product_name");

// Get stock history
$stock_history = $conn->query("SELECT sh.*, p.product_name, u.full_name FROM stock_history sh JOIN products p ON sh.product_id = p.id LEFT JOIN users u ON sh.created_by = u.id ORDER BY sh.created_at DESC LIMIT 50");

$page_title = 'Manajemen Stok';
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
        .btn-success { background: #27ae60; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        
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
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        
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
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        
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
        
        .form-group { margin-bottom: 20px; }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
        }
        
        .form-group textarea { resize: vertical; min-height: 80px; }
        
        .stock-alert {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
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
                <a href="stock.php" class="menu-item active"><i class="fas fa-warehouse"></i><span>Stok Barang</span></a>
                <a href="users.php" class="menu-item"><i class="fas fa-users"></i><span>Pengguna</span></a>
                <a href="reports.php" class="menu-item"><i class="fas fa-chart-line"></i><span>Laporan</span></a>
                <a href="settings.php" class="menu-item"><i class="fas fa-cog"></i><span>Pengaturan</span></a>
            </div>
        </div>
        
        <div class="main-content">
            <div class="top-bar">
                <div>
                    <h1 style="font-size: 28px; color: #2c3e50;"><i class="fas fa-warehouse"></i> Manajemen Stok</h1>
                    <p style="color: #7f8c8d; margin-top: 5px;">Kelola stok masuk, keluar, dan penyesuaian</p>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <a href="index.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Kembali</a>
                    <a href="../logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            
            <?php if($message): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($low_stock_products->num_rows > 0): ?>
            <div class="stock-alert">
                <h3 style="margin-bottom: 10px;"><i class="fas fa-exclamation-triangle"></i> Peringatan Stok Menipis!</h3>
                <p>Ada <?php echo $low_stock_products->num_rows; ?> produk dengan stok di bawah minimum. Segera lakukan restock!</p>
            </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2>Produk Stok Menipis</h2>
                    <button class="btn btn-success" onclick="openModal()">
                        <i class="fas fa-plus"></i> Tambah Stok
                    </button>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Stok Saat Ini</th>
                            <th>Min. Stok</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $low_stock_products->data_seek(0);
                        while($product = $low_stock_products->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><strong><?php echo $product['product_name']; ?></strong></td>
                            <td><span class="badge badge-danger"><?php echo $product['stock']; ?> <?php echo $product['unit']; ?></span></td>
                            <td><?php echo $product['min_stock']; ?> <?php echo $product['unit']; ?></td>
                            <td>
                                <?php if($product['stock'] == 0): ?>
                                    <span class="badge badge-danger">Habis</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Menipis</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-success" style="padding: 5px 10px; font-size: 12px;" onclick="quickAdjust(<?php echo $product['id']; ?>, '<?php echo addslashes($product['product_name']); ?>')">
                                    <i class="fas fa-plus"></i> Restock
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Riwayat Pergerakan Stok</h2>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Produk</th>
                            <th>Tipe</th>
                            <th>Jumlah</th>
                            <th>Referensi</th>
                            <th>Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($history = $stock_history->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($history['created_at'])); ?></td>
                            <td><?php echo $history['product_name']; ?></td>
                            <td>
                                <?php if($history['type'] == 'in'): ?>
                                    <span class="badge badge-success">Masuk</span>
                                <?php elseif($history['type'] == 'out'): ?>
                                    <span class="badge badge-danger">Keluar</span>
                                <?php else: ?>
                                    <span class="badge badge-info">Penyesuaian</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="color: <?php echo $history['quantity_change'] > 0 ? '#27ae60' : '#e74c3c'; ?>">
                                    <?php echo $history['quantity_change'] > 0 ? '+' : ''; ?><?php echo $history['quantity_change']; ?>
                                </strong>
                            </td>
                            <td><?php echo $history['reference'] ?: '-'; ?></td>
                            <td><?php echo $history['full_name']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="modal" id="stockModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Penyesuaian Stok</h2>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label>Produk *</label>
                    <select name="id" id="productSelect" required>
                        <option value="">Pilih Produk</option>
                        <?php 
                        $all_products->data_seek(0);
                        while($prod = $all_products->fetch_assoc()): 
                        ?>
                        <option value="<?php echo $prod['id']; ?>">
                            <?php echo $prod['product_name']; ?> (Stok: <?php echo $prod['stock']; ?>)
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Tipe Pergerakan *</label>
                    <select name="type" required>
                        <option value="in">Stok Masuk</option>
                        <option value="out">Stok Keluar</option>
                        <option value="adjustment">Penyesuaian</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Jumlah *</label>
                    <input type="number" name="quantity" min="1" required>
                </div>
                
                <div class="form-group">
                    <label>Referensi</label>
                    <input type="text" name="reference" placeholder="No. PO, Invoice, dll">
                </div>
                
                <div class="form-group">
                    <label>Catatan</label>
                    <textarea name="notes" placeholder="Catatan tambahan"></textarea>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" name="adjust_stock" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <button type="button" class="btn btn-danger" onclick="closeModal()">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openModal() {
            document.getElementById('stockModal').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('stockModal').classList.remove('active');
        }
        
        function quickAdjust(productId, productName) {
            openModal();
            document.getElementById('productSelect').value = productId;
        }
    </script>

>>>>>>> 882da412c224f7c20dfb67829049d92fbad8991f
<?php require_once '../includes/admin_footer.php'; ?>