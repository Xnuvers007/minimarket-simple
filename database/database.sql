-- =============================================
-- Database Minimarket System
-- =============================================

-- Drop database jika sudah ada (opsional)
-- DROP DATABASE IF EXISTS minimarket_db;

-- Buat database
CREATE DATABASE IF NOT EXISTS minimarket_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE minimarket_db;

-- =============================================
-- Tabel Users
-- =============================================
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'kasir', 'customer') NOT NULL,
    address TEXT,
    photo VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tabel Categories
-- =============================================
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category_name (category_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tabel Suppliers
-- =============================================
CREATE TABLE IF NOT EXISTS suppliers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    supplier_name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_supplier_name (supplier_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tabel Products
-- =============================================
CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT,
    supplier_id INT,
    product_name VARCHAR(200) NOT NULL,
    sku VARCHAR(50) UNIQUE NOT NULL,
    barcode VARCHAR(100),
    description TEXT,
    price DECIMAL(15,2) NOT NULL,
    cost_price DECIMAL(15,2),
    stock INT DEFAULT 0,
    min_stock INT DEFAULT 10,
    unit VARCHAR(20) DEFAULT 'pcs',
    image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_product_name (product_name),
    INDEX idx_sku (sku),
    INDEX idx_barcode (barcode),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tabel Transactions (POS)
-- =============================================
CREATE TABLE IF NOT EXISTS transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT NULL,
    kasir_id INT NOT NULL,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(15,2) NOT NULL,
    discount DECIMAL(15,2) DEFAULT 0,
    tax DECIMAL(15,2) DEFAULT 0,
    grand_total DECIMAL(15,2) NOT NULL,
    payment_method ENUM('cash', 'debit', 'credit', 'ewallet', 'qris') NOT NULL,
    payment_amount DECIMAL(15,2),
    change_amount DECIMAL(15,2),
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'completed',
    notes TEXT,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (kasir_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_invoice (invoice_number),
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tabel Transaction Details
-- =============================================
CREATE TABLE IF NOT EXISTS transaction_details (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(200) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_transaction_id (transaction_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tabel Cart (untuk customer online)
-- =============================================
CREATE TABLE IF NOT EXISTS cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tabel Stock History
-- =============================================
CREATE TABLE IF NOT EXISTS stock_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    quantity_before INT NOT NULL,
    quantity_change INT NOT NULL,
    quantity_after INT NOT NULL,
    type ENUM('in', 'out', 'adjustment', 'sale', 'return') NOT NULL,
    reference VARCHAR(100),
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_product_id (product_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tabel Orders (untuk customer online)
-- =============================================
CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(15,2) NOT NULL,
    shipping_address TEXT,
    status ENUM('pending', 'processing', 'ready', 'shipped', 'completed', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid',
    payment_method ENUM('cod', 'transfer', 'ewallet', 'qris') DEFAULT 'cod',
    notes TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_order_number (order_number),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tabel Order Details
-- =============================================
CREATE TABLE IF NOT EXISTS order_details (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(200) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_order_id (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tabel Activity Logs
-- =============================================
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    activity VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tabel Settings
-- =============================================
CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- INSERT DATA SAMPLE
-- =============================================

-- Insert Users (Password: password)
INSERT INTO users (username, password, full_name, email, phone, role, status) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@minimarket.com', '081234567890', 'admin', 'active'),
('kasir1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kasir Satu', 'kasir1@minimarket.com', '081234567891', 'kasir', 'active'),
('kasir2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kasir Dua', 'kasir2@minimarket.com', '081234567892', 'kasir', 'active'),
('customer1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Budi Santoso', 'budi@gmail.com', '081234567893', 'customer', 'active'),
('customer2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Siti Aminah', 'siti@gmail.com', '081234567894', 'customer', 'active');

-- Insert Categories
INSERT INTO categories (category_name, description, icon, status) VALUES
('Makanan Ringan', 'Snack dan makanan ringan', 'fa-cookie-bite', 'active'),
('Minuman', 'Minuman kemasan dan segar', 'fa-glass-water', 'active'),
('Sembako', 'Kebutuhan pokok sehari-hari', 'fa-wheat-awn', 'active'),
('Toiletries', 'Perlengkapan mandi dan kebersihan', 'fa-soap', 'active'),
('Elektronik', 'Barang elektronik rumah tangga', 'fa-plug', 'active'),
('Alat Tulis', 'Perlengkapan tulis menulis', 'fa-pen', 'active'),
('Bumbu Dapur', 'Bumbu dan rempah-rempah', 'fa-pepper-hot', 'active');

-- Insert Suppliers
INSERT INTO suppliers (supplier_name, contact_person, phone, email, address, status) VALUES
('PT Indofood CBP', 'Budi Hartono', '0215551234', 'contact@indofood.com', 'Jl. Jend. Sudirman Kav 76-78, Jakarta Selatan', 'active'),
('PT Unilever Indonesia', 'Siti Nurhaliza', '0215551235', 'contact@unilever.co.id', 'Green Office Park, BSD City, Tangerang', 'active'),
('PT Wings Surya', 'Ahmad Dahlan', '0215551236', 'contact@wings.co.id', 'Jl. Raya Pegangsaan Dua, Kelapa Gading, Jakarta Utara', 'active'),
('PT Mayora Indah', 'Rini Soemarno', '0215551237', 'contact@mayora.com', 'Jl. Tomang Raya No. 21-23, Jakarta Barat', 'active'),
('PT Coca-Cola Indonesia', 'Andi Wijaya', '0215551238', 'contact@coca-cola.co.id', 'Jl. Pulo Lentut No. 3, Kawasan Industri Pulogadung, Jakarta Timur', 'active');

-- Insert Products
INSERT INTO products (category_id, supplier_id, product_name, sku, barcode, description, price, cost_price, stock, min_stock, unit, status) VALUES
-- Makanan Ringan
(1, 1, 'Indomie Goreng', 'IDM-GRG-001', '8992388101015', 'Mie instan goreng original', 3500, 2800, 200, 50, 'pcs', 'active'),
(1, 1, 'Chitato Rasa Sapi Panggang', 'CHT-SPG-001', '8992388105013', 'Keripik kentang rasa sapi panggang 68gr', 10500, 8500, 150, 30, 'pcs', 'active'),
(1, 4, 'Biskuat Coklat', 'BSK-CKL-001', '8992388201018', 'Biskuit rasa coklat 120gr', 8500, 7000, 100, 30, 'pcs', 'active'),
(1, 4, 'Tango Wafer Coklat', 'TNG-WFR-001', '8992388301012', 'Wafer coklat 176gr', 9500, 7800, 80, 25, 'pcs', 'active'),

-- Minuman
(2, 5, 'Coca-Cola 390ml', 'CCL-390-001', '8992388401019', 'Minuman berkarbonasi 390ml', 6500, 5200, 300, 100, 'btl', 'active'),
(2, 5, 'Sprite 390ml', 'SPR-390-001', '8992388501016', 'Minuman berkarbonasi rasa lemon 390ml', 6500, 5200, 250, 80, 'btl', 'active'),
(2, 1, 'Teh Botol Sosro 450ml', 'TBS-450-001', '8992388601013', 'Teh kemasan botol 450ml', 5500, 4500, 400, 120, 'btl', 'active'),
(2, 2, 'Air Mineral Aqua 600ml', 'AQA-600-001', '8992388701010', 'Air mineral 600ml', 3500, 2800, 500, 150, 'btl', 'active'),

-- Sembako
(3, 1, 'Beras Rojolele 5kg', 'BRS-RJL-005', '8992388801017', 'Beras premium 5kg', 75000, 65000, 100, 20, 'pack', 'active'),
(3, 1, 'Minyak Goreng Bimoli 2L', 'MYK-BML-002', '8992388901014', 'Minyak goreng kemasan 2 liter', 35000, 30000, 80, 25, 'btl', 'active'),
(3, 1, 'Gula Pasir Gulaku 1kg', 'GLP-GLK-001', '8992389001018', 'Gula pasir putih 1kg', 15000, 12500, 150, 40, 'pack', 'active'),
(3, 1, 'Telur Ayam Negeri (10 butir)', 'TLR-AYN-010', '8992389101015', 'Telur ayam segar 10 butir', 25000, 21000, 100, 30, 'pack', 'active'),

-- Toiletries
(4, 2, 'Sabun Lifebuoy 85gr', 'SBN-LFB-085', '8992389201012', 'Sabun mandi batangan 85gr', 4500, 3500, 200, 50, 'pcs', 'active'),
(4, 2, 'Shampo Pantene 170ml', 'SHP-PNT-170', '8992389301019', 'Shampo anti rontok 170ml', 18500, 15000, 120, 30, 'btl', 'active'),
(4, 2, 'Pasta Gigi Pepsodent 190gr', 'PSG-PPD-190', '8992389401016', 'Pasta gigi keluarga 190gr', 12500, 10000, 150, 40, 'pcs', 'active'),
(4, 3, 'Detergen Rinso 800gr', 'DTG-RNS-800', '8992389501013', 'Detergen bubuk 800gr', 22000, 18000, 100, 25, 'pack', 'active'),

-- Elektronik
(5, NULL, 'Lampu LED Philips 9W', 'LMP-PHP-009', '8992389601010', 'Lampu LED hemat energi 9 watt', 25000, 20000, 50, 15, 'pcs', 'active'),
(5, NULL, 'Baterai ABC AA (4 pcs)', 'BTR-ABC-AA4', '8992389701017', 'Baterai alkaline AA isi 4', 15000, 12000, 80, 20, 'pack', 'active'),

-- Alat Tulis
(6, NULL, 'Pulpen Standard AE7 Hitam', 'PPN-STD-HT1', '8992389801014', 'Pulpen standar warna hitam', 2500, 2000, 200, 50, 'pcs', 'active'),
(6, NULL, 'Buku Tulis Sinar Dunia 58 Lembar', 'BKT-SND-058', '8992389901011', 'Buku tulis folio bergaris 58 lembar', 5000, 4000, 150, 40, 'pcs', 'active'),

-- Bumbu Dapur
(7, 1, 'Kecap Manis Bango 220ml', 'KCP-BNG-220', '8992390001015', 'Kecap manis 220ml', 12500, 10000, 100, 25, 'btl', 'active'),
(7, 1, 'Saos Sambal ABC 335ml', 'SOS-ABC-335', '8992390101012', 'Saos sambal pedas 335ml', 15000, 12000, 90, 25, 'btl', 'active'),
(7, 1, 'Royco Ayam 100gr', 'RYC-AYM-100', '8992390201019', 'Bumbu penyedap rasa ayam 100gr', 8500, 7000, 120, 30, 'pack', 'active');

-- Insert Settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
('store_name', 'Minimarket Sejahtera', 'Nama toko'),
('store_address', 'Jl. Raya Sejahtera No. 123, Jakarta', 'Alamat toko'),
('store_phone', '021-12345678', 'Telepon toko'),
('store_email', 'info@minimarket-sejahtera.com', 'Email toko'),
('tax_percentage', '10', 'Persentase pajak (%)'),
('currency', 'IDR', 'Mata uang'),
('receipt_footer', 'Terima kasih atas kunjungan Anda!', 'Footer untuk struk');

-- =============================================
-- VIEWS
-- =============================================

-- View untuk laporan produk
CREATE OR REPLACE VIEW v_products_report AS
SELECT 
    p.id,
    p.product_name,
    p.sku,
    p.barcode,
    c.category_name,
    s.supplier_name,
    p.price,
    p.cost_price,
    p.stock,
    p.min_stock,
    CASE 
        WHEN p.stock <= 0 THEN 'Habis'
        WHEN p.stock <= p.min_stock THEN 'Stok Menipis'
        ELSE 'Tersedia'
    END as stock_status,
    p.status,
    p.created_at
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN suppliers s ON p.supplier_id = s.id;

-- View untuk laporan transaksi harian
CREATE OR REPLACE VIEW v_daily_sales AS
SELECT 
    DATE(transaction_date) as sales_date,
    COUNT(*) as total_transactions,
    SUM(total_amount) as total_sales,
    SUM(discount) as total_discount,
    SUM(grand_total) as total_revenue
FROM transactions
WHERE status = 'completed'
GROUP BY DATE(transaction_date)
ORDER BY sales_date DESC;

-- =============================================
-- STORED PROCEDURES
-- =============================================

-- Procedure untuk update stock saat transaksi
DELIMITER //
CREATE PROCEDURE sp_update_stock_transaction(
    IN p_product_id INT,
    IN p_quantity INT,
    IN p_reference VARCHAR(100),
    IN p_user_id INT
)
BEGIN
    DECLARE v_stock_before INT;
    DECLARE v_stock_after INT;
    
    -- Get stock before
    SELECT stock INTO v_stock_before FROM products WHERE id = p_product_id;
    
    -- Update stock
    UPDATE products SET stock = stock - p_quantity WHERE id = p_product_id;
    
    -- Get stock after
    SELECT stock INTO v_stock_after FROM products WHERE id = p_product_id;
    
    -- Insert to history
    INSERT INTO stock_history (product_id, quantity_before, quantity_change, quantity_after, type, reference, created_by)
    VALUES (p_product_id, v_stock_before, -p_quantity, v_stock_after, 'sale', p_reference, p_user_id);
END //
DELIMITER ;

-- =============================================
-- TRIGGERS
-- =============================================

-- Trigger untuk mencatat perubahan stock
DELIMITER //
CREATE TRIGGER tr_product_stock_update 
AFTER UPDATE ON products
FOR EACH ROW
BEGIN
    IF OLD.stock != NEW.stock THEN
        INSERT INTO activity_logs (user_id, activity, description)
        VALUES (
            NULL,
            'Stock Update',
            CONCAT('Product: ', NEW.product_name, ' - Stock changed from ', OLD.stock, ' to ', NEW.stock)
        );
    END IF;
END //
DELIMITER ;

-- =============================================
-- DATABASE READY
-- =============================================
SELECT 'Database minimarket_db berhasil dibuat!' as status;