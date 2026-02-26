<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$conn = getConnection();

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$error = '';
$success = '';

// Handle checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delivery_address = sanitize($_POST['delivery_address'] ?? '');
    $delivery_phone = sanitize($_POST['delivery_phone'] ?? '');
    $payment_method = sanitize($_POST['payment_method'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');
    
    if (empty($delivery_address) || empty($delivery_phone)) {
        $error = 'Alamat dan nomor telepon harus diisi!';
    } else {
        // Get cart from localStorage (via JavaScript POST)
        $cart_data = isset($_POST['cart_data']) ? json_decode($_POST['cart_data'], true) : [];
        
        if (empty($cart_data)) {
            $error = 'Keranjang belanja kosong!';
        } else {
            // Validate stock availability
            $stock_error = false;
            foreach ($cart_data as $item) {
                $stock_stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
                $stock_stmt->bind_param("i", $item['id']);
                $stock_stmt->execute();
                $stock_result = $stock_stmt->get_result();
                if ($stock_result->num_rows > 0) {
                    $product = $stock_result->fetch_assoc();
                    if ($product['stock'] < $item['quantity']) {
                        $stock_error = true;
                        break;
                    }
                }
                $stock_stmt->close();
            }
            
            if ($stock_error) {
                $error = 'Stok produk tidak mencukupi untuk beberapa item. Silakan periksa keranjang Anda.';
            } else {
                // Calculate total
                $total_amount = 0;
                foreach ($cart_data as $item) {
                    $total_amount += $item['price'] * $item['quantity'];
                }
                
                // Generate order number
                $order_number = generateOrderNumber();
                
                // Insert order
                $stmt = $conn->prepare("INSERT INTO orders (user_id, order_number, total_amount, payment_method, delivery_address, delivery_phone, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isdssss", $_SESSION['user_id'], $order_number, $total_amount, $payment_method, $delivery_address, $delivery_phone, $notes);
                
                if ($stmt->execute()) {
                    $order_id = $conn->insert_id;
                    
                    // Insert order items and update stock
                    foreach ($cart_data as $item) {
                        $subtotal = $item['price'] * $item['quantity'];
                        $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
                        $item_stmt->bind_param("iiidd", $order_id, $item['id'], $item['quantity'], $item['price'], $subtotal);
                        $item_stmt->execute();
                        $item_stmt->close();
                        
                        // Update stock
                        $stock_stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                        $stock_stmt->bind_param("ii", $item['quantity'], $item['id']);
                        $stock_stmt->execute();
                        $stock_stmt->close();
                    }
                    
                    $stmt->close();
                    $conn->close();
                    
                    // Clear cart
                    echo '<script>localStorage.removeItem("sateCart");</script>';
                    
                    // Redirect based on payment method
                    if ($payment_method === 'qris') {
                        redirect('payment_qris.php?order_id=' . $order_id);
                    } else {
                        redirect('payment_cod.php?order_id=' . $order_id);
                    }
                } else {
                    $error = 'Gagal membuat pesanan! Silakan coba lagi.';
                }
            }
        }
    }
}

$conn->close();
?>

<?php include 'header.php'; ?>

<div class="section-padding">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Checkout</h2>
            <p class="section-subtitle">Lengkapi data pesanan Anda</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="checkout-form">
            <input type="hidden" name="cart_data" id="cart-data">
            
            <div class="row">
                <div class="col-lg-8">
                    <!-- Delivery Information -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Alamat Pengiriman</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Nama Pelanggan</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nomor WhatsApp</label>
                                <input type="tel" class="form-control" name="delivery_phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alamat Lengkap</label>
                                <textarea class="form-control" name="delivery_address" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Catatan (Opsional)</label>
                                <textarea class="form-control" name="notes" rows="2" placeholder="Tambahkan catatan untuk pesanan..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- Order Summary -->
                    <div class="card shadow">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="fas fa-shopping-bag me-2"></i>Ringkasan Pesanan</h5>
                        </div>
                        <div class="card-body">
                            <div id="checkout-items">
                                <!-- Items loaded via JS -->
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <span id="checkout-subtotal">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Biaya Pengiriman</span>
                                <span>Gratis</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total</strong>
                                <strong id="checkout-total" class="text-warning">Rp 0</strong>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="card shadow mt-4">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Metode Pembayaran</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="payment-qris" value="qris" checked>
                                <label class="form-check-label" for="payment-qris">
                                    <i class="fas fa-qrcode me-2"></i>QRIS
                                    <br><small class="text-muted">Scan QRIS untuk pembayaran</small>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="payment-cod" value="cod">
                                <label class="form-check-label" for="payment-cod">
                                    <i class="fas fa-money-bill-wave me-2"></i>Cash on Delivery (COD)
                                    <br><small class="text-muted">Bayar saat pesanan diterima</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-warning w-100 py-3 mt-4">
                        <i class="fas fa-check-circle me-2"></i>Buat Pesanan
                    </button>
                    
                    <a href="cart.php" class="btn btn-outline-dark w-100 mt-2">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Keranjang
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Pass cart data to form and render checkout items
document.addEventListener('DOMContentLoaded', function() {
    // Render checkout items
    if (typeof renderCheckoutItems === 'function') {
        renderCheckoutItems();
    }
    
    // Pass cart data to form on submit
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            const cart = JSON.parse(localStorage.getItem('sateCart')) || [];
            document.getElementById('cart-data').value = JSON.stringify(cart);
            
            if (cart.length === 0) {
                e.preventDefault();
                alert('Keranjang belanja kosong! Silakan pilih menu terlebih dahulu.');
            }
        });
    }
});
</script>

<?php include 'footer.php'; ?>
