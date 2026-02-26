<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$conn = getConnection();

// Get user's orders
$orders = [];
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();

$conn->close();
?>

<?php include 'header.php'; ?>

<div class="section-padding">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Pesanan Saya</h2>
            <p class="section-subtitle">Lihat riwayat pesanan Anda</p>
        </div>
        
        <div class="row">
            <div class="col-12">
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="card shadow mb-4">
                            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">Order #<?php echo $order['order_number']; ?></h5>
                                    <small><?php echo date('d F Y, H:i', strtotime($order['created_at'])); ?></small>
                                </div>
                                <div>
                                    <span class="order-badge <?php echo $order['payment_status']; ?>">
                                        <?php echo ucfirst($order['payment_status']); ?>
                                    </span>
                                    <span class="order-badge <?php echo $order['order_status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Detail Pesanan:</h6>
                                        <table class="table table-sm">
                                            <tr>
                                                <td>Metode Pembayaran</td>
                                                <td><strong><?php echo strtoupper($order['payment_method']); ?></strong></td>
                                            </tr>
                                            <tr>
                                                <td>Total</td>
                                                <td><strong class="text-warning">Rp <?php echo formatNumber($order['total_amount']); ?></strong></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Alamat Pengiriman:</h6>
                                        <p><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></p>
                                        <p><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($order['delivery_phone']); ?></p>
                                    </div>
                                </div>
                                
                                <?php if ($order['notes']): ?>
                                    <div class="alert alert-info mt-3">
                                        <strong>Catatan:</strong> <?php echo htmlspecialchars($order['notes']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mt-3">
                                    <?php if ($order['payment_method'] === 'qris' && $order['payment_status'] === 'pending'): ?>
                                        <a href="payment_qris.php?order_id=<?php echo $order['id']; ?>" class="btn btn-warning">
                                            <i class="fas fa-qrcode me-2"></i>Bayar Sekarang
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($order['order_status'] !== 'cancelled' && $order['order_status'] !== 'delivered'): ?>
                                        <a href="https://wa.me/<?php echo WA_BUSINESS_NUMBER; ?>?text=Halo%20saya%20ingin%20melacak%20pesanan%20<?php echo $order['order_number']; ?>" class="btn btn-success" target="_blank">
                                            <i class="fab fa-whatsapp me-2"></i>Hubungi via WhatsApp
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-eye me-2"></i>Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                        <h4>Belum Ada Pesanan</h4>
                        <p class="text-muted">Anda belum melakukan pesanan apapun.</p>
                        <a href="index.php#menu" class="btn btn-warning">
                            <i class="fas fa-utensils me-2"></i>Pesan Sekarang
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
