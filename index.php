<?php
require_once 'config.php';

// Get categories from database
$conn = getConnection();
$categories_sql = "SELECT * FROM categories ORDER BY name";
$categories_result = $conn->query($categories_sql);
$categories = [];
if ($categories_result && $categories_result->num_rows > 0) {
    while ($row = $categories_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Get products from database
$sql = "SELECT p.*, c.name as category_name FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.is_available = 1 
        ORDER BY p.created_at DESC";
$result = $conn->query($sql);

$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$conn->close();
?>

<?php include 'header.php'; ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-7">
                <div class="hero-content">
                    <h1 class="hero-title fade-in">Nikmati Kelezatan <span>Sate Madura</span> Asli</h1>
                    <p class="hero-subtitle fade-in fade-in-delay-1">Diproses dengan daging pilihan dan bumbu rahasia turun-temurun. Rasakan kenikmatan autentik dari Madura langsung di rumah Anda!</p>
                    <div class="fade-in fade-in-delay-2">
                        <a href="#menu" class="btn btn-warning me-2 mb-2">
                            <i class="fas fa-utensils me-2"></i>Pesan Sekarang
                        </a>
                        <a href="#about" class="btn btn-outline-warning mb-2">
                            <i class="fas fa-info-circle me-2"></i>Tentang Kami
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="feature-box fade-in">
                    <div class="feature-icon">
                        <i class="fas fa-fire"></i>
                    </div>
                    <h5>Bumbu Khas Madura</h5>
                    <p class="text-muted">Resep turun temurun dengan rempah pilihan</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-box fade-in fade-in-delay-1">
                    <div class="feature-icon">
                        <i class="fas fa-truck-fast"></i>
                    </div>
                    <h5>Pengiriman Cepat</h5>
                    <p class="text-muted">Pesanan diantar langsung ke rumah Anda</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-box fade-in fade-in-delay-2">
                    <div class="feature-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h5>Kualitas Terjamin</h5>
                    <p class="text-muted">Daging segar dan bersih setiap hari</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Menu Section -->
<section id="menu" class="section-padding">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Menu Kami</h2>
            <p class="section-subtitle">Pilih berbagai menu sate favorit Anda</p>
        </div>
        
        <!-- Category Filter -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <button class="btn btn-outline-warning category-filter active" data-category="all">Semua</button>
                    <?php foreach ($categories as $category): ?>
                        <button class="btn btn-outline-warning category-filter" data-category="<?php echo (string)$category['id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Search -->
        <div class="row mb-4">
            <div class="col-md-6 mx-auto">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" id="search-input" class="form-control border-start-0" placeholder="Cari menu...">
                </div>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="row g-4" id="products-grid">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $product): ?>
                    <div class="col-md-6 col-lg-4 menu-card-wrapper" data-category="<?php echo (string)$product['category_id']; ?>">
                        <div class="menu-card">
                            <div class="menu-image">
                                <img src="assets/images/<?php echo htmlspecialchars($product['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     onerror="this.src='assets/images/default-food.jpg'">
                                <?php if ($product['stock'] < 10): ?>
                                    <span class="menu-badge">Tersisa <?php echo $product['stock']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="menu-content">
                                <h5 class="menu-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="menu-description"><?php echo htmlspecialchars($product['description']); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="menu-price">Rp <?php echo formatNumber($product['price']); ?></span>
                                    <span class="menu-stock <?php echo $product['stock'] < 5 ? 'low' : ''; ?>">
                                        <i class="fas fa-box me-1"></i>Stok: <?php echo $product['stock']; ?>
                                    </span>
                                </div>
                                <?php if ($product['stock'] > 0): ?>
                                    <button class="btn btn-primary w-100 mt-3" onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>', <?php echo $product['price']; ?>, '<?php echo $product['image']; ?>')">
                                        <i class="fas fa-cart-plus me-2"></i>Tambah ke Keranjang
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-secondary w-100 mt-3" disabled>
                                        <i class="fas fa-times-circle me-2"></i>Stok Habis
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted">Menu tidak tersedia saat ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- About Section -->
<section id="about" class="section-padding bg-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <img src="https://images.unsplash.com/photo-1555939594-58d7cb561ad1?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Tentang Sate Madura" class="img-fluid rounded-20 shadow">
            </div>
            <div class="col-lg-6">
                <h2 class="section-title">Tentang Kami</h2>
                <p class="lead">Sate Madura asli dengan kualitas terbaik sejak 2020.</p>
                <p class="text-muted">Kami menghadirkan kelezatan sate madura autentik dengan bahan-bahan segar dan bumbu rahasia yang telah diwariskan secara turun-temurun. Setiap tusuk sate kami buat dengan penuh kasih sayang untuk memastikan kepuasan pelanggan.</p>
                
                <div class="row mt-4">
                    <div class="col-6">
                        <div class="d-flex align-items-center mb-3">
                            <div class="feature-icon-small bg-warning me-3">
                                <i class="fas fa-check text-dark"></i>
                            </div>
                            <span>Bahan Fresh</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center mb-3">
                            <div class="feature-icon-small bg-warning me-3">
                                <i class="fas fa-check text-dark"></i>
                            </div>
                            <span>Bumbu Autentik</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center mb-3">
                            <div class="feature-icon-small bg-warning me-3">
                                <i class="fas fa-check text-dark"></i>
                            </div>
                            <span>Pengiriman Cepat</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center mb-3">
                            <div class="feature-icon-small bg-warning me-3">
                                <i class="fas fa-check text-dark"></i>
                            </div>
                            <span>Harga Terjangkau</span>
                        </div>
                    </div>
                </div>
                
                <a href="#menu" class="btn btn-warning mt-3">
                    <i class="fas fa-shopping-cart me-2"></i>Pesan Sekarang
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="section-padding">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Hubungi Kami</h2>
            <p class="section-subtitle">Punya pertanyaan? Silakan hubungi kami</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-body p-5">
                        <div class="row text-center">
                            <div class="col-md-4 mb-4">
                                <div class="contact-icon mb-3">
                                    <i class="fas fa-phone fa-2x text-warning"></i>
                                </div>
                                <h6>Telepon</h6>
                                <p class="text-muted mb-0">0895402357182</p>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="contact-icon mb-3">
                                    <i class="fas fa-envelope fa-2x text-warning"></i>
                                </div>
                                <h6>Email</h6>
                                <p class="text-muted mb-0">info@satemadura.com</p>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="contact-icon mb-3">
                                    <i class="fab fa-whatsapp fa-2x text-warning"></i>
                                </div>
                                <h6>WhatsApp</h6>
                                <p class="text-muted mb-0">0895402357182</p>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="https://wa.me/0895402357182?text=Halo%20saya%20ingin%20memesan%20sate" target="_blank" class="btn btn-success">
                                <i class="fab fa-whatsapp me-2"></i>Chat via WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.feature-icon-small {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.contact-icon {
    width: 80px;
    height: 80px;
    background: rgba(212, 160, 23, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}
</style>

<?php include 'footer.php'; ?>
