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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Filter Fix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .menu-card-wrapper { transition: opacity 0.3s; }
        .menu-card { border: 1px solid #ddd; padding: 15px; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Test Filter Kategori - Fix</h2>

        <!-- Category Filter -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <button class="btn btn-outline-warning category-filter active" data-category="all">Semua</button>
                    <?php foreach ($categories as $category): ?>
                        <button class="btn btn-outline-warning category-filter" data-category="<?php echo (string)$category['id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?> (ID: <?php echo $category['id']; ?>)
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="row g-4" id="products-grid">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $product): ?>
                    <div class="col-md-6 col-lg-4 menu-card-wrapper" data-category="<?php echo (string)$product['category_id']; ?>">
                        <div class="menu-card">
                            <h5><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p>Kategori: <?php echo htmlspecialchars($product['category_name']); ?> (ID: <?php echo $product['category_id']; ?>)</p>
                            <p>Harga: Rp <?php echo number_format($product['price']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded');

            // Category filter buttons
            const categoryButtons = document.querySelectorAll('.category-filter');
            console.log('Found', categoryButtons.length, 'category buttons');

            categoryButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const categoryId = this.dataset.category;
                    console.log('Clicked category:', categoryId);
                    filterByCategory(categoryId);

                    // Update active state
                    categoryButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        });

        // Filter by category
        function filterByCategory(categoryId) {
            console.log('Filtering by:', categoryId);
            const menuCards = document.querySelectorAll('.menu-card-wrapper');
            console.log('Found', menuCards.length, 'cards');

            let visibleCount = 0;
            menuCards.forEach(card => {
                const cardCategory = card.dataset.category;
                console.log('Card category:', cardCategory, 'Filter:', categoryId);

                if (categoryId === 'all' || cardCategory === categoryId) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            console.log('Visible cards:', visibleCount);
        }
    </script>
</body>
</html>