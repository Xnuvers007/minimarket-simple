<?php
require_once '../config.php';
checkRole(['customer']);

$user_id = $_SESSION['user_id'];
$message = '';

// Handle Update Quantity
if (isset($_POST['update_cart'])) {
    $cart_id = $_POST['cart_id'];
    $quantity = $_POST['quantity'];
    
    $stmt = $conn->prepare("UPDATE cart SET quantity=? WHERE id=? AND user_id=?");
    $stmt->bind_param("iii", $quantity, $cart_id, $user_id);
    $stmt->execute();
    
    $message = "Keranjang berhasil diupdate!";
}

// Handle Remove Item
if (isset($_GET['remove'])) {
    $cart_id = $_GET['remove'];
    
    $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id=? AND user_id=?");
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();
    
    header("Location: cart.php?removed=1");
    exit;
}

// Get Cart Items
$cart_items = $conn->query("SELECT c.*, p.product_name, p.price, p.stock, p.unit FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id=$user_id");

$total = 0;

$page_title = 'Keranjang Belanja';
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
        
        .page-header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .page-header h1 {
            font-size: 32px;
            color: #2c3e50;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        
        .cart-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
        }
        
        .cart-items {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .cart-item {
            display: grid;
            grid-template-columns: 80px 1fr auto;
            gap: 20px;
            padding: 20px;
            border-bottom: 2px solid #f0f0f0;
            align-items: center;
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .item-price {
            color: #27ae60;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .item-stock {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .item-actions {
            display: flex;
            flex-direction: column;
            gap: 15px;
            align-items: flex-end;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .qty-btn {
            width: 35px;
            height: 35px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 18px;
        }
        
        .qty-input {
            width: 60px;
            text-align: center;
            padding: 8px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-weight: bold;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-success {
            background: #27ae60;
            color: white;
            font-size: 16px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .cart-summary {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        .summary-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
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
        
        .empty-cart {
            background: white;
            border-radius: 10px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .empty-cart i {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-cart h2 {
            color: #666;
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .cart-container {
                grid-template-columns: 1fr;
            }
            
            .cart-item {
                grid-template-columns: 60px 1fr;
            }
            
            .item-actions {
                grid-column: 1 / -1;
                flex-direction: row;
                justify-content: space-between;
                margin-top: 15px;
            }
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
        <?php if($message): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if(isset($_GET['removed'])): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> Item berhasil dihapus dari keranjang!</div>
        <?php endif; ?>
        
        <div class="page-header">
            <h1><i class="fas fa-shopping-cart"></i> Keranjang Belanja</h1>
            <p style="color: #7f8c8d; margin-top: 5px;">Review dan checkout pesanan Anda</p>
        </div>
        
        <?php if($cart_items->num_rows > 0): ?>
        <div class="cart-container">
            <div class="cart-items">
                <?php while($item = $cart_items->fetch_assoc()): 
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal;
                ?>
                <div class="cart-item">
                    <div class="item-image">
                        <i class="fas fa-box"></i>
                    </div>
                    
                    <div class="item-details">
                        <div class="item-name"><?php echo $item['product_name']; ?></div>
                        <div class="item-price"><?php echo formatRupiah($item['price']); ?></div>
                        <div class="item-stock">Stok tersedia: <?php echo $item['stock']; ?> <?php echo $item['unit']; ?></div>
                    </div>
                    
                    <div class="item-actions">
                        <form method="POST" style="display: flex; gap: 10px; align-items: center;">
                            <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                            <div class="quantity-controls">
                                <button type="button" class="qty-btn" onclick="updateQty(<?php echo $item['id']; ?>, <?php echo $item['quantity']; ?>, -1, <?php echo $item['stock']; ?>)">-</button>
                                <input type="number" name="quantity" id="qty-<?php echo $item['id']; ?>" class="qty-input" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" readonly>
                                <button type="button" class="qty-btn" onclick="updateQty(<?php echo $item['id']; ?>, <?php echo $item['quantity']; ?>, 1, <?php echo $item['stock']; ?>)">+</button>
                            </div>
                            <button type="submit" name="update_cart" class="btn btn-primary" style="padding: 8px 15px;">
                                <i class="fas fa-sync"></i> Update
                            </button>
                        </form>
                        
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <strong style="font-size: 18px;"><?php echo formatRupiah($subtotal); ?></strong>
                            <a href="?remove=<?php echo $item['id']; ?>" class="btn btn-danger" style="padding: 8px 15px;" onclick="return confirm('Hapus item ini?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            
            <div class="cart-summary">
                <div class="summary-title">Ringkasan Belanja</div>
                
                <div class="summary-row">
                    <span>Subtotal (<?php echo $cart_items->num_rows; ?> item)</span>
                    <strong><?php echo formatRupiah($total); ?></strong>
                </div>
                
                <div class="summary-total">
                    <span>Total</span>
                    <span><?php echo formatRupiah($total); ?></span>
                </div>
                
                <a href="checkout.php" class="btn btn-success" style="width: 100%; justify-content: center; margin-top: 20px; padding: 15px;">
                    <i class="fas fa-check-circle"></i> Checkout
                </a>
                
                <a href="shop.php" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 10px;">
                    <i class="fas fa-arrow-left"></i> Lanjut Belanja
                </a>
            </div>
        </div>
        <?php else: ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <h2>Keranjang Anda Kosong</h2>
            <p style="color: #999; margin-bottom: 20px;">Belum ada produk di keranjang. Yuk mulai belanja!</p>
            <a href="shop.php" class="btn btn-primary" style="font-size: 18px; padding: 15px 30px;">
                <i class="fas fa-shopping-bag"></i> Mulai Belanja
            </a>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        function updateQty(cartId, currentQty, change, maxStock) {
            const input = document.getElementById('qty-' + cartId);
            const newQty = currentQty + change;
            
            if (newQty >= 1 && newQty <= maxStock) {
                input.value = newQty;
            }
        }
    </script>

<?php require_once '../includes/admin_footer.php'; ?>