<?php
require_once '../config.php';
checkRole(['admin']);

$message = '';
$error = '';

// Handle Add/Edit Product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_product'])) {
    $product_id = $_POST['product_id'] ?? null;
    $product_name = clean_input($_POST['product_name']);
    $sku = clean_input($_POST['sku']);
    $barcode = clean_input($_POST['barcode']);
    $category_id = $_POST['category_id'];
    $supplier_id = $_POST['supplier_id'];
    $price = $_POST['price'];
    $cost_price = $_POST['cost_price'];
    $stock = $_POST['stock'];
    $min_stock = $_POST['min_stock'];
    $unit = clean_input($_POST['unit']);
    $description = clean_input($_POST['description']);
    $status = $_POST['status'];
    
    // Handle image upload
    $image_filename = $_POST['existing_image'] ?? '';
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $upload_result = uploadImage($_FILES['product_image']);
        if ($upload_result['success']) {
            // Delete old image if exists
            if (!empty($image_filename) && $image_filename != 'default.jpg') {
                deleteImage($image_filename);
            }
            $image_filename = $upload_result['filename'];
        } else {
            $error = $upload_result['message'];
        }
    }
    
    if (!isset($error)) {
        if ($product_id) {
            // Update
            $stmt = $conn->prepare("UPDATE products SET product_name=?, sku=?, barcode=?, category_id=?, supplier_id=?, price=?, cost_price=?, stock=?, min_stock=?, unit=?, description=?, status=?, image=? WHERE id=?");
            $stmt->bind_param("sssiiddiisssi", $product_name, $sku, $barcode, $category_id, $supplier_id, $price, $cost_price, $stock, $min_stock, $unit, $description, $status, $image_filename, $product_id);
            
            if ($stmt->execute()) {
                $message = "Produk berhasil diupdate!";
            } else {
                $error = "Gagal mengupdate produk!";
            }
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO products (product_name, sku, barcode, category_id, supplier_id, price, cost_price, stock, min_stock, unit, description, status, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssiiddiissss", $product_name, $sku, $barcode, $category_id, $supplier_id, $price, $cost_price, $stock, $min_stock, $unit, $description, $status, $image_filename);
            
            if ($stmt->execute()) {
                $message = "Produk berhasil ditambahkan!";
            } else {
                $error = "Gagal menambahkan produk!";
            }
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $product_id = $_GET['delete'];
    $stmt = $conn->prepare("UPDATE products SET status='inactive' WHERE id=?");
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        $message = "Produk berhasil dihapus!";
    } else {
        $error = "Gagal menghapus produk!";
    }
}

// Get Products
$products = $conn->query("SELECT p.*, c.category_name, s.supplier_name FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN suppliers s ON p.supplier_id = s.id ORDER BY p.created_at DESC");

// Get Categories and Suppliers for form
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name");
$suppliers = $conn->query("SELECT * FROM suppliers ORDER BY supplier_name");

// Get product for edit
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_product = $stmt->get_result()->fetch_assoc();
}

