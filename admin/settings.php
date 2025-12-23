<<<<<<< HEAD
<?php
require_once '../config.php';
checkRole(['admin']);

$message = '';
$error = '';

$user_id = $_SESSION['user_id'];

// Handle Change Password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    $user = $conn->query("SELECT password FROM users WHERE id=$user_id")->fetch_assoc();
    
    if (!password_verify($current_password, $user['password'])) {
        $error = "Password saat ini salah!";
    } elseif ($new_password !== $confirm_password) {
        $error = "Konfirmasi password tidak cocok!";
    } elseif (strlen($new_password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $hashed, $user_id);
        
        if ($stmt->execute()) {
            $message = "Password berhasil diubah!";
        } else {
            $error = "Gagal mengubah password!";
        }
    }
}

// Handle Update Profile
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = clean_input($_POST['full_name']);
    $email = clean_input($_POST['email']);
    $phone = clean_input($_POST['phone']);
    
    $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, phone=? WHERE id=?");
    $stmt->bind_param("sssi", $full_name, $email, $phone, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['full_name'] = $full_name;
        $message = "Profil berhasil diupdate!";
    } else {
        $error = "Gagal mengupdate profil!";
    }
}

// Get user info
$user_info = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

// Get system stats
$stats = [
    'total_products' => $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'],
    'total_users' => $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'],
    'total_categories' => $conn->query("SELECT COUNT(*) as count FROM categories")->fetch_assoc()['count'],
    'total_suppliers' => $conn->query("SELECT COUNT(*) as count FROM suppliers")->fetch_assoc()['count'],
    'total_transactions' => $conn->query("SELECT COUNT(*) as count FROM transactions")->fetch_assoc()['count'],
];

// Get database size
$db_name = DB_NAME;
$db_size_query = $conn->query("SELECT SUM(data_length + index_length) as size FROM information_schema.TABLES WHERE table_schema = '$db_name'");
$db_size_bytes = $db_size_query->fetch_assoc()['size'] ?? 0;

// Convert to readable format
if ($db_size_bytes >= 1073741824) {
    $stats['db_size'] = number_format($db_size_bytes / 1073741824, 2) . ' GB';
} elseif ($db_size_bytes >= 1048576) {
    $stats['db_size'] = number_format($db_size_bytes / 1048576, 2) . ' MB';
} elseif ($db_size_bytes >= 1024) {
    $stats['db_size'] = number_format($db_size_bytes / 1024, 2) . ' KB';
} else {
    $stats['db_size'] = $db_size_bytes . ' Bytes';
}

