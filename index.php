<<<<<<< HEAD
<?php
require_once 'config.php';
require_once 'includes/admin_header.php';

// If already logged in, redirect based on role
if (isLoggedIn()) {
    $role = getUserRole();
    switch ($role) {
        case 'admin':
            redirect('admin/index.php');
            break;
        case 'kasir':
            redirect('kasir/index.php');
            break;
        case 'customer':
            redirect('customer/index.php');
            break;
    }
}

$error = '';
$success = '';

// Handle Login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT id, username, password, full_name, role, status FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if ($user['status'] == 'inactive') {
            $error = "Akun Anda tidak aktif. Hubungi administrator.";
        } elseif (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            
            switch ($user['role']) {
                case 'admin':
                    redirect('admin/index.php');
                    break;
                case 'kasir':
                    redirect('kasir/index.php');
                    break;
                case 'customer':
                    redirect('customer/index.php');
                    break;
            }
        } else {
            $error = "Username atau password salah!";
        }
    } else {
        $error = "Username atau password salah!";
    }
}

// Handle Register
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = clean_input($_POST['reg_username']);
    $email = clean_input($_POST['reg_email']);
    $full_name = clean_input($_POST['reg_fullname']);
    $phone = clean_input($_POST['reg_phone']);
    $password = password_hash($_POST['reg_password'], PASSWORD_DEFAULT);
    
    // Check if username exists
    $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        $error = "Username atau email sudah terdaftar!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, full_name, phone, password, role) VALUES (?, ?, ?, ?, ?, 'customer')");
        $stmt->bind_param("sssss", $username, $email, $full_name, $phone, $password);
        
        if ($stmt->execute()) {
            $success = "Registrasi berhasil! Silakan login.";
        } else {
            $error = "Registrasi gagal! Coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Login</title>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
        
        .left-panel {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .left-panel h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }
        
        .left-panel p {
            font-size: 1.1em;
            line-height: 1.6;
            opacity: 0.9;
        }
        
        .features {
            margin-top: 40px;
        }
        
        .feature {
            display: flex;
            align-items: center;
            margin: 15px 0;
        }
        
        .feature i {
            font-size: 24px;
            margin-right: 15px;
        }
        
        .right-panel {
            padding: 60px 40px;
        }
        
        .tabs {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
        }
        
        .tab {
            padding: 15px 30px;
            cursor: pointer;
            font-weight: 600;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }
        
        .form-content {
            display: none;
        }
        
        .form-content.active {
            display: block;
            animation: fadeIn 0.5s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }
        
        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }
            
            .left-panel {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <h1><i class="fas fa-store"></i> Minimarket</h1>
            <p>Sistem manajemen minimarket terlengkap dengan fitur Point of Sale, inventory management, dan laporan real-time.</p>
            
            <div class="features">
                <div class="feature">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Belanja Online</span>
                </div>
                <div class="feature">
                    <i class="fas fa-cash-register"></i>
                    <span>POS System</span>
                </div>
                <div class="feature">
                    <i class="fas fa-chart-line"></i>
                    <span>Laporan & Analitik</span>
                </div>
                <div class="feature">
                    <i class="fas fa-boxes"></i>
                    <span>Manajemen Stok</span>
                </div>
            </div>
        </div>
        
        <div class="right-panel">
            <div class="tabs">
                <div class="tab active" onclick="showTab('login')">Login</div>
                <div class="tab" onclick="showTab('register')">Register</div>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <div id="login" class="form-content active">
                <form method="POST">
                    <div class="form-group">
                        <label for="login-username"><i class="fas fa-user"></i> Username atau Email</label>
                        <input type="text" id="login-username" name="username" autocomplete="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="login-password"><i class="fas fa-lock"></i> Password</label>
                        <input type="password" id="login-password" name="password" autocomplete="current-password" required>
                    </div>
                    
                    <button type="submit" name="login" class="btn">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>
                
                <p style="margin-top: 20px; text-align: center; color: #666;">
                    Demo: admin/password, kasir1/password, customer1/password
                </p>
                <br />
                <div style="font-size: 12px; color: #aaa; text-align: center;">
                    &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.
                </div>
                <br />
                <!-- link for check syntax and data and test connection -->
                <div style="font-size: 12px; color: #aaa; text-align: center;">
                    <a href="./testing/check_syntax.php" target="_blank">Check Syntax</a> | <a href="./testing/check_data.php" target="_blank">Check Data</a> | <a href="./testing/test_connection.php" target="_blank">Test Connection</a> | <a href="./testing/icon_generator.html" target="_blank">Icon Generator</a>
                </div>
            </div>
            
            <!-- Register Form -->
            <div id="register" class="form-content">
                <form method="POST">
                    <div class="form-group">
                        <label for="reg-username"><i class="fas fa-user"></i> Username</label>
                        <input type="text" id="reg-username" name="reg_username" autocomplete="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="reg-email"><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" id="reg-email" name="reg_email" autocomplete="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="reg-fullname"><i class="fas fa-id-card"></i> Nama Lengkap</label>
                        <input type="text" id="reg-fullname" name="reg_fullname" autocomplete="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="reg-phone"><i class="fas fa-phone"></i> No. Telepon</label>
                        <input type="tel" id="reg-phone" name="reg_phone" autocomplete="tel" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="reg-password"><i class="fas fa-lock"></i> Password</label>
                        <input type="password" id="reg-password" name="reg_password" autocomplete="new-password" required>
                    </div>
                    
                    <button type="submit" name="register" class="btn">
                        <i class="fas fa-user-plus"></i> Register
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function showTab(tabName) {
            const tabs = document.querySelectorAll('.tab');
            const contents = document.querySelectorAll('.form-content');
            
            tabs.forEach(tab => tab.classList.remove('active'));
            contents.forEach(content => content.classList.remove('active'));
            
            event.target.classList.add('active');
            document.getElementById(tabName).classList.add('active');
        }
    </script>
</body>
=======
<?php
require_once 'config.php';
require_once 'includes/admin_header.php';

// If already logged in, redirect based on role
if (isLoggedIn()) {
    $role = getUserRole();
    switch ($role) {
        case 'admin':
            redirect('admin/index.php');
            break;
        case 'kasir':
            redirect('kasir/index.php');
            break;
        case 'customer':
            redirect('customer/index.php');
            break;
    }
}

$error = '';
$success = '';

// Handle Login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT id, username, password, full_name, role, status FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if ($user['status'] == 'inactive') {
            $error = "Akun Anda tidak aktif. Hubungi administrator.";
        } elseif (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            
            switch ($user['role']) {
                case 'admin':
                    redirect('admin/index.php');
                    break;
                case 'kasir':
                    redirect('kasir/index.php');
                    break;
                case 'customer':
                    redirect('customer/index.php');
                    break;
            }
        } else {
            $error = "Username atau password salah!";
        }
    } else {
        $error = "Username atau password salah!";
    }
}

