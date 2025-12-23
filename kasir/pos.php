<?php
require_once '../config.php';
checkRole(['kasir', 'admin']);

// Handle Add to Cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    
    // Get product info
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    
    if ($product && $product['stock'] >= $quantity) {
        $_SESSION['pos_cart'][$product_id] = [
            'name' => $product['product_name'],
            'price' => $product['price'],
            'quantity' => $quantity,
            'stock' => $product['stock']
        ];
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Stok tidak cukup']);
    }
    exit;
}

// Handle Remove from Cart
if (isset($_POST['remove_from_cart'])) {
    $product_id = $_POST['product_id'];
    unset($_SESSION['pos_cart'][$product_id]);
    echo json_encode(['success' => true]);
    exit;
}

// Handle Checkout
if (isset($_POST['checkout'])) {
    $payment_method = $_POST['payment_method'];
    $payment_amount = floatval($_POST['payment_amount']);
    $discount = floatval($_POST['discount'] ?? 0);
    
    if (empty($_SESSION['pos_cart'])) {
        echo json_encode(['success' => false, 'message' => 'Keranjang kosong']);
        exit;
    }
    
    $total = 0;
    foreach ($_SESSION['pos_cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    $grand_total = $total - $discount;
    $change = $payment_amount - $grand_total;
    
    if ($change < 0) {
        echo json_encode(['success' => false, 'message' => 'Pembayaran kurang']);
        exit;
    }
    
    $conn->begin_transaction();
    
    try {
        $invoice = generateInvoiceNumber();
        $kasir_id = $_SESSION['user_id'];
        
        $stmt = $conn->prepare("INSERT INTO transactions (invoice_number, kasir_id, total_amount, discount, grand_total, payment_method, payment_amount, change_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sidddsd d", $invoice, $kasir_id, $total, $discount, $grand_total, $payment_method, $payment_amount, $change);
        $stmt->execute();
        
        $transaction_id = $conn->insert_id;
        
        foreach ($_SESSION['pos_cart'] as $product_id => $item) {
            $subtotal = $item['price'] * $item['quantity'];
            
            $stmt = $conn->prepare("INSERT INTO transaction_details (transaction_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiidd", $transaction_id, $product_id, $item['quantity'], $item['price'], $subtotal);
            $stmt->execute();
            
            $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stmt->bind_param("ii", $item['quantity'], $product_id);
            $stmt->execute();
            
            $stmt = $conn->prepare("INSERT INTO stock_history (product_id, quantity_change, type, reference, created_by) VALUES (?, ?, 'out', ?, ?)");
            $qty = -$item['quantity'];
            $stmt->bind_param("iisi", $product_id, $qty, $invoice, $kasir_id);
            $stmt->execute();
        }
        
        $conn->commit();
        unset($_SESSION['pos_cart']);
        
        echo json_encode([
            'success' => true,
            'invoice' => $invoice,
            'change' => $change
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Get Products
$products = $conn->query("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 'active' ORDER BY p.product_name");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 24px;
        }
        
        .header-actions {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: white;
            color: #667eea;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
            padding: 20px;
            max-width: 1600px;
            margin: 0 auto;
        }
        
        /* Products Section */
        .products-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

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
        
        .search-bar {
            margin-bottom: 20px;
        }
        
        .search-bar input {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
            max-height: calc(100vh - 250px);
            overflow-y: auto;
        }
        
        .product-card {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        
        .product-card:hover {
            border-color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }
        
        .product-icon {
            font-size: 40px;
            margin-bottom: 10px;
            color: #667eea;
        }
        
        .product-name {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .product-price {
            color: #27ae60;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .product-stock {
            color: #7f8c8d;
            font-size: 12px;
        }
        
        /* Cart Section */
        .cart-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 20px;
            height: fit-content;
        }
        
        .cart-header {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .cart-items {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .item-price {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .item-quantity {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 5px 0;
        }
        
        .qty-btn {
            width: 25px;
            height: 25px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .remove-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .cart-summary {
            border-top: 2px solid #f0f0f0;
            padding-top: 15px;
            margin-top: 15px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
        }
        
        .summary-total {
            font-size: 20px;
            font-weight: bold;
            color: #27ae60;
        }
        
        .form-group {
            margin: 15px 0;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
        }
        
        .btn-checkout {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 15px;
        }
        
        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .empty-cart {
            text-align: center;
            padding: 40px 20px;
            color: #7f8c8d;
        }
        
        .empty-cart i {
            font-size: 60px;
            margin-bottom: 15px;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
            text-align: center;
        }
        
        .modal-icon {
            font-size: 60px;
            margin-bottom: 15px;
        }
        
        .modal-icon.success { color: #27ae60; }
        .modal-icon.error { color: #e74c3c; }
        
        @media (max-width: 1024px) {
            .container {
                grid-template-columns: 1fr;
            }
            
            .cart-section {
                position: static;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-cash-register"></i> Point of Sale</h1>
        <div class="header-actions">
            <span><?php echo $_SESSION['full_name']; ?></span>
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="orders.php" class="btn btn-warning">
                <i class="fas fa-shopping-bag"></i> Order Customer
            </a>
            <a href="pos.php" class="btn btn-success">
                <i class="fas fa-cash-register"></i> POS
            </a>
            <a href="transactions.php" class="btn btn-info">
                <i class="fas fa-history"></i> Transaksi
            </a>
            <a href="../logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
    
    <div class="container">
        <!-- Products Section -->
        <div class="products-section">
            <div class="search-bar">
                <input type="text" id="searchProduct" placeholder="Cari produk...">
            </div>
            
            <div class="products-grid" id="productsGrid">
                <?php while($product = $products->fetch_assoc()): ?>
                <div class="product-card" onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo addslashes($product['product_name']); ?>', <?php echo $product['price']; ?>, <?php echo $product['stock']; ?>)">
                    <div class="product-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="product-name"><?php echo $product['product_name']; ?></div>
                    <div class="product-price"><?php echo formatRupiah($product['price']); ?></div>
                    <div class="product-stock">Stok: <?php echo $product['stock']; ?></div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        
        <!-- Cart Section -->
        <div class="cart-section">
            <div class="cart-header">
                <i class="fas fa-shopping-cart"></i> Keranjang Belanja
            </div>
            
            <div class="cart-items" id="cartItems">
                <?php if(empty($_SESSION['pos_cart'])): ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-basket"></i>
                    <p>Keranjang masih kosong</p>
                </div>
                <?php else: ?>
                    <?php 
                    $total = 0;
                    foreach($_SESSION['pos_cart'] as $id => $item): 
                        $subtotal = $item['price'] * $item['quantity'];
                        $total += $subtotal;
                    ?>
                    <div class="cart-item" id="item-<?php echo $id; ?>">
                        <div class="item-info">
                            <div class="item-name"><?php echo $item['name']; ?></div>
                            <div class="item-price"><?php echo formatRupiah($item['price']); ?></div>
                            <div class="item-quantity">
                                <button class="qty-btn" onclick="updateQuantity(<?php echo $id; ?>, -1)">-</button>
                                <span id="qty-<?php echo $id; ?>"><?php echo $item['quantity']; ?></span>
                                <button class="qty-btn" onclick="updateQuantity(<?php echo $id; ?>, 1)">+</button>
                            </div>
                        </div>
                        <div>
                            <div style="font-weight: bold; margin-bottom: 10px;"><?php echo formatRupiah($subtotal); ?></div>
                            <button class="remove-btn" onclick="removeFromCart(<?php echo $id; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span id="subtotal"><?php echo formatRupiah($total ?? 0); ?></span>
                </div>
                
                <div class="form-group">
                    <label>Diskon:</label>
                    <input type="number" id="discount" value="0" min="0" onchange="calculateTotal()">
                </div>
                
                <div class="summary-row summary-total">
                    <span>Total:</span>
                    <span id="grandTotal"><?php echo formatRupiah($total ?? 0); ?></span>
                </div>
                
                <div class="form-group">
                    <label>Metode Pembayaran:</label>
                    <select id="paymentMethod">
                        <option value="cash">Tunai</option>
                        <option value="debit">Debit Card</option>
                        <option value="credit">Credit Card</option>
                        <option value="ewallet">E-Wallet</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Jumlah Bayar:</label>
                    <input type="number" id="paymentAmount" min="0" onchange="calculateChange()">
                </div>
                
                <div class="summary-row">
                    <span>Kembalian:</span>
                    <span id="change">Rp 0</span>
                </div>
                
                <button class="btn-checkout" onclick="checkout()">
                    <i class="fas fa-check"></i> Proses Pembayaran
                </button>
            </div>
        </div>
    </div>
    
    <!-- Modal -->
    <div class="modal" id="resultModal">
        <div class="modal-content">
            <div class="modal-icon" id="modalIcon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 id="modalTitle">Sukses!</h2>
            <p id="modalMessage">Transaksi berhasil</p>
            <button class="btn btn-primary" onclick="closeModal()" style="margin-top: 20px;">OK</button>
        </div>
    </div>
    
    <script>
        let cart = <?php echo json_encode($_SESSION['pos_cart'] ?? []); ?>;
        
        function addToCart(id, name, price, stock) {
            if (cart[id]) {
                if (cart[id].quantity < stock) {
                    cart[id].quantity++;
                } else {
                    showModal('error', 'Gagal', 'Stok tidak mencukupi');
                    return;
                }
            } else {
                cart[id] = { name, price, quantity: 1, stock };
            }
            
            updateCartDisplay();
        }
        
        function removeFromCart(id) {
            delete cart[id];
            updateCartDisplay();
        }
        
        function updateQuantity(id, change) {
            if (cart[id]) {
                const newQty = cart[id].quantity + change;
                if (newQty > 0 && newQty <= cart[id].stock) {
                    cart[id].quantity = newQty;
                    updateCartDisplay();
                }
            }
        }
        
        function updateCartDisplay() {
            const cartItems = document.getElementById('cartItems');
            let html = '';
            let total = 0;
            
            if (Object.keys(cart).length === 0) {
                html = '<div class="empty-cart"><i class="fas fa-shopping-basket"></i><p>Keranjang masih kosong</p></div>';
            } else {
                for (let id in cart) {
                    const item = cart[id];
                    const subtotal = item.price * item.quantity;
                    total += subtotal;
                    
                    html += `
                        <div class="cart-item">
                            <div class="item-info">
                                <div class="item-name">${item.name}</div>
                                <div class="item-price">${formatRupiah(item.price)}</div>
                                <div class="item-quantity">
                                    <button class="qty-btn" onclick="updateQuantity(${id}, -1)">-</button>
                                    <span>${item.quantity}</span>
                                    <button class="qty-btn" onclick="updateQuantity(${id}, 1)">+</button>
                                </div>
                            </div>
                            <div>
                                <div style="font-weight: bold; margin-bottom: 10px;">${formatRupiah(subtotal)}</div>
                                <button class="remove-btn" onclick="removeFromCart(${id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                }
            }
            
            cartItems.innerHTML = html;
            document.getElementById('subtotal').textContent = formatRupiah(total);
            calculateTotal();
        }
        
        function calculateTotal() {
            let subtotal = 0;
            for (let id in cart) {
                subtotal += cart[id].price * cart[id].quantity;
            }
            
            const discount = parseFloat(document.getElementById('discount').value) || 0;
            const grandTotal = subtotal - discount;
            
            document.getElementById('grandTotal').textContent = formatRupiah(grandTotal);
            calculateChange();
        }
        
        function calculateChange() {
            const grandTotal = parseFloat(document.getElementById('grandTotal').textContent.replace(/[^0-9]/g, ''));
            const payment = parseFloat(document.getElementById('paymentAmount').value) || 0;
            const change = payment - grandTotal;
            
            document.getElementById('change').textContent = formatRupiah(Math.max(0, change));
        }
        
        function checkout() {
            if (Object.keys(cart).length === 0) {
                showModal('error', 'Gagal', 'Keranjang masih kosong');
                return;
            }
            
            const payment = parseFloat(document.getElementById('paymentAmount').value) || 0;
            const grandTotal = parseFloat(document.getElementById('grandTotal').textContent.replace(/[^0-9]/g, ''));
            
            if (payment < grandTotal) {
                showModal('error', 'Gagal', 'Jumlah pembayaran kurang');
                return;
            }
            
            const formData = new FormData();
            formData.append('checkout', '1');
            formData.append('payment_method', document.getElementById('paymentMethod').value);
            formData.append('payment_amount', payment);
            formData.append('discount', document.getElementById('discount').value);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showModal('success', 'Sukses!', `Transaksi berhasil!
Invoice: ${data.invoice}
Kembalian: ${formatRupiah(data.change)}`);
                    cart = {};
                    updateCartDisplay();
                    document.getElementById('discount').value = 0;
                    document.getElementById('paymentAmount').value = '';
                } else {
                    showModal('error', 'Gagal', data.message);
                }
            });
        }
        
        function showModal(type, title, message) {
            const modal = document.getElementById('resultModal');
            const icon = document.getElementById('modalIcon');
            
            icon.className = `modal-icon ${type}`;
            icon.innerHTML = type === 'success' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-times-circle"></i>';
            
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalMessage').textContent = message;
            modal.classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('resultModal').classList.remove('active');
            location.reload();
        }
        
        function formatRupiah(amount) {
            return 'Rp ' + amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
        
        // Search functionality
        document.getElementById('searchProduct').addEventListener('input', function(e) {
            const search = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.product-card');
            
            cards.forEach(card => {
                const name = card.querySelector('.product-name').textContent.toLowerCase();
                card.style.display = name.includes(search) ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>