$page_title = 'Pengaturan Sistem';
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
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .card-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        
        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        
        .info-icon {
            font-size: 40px;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .info-value {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .info-label {
            color: #7f8c8d;
            font-size: 14px;
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
                <a href="reports.php" class="menu-item"><i class="fas fa-chart-line"></i><span>Laporan</span></a>
                <a href="settings.php" class="menu-item active"><i class="fas fa-cog"></i><span>Pengaturan</span></a>
            </div>
        </div>
        
        <div class="main-content">
            <div class="top-bar">
                <div>
                    <h1 style="font-size: 28px; color: #2c3e50;"><i class="fas fa-cog"></i> Pengaturan Sistem</h1>
                    <p style="color: #7f8c8d; margin-top: 5px;">Kelola pengaturan dan profil Anda</p>
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
                <h2 class="card-title"><i class="fas fa-chart-bar"></i> Statistik Sistem</h2>
                
                <div class="info-grid">
                    <div class="info-card">
                        <div class="info-icon"><i class="fas fa-box"></i></div>
                        <div class="info-value"><?php echo number_format($stats['total_products']); ?></div>
                        <div class="info-label">Total Produk</div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon"><i class="fas fa-users"></i></div>
                        <div class="info-value"><?php echo number_format($stats['total_users']); ?></div>
                        <div class="info-label">Total Pengguna</div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon"><i class="fas fa-tags"></i></div>
                        <div class="info-value"><?php echo number_format($stats['total_categories']); ?></div>
                        <div class="info-label">Total Kategori</div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon"><i class="fas fa-truck"></i></div>
                        <div class="info-value"><?php echo number_format($stats['total_suppliers']); ?></div>
                        <div class="info-label">Total Supplier</div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon"><i class="fas fa-receipt"></i></div>
                        <div class="info-value"><?php echo number_format($stats['total_transactions']); ?></div>
                        <div class="info-label">Total Transaksi</div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon"><i class="fas fa-database"></i></div>
                        <div class="info-value"><?php echo $stats['db_size']; ?></div>
                        <div class="info-label">Ukuran Database</div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <h2 class="card-title"><i class="fas fa-user"></i> Informasi Profil</h2>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="admin-username">Username</label>
                        <input type="text" id="admin-username" value="<?php echo $user_info['username']; ?>" autocomplete="username" readonly style="background: #f5f5f5;">
                    </div>
                    
                    <div class="form-group">
                        <label for="admin-fullname">Nama Lengkap *</label>
                        <input type="text" id="admin-fullname" name="full_name" value="<?php echo $user_info['full_name']; ?>" autocomplete="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin-email">Email *</label>
                        <input type="email" id="admin-email" name="email" value="<?php echo $user_info['email']; ?>" autocomplete="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin-phone">No. Telepon</label>
                        <input type="tel" id="admin-phone" name="phone" value="<?php echo $user_info['phone']; ?>" autocomplete="tel">
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
            
            <div class="card">
                <h2 class="card-title"><i class="fas fa-lock"></i> Ubah Password</h2>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="admin-current-password">Password Lama *</label>
                        <input type="password" id="admin-current-password" name="current_password" autocomplete="current-password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin-new-password">Password Baru *</label>
                        <input type="password" id="admin-new-password" name="new_password" autocomplete="new-password" required minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label for="admin-confirm-password">Konfirmasi Password Baru *</label>
                        <input type="password" id="admin-confirm-password" name="confirm_password" autocomplete="new-password" required minlength="6">
                    </div>
                    
                    <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="fas fa-key"></i> Ubah Password
                    </button>
                </form>
            </div>
            
            <div class="card">
                <h2 class="card-title"><i class="fas fa-info-circle"></i> Informasi Aplikasi</h2>
                
                <table style="width: 100%;">
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #f0f0f0;"><strong>Nama Aplikasi</strong></td>
                        <td style="padding: 10px; border-bottom: 1px solid #f0f0f0;"><?php echo SITE_NAME; ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #f0f0f0;"><strong>Versi</strong></td>
                        <td style="padding: 10px; border-bottom: 1px solid #f0f0f0;">1.0.0</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #f0f0f0;"><strong>PHP Version</strong></td>
                        <td style="padding: 10px; border-bottom: 1px solid #f0f0f0;"><?php echo phpversion(); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #f0f0f0;"><strong>Server</strong></td>
                        <td style="padding: 10px; border-bottom: 1px solid #f0f0f0;"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;"><strong>Tanggal Install</strong></td>
                        <td style="padding: 10px;"><?php echo date('d F Y'); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

=======
<?php
require_once '../config.php';
checkRole(['admin']);

$message = '';
$error = '';

$user_id = $_SESSION['user_id'];

// Handle Change Password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    $user = $conn->query("SELECT password FROM users WHERE id=$user_id")->fetch_assoc();
    
    if (!password_verify($current_password, $user['password'])) {
        $error = "Password saat ini salah!";
    } elseif ($new_password !== $confirm_password) {
        $error = "Konfirmasi password tidak cocok!";
    } elseif (strlen($new_password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $hashed, $user_id);
        
        if ($stmt->execute()) {
            $message = "Password berhasil diubah!";
        } else {
            $error = "Gagal mengubah password!";
        }
    }
}

// Handle Update Profile
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = clean_input($_POST['full_name']);
    $email = clean_input($_POST['email']);
    $phone = clean_input($_POST['phone']);
    
    $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, phone=? WHERE id=?");
    $stmt->bind_param("sssi", $full_name, $email, $phone, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['full_name'] = $full_name;
        $message = "Profil berhasil diupdate!";
    } else {
        $error = "Gagal mengupdate profil!";
    }
}

// Get user info
$user_info = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

// Get system stats
$stats = [
    'total_products' => $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'],
    'total_users' => $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'],
    'total_categories' => $conn->query("SELECT COUNT(*) as count FROM categories")->fetch_assoc()['count'],
    'total_suppliers' => $conn->query("SELECT COUNT(*) as count FROM suppliers")->fetch_assoc()['count'],
    'total_transactions' => $conn->query("SELECT COUNT(*) as count FROM transactions")->fetch_assoc()['count'],
];

// Get database size
$db_name = DB_NAME;
$db_size_query = $conn->query("SELECT SUM(data_length + index_length) as size FROM information_schema.TABLES WHERE table_schema = '$db_name'");
$db_size_bytes = $db_size_query->fetch_assoc()['size'] ?? 0;

// Convert to readable format
if ($db_size_bytes >= 1073741824) {
    $stats['db_size'] = number_format($db_size_bytes / 1073741824, 2) . ' GB';
} elseif ($db_size_bytes >= 1048576) {
    $stats['db_size'] = number_format($db_size_bytes / 1048576, 2) . ' MB';
} elseif ($db_size_bytes >= 1024) {
    $stats['db_size'] = number_format($db_size_bytes / 1024, 2) . ' KB';
} else {
    $stats['db_size'] = $db_size_bytes . ' Bytes';
}

$page_title = 'Pengaturan Sistem';
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
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .card-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        
        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        
        .info-icon {
            font-size: 40px;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .info-value {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .info-label {
            color: #7f8c8d;
            font-size: 14px;
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
                <a href="reports.php" class="menu-item"><i class="fas fa-chart-line"></i><span>Laporan</span></a>
                <a href="settings.php" class="menu-item active"><i class="fas fa-cog"></i><span>Pengaturan</span></a>
            </div>
        </div>
        
        <div class="main-content">
            <div class="top-bar">
                <div>
                    <h1 style="font-size: 28px; color: #2c3e50;"><i class="fas fa-cog"></i> Pengaturan Sistem</h1>
                    <p style="color: #7f8c8d; margin-top: 5px;">Kelola pengaturan dan profil Anda</p>
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
                <h2 class="card-title"><i class="fas fa-chart-bar"></i> Statistik Sistem</h2>
                
                <div class="info-grid">
                    <div class="info-card">
                        <div class="info-icon"><i class="fas fa-box"></i></div>
                        <div class="info-value"><?php echo number_format($stats['total_products']); ?></div>
                        <div class="info-label">Total Produk</div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon"><i class="fas fa-users"></i></div>
                        <div class="info-value"><?php echo number_format($stats['total_users']); ?></div>
                        <div class="info-label">Total Pengguna</div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon"><i class="fas fa-tags"></i></div>
                        <div class="info-value"><?php echo number_format($stats['total_categories']); ?></div>
                        <div class="info-label">Total Kategori</div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon"><i class="fas fa-truck"></i></div>
                        <div class="info-value"><?php echo number_format($stats['total_suppliers']); ?></div>
                        <div class="info-label">Total Supplier</div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon"><i class="fas fa-receipt"></i></div>
                        <div class="info-value"><?php echo number_format($stats['total_transactions']); ?></div>
                        <div class="info-label">Total Transaksi</div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon"><i class="fas fa-database"></i></div>
                        <div class="info-value"><?php echo $stats['db_size']; ?></div>
                        <div class="info-label">Ukuran Database</div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <h2 class="card-title"><i class="fas fa-user"></i> Informasi Profil</h2>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="admin-username">Username</label>
                        <input type="text" id="admin-username" value="<?php echo $user_info['username']; ?>" autocomplete="username" readonly style="background: #f5f5f5;">
                    </div>
                    
                    <div class="form-group">
                        <label for="admin-fullname">Nama Lengkap *</label>
                        <input type="text" id="admin-fullname" name="full_name" value="<?php echo $user_info['full_name']; ?>" autocomplete="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin-email">Email *</label>
                        <input type="email" id="admin-email" name="email" value="<?php echo $user_info['email']; ?>" autocomplete="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin-phone">No. Telepon</label>
                        <input type="tel" id="admin-phone" name="phone" value="<?php echo $user_info['phone']; ?>" autocomplete="tel">
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
            
            <div class="card">
                <h2 class="card-title"><i class="fas fa-lock"></i> Ubah Password</h2>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="admin-current-password">Password Lama *</label>
                        <input type="password" id="admin-current-password" name="current_password" autocomplete="current-password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin-new-password">Password Baru *</label>
                        <input type="password" id="admin-new-password" name="new_password" autocomplete="new-password" required minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label for="admin-confirm-password">Konfirmasi Password Baru *</label>
                        <input type="password" id="admin-confirm-password" name="confirm_password" autocomplete="new-password" required minlength="6">
                    </div>
                    
                    <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="fas fa-key"></i> Ubah Password
                    </button>
                </form>
            </div>
            
            <div class="card">
                <h2 class="card-title"><i class="fas fa-info-circle"></i> Informasi Aplikasi</h2>
                
                <table style="width: 100%;">
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #f0f0f0;"><strong>Nama Aplikasi</strong></td>
                        <td style="padding: 10px; border-bottom: 1px solid #f0f0f0;"><?php echo SITE_NAME; ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #f0f0f0;"><strong>Versi</strong></td>
                        <td style="padding: 10px; border-bottom: 1px solid #f0f0f0;">1.0.0</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #f0f0f0;"><strong>PHP Version</strong></td>
                        <td style="padding: 10px; border-bottom: 1px solid #f0f0f0;"><?php echo phpversion(); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #f0f0f0;"><strong>Server</strong></td>
                        <td style="padding: 10px; border-bottom: 1px solid #f0f0f0;"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;"><strong>Tanggal Install</strong></td>
                        <td style="padding: 10px;"><?php echo date('d F Y'); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

>>>>>>> 882da412c224f7c20dfb67829049d92fbad8991f
<?php require_once '../includes/admin_footer.php'; ?>