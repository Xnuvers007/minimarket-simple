    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-store"></i> Sistem Toko</h5>
                    <p>Sistem manajemen toko yang lengkap untuk mengelola produk, stok, penjualan, dan laporan.</p>
                </div>
                <div class="col-md-4">
                    <h5>Link Cepat</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo BASE_URL; ?>" class="text-white-50 text-decoration-none">Home</a></li>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'customer'): ?>
                            <li><a href="<?php echo BASE_URL; ?>customer/shop.php" class="text-white-50 text-decoration-none">Shop</a></li>
                            <li><a href="<?php echo BASE_URL; ?>customer/cart.php" class="text-white-50 text-decoration-none">Keranjang</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Kontak</h5>
                    <p class="text-white-50">
                        <i class="fas fa-envelope"></i> info@tokoku.com<br>
                        <i class="fas fa-phone"></i> +62 123 4567 890
                    </p>
                </div>
            </div>
            <hr class="bg-white">
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Sistem Manajemen Toko. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="./assets/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="./assets/js/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
    
    <?php if (isset($include_chart) && $include_chart): ?>
        <script src="<?php echo BASE_URL; ?>assets/js/chart.min.js"></script>
    <?php endif; ?>
</body>
</html>