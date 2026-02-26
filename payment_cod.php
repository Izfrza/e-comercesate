<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Check if order_id is provided
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    redirect('index.php');
}

$order_id = intval($_GET['order_id']);

$conn = getConnection();

// Get order details
$stmt = $conn->prepare("SELECT o.*, u.name as customer_name, u.phone as customer_phone 
                        FROM orders o 
                        LEFT JOIN users u ON o.user_id = u.id 
                        WHERE o.id = ? AND o.user_id = ?");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    redirect('index.php');
}

$order = $result->fetch_assoc();
$stmt->close();

// Get order items
$items = [];
$stmt = $conn->prepare("SELECT oi.*, p.name as product_name 
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
            <h2 class="section-title">Cash on Delivery (COD)</h2>
            <p class="section-subtitle">Bayar saat pesanan diterima</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Order #<?php echo $order['order_number']; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Pesanan Berhasil Dibuat!</strong><br>
                            Anda memilih metode pembayaran Cash on Delivery (COD).
                        </div>
                        
                        <div class="text-start mt-4">
                            <h6>Detail Pesanan:</h6>
                            <table class="table table-sm">
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['product_name']); ?> x<?php echo $item['quantity']; ?></td>
                                        <td class="text-end">Rp <?php echo formatNumber($item['subtotal']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="table-warning">
                                    <td><strong>Total</strong></td>
                                    <td class="text-end"><strong>Rp <?php echo formatNumber($order['total_amount']); ?></strong></td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <strong>Alamat Pengiriman:</strong><br>
                            <?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?>
                        </div>
                        
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Catatan:</strong><br>
                            Pesanan akan diantar ke alamat Anda. Siapkan uang pas sebesar <strong>Rp <?php echo formatNumber($order['total_amount']); ?></strong> saat driver mengantar pesanan.
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="fab fa-whatsapp me-2"></i>
                            <strong>Driver akan menghubungi Anda via WhatsApp</strong> sebelum mengantar pesanan.
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <a href="https://wa.me/<?php echo WA_BUSINESS_NUMBER; ?>?text=Halo%20saya%20ingin%20melacak%20pesanan%20<?php echo $order['order_number']; ?>" class="btn btn-success" target="_blank">
                                <i class="fab fa-whatsapp me-2"></i>Hubungi via WhatsApp
                            </a>
                            <a href="my_orders.php" class="btn btn-outline-dark">
                                <i class="fas fa-list me-2"></i>Lihat Pesanan Saya
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
