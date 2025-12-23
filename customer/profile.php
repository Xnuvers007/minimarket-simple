<?php
require_once '../config.php';
checkRole(['customer']);

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle Update Profile
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = clean_input($_POST['full_name']);
    $email = clean_input($_POST['email']);
    $phone = clean_input($_POST['phone']);
    $address = clean_input($_POST['address']);
    
    $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, phone=?, address=? WHERE id=?");
    $stmt->bind_param("ssssi", $full_name, $email, $phone, $address, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['full_name'] = $full_name;
        $message = "Profil berhasil diupdate!";
    } else {
        $error = "Gagal mengupdate profil!";
    }
}

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

// Get user info
$user_info = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

$page_title = 'Profil Saya';
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
        
        .container { max-width: 900px; margin: 30px auto; padding: 0 30px; }
        
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
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
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
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            font-size: 15px;
        }
        
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 40px;
            font-weight: bold;
            margin: 0 auto 20px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .info-grid .form-group:last-child {
            grid-column: 1 / -1;
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
        <?php if($message): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="page-header">
            <h1><i class="fas fa-user-circle"></i> Profil Saya</h1>
            <p style="color: #7f8c8d; margin-top: 5px;">Kelola informasi profil dan keamanan akun Anda</p>
        </div>
        
        <div class="card">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($user_info['full_name'], 0, 2)); ?>
            </div>
            
            <h2 class="card-title"><i class="fas fa-user"></i> Informasi Profil</h2>
            
            <form method="POST">
                <div class="info-grid">
                    <div class="form-group">
                        <label for="profile-fullname">Nama Lengkap *</label>
                        <input type="text" id="profile-fullname" name="full_name" value="<?php echo $user_info['full_name']; ?>" autocomplete="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="profile-username">Username</label>
                        <input type="text" id="profile-username" value="<?php echo $user_info['username']; ?>" autocomplete="username" readonly style="background: #f5f5f5;">
                    </div>
                    
                    <div class="form-group">
                        <label for="profile-email">Email *</label>
                        <input type="email" id="profile-email" name="email" value="<?php echo $user_info['email']; ?>" autocomplete="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="profile-phone">No. Telepon *</label>
                        <input type="tel" id="profile-phone" name="phone" value="<?php echo $user_info['phone']; ?>" autocomplete="tel" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea name="address"><?php echo $user_info['address']; ?></textarea>
                    </div>
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
                    <label for="current-password">Password Lama *</label>
                    <input type="password" id="current-password" name="current_password" autocomplete="current-password" required>
                </div>
                
                <div class="form-group">
                    <label for="new-password">Password Baru *</label>
                    <input type="password" id="new-password" name="new_password" autocomplete="new-password" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label for="confirm-password">Konfirmasi Password Baru *</label>
                    <input type="password" id="confirm-password" name="confirm_password" autocomplete="new-password" required minlength="6">
                </div>
                
                <button type="submit" name="change_password" class="btn btn-primary">
                    <i class="fas fa-key"></i> Ubah Password
                </button>
            </form>
        </div>
    </div>

<?php require_once '../includes/admin_footer.php'; ?>