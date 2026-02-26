<?php
require_once 'config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$conn = getConnection();

// Get all customers
$customers = [];
$result = $conn->query("SELECT * FROM users WHERE role = 'customer' ORDER BY created_at DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
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
                    <a href="orders.php">
                        <i class="fas fa-shopping-cart"></i>Orders
                    </a>
                </li>
                <li>
                    <a href="customers.php" class="active">
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
                <h2><i class="fas fa-users me-2"></i>Data Pelanggan</h2>
            </div>
            
            <!-- Customers Table -->
            <div class="table-card">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>No. WhatsApp</th>
                                <th>Alamat</th>
                                <th>Tanggal Daftar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($customers) > 0): ?>
                                <?php foreach ($customers as $index => $customer): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><strong><?php echo htmlspecialchars($customer['name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                        <td>
                                            <?php 
                                            // Format phone number for WhatsApp (Indonesian format: replace 0 with 62)
                                            $wa_phone = preg_replace('/^0/', '62', $customer['phone']);
                                            $wa_phone = preg_replace('/[^0-9]/', '', $wa_phone);
                                            ?>
                                            <a href="https://wa.me/<?php echo $wa_phone; ?>" target="_blank" class="text-success">
                                                <i class="fab fa-whatsapp"></i> <?php echo htmlspecialchars($customer['phone']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($customer['address']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($customer['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">Belum ada pelanggan</td>
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
