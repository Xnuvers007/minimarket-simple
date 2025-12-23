<?php
require_once '../config.php';
checkRole(['admin']);

$message = '';
$error = '';

// Handle Add/Edit User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_user'])) {
    $user_id = $_POST['user_id'] ?? null;
    $username = clean_input($_POST['username']);
    $email = clean_input($_POST['email']);
    $full_name = clean_input($_POST['full_name']);
    $phone = clean_input($_POST['phone']);
    $role = $_POST['role'];
    $status = $_POST['status'];
    $address = clean_input($_POST['address']);
    
    if ($user_id) {
        // Update
        $stmt = $conn->prepare("UPDATE users SET username=?, email=?, full_name=?, phone=?, role=?, status=?, address=? WHERE id=?");
        $stmt->bind_param("ssssssi", $username, $email, $full_name, $phone, $role, $status, $address, $user_id);
        $message = "User berhasil diupdate!";
    } else {
        // Check if username exists
        $check = $conn->prepare("SELECT id FROM users WHERE username=? OR email=?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            $error = "Username atau email sudah terdaftar!";
        } else {
            // Insert with default password
            $password = password_hash('password', PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, phone, role, status, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $username, $email, $password, $full_name, $phone, $role, $status, $address);
            $message = "User berhasil ditambahkan! Password default: password";
        }
    }
    
    if (empty($error) && isset($stmt)) {
        if (!$stmt->execute()) {
            $error = "Gagal menyimpan user!";
            $message = '';
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    
    // Don't allow deleting yourself
    if ($user_id == $_SESSION['user_id']) {
        $error = "Tidak dapat menghapus akun sendiri!";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $message = "User berhasil dihapus!";
        } else {
            $error = "Gagal menghapus user!";
        }
    }
}

// Get users
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");

// Get user for edit
$edit_user = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_user = $stmt->get_result()->fetch_assoc();
}

$page_title = 'Manajemen User';
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
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-secondary { background: #e2e3e5; color: #383d41; }
        
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
                <a href="users.php" class="menu-item active"><i class="fas fa-users"></i><span>Pengguna</span></a>
                <a href="reports.php" class="menu-item"><i class="fas fa-chart-line"></i><span>Laporan</span></a>
                <a href="settings.php" class="menu-item"><i class="fas fa-cog"></i><span>Pengaturan</span></a>
            </div>
        </div>
        
        <div class="main-content">
            <div class="top-bar">
                <div>
                    <h1 style="font-size: 28px; color: #2c3e50;"><i class="fas fa-users"></i> Manajemen Pengguna</h1>
                    <p style="color: #7f8c8d; margin-top: 5px;">Kelola akun admin, kasir, dan customer</p>
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
                    <h2>Daftar Pengguna</h2>
                    <button class="btn btn-success" onclick="openModal()">
                        <i class="fas fa-plus"></i> Tambah User
                    </button>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo $user['username']; ?></strong></td>
                            <td><?php echo $user['full_name']; ?></td>
                            <td><?php echo $user['email']; ?></td>
                            <td>
                                <?php 
                                $role_badges = [
                                    'admin' => 'badge-danger',
                                    'kasir' => 'badge-info',
                                    'customer' => 'badge-secondary'
                                ];
                                ?>
                                <span class="badge <?php echo $role_badges[$user['role']]; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if($user['status'] == 'active'): ?>
                                    <span class="badge badge-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <a href="?edit=<?php echo $user['id']; ?>" class="btn btn-warning" style="padding: 5px 10px; font-size: 12px;">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if($user['id'] != $_SESSION['user_id']): ?>
                                <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;" onclick="return confirm('Yakin ingin menghapus?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="modal <?php echo $edit_user ? 'active' : ''; ?>" id="userModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><?php echo $edit_user ? 'Edit User' : 'Tambah User'; ?></h2>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            
            <form method="POST">
                <input type="hidden" name="user_id" value="<?php echo $edit_user['user_id'] ?? ''; ?>">
                
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" value="<?php echo $edit_user['username'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" value="<?php echo $edit_user['email'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Nama Lengkap *</label>
                    <input type="text" name="full_name" value="<?php echo $edit_user['full_name'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>No. Telepon</label>
                    <input type="tel" name="phone" value="<?php echo $edit_user['phone'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Role *</label>
                    <select name="role" required>
                        <option value="customer" <?php echo ($edit_user['role'] ?? '') == 'customer' ? 'selected' : ''; ?>>Customer</option>
                        <option value="kasir" <?php echo ($edit_user['role'] ?? '') == 'kasir' ? 'selected' : ''; ?>>Kasir</option>
                        <option value="admin" <?php echo ($edit_user['role'] ?? '') == 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" required>
                        <option value="active" <?php echo ($edit_user['status'] ?? 'active') == 'active' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="inactive" <?php echo ($edit_user['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>Nonaktif</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="address"><?php echo $edit_user['address'] ?? ''; ?></textarea>
                </div>
                
                <?php if(!$edit_user): ?>
                <p style="background: #fff3cd; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 14px;">
                    <i class="fas fa-info-circle"></i> Password default: <strong>password</strong>
                </p>
                <?php endif; ?>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" name="save_user" class="btn btn-success">
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
            document.getElementById('userModal').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('userModal').classList.remove('active');
            if (window.location.search.includes('edit')) {
                window.location.href = 'users.php';
            }
        }
    </script>

<?php require_once '../includes/admin_footer.php'; ?>