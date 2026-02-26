<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Check if order_id is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('index.php');
}

$order_id = intval($_GET['id']);

$conn = getConnection();

// Get order details
$stmt = $conn->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone, u.address as customer_address 
                        FROM orders o 
                        LEFT JOIN users u ON o.user_id = u.id 
                        WHERE o.id = ? AND (o.user_id = ? OR ? = 1)");
$is_admin = isAdmin() ? 1 : 0;
$stmt->bind_param("iii", $order_id, $_SESSION['user_id'], $is_admin);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    redirect('index.php');
}

$order = $result->fetch_assoc();
$stmt->close();

// Get order items
$items = [];
$stmt = $conn->prepare("SELECT oi.*, p.name as product_name, p.image as product_image 
                        FROM order_items oi 
                        LEFT JOIN products p ON oi.product_id = p.id 
                        WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}
$stmt->close();

$conn->close();
?>

<?php include 'header.php'; ?>

<div class="section-padding">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Detail Pesanan</h2>
            <p class="section-subtitle">Order #<?php echo $order['order_number']; ?></p>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <!-- Order Items -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-utensils me-2"></i>Item Pesanan</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($items) > 0): ?>
                            <?php foreach ($items as $item): ?>
                                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                    <div class="cart-image me-3">
                                        <img src="https://images.unsplash.com/photo-1564676713077-4e3a1c2b5f8d?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                        <p class="text-muted mb-0">Rp <?php echo formatNumber($item['price']); ?> x <?php echo $item['quantity']; ?></p>
                                    </div>
                                    <div class="text-end">
                                        <strong>Rp <?php echo formatNumber($item['subtotal']); ?></strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-between mt-3">
                            <span>Subtotal</span>
                            <span>Rp <?php echo formatNumber($order['total_amount']); ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Biaya Pengiriman</span>
                            <span>Gratis</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total</strong>
                            <strong class="text-warning">Rp <?php echo formatNumber($order['total_amount']); ?></strong>
                        </div>
                    </div>
                </div>
                
                <!-- Delivery Information -->
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Alamat Pengiriman</h5>
                    </div>
                    <div class="card-body">
                        <p><strong><?php echo htmlspecialchars($order['customer_name']); ?></strong></p>
                        <p><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($order['delivery_phone']); ?></p>
                        <p><i class="fas fa-home me-2"></i><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></p>
                        
                        <?php if ($order['notes']): ?>
                            <hr>
                            <p><strong>Catatan:</strong></p>
                            <p><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Order Status -->
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Status Pesanan</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Status Pembayaran</label>
                            <br>
                            <span class="order-badge <?php echo $order['payment_status']; ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status Pesanan</label>
                            <br>
                            <span class="order-badge <?php echo $order['order_status']; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                            </span>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Metode Pembayaran</label>
                            <br>
                            <strong><?php echo strtoupper($order['payment_method']); ?></strong>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tanggal Pesanan</label>
                            <br>
                            <?php echo date('d F Y, H:i', strtotime($order['created_at'])); ?>
                        </div>
                        
                        <hr>
                        
                        <?php if (!isAdmin()): ?>
                            <?php if ($order['payment_method'] === 'qris' && $order['payment_status'] === 'pending'): ?>
                                <a href="payment_qris.php?order_id=<?php echo $order['id']; ?>" class="btn btn-warning w-100 mb-2">
                                    <i class="fas fa-qrcode me-2"></i>Bayar Sekarang
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($order['order_status'] !== 'cancelled' && $order['order_status'] !== 'delivered'): ?>
                                <a href="https://wa.me/<?php echo WA_BUSINESS_NUMBER; ?>?text=Halo%20saya%20ingin%20melacak%20pesanan%20<?php echo $order['order_number']; ?>" class="btn btn-success w-100 mb-2" target="_blank">
                                    <i class="fab fa-whatsapp me-2"></i>Hubungi via WhatsApp
                                </a>
                            <?php endif; ?>
                            
                            <a href="my_orders.php" class="btn btn-outline-dark w-100">
                                <i class="fas fa-arrow-left me-2"></i>Kembali ke Pesanan
                            </a>
                        <?php else: ?>
                            <a href="orders.php" class="btn btn-outline-dark w-100">
                                <i class="fas fa-arrow-left me-2"></i>Kembali ke Orders
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
