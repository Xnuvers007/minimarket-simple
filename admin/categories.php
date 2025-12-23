<<<<<<< HEAD
<?php
require_once '../config.php';
checkRole(['admin']);

$message = '';
$error = '';

// Handle Add/Edit Category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_category'])) {
    $category_id = $_POST['category_id'] ?? null;
    $category_name = clean_input($_POST['category_name']);
    $description = clean_input($_POST['description']);
    $icon = clean_input($_POST['icon']);
    
    if ($category_id) {
        $stmt = $conn->prepare("UPDATE categories SET category_name=?, description=?, icon=? WHERE id=?");
        $stmt->bind_param("sssi", $category_name, $description, $icon, $category_id);
        $message = "Kategori berhasil diupdate!";
    } else {
        $stmt = $conn->prepare("INSERT INTO categories (category_name, description, icon) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $category_name, $description, $icon);
        $message = "Kategori berhasil ditambahkan!";
    }
    
    if (!$stmt->execute()) {
        $error = "Gagal menyimpan kategori!";
        $message = '';
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $category_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
    $stmt->bind_param("i", $category_id);
    
    if ($stmt->execute()) {
        $message = "Kategori berhasil dihapus!";
    } else {
        $error = "Gagal menghapus kategori! Mungkin masih ada produk di kategori ini.";
    }
}

$categories = $conn->query("SELECT c.*, COUNT(p.id) as total_products FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.category_name");

$edit_category = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_category = $stmt->get_result()->fetch_assoc();
}

