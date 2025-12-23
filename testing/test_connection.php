<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Koneksi - Minimarket System</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0"><i class="fas fa-check-circle"></i> System Connection Test</h3>
                    </div>
                    <div class="card-body">
                        <h5 class="mb-4">Testing Koneksi Sistem Minimarket</h5>
                        
                        <?php
                        $tests = [];
                        
                        // Test 1: Config File
                        if (file_exists('../config.php')) {
                            $tests[] = ['name' => 'File config.php', 'status' => true, 'message' => 'File ditemukan'];
                            require_once '../config.php';
                        } else {
                            $tests[] = ['name' => 'File config.php', 'status' => false, 'message' => 'File tidak ditemukan'];
                        }
                        
                        // Test 2: Database Connection
                        if (isset($conn) && $conn->ping()) {
                            $tests[] = ['name' => 'Database Connection', 'status' => true, 'message' => 'Terhubung ke database: ' . DB_NAME];
                        } else {
                            $tests[] = ['name' => 'Database Connection', 'status' => false, 'message' => 'Gagal terhubung ke database'];
                        }
                        
                        // Test 3: Check Tables
                        $required_tables = ['users', 'products', 'categories', 'suppliers', 'transactions', 'orders'];
                        $existing_tables = [];
                        
                        if (isset($conn)) {
                            $result = $conn->query("SHOW TABLES");
                            while ($row = $result->fetch_array()) {
                                $existing_tables[] = $row[0];
                            }
                            
                            $missing_tables = array_diff($required_tables, $existing_tables);
                            if (empty($missing_tables)) {
                                $tests[] = ['name' => 'Database Tables', 'status' => true, 'message' => count($existing_tables) . ' tabel ditemukan'];
                            } else {
                                $tests[] = ['name' => 'Database Tables', 'status' => false, 'message' => 'Tabel kurang: ' . implode(', ', $missing_tables)];
                            }
                        }
                        
                        // Test 4: Functions File
                        if (file_exists('../includes/functions.php')) {
                            require_once '../includes/functions.php';
                            $tests[] = ['name' => 'Functions File', 'status' => true, 'message' => 'File functions.php tersedia'];
                        } else {
                            $tests[] = ['name' => 'Functions File', 'status' => false, 'message' => 'File functions.php tidak ditemukan'];
                        }
                        
                        // Test 5: Upload Folder
                        $upload_path = '../assets/images/products/';
                        if (file_exists($upload_path)) {
                            if (is_writable($upload_path)) {
                                $tests[] = ['name' => 'Upload Folder', 'status' => true, 'message' => 'Folder writable'];
                            } else {
                                $tests[] = ['name' => 'Upload Folder', 'status' => false, 'message' => 'Folder tidak writable'];
                            }
                        } else {
                            $tests[] = ['name' => 'Upload Folder', 'status' => false, 'message' => 'Folder tidak ditemukan'];
                        }
                        
                        // Test 6: CSS Files
                        $css_files = ['../assets/css/style.css', '../assets/css/responsive.css'];
                        $css_found = 0;
                        foreach ($css_files as $css) {
                            if (file_exists($css)) $css_found++;
                        }
                        $tests[] = ['name' => 'CSS Files', 'status' => ($css_found == count($css_files)), 'message' => $css_found . '/' . count($css_files) . ' file ditemukan'];
                        
                        // Test 7: JavaScript Files
                        $js_files = ['../assets/js/main.js'];
                        $js_found = 0;
                        foreach ($js_files as $js) {
                            if (file_exists($js)) $js_found++;
                        }
                        $tests[] = ['name' => 'JavaScript Files', 'status' => ($js_found == count($js_files)), 'message' => $js_found . '/' . count($js_files) . ' file ditemukan'];
                        
                        // Test 8: Include Files
                        $include_files = ['../includes/header.php', '../includes/footer.php', '../includes/sidebar.php'];
                        $include_found = 0;
                        foreach ($include_files as $inc) {
                            if (file_exists($inc)) $include_found++;
                        }
                        $tests[] = ['name' => 'Include Files', 'status' => ($include_found == count($include_files)), 'message' => $include_found . '/' . count($include_files) . ' file ditemukan'];
                        
                        // Test 9: Check Sample Data
                        if (isset($conn)) {
                            $user_count = $conn->query("SELECT COUNT(*) as cnt FROM users")->fetch_assoc()['cnt'];
                            $product_count = $conn->query("SELECT COUNT(*) as cnt FROM products")->fetch_assoc()['cnt'];
                            $category_count = $conn->query("SELECT COUNT(*) as cnt FROM categories")->fetch_assoc()['cnt'];
                            
                            $tests[] = ['name' => 'Sample Data', 'status' => true, 'message' => "$user_count users, $product_count produk, $category_count kategori"];
                        }
                        
                        // Test 10: Session
                        if (session_status() === PHP_SESSION_ACTIVE) {
                            $tests[] = ['name' => 'PHP Session', 'status' => true, 'message' => 'Session aktif'];
                        } else {
                            $tests[] = ['name' => 'PHP Session', 'status' => false, 'message' => 'Session tidak aktif'];
                        }
                        
                        // Display Results
                        echo '<div class="table-responsive">';
                        echo '<table class="table table-bordered">';
                        echo '<thead class="table-dark">';
                        echo '<tr><th width="40">#</th><th>Test</th><th>Status</th><th>Message</th></tr>';
                        echo '</thead><tbody>';
                        
                        $success_count = 0;
                        foreach ($tests as $i => $test) {
                            if ($test['status']) $success_count++;
                            
                            $status_icon = $test['status'] ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>';
                            $status_text = $test['status'] ? '<span class="badge bg-success">PASS</span>' : '<span class="badge bg-danger">FAIL</span>';
                            
                            echo '<tr>';
                            echo '<td>' . ($i + 1) . '</td>';
                            echo '<td>' . htmlspecialchars($test['name']) . '</td>';
                            echo '<td class="text-center">' . $status_icon . ' ' . $status_text . '</td>';
                            echo '<td>' . htmlspecialchars($test['message']) . '</td>';
                            echo '</tr>';
                        }
                        
                        echo '</tbody></table>';
                        echo '</div>';
                        
                        // Summary
                        $total_tests = count($tests);
                        $percentage = round(($success_count / $total_tests) * 100);
                        
                        echo '<div class="alert alert-' . ($percentage == 100 ? 'success' : ($percentage >= 70 ? 'warning' : 'danger')) . ' mt-4">';
                        echo '<h5><i class="fas fa-chart-pie"></i> Summary</h5>';
                        echo '<p class="mb-0">Test Result: <strong>' . $success_count . '/' . $total_tests . '</strong> (' . $percentage . '%)</p>';
                        
                        if ($percentage == 100) {
                            echo '<hr>';
                            echo '<p class="mb-0"><i class="fas fa-thumbs-up"></i> <strong>Semua test berhasil! Sistem siap digunakan.</strong></p>';
                            echo '<p class="mt-2 mb-0">Silakan login di: <a href="../index.php" class="alert-link">index.php</a></p>';
                        } else {
                            echo '<hr>';
                            echo '<p class="mb-0"><i class="fas fa-exclamation-triangle"></i> Ada beberapa masalah yang perlu diperbaiki.</p>';
                        }
                        echo '</div>';
                        
                        // System Info
                        if (isset($conn)) {
                            echo '<div class="card mt-4">';
                            echo '<div class="card-header bg-info text-white">';
                            echo '<h6 class="mb-0"><i class="fas fa-info-circle"></i> System Information</h6>';
                            echo '</div>';
                            echo '<div class="card-body">';
                            echo '<div class="row">';
                            echo '<div class="col-md-6">';
                            echo '<p><strong>PHP Version:</strong> ' . phpversion() . '</p>';
                            echo '<p><strong>Database:</strong> ' . DB_NAME . '</p>';
                            echo '<p><strong>Base URL:</strong> ' . BASE_URL . '</p>';
                            echo '</div>';
                            echo '<div class="col-md-6">';
                            echo '<p><strong>MySQL Version:</strong> ' . $conn->server_info . '</p>';
                            echo '<p><strong>Server:</strong> ' . $_SERVER['SERVER_SOFTWARE'] . '</p>';
                            echo '<p><strong>Document Root:</strong> ' . $_SERVER['DOCUMENT_ROOT'] . '</p>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                        ?>
                        
                        <div class="mt-4 text-center">
                            <a href="../index.php" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i> Go to Login Page
                            </a>
                            <a href="./DOKUMENTASI_LENGKAP.html" class="btn btn-secondary" target="_blank">
                                <i class="fas fa-book"></i> View Documentation
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4 shadow">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-key"></i> Login Credentials</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6 class="text-primary">Admin</h6>
                                <p class="mb-1"><strong>Username:</strong> admin</p>
                                <p class="mb-1"><strong>Password:</strong> password</p>
                                <p class="text-muted small">Full access to system</p>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-success">Kasir</h6>
                                <p class="mb-1"><strong>Username:</strong> kasir1</p>
                                <p class="mb-1"><strong>Password:</strong> password</p>
                                <p class="text-muted small">POS & Transactions</p>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-info">Customer</h6>
                                <p class="mb-1"><strong>Username:</strong> customer1</p>
                                <p class="mb-1"><strong>Password:</strong> password</p>
                                <p class="text-muted small">Shopping & Orders</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="./assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>