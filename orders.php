<?php
require_once 'config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$conn = getConnection();

// Handle order status update
$message = '';
$message_type = '';

if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $order_status = sanitize($_POST['order_status']);
    $payment_status = sanitize($_POST['payment_status']);
    
    $stmt = $conn->prepare("UPDATE orders SET order_status = ?, payment_status = ? WHERE id = ?");
    $stmt->bind_param("ssi", $order_status, $payment_status, $order_id);
    
    if ($stmt->execute()) {
        $message = 'Status pesanan berhasil diperbarui!';
        $message_type = 'success';
    } else {
        $message = 'Gagal memperbarui status!';
        $message_type = 'danger';
    }
    $stmt->close();
}

// Get all orders
$orders = [];
$result = $conn->query("SELECT o.*, u.name as customer_name, u.phone as customer_phone, u.address as customer_address 
                        FROM orders o 
                        LEFT JOIN users u ON o.user_id = u.id 
                        ORDER BY o.created_at DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

$conn->close();
?>

<?php include 'header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 dashboard-sidebar p-0">
            <div class="text-center py-4">
                <i class="fas fa-utensils fa-3x text-warning"></i>
                <h5 class="text-white mt-2">Sate Madura</h5>
                <p class="text-muted small">Admin Panel</p>
            </div>
            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php">
                        <i class="fas fa-home"></i>Dashboard
                    </a>
                </li>
                <li>
                    <a href="menu_management.php">
                        <i class="fas fa-utensils"></i>Menu Management
                    </a>
                </li>
                <li>
                    <a href="orders.php" class="active">
                        <i class="fas fa-shopping-cart"></i>Orders
                    </a>
                </li>
                <li>
                    <a href="customers.php">
                        <i class="fas fa-users"></i>Customers
                    </a>
                </li>
                <li>
                    <a href="index.php">
                        <i class="fas fa-store"></i>Lihat Website
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>Logout
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 dashboard-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-shopping-cart me-2"></i>Kelola Pesanan</h2>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i><?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Orders Table -->
            <div class="table-card">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No. Pesanan</th>
                                <th>Pelanggan</th>
                                <th>Total</th>
                                <th>Metode Pembayaran</th>
                                <th>Status Pembayaran</th>
                                <th>Status Pesanan</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($orders) > 0): ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><strong><?php echo $order['order_number']; ?></strong></td>
                                        <td>
                                            <?php echo htmlspecialchars($order['customer_name']); ?>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($order['customer_phone']); ?></small>
                                        </td>
                                        <td>Rp <?php echo formatNumber($order['total_amount']); ?></td>
                                        <td><?php echo strtoupper($order['payment_method']); ?></td>
                                        <td>
                                            <span class="order-badge <?php echo $order['payment_status']; ?>">
                                                <?php echo ucfirst($order['payment_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="order-badge <?php echo $order['order_status']; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editOrderModal<?php echo $order['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php 
                                            // Format phone number for WhatsApp (Indonesian format: replace 0 with 62)
                                            $wa_phone = preg_replace('/^0/', '62', $order['delivery_phone']);
                                            $wa_phone = preg_replace('/[^0-9]/', '', $wa_phone);
                                            $wa_message = "Halo%20" . urlencode($order['customer_name']) . ",%20pesanan%20Anda%20dengan%20nomor%20" . $order['order_number'] . "%20sedang%20dalam%20pengiriman.%20Total%20Rp%20" . number_format($order['total_amount'], 0, ',', '.') . ".%20Alamat:%20" . urlencode($order['delivery_address']);
                                            ?>
                                            <a href="https://wa.me/<?php echo $wa_phone; ?>?text=<?php echo $wa_message; ?>" class="btn btn-sm btn-success" target="_blank" title="Kirim via WhatsApp">
                                                <i class="fab fa-whatsapp"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    
                                    <!-- Edit Order Modal -->
                                    <div class="modal fade" id="editOrderModal<?php echo $order['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Update Pesanan: <?php echo $order['order_number']; ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">Status Pembayaran</label>
                                                            <select class="form-select" name="payment_status">
                                                                <option value="pending" <?php echo $order['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                                <option value="paid" <?php echo $order['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                                <option value="failed" <?php echo $order['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Status Pesanan</label>
                                                            <select class="form-select" name="order_status">
                                                                <option value="pending" <?php echo $order['order_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                                <option value="confirmed" <?php echo $order['order_status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                                <option value="processing" <?php echo $order['order_status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                                <option value="on_delivery" <?php echo $order['order_status'] === 'on_delivery' ? 'selected' : ''; ?>>On Delivery</option>
                                                                <option value="delivered" <?php echo $order['order_status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                                <option value="cancelled" <?php echo $order['order_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                            </select>
                                                        </div>
                                                        <div class="alert alert-info">
                                                            <strong>Alamat Pengiriman:</strong><br>
                                                            <?php echo htmlspecialchars($order['delivery_address']); ?><br>
                                                            <strong>Telp:</strong> <?php echo htmlspecialchars($order['delivery_phone']); ?>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" name="update_status" class="btn btn-warning">Simpan Perubahan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">Belum ada pesanan</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
