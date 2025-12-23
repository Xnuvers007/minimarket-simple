<<<<<<< HEAD
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Sistem Manajemen Toko</title>
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#667eea">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Minimarket">
    <meta name="description" content="Sistem Informasi Minimarket Sejahtera - Admin, Kasir & Customer">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="<?php echo BASE_URL; ?>manifest.json">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" sizes="72x72" href="<?php echo BASE_URL; ?>assets/images/icon-72x72.png">
    <link rel="apple-touch-icon" sizes="96x96" href="<?php echo BASE_URL; ?>assets/images/icon-96x96.png">
    <link rel="apple-touch-icon" sizes="128x128" href="<?php echo BASE_URL; ?>assets/images/icon-128x128.png">
    <link rel="apple-touch-icon" sizes="144x144" href="<?php echo BASE_URL; ?>assets/images/icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="<?php echo BASE_URL; ?>assets/images/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="192x192" href="<?php echo BASE_URL; ?>assets/images/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="384x384" href="<?php echo BASE_URL; ?>assets/images/icon-384x384.png">
    <link rel="apple-touch-icon" sizes="512x512" href="<?php echo BASE_URL; ?>assets/images/icon-512x512.png">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo BASE_URL; ?>assets/images/icon-72x72.png">
    
    <!-- Bootstrap CSS -->
    <link href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/responsive.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <i class="fas fa-store"></i> Toko Management
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>customer/profile.php">
                                    <i class="fas fa-user-edit"></i> Profile
                                </a></li>
                                <?php if ($_SESSION['role'] == 'admin'): ?>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>admin/">
                                        <i class="fas fa-cog"></i> Admin Panel
                                    </a></li>
                                <?php elseif ($_SESSION['role'] == 'kasir'): ?>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>kasir/">
                                        <i class="fas fa-cash-register"></i> Kasir Panel
                                    </a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>index.php">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?php echo BASE_URL; ?>service-worker.js')
                    .then(registration => {
                        console.log('[PWA] Service Worker registered successfully:', registration.scope);
                    })
                    .catch(error => {
                        console.log('[PWA] Service Worker registration failed:', error);
                    });
            });
        }
    </script>
    
    <!-- PWA Install Script -->
=======
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Sistem Manajemen Toko</title>
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#667eea">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Minimarket">
    <meta name="description" content="Sistem Informasi Minimarket Sejahtera - Admin, Kasir & Customer">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="<?php echo BASE_URL; ?>manifest.json">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" sizes="72x72" href="<?php echo BASE_URL; ?>assets/images/icon-72x72.png">
    <link rel="apple-touch-icon" sizes="96x96" href="<?php echo BASE_URL; ?>assets/images/icon-96x96.png">
    <link rel="apple-touch-icon" sizes="128x128" href="<?php echo BASE_URL; ?>assets/images/icon-128x128.png">
    <link rel="apple-touch-icon" sizes="144x144" href="<?php echo BASE_URL; ?>assets/images/icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="<?php echo BASE_URL; ?>assets/images/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="192x192" href="<?php echo BASE_URL; ?>assets/images/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="384x384" href="<?php echo BASE_URL; ?>assets/images/icon-384x384.png">
    <link rel="apple-touch-icon" sizes="512x512" href="<?php echo BASE_URL; ?>assets/images/icon-512x512.png">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo BASE_URL; ?>assets/images/icon-72x72.png">
    
    <!-- Bootstrap CSS -->
    <link href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/responsive.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <i class="fas fa-store"></i> Toko Management
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>customer/profile.php">
                                    <i class="fas fa-user-edit"></i> Profile
                                </a></li>
                                <?php if ($_SESSION['role'] == 'admin'): ?>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>admin/">
                                        <i class="fas fa-cog"></i> Admin Panel
                                    </a></li>
                                <?php elseif ($_SESSION['role'] == 'kasir'): ?>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>kasir/">
                                        <i class="fas fa-cash-register"></i> Kasir Panel
                                    </a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>index.php">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?php echo BASE_URL; ?>service-worker.js')
                    .then(registration => {
                        console.log('[PWA] Service Worker registered successfully:', registration.scope);
                    })
                    .catch(error => {
                        console.log('[PWA] Service Worker registration failed:', error);
                    });
            });
        }
    </script>
    
    <!-- PWA Install Script -->
>>>>>>> 882da412c224f7c20dfb67829049d92fbad8991f
    <script src="<?php echo BASE_URL; ?>pwa-install.js"></script>