$page_title = 'Manajemen Produk';
require_once '../includes/admin_header.php';
?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }
        
        .wrapper { display: flex; min-height: 100vh; }
        
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
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
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-success {
            background: #27ae60;
            color: white;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-warning {
            background: #f39c12;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
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
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
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
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
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
        
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
        
        .search-box {
            margin-bottom: 20px;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
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
            overflow-y: auto;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
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
        
        .close-modal {
            font-size: 24px;
            cursor: pointer;
            color: #999;
        }
        
        .close-modal:hover {
            color: #333;
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
                <a href="index.php" class="menu-item">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="products.php" class="menu-item active">
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
                <div>
                    <h1 style="font-size: 28px; color: #2c3e50;">
                        <i class="fas fa-box"></i> Manajemen Produk
                    </h1>
                    <p style="color: #7f8c8d; margin-top: 5px;">Kelola semua produk minimarket Anda</p>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <a href="../logout.php" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
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
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Daftar Produk</h2>
                    <button class="btn btn-success" onclick="openModal()">
                        <i class="fas fa-plus"></i> Tambah Produk
                    </button>
                </div>
                
                <div class="search-box">
                    <input type="text" id="searchProduct" placeholder="Cari produk berdasarkan nama atau SKU...">
                </div>
                
                <div style="overflow-x: auto;">
                    <table id="productsTable">
                        <thead>
                            <tr>
                                <th>Gambar</th>
                                <th>SKU</th>
                                <th>Nama Produk</th>
                                <th>Kategori</th>
                                <th>Supplier</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($product = $products->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="<?php echo UPLOAD_URL . $product['image']; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;" alt="<?php echo $product['product_name']; ?>">
                                    <?php else: ?>
                                        <div style="width: 50px; height: 50px; background: #e0e0e0; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-image" style="color: #95a5a6;"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo $product['sku']; ?></strong></td>
                                <td><?php echo $product['product_name']; ?></td>
                                <td><?php echo $product['category_name'] ?? 'N/A'; ?></td>
                                <td><?php echo $product['supplier_name'] ?? 'N/A'; ?></td>
                                <td><strong><?php echo formatRupiah($product['price']); ?></strong></td>
                                <td>
                                    <span class="badge <?php echo $product['stock'] <= $product['min_stock'] ? 'badge-warning' : 'badge-success'; ?>">
                                        <?php echo $product['stock']; ?> <?php echo $product['unit']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($product['status'] == 'active'): ?>
                                        <span class="badge badge-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?edit=<?php echo $product['id']; ?>" class="btn btn-warning" style="padding: 5px 10px; font-size: 12px;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?delete=<?php echo $product['id']; ?>" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;" onclick="return confirm('Yakin ingin menghapus produk ini?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Form -->
    <div class="modal <?php echo $edit_product ? 'active' : ''; ?>" id="productModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><?php echo $edit_product ? 'Edit Produk' : 'Tambah Produk Baru'; ?></h2>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="<?php echo $edit_product['id'] ?? ''; ?>">
                <input type="hidden" name="existing_image" value="<?php echo $edit_product['image'] ?? ''; ?>">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Produk *</label>
                        <input type="text" name="product_name" value="<?php echo $edit_product['product_name'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>SKU *</label>
                        <input type="text" name="sku" value="<?php echo $edit_product['sku'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Barcode</label>
                        <input type="text" name="barcode" value="<?php echo $edit_product['barcode'] ?? ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Kategori *</label>
                        <select name="category_id" required>
                            <option value="">Pilih Kategori</option>
                            <?php 
                            $categories->data_seek(0);
                            while($cat = $categories->fetch_assoc()): 
                            ?>
                            <option value="<?php echo $cat['category_id']; ?>" <?php echo ($edit_product['category_id'] ?? '') == $cat['category_id'] ? 'selected' : ''; ?>>
                                <?php echo $cat['category_name']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Supplier *</label>
                        <select name="supplier_id" required>
                            <option value="">Pilih Supplier</option>
                            <?php 
                            $suppliers->data_seek(0);
                            while($sup = $suppliers->fetch_assoc()): 
                            ?>
                            <option value="<?php echo $sup['supplier_id']; ?>" <?php echo ($edit_product['supplier_id'] ?? '') == $sup['supplier_id'] ? 'selected' : ''; ?>>
                                <?php echo $sup['supplier_name']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Harga Jual *</label>
                        <input type="number" name="price" value="<?php echo $edit_product['price'] ?? ''; ?>" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Harga Beli</label>
                        <input type="number" name="cost_price" value="<?php echo $edit_product['cost_price'] ?? ''; ?>" step="0.01">
                    </div>
                    
                    <div class="form-group">
                        <label>Stok Awal *</label>
                        <input type="number" name="stock" value="<?php echo $edit_product['stock'] ?? '0'; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Minimum Stok *</label>
                        <input type="number" name="min_stock" value="<?php echo $edit_product['min_stock'] ?? '10'; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Satuan *</label>
                        <input type="text" name="unit" value="<?php echo $edit_product['unit'] ?? 'pcs'; ?>" placeholder="pcs, kg, liter, dll" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Status *</label>
                        <select name="status" required>
                            <option value="active" <?php echo ($edit_product['status'] ?? 'active') == 'active' ? 'selected' : ''; ?>>Aktif</option>
                            <option value="inactive" <?php echo ($edit_product['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>Nonaktif</option>
                        </select>
                    </div>
                    
                    <div class="form-group full-width">
                        <label>Gambar Produk</label>
                        <input type="file" name="product_image" accept="image/*" onchange="previewImage(event)">
                        <small style="color: #7f8c8d; display: block; margin-top: 5px;">Format: JPG, PNG, GIF. Maksimal 5MB</small>
                        <?php if (!empty($edit_product['image'])): ?>
                        <div id="currentImage" style="margin-top: 10px;">
                            <img src="<?php echo UPLOAD_URL . $edit_product['image']; ?>" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e0e0e0;">
                            <p style="margin-top: 5px; font-size: 12px; color: #7f8c8d;">Gambar saat ini</p>
                        </div>
                        <?php endif; ?>
                        <div id="imagePreview" style="margin-top: 10px; display: none;">
                            <img id="preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e0e0e0;">
                            <p style="margin-top: 5px; font-size: 12px; color: #7f8c8d;">Preview gambar baru</p>
                        </div>
                    </div>
                    
                    <div class="form-group full-width">
                        <label>Deskripsi</label>
                        <textarea name="description"><?php echo $edit_product['description'] ?? ''; ?></textarea>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" name="save_product" class="btn btn-success">
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
            document.getElementById('productModal').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('productModal').classList.remove('active');
            if (window.location.search.includes('edit')) {
                window.location.href = 'products.php';
            }
        }
        
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview').src = e.target.result;
                    document.getElementById('imagePreview').style.display = 'block';
                    const currentImage = document.getElementById('currentImage');
                    if (currentImage) {
                        currentImage.style.display = 'none';
                    }
                };
                reader.readAsDataURL(file);
            }
        }
        
        // Search functionality
        document.getElementById('searchProduct').addEventListener('input', function(e) {
            const search = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#productsTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(search) ? '' : 'none';
            });
        });
    </script>

<?php require_once '../includes/admin_footer.php'; ?>