<?php
require_once 'config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$conn = getConnection();

// Get statistics
$stats = [
    'total_orders' => 0,
    'total_customers' => 0,
    'total_revenue' => 0,
    'total_products' => 0
];

// Total orders
$result = $conn->query("SELECT COUNT(*) as count FROM orders");
if ($result) $stats['total_orders'] = $result->fetch_assoc()['count'];

// Total customers
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
if ($result) $stats['total_customers'] = $result->fetch_assoc()['count'];

// Total revenue
$result = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE payment_status = 'paid'");
if ($result) $stats['total_revenue'] = $result->fetch_assoc()['total'];

// Total products
$result = $conn->query("SELECT COUNT(*) as count FROM products");
if ($result) $stats['total_products'] = $result->fetch_assoc()['count'];

// Get recent orders
$recent_orders = [];
$result = $conn->query("SELECT o.*, u.name as customer_name FROM orders o 
                        LEFT JOIN users u ON o.user_id = u.id 
                        ORDER BY o.created_at DESC LIMIT 10");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recent_orders[] = $row;
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
                    <a href="dashboard.php" class="active">
                        <i class="fas fa-home"></i>Dashboard
                    </a>
                </li>
                <li>
                    <a href="menu_management.php">
                        <i class="fas fa-utensils"></i>Menu Management
                    </a>
                </li>
                <li>
                    <a href="orders.php">
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
                <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>
                <div>
                    <span class="text-muted">Welcome,</span>
                    <strong><?php echo $_SESSION['name']; ?></strong>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="stat-label mb-1">Total Pesanan</p>
                                <h3 class="stat-value"><?php echo $stats['total_orders']; ?></h3>
                            </div>
                            <div class="stat-icon" style="background: rgba(212, 160, 23, 0.1);">
                                <i class="fas fa-shopping-cart text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="stat-label mb-1">Total Pelanggan</p>
                                <h3 class="stat-value"><?php echo $stats['total_customers']; ?></h3>
                            </div>
                            <div class="stat-icon" style="background: rgba(40, 167, 69, 0.1);">
                                <i class="fas fa-users text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="stat-label mb-1">Total Pendapatan</p>
                                <h3 class="stat-value">Rp <?php echo formatNumber($stats['total_revenue']); ?></h3>
                            </div>
                            <div class="stat-icon" style="background: rgba(0, 123, 255, 0.1);">
                                <i class="fas fa-money-bill text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="stat-label mb-1">Total Menu</p>
                                <h3 class="stat-value"><?php echo $stats['total_products']; ?></h3>
                            </div>
                            <div class="stat-icon" style="background: rgba(255, 107, 53, 0.1);">
                                <i class="fas fa-utensils text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="row">
                <div class="col-12">
                    <div class="table-card">
                        <div class="card-header bg-dark text-white p-3">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Pesanan Terbaru</h5>
                        </div>
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
                                    <?php if (count($recent_orders) > 0): ?>
                                        <?php foreach ($recent_orders as $order): ?>
                                            <tr>
                                                <td><strong><?php echo $order['order_number']; ?></strong></td>
                                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
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
                                                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
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
            
            <!-- Quick Actions -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <a href="menu_management.php" class="text-decoration-none">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon me-3" style="background: rgba(212, 160, 23, 0.1);">
                                    <i class="fas fa-plus text-warning"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Tambah Menu Baru</h6>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="orders.php" class="text-decoration-none">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon me-3" style="background: rgba(40, 167, 69, 0.1);">
                                    <i class="fas fa-check text-success"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Kelola Pesanan</h6>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="index.php" class="text-decoration-none">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon me-3" style="background: rgba(0, 123, 255, 0.1);">
                                    <i class="fas fa-store text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Lihat Website</h6>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