// Handle Register
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = clean_input($_POST['reg_username']);
    $email = clean_input($_POST['reg_email']);
    $full_name = clean_input($_POST['reg_fullname']);
    $phone = clean_input($_POST['reg_phone']);
    $password = password_hash($_POST['reg_password'], PASSWORD_DEFAULT);
    
    // Check if username exists
    $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        $error = "Username atau email sudah terdaftar!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, full_name, phone, password, role) VALUES (?, ?, ?, ?, ?, 'customer')");
        $stmt->bind_param("sssss", $username, $email, $full_name, $phone, $password);
        
        if ($stmt->execute()) {
            $success = "Registrasi berhasil! Silakan login.";
        } else {
            $error = "Registrasi gagal! Coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Login</title>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
        
        .left-panel {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .left-panel h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }
        
        .left-panel p {
            font-size: 1.1em;
            line-height: 1.6;
            opacity: 0.9;
        }
        
        .features {
            margin-top: 40px;
        }
        
        .feature {
            display: flex;
            align-items: center;
            margin: 15px 0;
        }
        
        .feature i {
            font-size: 24px;
            margin-right: 15px;
        }
        
        .right-panel {
            padding: 60px 40px;
        }
        
        .tabs {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
        }
        
        .tab {
            padding: 15px 30px;
            cursor: pointer;
            font-weight: 600;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }
        
        .form-content {
            display: none;
        }
        
        .form-content.active {
            display: block;
            animation: fadeIn 0.5s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }
        
        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }
            
            .left-panel {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <h1><i class="fas fa-store"></i> Minimarket</h1>
            <p>Sistem manajemen minimarket terlengkap dengan fitur Point of Sale, inventory management, dan laporan real-time.</p>
            
            <div class="features">
                <div class="feature">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Belanja Online</span>
                </div>
                <div class="feature">
                    <i class="fas fa-cash-register"></i>
                    <span>POS System</span>
                </div>
                <div class="feature">
                    <i class="fas fa-chart-line"></i>
                    <span>Laporan & Analitik</span>
                </div>
                <div class="feature">
                    <i class="fas fa-boxes"></i>
                    <span>Manajemen Stok</span>
                </div>
            </div>
        </div>
        
        <div class="right-panel">
            <div class="tabs">
                <div class="tab active" onclick="showTab('login')">Login</div>
                <div class="tab" onclick="showTab('register')">Register</div>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <div id="login" class="form-content active">
                <form method="POST">
                    <div class="form-group">
                        <label for="login-username"><i class="fas fa-user"></i> Username atau Email</label>
                        <input type="text" id="login-username" name="username" autocomplete="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="login-password"><i class="fas fa-lock"></i> Password</label>
                        <input type="password" id="login-password" name="password" autocomplete="current-password" required>
                    </div>
                    
                    <button type="submit" name="login" class="btn">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>
                
                <p style="margin-top: 20px; text-align: center; color: #666;">
                    Demo: admin/password, kasir1/password, customer1/password
                </p>
                <br />
                <div style="font-size: 12px; color: #aaa; text-align: center;">
                    &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.
                </div>
                <br />
                <!-- link for check syntax and data and test connection -->
                <div style="font-size: 12px; color: #aaa; text-align: center;">
                    <a href="./testing/check_syntax.php" target="_blank">Check Syntax</a> | <a href="./testing/check_data.php" target="_blank">Check Data</a> | <a href="./testing/test_connection.php" target="_blank">Test Connection</a> | <a href="./testing/icon_generator.html" target="_blank">Icon Generator</a>
                </div>
            </div>
            
            <!-- Register Form -->
            <div id="register" class="form-content">
                <form method="POST">
                    <div class="form-group">
                        <label for="reg-username"><i class="fas fa-user"></i> Username</label>
                        <input type="text" id="reg-username" name="reg_username" autocomplete="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="reg-email"><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" id="reg-email" name="reg_email" autocomplete="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="reg-fullname"><i class="fas fa-id-card"></i> Nama Lengkap</label>
                        <input type="text" id="reg-fullname" name="reg_fullname" autocomplete="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="reg-phone"><i class="fas fa-phone"></i> No. Telepon</label>
                        <input type="tel" id="reg-phone" name="reg_phone" autocomplete="tel" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="reg-password"><i class="fas fa-lock"></i> Password</label>
                        <input type="password" id="reg-password" name="reg_password" autocomplete="new-password" required>
                    </div>
                    
                    <button type="submit" name="register" class="btn">
                        <i class="fas fa-user-plus"></i> Register
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function showTab(tabName) {
            const tabs = document.querySelectorAll('.tab');
            const contents = document.querySelectorAll('.form-content');
            
            tabs.forEach(tab => tab.classList.remove('active'));
            contents.forEach(content => content.classList.remove('active'));
            
            event.target.classList.add('active');
            document.getElementById(tabName).classList.add('active');
        }
    </script>
</body>
>>>>>>> 882da412c224f7c20dfb67829049d92fbad8991f
</html>