<<<<<<< HEAD
<?php
require_once '../config.php';
checkRole(['customer']);

$user_id = $_SESSION['user_id'];

// Get cart items
$cart_items = $conn->query("SELECT c.*, p.product_name, p.price, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id=$user_id");

if ($cart_items->num_rows == 0) {
    header("Location: cart.php");
    exit;
}

$total = 0;
$items = [];
while($item = $cart_items->fetch_assoc()) {
    $subtotal = $item['price'] * $item['quantity'];
    $total += $subtotal;
    $items[] = $item;
}

// Get user info
$user_info = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

// Handle Checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $shipping_address = clean_input($_POST['shipping_address']);
    $notes = clean_input($_POST['notes']);
    
    $conn->begin_transaction();
    
    try {
        // Create order
        $order_number = generateOrderNumber();
        $stmt = $conn->prepare("INSERT INTO orders (order_number, user_id, total_amount, shipping_address, notes, status, payment_status) VALUES (?, ?, ?, ?, ?, 'pending', 'unpaid')");
        $stmt->bind_param("sidss", $order_number, $user_id, $total, $shipping_address, $notes);
        $stmt->execute();
        
        $order_id = $conn->insert_id;
        
        // Add order details and update stock
        foreach ($items as $item) {
            $subtotal = $item['price'] * $item['quantity'];
            
            // Insert order detail
            $stmt = $conn->prepare("INSERT INTO order_details (order_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiidd", $order_id, $item['product_id'], $item['quantity'], $item['price'], $subtotal);
            $stmt->execute();
            
            // Update stock
            $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stmt->bind_param("ii", $item['quantity'], $item['product_id']);
            $stmt->execute();
            
            // Add stock history
            $stmt = $conn->prepare("INSERT INTO stock_history (product_id, quantity_change, type, reference, created_by) VALUES (?, ?, 'out', ?, ?)");
            $qty_change = -$item['quantity'];
            $stmt->bind_param("iisi", $item['product_id'], $qty_change, $order_number, $user_id);
            $stmt->execute();
        }
        
        // Clear cart
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        $conn->commit();
        
        header("Location: orders.php?success=1");
        exit;
        
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Gagal memproses pesanan: " . $e->getMessage();
    }
}

