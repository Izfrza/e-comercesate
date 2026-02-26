<?php
require_once 'config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$conn = getConnection();

// Handle form submissions
$message = '';
$message_type = '';

// Add new product
if (isset($_POST['add_product'])) {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);
    
    $image_path = 'default-food.jpg'; // Default image
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = 'assets/images/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = uniqid('product_') . '.' . $file_extension;
            $target_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                $image_path = $new_filename;
            }
        }
    }
    
    if (empty($name) || empty($price)) {
        $message = 'Nama dan harga harus diisi!';
        $message_type = 'danger';
    } else {
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, category_id, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiss", $name, $description, $price, $stock, $category_id, $image_path);
        
        if ($stmt->execute()) {
            $message = 'Menu berhasil ditambahkan!';
            $message_type = 'success';
        } else {
            $message = 'Gagal menambahkan menu!';
            $message_type = 'danger';
        }
        $stmt->close();
    }
}

// Update product
if (isset($_POST['update_product'])) {
    $id = intval($_POST['product_id']);
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    // Get current image
    $current_image = '';
    $result = $conn->query("SELECT image FROM products WHERE id = $id");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $current_image = $row['image'];
    }
    
    $image_path = $current_image; // Keep current image by default
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = 'assets/images/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = uniqid('product_') . '.' . $file_extension;
            $target_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                $image_path = $new_filename;
                // Delete old image if it's not default
                if ($current_image != 'default-food.jpg' && file_exists($upload_dir . $current_image)) {
                    unlink($upload_dir . $current_image);
                }
            }
        }
    }
    
    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, is_available = ?, image = ? WHERE id = ?");
    $stmt->bind_param("ssdiiisi", $name, $description, $price, $stock, $category_id, $is_available, $image_path, $id);
    
    if ($stmt->execute()) {
        $message = 'Menu berhasil diperbarui!';
        $message_type = 'success';
    } else {
        $message = 'Gagal memperbarui menu!';
        $message_type = 'danger';
    }
    $stmt->close();
}

// Delete product
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = 'Menu berhasil dihapus!';
        $message_type = 'success';
    } else {
        $message = 'Gagal menghapus menu!';
        $message_type = 'danger';
    }
    $stmt->close();
}

// Get categories
$categories = [];
$result = $conn->query("SELECT * FROM categories ORDER BY name");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Get products
$products = [];
$result = $conn->query("SELECT p.*, c.name as category_name FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        ORDER BY p.created_at DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
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
                    <a href="menu_management.php" class="active">
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
                <h2><i class="fas fa-utensils me-2"></i>Menu Management</h2>
                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fas fa-plus me-2"></i>Tambah Menu
                </button>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i><?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Products Table -->
            <div class="table-card">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Gambar</th>
                                <th>Nama Menu</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($products) > 0): ?>
                                <?php foreach ($products as $index => $product): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td>
                                            <img src="assets/images/<?php echo htmlspecialchars($product['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                 class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($product['description']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                        <td>Rp <?php echo formatNumber($product['price']); ?></td>
                                        <td>
                                            <span class="<?php echo $product['stock'] < 5 ? 'text-danger' : ''; ?>">
                                                <?php echo $product['stock']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($product['is_available']): ?>
                                                <span class="badge bg-success">Tersedia</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Tidak Tersedia</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editProductModal<?php echo $product['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="menu_management.php?delete=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus menu ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    
                                    <!-- Edit Product Modal -->
                                    <div class="modal fade" id="editProductModal<?php echo $product['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Menu</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="" enctype="multipart/form-data">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">Nama Menu</label>
                                                            <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Deskripsi</label>
                                                            <textarea class="form-control" name="description" rows="2"><?php echo htmlspecialchars($product['description']); ?></textarea>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Harga</label>
                                                                <input type="number" class="form-control" name="price" value="<?php echo $product['price']; ?>" required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Stok</label>
                                                                <input type="number" class="form-control" name="stock" value="<?php echo $product['stock']; ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Kategori</label>
                                                            <select class="form-select" name="category_id">
                                                                <?php foreach ($categories as $cat): ?>
                                                                    <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                                                        <?php echo htmlspecialchars($cat['name']); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Gambar Menu</label>
                                                            <input type="file" class="form-control" name="image" accept="image/*">
                                                            <small class="text-muted">Biarkan kosong jika tidak ingin mengubah gambar. Format: JPG, PNG, GIF. Maksimal 2MB</small>
                                                            <div class="mt-2">
                                                                <img src="assets/images/<?php echo htmlspecialchars($product['image']); ?>" 
                                                                     alt="Current image" 
                                                                     class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                                                            </div>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="is_available" value="1" <?php echo $product['is_available'] ? 'checked' : ''; ?>>
                                                            <label class="form-check-label">Tersedia</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" name="update_product" class="btn btn-warning">Simpan Perubahan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">Belum ada menu</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Menu Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Menu</label>
                        <input type="text" class="form-control" name="name" placeholder="Contoh: Sate Ayam Special" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="Deskripsi menu..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Harga</label>
                            <input type="number" class="form-control" name="price" placeholder="25000" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stok</label>
                            <input type="number" class="form-control" name="stock" value="100" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select class="form-select" name="category_id">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gambar Menu</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                        <small class="text-muted">Format: JPG, PNG, GIF. Maksimal 2MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_product" class="btn btn-warning">Tambah Menu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
