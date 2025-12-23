<<<<<<< HEAD
<?php
// Mendapatkan current page untuk active menu
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div class="sidebar-header">
        <h4><i class="fas fa-bars"></i> Menu</h4>
    </div>
    
    <ul class="sidebar-menu">
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
            <!-- Admin Menu -->
            <li class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/index.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="<?php echo ($current_page == 'products.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/products.php">
                    <i class="fas fa-box"></i> Produk
                </a>
            </li>
            <li class="<?php echo ($current_page == 'categories.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/categories.php">
                    <i class="fas fa-tags"></i> Kategori
                </a>
            </li>
            <li class="<?php echo ($current_page == 'stock.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/stock.php">
                    <i class="fas fa-warehouse"></i> Stok
                </a>
            </li>
            <li class="<?php echo ($current_page == 'suppliers.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/suppliers.php">
                    <i class="fas fa-truck"></i> Supplier
                </a>
            </li>
            <li class="<?php echo ($current_page == 'users.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/users.php">
                    <i class="fas fa-users"></i> Pengguna
                </a>
            </li>
            <li class="<?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/reports.php">
                    <i class="fas fa-chart-bar"></i> Laporan
                </a>
            </li>
            <li class="<?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/settings.php">
                    <i class="fas fa-cog"></i> Pengaturan
                </a>
            </li>
            
        <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] == 'kasir'): ?>
            <!-- Kasir Menu -->
            <li class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>kasir/index.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="<?php echo ($current_page == 'pos.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>kasir/pos.php">
                    <i class="fas fa-cash-register"></i> POS
                </a>
            </li>
            <li class="<?php echo ($current_page == 'transactions.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>kasir/transactions.php">
                    <i class="fas fa-receipt"></i> Transaksi
                </a>
            </li>
            
        <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] == 'customer'): ?>
            <!-- Customer Menu -->
            <li class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>customer/index.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="<?php echo ($current_page == 'shop.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>customer/shop.php">
                    <i class="fas fa-shopping-bag"></i> Belanja
                </a>
            </li>
            <li class="<?php echo ($current_page == 'cart.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>customer/cart.php">
                    <i class="fas fa-shopping-cart"></i> Keranjang
                </a>
            </li>
            <li class="<?php echo ($current_page == 'orders.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>customer/orders.php">
                    <i class="fas fa-box"></i> Pesanan Saya
                </a>
            </li>
            <li class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>customer/profile.php">
                    <i class="fas fa-user"></i> Profile
                </a>
            </li>
        <?php endif; ?>
        
        <!-- Logout untuk semua role -->
        <li>
            <a href="<?php echo BASE_URL; ?>logout.php" class="text-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
=======
<?php
// Mendapatkan current page untuk active menu
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div class="sidebar-header">
        <h4><i class="fas fa-bars"></i> Menu</h4>
    </div>
    
    <ul class="sidebar-menu">
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
            <!-- Admin Menu -->
            <li class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/index.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="<?php echo ($current_page == 'products.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/products.php">
                    <i class="fas fa-box"></i> Produk
                </a>
            </li>
            <li class="<?php echo ($current_page == 'categories.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/categories.php">
                    <i class="fas fa-tags"></i> Kategori
                </a>
            </li>
            <li class="<?php echo ($current_page == 'stock.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/stock.php">
                    <i class="fas fa-warehouse"></i> Stok
                </a>
            </li>
            <li class="<?php echo ($current_page == 'suppliers.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/suppliers.php">
                    <i class="fas fa-truck"></i> Supplier
                </a>
            </li>
            <li class="<?php echo ($current_page == 'users.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/users.php">
                    <i class="fas fa-users"></i> Pengguna
                </a>
            </li>
            <li class="<?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/reports.php">
                    <i class="fas fa-chart-bar"></i> Laporan
                </a>
            </li>
            <li class="<?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/settings.php">
                    <i class="fas fa-cog"></i> Pengaturan
                </a>
            </li>
            
        <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] == 'kasir'): ?>
            <!-- Kasir Menu -->
            <li class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>kasir/index.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="<?php echo ($current_page == 'pos.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>kasir/pos.php">
                    <i class="fas fa-cash-register"></i> POS
                </a>
            </li>
            <li class="<?php echo ($current_page == 'transactions.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>kasir/transactions.php">
                    <i class="fas fa-receipt"></i> Transaksi
                </a>
            </li>
            
        <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] == 'customer'): ?>
            <!-- Customer Menu -->
            <li class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>customer/index.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="<?php echo ($current_page == 'shop.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>customer/shop.php">
                    <i class="fas fa-shopping-bag"></i> Belanja
                </a>
            </li>
            <li class="<?php echo ($current_page == 'cart.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>customer/cart.php">
                    <i class="fas fa-shopping-cart"></i> Keranjang
                </a>
            </li>
            <li class="<?php echo ($current_page == 'orders.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>customer/orders.php">
                    <i class="fas fa-box"></i> Pesanan Saya
                </a>
            </li>
            <li class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>customer/profile.php">
                    <i class="fas fa-user"></i> Profile
                </a>
            </li>
        <?php endif; ?>
        
        <!-- Logout untuk semua role -->
        <li>
            <a href="<?php echo BASE_URL; ?>logout.php" class="text-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
>>>>>>> 882da412c224f7c20dfb67829049d92fbad8991f
</div>