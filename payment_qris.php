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
            <h2 class="section-title">Pembayaran QRIS</h2>
            <p class="section-subtitle">Scan QRIS untuk melakukan pembayaran</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-qrcode me-2"></i>Order #<?php echo $order['order_number']; ?></h5>
                    </div>
                    <div class="card-body text-center">
                        <!-- QRIS Image - Replace with actual QRIS image -->
                        <div class="qris-container">
                            <img src="assets/images/qris.jpg" alt="QRIS" class="qris-image img-fluid" onerror="this.style.display='none'; document.getElementById('qris-placeholder').style.display='block';">
                            <div id="qris-placeholder" style="display:none;">
                                <i class="fas fa-qrcode fa-10x text-muted"></i>
                                <p class="mt-3 text-muted">Gambar QRIS akan muncul di sini</p>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle me-2"></i>
                            Scan QRIS di atas menggunakan aplikasi mobile banking atau e-wallet Anda
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
                        
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-clock me-2"></i>
                            <strong>Konfirmasi Pembayaran:</strong><br>
                            Setelah melakukan pembayaran, kami akan menghubungi Anda via WhatsApp untuk konfirmasi.
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <a href="https://wa.me/<?php echo WA_BUSINESS_NUMBER; ?>?text=Halo%20saya%20sudah%20melakukan%20pembayaran%20untuk%20pesanan%20<?php echo $order['order_number']; ?>" class="btn btn-success" target="_blank">
                                <i class="fab fa-whatsapp me-2"></i>Konfirmasi via WhatsApp
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