$page_title = 'Checkout';
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
        
        .nav-menu {
            display: flex;
            gap: 25px;
            align-items: center;
        }
        
        .nav-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: opacity 0.3s;
        }
        
        .nav-link:hover { opacity: 0.8; }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 30px;
        }
        
        .checkout-steps {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .step {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px 25px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .step.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .step-number {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .step.active .step-number {
            background: white;
            color: #667eea;
        }
        
        .checkout-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .card-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
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
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .item-qty {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .item-price {
            font-weight: bold;
            color: #27ae60;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
            font-size: 16px;
        }
        
        .summary-total {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
            font-size: 24px;
            font-weight: bold;
            color: #27ae60;
        }
        
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            font-size: 16px;
        }
        
        .btn-success {
            background: #27ae60;
            color: white;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-store"></i> Minimarket
            </div>
            
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
        <div class="checkout-steps">
            <div class="step">
                <div class="step-number">1</div>
                <span>Keranjang</span>
            </div>
            <div class="step active">
                <div class="step-number">2</div>
                <span>Checkout</span>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <span>Selesai</span>
            </div>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="checkout-container">
                <div>
                    <div class="card">
                        <h2 class="card-title"><i class="fas fa-map-marker-alt"></i> Informasi Pengiriman</h2>
                        
                        <div class="form-group">
                            <label for="checkout-name">Nama Lengkap</label>
                            <input type="text" id="checkout-name" value="<?php echo $user_info['full_name']; ?>" autocomplete="name" readonly style="background: #f5f5f5;">
                        </div>
                        
                        <div class="form-group">
                            <label for="checkout-phone">No. Telepon</label>
                            <input type="text" id="checkout-phone" value="<?php echo $user_info['phone']; ?>" autocomplete="tel" readonly style="background: #f5f5f5;">
                        </div>
                        
                        <div class="form-group">
                            <label for="checkout-address">Alamat Pengiriman *</label>
                            <textarea id="checkout-address" name="shipping_address" autocomplete="street-address" required placeholder="Masukkan alamat lengkap untuk pengiriman"><?php echo $user_info['address']; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Catatan Pesanan</label>
                            <textarea name="notes" placeholder="Catatan tambahan untuk pesanan Anda (opsional)"></textarea>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 10px;">
                        <a href="cart.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali ke Keranjang
                        </a>
                    </div>
                </div>
                
                <div>
                    <div class="card">
                        <h2 class="card-title"><i class="fas fa-receipt"></i> Ringkasan Pesanan</h2>
                        
                        <div style="max-height: 400px; overflow-y: auto;">
                            <?php foreach($items as $item): 
                                $subtotal = $item['price'] * $item['quantity'];
                            ?>
                            <div class="order-item">
                                <div class="item-info">
                                    <div class="item-name"><?php echo $item['product_name']; ?></div>
                                    <div class="item-qty"><?php echo $item['quantity']; ?> x <?php echo formatRupiah($item['price']); ?></div>
                                </div>
                                <div class="item-price"><?php echo formatRupiah($subtotal); ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <strong><?php echo formatRupiah($total); ?></strong>
                        </div>
                        
                        <div class="summary-row">
                            <span>Biaya Pengiriman</span>
                            <strong>Gratis</strong>
                        </div>
                        
                        <div class="summary-total">
                            <span>Total</span>
                            <span><?php echo formatRupiah($total); ?></span>
                        </div>
                        
                        <button type="submit" name="place_order" class="btn btn-success" style="width: 100%; justify-content: center; margin-top: 20px;">
                            <i class="fas fa-check-circle"></i> Buat Pesanan
                        </button>
                        
                        <p style="text-align: center; color: #7f8c8d; font-size: 13px; margin-top: 15px;">
                            <i class="fas fa-info-circle"></i> Pesanan akan diproses setelah Anda melakukan pembayaran
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>

=======
<?php
require_once '../config.php';
checkRole(['customer']);

$user_id = $_SESSION['user_id'];

// Get cart items
$cart_items = $conn->query("SELECT c.*, p.product_name, p.price, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id=$user_id");

if ($cart_items->num_rows == 0) {
    header("Location: cart.php");
    exit;
}

$total = 0;
$items = [];
while($item = $cart_items->fetch_assoc()) {
    $subtotal = $item['price'] * $item['quantity'];
    $total += $subtotal;
    $items[] = $item;
}

// Get user info
$user_info = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

// Handle Checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $shipping_address = clean_input($_POST['shipping_address']);
    $notes = clean_input($_POST['notes']);
    
    $conn->begin_transaction();
    
    try {
        // Create order
        $order_number = generateOrderNumber();
        $stmt = $conn->prepare("INSERT INTO orders (order_number, user_id, total_amount, shipping_address, notes, status, payment_status) VALUES (?, ?, ?, ?, ?, 'pending', 'unpaid')");
        $stmt->bind_param("sidss", $order_number, $user_id, $total, $shipping_address, $notes);
        $stmt->execute();
        
        $order_id = $conn->insert_id;
        
        // Add order details and update stock
        foreach ($items as $item) {
            $subtotal = $item['price'] * $item['quantity'];
            
            // Insert order detail
            $stmt = $conn->prepare("INSERT INTO order_details (order_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiidd", $order_id, $item['product_id'], $item['quantity'], $item['price'], $subtotal);
            $stmt->execute();
            
            // Update stock
            $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stmt->bind_param("ii", $item['quantity'], $item['product_id']);
            $stmt->execute();
            
            // Add stock history
            $stmt = $conn->prepare("INSERT INTO stock_history (product_id, quantity_change, type, reference, created_by) VALUES (?, ?, 'out', ?, ?)");
            $qty_change = -$item['quantity'];
            $stmt->bind_param("iisi", $item['product_id'], $qty_change, $order_number, $user_id);
            $stmt->execute();
        }
        
        // Clear cart
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        $conn->commit();
        
        header("Location: orders.php?success=1");
        exit;
        
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Gagal memproses pesanan: " . $e->getMessage();
    }
}

$page_title = 'Checkout';
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
        
        .nav-menu {
            display: flex;
            gap: 25px;
            align-items: center;
        }
        
        .nav-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: opacity 0.3s;
        }
        
        .nav-link:hover { opacity: 0.8; }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 30px;
        }
        
        .checkout-steps {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .step {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px 25px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .step.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .step-number {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .step.active .step-number {
            background: white;
            color: #667eea;
        }
        
        .checkout-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .card-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
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
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .item-qty {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .item-price {
            font-weight: bold;
            color: #27ae60;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
            font-size: 16px;
        }
        
        .summary-total {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
            font-size: 24px;
            font-weight: bold;
            color: #27ae60;
        }
        
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            font-size: 16px;
        }
        
        .btn-success {
            background: #27ae60;
            color: white;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-store"></i> Minimarket
            </div>
            
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
        <div class="checkout-steps">
            <div class="step">
                <div class="step-number">1</div>
                <span>Keranjang</span>
            </div>
            <div class="step active">
                <div class="step-number">2</div>
                <span>Checkout</span>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <span>Selesai</span>
            </div>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="checkout-container">
                <div>
                    <div class="card">
                        <h2 class="card-title"><i class="fas fa-map-marker-alt"></i> Informasi Pengiriman</h2>
                        
                        <div class="form-group">
                            <label for="checkout-name">Nama Lengkap</label>
                            <input type="text" id="checkout-name" value="<?php echo $user_info['full_name']; ?>" autocomplete="name" readonly style="background: #f5f5f5;">
                        </div>
                        
                        <div class="form-group">
                            <label for="checkout-phone">No. Telepon</label>
                            <input type="text" id="checkout-phone" value="<?php echo $user_info['phone']; ?>" autocomplete="tel" readonly style="background: #f5f5f5;">
                        </div>
                        
                        <div class="form-group">
                            <label for="checkout-address">Alamat Pengiriman *</label>
                            <textarea id="checkout-address" name="shipping_address" autocomplete="street-address" required placeholder="Masukkan alamat lengkap untuk pengiriman"><?php echo $user_info['address']; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Catatan Pesanan</label>
                            <textarea name="notes" placeholder="Catatan tambahan untuk pesanan Anda (opsional)"></textarea>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 10px;">
                        <a href="cart.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali ke Keranjang
                        </a>
                    </div>
                </div>
                
                <div>
                    <div class="card">
                        <h2 class="card-title"><i class="fas fa-receipt"></i> Ringkasan Pesanan</h2>
                        
                        <div style="max-height: 400px; overflow-y: auto;">
                            <?php foreach($items as $item): 
                                $subtotal = $item['price'] * $item['quantity'];
                            ?>
                            <div class="order-item">
                                <div class="item-info">
                                    <div class="item-name"><?php echo $item['product_name']; ?></div>
                                    <div class="item-qty"><?php echo $item['quantity']; ?> x <?php echo formatRupiah($item['price']); ?></div>
                                </div>
                                <div class="item-price"><?php echo formatRupiah($subtotal); ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <strong><?php echo formatRupiah($total); ?></strong>
                        </div>
                        
                        <div class="summary-row">
                            <span>Biaya Pengiriman</span>
                            <strong>Gratis</strong>
                        </div>
                        
                        <div class="summary-total">
                            <span>Total</span>
                            <span><?php echo formatRupiah($total); ?></span>
                        </div>
                        
                        <button type="submit" name="place_order" class="btn btn-success" style="width: 100%; justify-content: center; margin-top: 20px;">
                            <i class="fas fa-check-circle"></i> Buat Pesanan
                        </button>
                        
                        <p style="text-align: center; color: #7f8c8d; font-size: 13px; margin-top: 15px;">
                            <i class="fas fa-info-circle"></i> Pesanan akan diproses setelah Anda melakukan pembayaran
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>

>>>>>>> 882da412c224f7c20dfb67829049d92fbad8991f
<?php require_once '../includes/admin_footer.php'; ?>