$page_title = 'Manajemen Kategori';
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
        .btn-warning { background: #f39c12; color: white; }
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
        
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .category-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        
        .category-icon {
            font-size: 40px;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        
        .category-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .category-products {
            font-size: 14px;
            opacity: 0.8;
            margin-bottom: 15px;
        }
        
        .category-actions {
            display: flex;
            gap: 10px;
        }
        
        .category-actions .btn {
            padding: 8px 15px;
            font-size: 13px;
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
        
        .modal.active { display: flex; }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
        }
        
        .form-group textarea { resize: vertical; min-height: 80px; }
        
        .icon-picker {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 10px;
            margin-top: 10px;
        }
        
        .icon-option {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            font-size: 24px;
            transition: all 0.3s;
        }
        
        .icon-option:hover, .icon-option.selected {
            border-color: #667eea;
            background: #f0f4ff;
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
                <a href="categories.php" class="menu-item active"><i class="fas fa-tags"></i><span>Kategori</span></a>
                <a href="suppliers.php" class="menu-item"><i class="fas fa-truck"></i><span>Supplier</span></a>
                <a href="stock.php" class="menu-item"><i class="fas fa-warehouse"></i><span>Stok Barang</span></a>
                <a href="users.php" class="menu-item"><i class="fas fa-users"></i><span>Pengguna</span></a>
                <a href="reports.php" class="menu-item"><i class="fas fa-chart-line"></i><span>Laporan</span></a>
                <a href="settings.php" class="menu-item"><i class="fas fa-cog"></i><span>Pengaturan</span></a>
            </div>
        </div>
        
        <div class="main-content">
            <div class="top-bar">
                <div>
                    <h1 style="font-size: 28px; color: #2c3e50;"><i class="fas fa-tags"></i> Manajemen Kategori</h1>
                    <p style="color: #7f8c8d; margin-top: 5px;">Kelola kategori produk minimarket</p>
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
            
            <div class="card">
                <div class="card-header">
                    <h2>Daftar Kategori</h2>
                    <button class="btn btn-success" onclick="openModal()">
                        <i class="fas fa-plus"></i> Tambah Kategori
                    </button>
                </div>
                
                <div class="categories-grid">
                    <?php while($cat = $categories->fetch_assoc()): ?>
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas <?php echo $cat['icon'] ?: 'fa-tag'; ?>"></i>
                        </div>
                        <div class="category-name"><?php echo $cat['category_name']; ?></div>
                        <div class="category-products"><?php echo $cat['total_products']; ?> produk</div>
                        <p style="font-size: 13px; opacity: 0.8; margin-bottom: 15px;"><?php echo substr($cat['description'], 0, 50); ?>...</p>
                        <div class="category-actions">
                            <a href="?edit=<?php echo $cat['id']; ?>" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="?delete=<?php echo $cat['id']; ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal <?php echo $edit_category ? 'active' : ''; ?>" id="categoryModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><?php echo $edit_category ? 'Edit Kategori' : 'Tambah Kategori'; ?></h2>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            
            <form method="POST">
                <input type="hidden" name="category_id" value="<?php echo $edit_category['category_id'] ?? ''; ?>">
                
                <div class="form-group">
                    <label>Nama Kategori *</label>
                    <input type="text" name="category_name" value="<?php echo $edit_category['category_name'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="description"><?php echo $edit_category['description'] ?? ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Pilih Icon</label>
                    <input type="hidden" name="icon" id="selectedIcon" value="<?php echo $edit_category['icon'] ?? 'fa-tag'; ?>">
                    <div class="icon-picker">
                        <?php 
                        $icons = ['fa-utensils', 'fa-glass-water', 'fa-wheat-awn', 'fa-soap', 'fa-plug', 'fa-shirt', 'fa-baby', 'fa-pills', 'fa-bone', 'fa-book', 'fa-futbol', 'fa-hammer', 'fa-paint-roller', 'fa-couch', 'fa-lightbulb', 'fa-mobile'];
                        foreach($icons as $icon): 
                        ?>
                        <div class="icon-option <?php echo ($edit_category['icon'] ?? 'fa-tag') == $icon ? 'selected' : ''; ?>" onclick="selectIcon('<?php echo $icon; ?>')">
                            <i class="fas <?php echo $icon; ?>"></i>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" name="save_category" class="btn btn-success">
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
            document.getElementById('categoryModal').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('categoryModal').classList.remove('active');
            if (window.location.search.includes('edit')) {
                window.location.href = 'categories.php';
            }
        }
        
        function selectIcon(iconClass) {
            document.getElementById('selectedIcon').value = iconClass;
            document.querySelectorAll('.icon-option').forEach(el => el.classList.remove('selected'));
            event.target.closest('.icon-option').classList.add('selected');
        }
    </script>

=======
<?php
require_once '../config.php';
checkRole(['admin']);

$message = '';
$error = '';

// Handle Add/Edit Category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_category'])) {
    $category_id = $_POST['category_id'] ?? null;
    $category_name = clean_input($_POST['category_name']);
    $description = clean_input($_POST['description']);
    $icon = clean_input($_POST['icon']);
    
    if ($category_id) {
        $stmt = $conn->prepare("UPDATE categories SET category_name=?, description=?, icon=? WHERE id=?");
        $stmt->bind_param("sssi", $category_name, $description, $icon, $category_id);
        $message = "Kategori berhasil diupdate!";
    } else {
        $stmt = $conn->prepare("INSERT INTO categories (category_name, description, icon) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $category_name, $description, $icon);
        $message = "Kategori berhasil ditambahkan!";
    }
    
    if (!$stmt->execute()) {
        $error = "Gagal menyimpan kategori!";
        $message = '';
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $category_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
    $stmt->bind_param("i", $category_id);
    
    if ($stmt->execute()) {
        $message = "Kategori berhasil dihapus!";
    } else {
        $error = "Gagal menghapus kategori! Mungkin masih ada produk di kategori ini.";
    }
}

$categories = $conn->query("SELECT c.*, COUNT(p.id) as total_products FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.category_name");

$edit_category = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_category = $stmt->get_result()->fetch_assoc();
}

$page_title = 'Manajemen Kategori';
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
        .btn-warning { background: #f39c12; color: white; }
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
        
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .category-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        
        .category-icon {
            font-size: 40px;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        
        .category-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .category-products {
            font-size: 14px;
            opacity: 0.8;
            margin-bottom: 15px;
        }
        
        .category-actions {
            display: flex;
            gap: 10px;
        }
        
        .category-actions .btn {
            padding: 8px 15px;
            font-size: 13px;
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
        
        .modal.active { display: flex; }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
        }
        
        .form-group textarea { resize: vertical; min-height: 80px; }
        
        .icon-picker {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 10px;
            margin-top: 10px;
        }
        
        .icon-option {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            font-size: 24px;
            transition: all 0.3s;
        }
        
        .icon-option:hover, .icon-option.selected {
            border-color: #667eea;
            background: #f0f4ff;
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
                <a href="categories.php" class="menu-item active"><i class="fas fa-tags"></i><span>Kategori</span></a>
                <a href="suppliers.php" class="menu-item"><i class="fas fa-truck"></i><span>Supplier</span></a>
                <a href="stock.php" class="menu-item"><i class="fas fa-warehouse"></i><span>Stok Barang</span></a>
                <a href="users.php" class="menu-item"><i class="fas fa-users"></i><span>Pengguna</span></a>
                <a href="reports.php" class="menu-item"><i class="fas fa-chart-line"></i><span>Laporan</span></a>
                <a href="settings.php" class="menu-item"><i class="fas fa-cog"></i><span>Pengaturan</span></a>
            </div>
        </div>
        
        <div class="main-content">
            <div class="top-bar">
                <div>
                    <h1 style="font-size: 28px; color: #2c3e50;"><i class="fas fa-tags"></i> Manajemen Kategori</h1>
                    <p style="color: #7f8c8d; margin-top: 5px;">Kelola kategori produk minimarket</p>
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
            
            <div class="card">
                <div class="card-header">
                    <h2>Daftar Kategori</h2>
                    <button class="btn btn-success" onclick="openModal()">
                        <i class="fas fa-plus"></i> Tambah Kategori
                    </button>
                </div>
                
                <div class="categories-grid">
                    <?php while($cat = $categories->fetch_assoc()): ?>
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas <?php echo $cat['icon'] ?: 'fa-tag'; ?>"></i>
                        </div>
                        <div class="category-name"><?php echo $cat['category_name']; ?></div>
                        <div class="category-products"><?php echo $cat['total_products']; ?> produk</div>
                        <p style="font-size: 13px; opacity: 0.8; margin-bottom: 15px;"><?php echo substr($cat['description'], 0, 50); ?>...</p>
                        <div class="category-actions">
                            <a href="?edit=<?php echo $cat['id']; ?>" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="?delete=<?php echo $cat['id']; ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal <?php echo $edit_category ? 'active' : ''; ?>" id="categoryModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><?php echo $edit_category ? 'Edit Kategori' : 'Tambah Kategori'; ?></h2>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            
            <form method="POST">
                <input type="hidden" name="category_id" value="<?php echo $edit_category['category_id'] ?? ''; ?>">
                
                <div class="form-group">
                    <label>Nama Kategori *</label>
                    <input type="text" name="category_name" value="<?php echo $edit_category['category_name'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="description"><?php echo $edit_category['description'] ?? ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Pilih Icon</label>
                    <input type="hidden" name="icon" id="selectedIcon" value="<?php echo $edit_category['icon'] ?? 'fa-tag'; ?>">
                    <div class="icon-picker">
                        <?php 
                        $icons = ['fa-utensils', 'fa-glass-water', 'fa-wheat-awn', 'fa-soap', 'fa-plug', 'fa-shirt', 'fa-baby', 'fa-pills', 'fa-bone', 'fa-book', 'fa-futbol', 'fa-hammer', 'fa-paint-roller', 'fa-couch', 'fa-lightbulb', 'fa-mobile'];
                        foreach($icons as $icon): 
                        ?>
                        <div class="icon-option <?php echo ($edit_category['icon'] ?? 'fa-tag') == $icon ? 'selected' : ''; ?>" onclick="selectIcon('<?php echo $icon; ?>')">
                            <i class="fas <?php echo $icon; ?>"></i>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" name="save_category" class="btn btn-success">
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
            document.getElementById('categoryModal').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('categoryModal').classList.remove('active');
            if (window.location.search.includes('edit')) {
                window.location.href = 'categories.php';
            }
        }
        
        function selectIcon(iconClass) {
            document.getElementById('selectedIcon').value = iconClass;
            document.querySelectorAll('.icon-option').forEach(el => el.classList.remove('selected'));
            event.target.closest('.icon-option').classList.add('selected');
        }
    </script>

>>>>>>> 882da412c224f7c20dfb67829049d92fbad8991f
<?php require_once '../includes/admin_footer.php'; ?>