<?php
require_once '../config.php';
checkRole(['customer']);

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    
    // Check if already in cart
    $check = $conn->prepare("SELECT * FROM cart WHERE user_id=? AND product_id=?");
    $check->bind_param("ii", $_SESSION['user_id'], $product_id);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows > 0) {
        // Update quantity
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id=? AND product_id=?");
        $stmt->bind_param("iii", $quantity, $_SESSION['user_id'], $product_id);
    } else {
        // Insert new
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $_SESSION['user_id'], $product_id, $quantity);
    }
    
    $stmt->execute();
    header("Location: shop.php?added=1");
    exit;
}

// Get categories for filter
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name");

// Get products with filters
$where = "WHERE p.status='active'";
$search = '';
$category_filter = '';

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = clean_input($_GET['search']);
    $where .= " AND p.product_name LIKE '%$search%'";
}

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $category_filter = $_GET['category'];
    $where .= " AND p.category_id = " . intval($category_filter);
}

$products = $conn->query("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id $where ORDER BY p.product_name");

// Get cart count
$cart_count = $conn->query("SELECT SUM(quantity) as total FROM cart WHERE user_id=" . $_SESSION['user_id'])->fetch_assoc()['total'] ?? 0;

$page_title = 'Belanja Online';
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
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .nav-brand {
            font-size: 24px;
            font-weight: bold;
        }
        
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
        
        .nav-link:hover {
            opacity: 0.8;
        }
        
        .cart-badge {
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 5px;
        }
        
        .container {
            max-width: 1400px;
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
            margin-bottom: 10px;
        }
        
        .filter-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .filter-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 200px;
            gap: 15px;
            align-items: end;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
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
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-success {
            background: #27ae60;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        
        .product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .product-image {
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 60px;
        }
        
        .product-body {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .product-category {
            color: #667eea;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .product-name {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .product-price {
            font-size: 24px;
            font-weight: bold;
            color: #27ae60;
            margin-bottom: 10px;
        }
        
        .product-stock {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .product-actions {
            margin-top: auto;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
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
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
        }
        
        .empty-state i {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: #666;
            margin-bottom: 10px;
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
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="shop.php" class="nav-link">
                    <i class="fas fa-shopping-bag"></i> Belanja
                </a>
                <a href="cart.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i> Keranjang
                    <?php if($cart_count > 0): ?>
                        <span class="cart-badge"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
                <a href="orders.php" class="nav-link">
                    <i class="fas fa-box"></i> Pesanan
                </a>
                <a href="profile.php" class="nav-link">
                    <i class="fas fa-user"></i> Profil
                </a>
                <a href="../logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <?php if(isset($_GET['added'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Produk berhasil ditambahkan ke keranjang!
            </div>
        <?php endif; ?>
        
        <div class="page-header">
            <h1><i class="fas fa-shopping-bag"></i> Katalog Produk</h1>
            <p style="color: #7f8c8d;">Temukan produk yang Anda butuhkan</p>
        </div>
        
        <div class="filter-section">
            <form method="GET">
                <div class="filter-grid">
                    <div class="form-group">
                        <label for="search-product">Cari Produk</label>
                        <input type="text" id="search-product" name="search" autocomplete="off" placeholder="Nama produk..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="filter-category">Kategori</label>
                        <select id="filter-category" name="category" autocomplete="off">
                            <option value="">Semua Kategori</option>
                            <?php while($cat = $categories->fetch_assoc()): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo $cat['category_name']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
        
        <?php if($products->num_rows > 0): ?>
        <div class="products-grid">
            <?php while($product = $products->fetch_assoc()): ?>
            <div class="product-card">
                <div class="product-image">
                    <i class="fas fa-box"></i>
                </div>
                
                <div class="product-body">
                    <div class="product-category">
                        <i class="fas fa-tag"></i> <?php echo $product['category_name'] ?? 'Uncategorized'; ?>
                    </div>
                    
                    <div class="product-name"><?php echo $product['product_name']; ?></div>
                    
                    <div class="product-price"><?php echo formatRupiah($product['price']); ?></div>
                    
                    <div class="product-stock">
                        <i class="fas fa-cube"></i> Stok: <?php echo $product['stock']; ?> <?php echo $product['unit']; ?>
                    </div>
                    
                    <?php if($product['stock'] > 0): ?>
                    <div class="product-actions">
                        <form method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            
                            <div class="quantity-selector">
                                <button type="button" class="qty-btn" onclick="changeQty(this, -1)">-</button>
                                <input type="number" name="quantity" class="qty-input" value="1" min="1" max="<?php echo $product['stock']; ?>" readonly>
                                <button type="button" class="qty-btn" onclick="changeQty(this, 1)">+</button>
                            </div>
                            
                            <button type="submit" name="add_to_cart" class="btn btn-success" style="width: 100%;">
                                <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                            </button>
                        </form>
                    </div>
                    <?php else: ?>
                    <button class="btn" style="width: 100%; background: #ccc; cursor: not-allowed;" disabled>
                        <i class="fas fa-times"></i> Stok Habis
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-search"></i>
            <h3>Produk tidak ditemukan</h3>
            <p>Coba ubah filter atau kata kunci pencarian Anda</p>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        function changeQty(btn, change) {
            const input = btn.parentElement.querySelector('.qty-input');
            const currentVal = parseInt(input.value);
            const max = parseInt(input.max);
            const min = parseInt(input.min);
            
            const newVal = currentVal + change;
            
            if (newVal >= min && newVal <= max) {
                input.value = newVal;
            }
        }
    </script>

<?php require_once '../includes/admin_footer.php'; ?>