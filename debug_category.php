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
    <title>Debug Category Filter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Debug Category Filter</h2>

        <h3>Categories from Database:</h3>
        <ul>
            <?php foreach ($categories as $category): ?>
                <li>ID: <?php echo $category['id']; ?> - Name: <?php echo htmlspecialchars($category['name']); ?></li>
            <?php endforeach; ?>
        </ul>

        <h3>Category Filter Buttons:</h3>
        <div class="d-flex flex-wrap gap-2 mb-4">
            <button class="btn btn-outline-warning category-filter active" data-category="all">Semua</button>
            <?php foreach ($categories as $category): ?>
                <button class="btn btn-outline-warning category-filter" data-category="<?php echo $category['id']; ?>">
                    <?php echo htmlspecialchars($category['name']); ?> (ID: <?php echo $category['id']; ?>)
                </button>
            <?php endforeach; ?>
        </div>

        <h3>Products with Categories:</h3>
        <div class="row g-4">
            <?php foreach ($products as $product): ?>
                <div class="col-md-4 menu-card-wrapper" data-category="<?php echo $product['category_id']; ?>">
                    <div class="border p-3">
                        <h5><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p>Category ID: <?php echo $product['category_id']; ?></p>
                        <p>Category Name: <?php echo htmlspecialchars($product['category_name']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        // Category filter buttons
        const categoryButtons = document.querySelectorAll('.category-filter');
        console.log('Found', categoryButtons.length, 'category filter buttons');
        categoryButtons.forEach(button => {
            button.addEventListener('click', function() {
                const categoryId = this.dataset.category;
                console.log('Category button clicked:', categoryId);
                filterByCategory(categoryId);

                // Update active state
                categoryButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Filter by category
        function filterByCategory(categoryId) {
            console.log('Filtering by category:', categoryId);
            const menuCards = document.querySelectorAll('.menu-card-wrapper');
            console.log('Found', menuCards.length, 'menu card wrappers');

            menuCards.forEach(card => {
                const cardCategory = card.dataset.category;
                console.log('Card category:', cardCategory, 'vs filter:', categoryId);

                if (categoryId === 'all' || cardCategory === categoryId) {
                    card.style.display = '';
                    console.log('Showing card');
                } else {
                    card.style.display = 'none';
                    console.log('Hiding card');
                }
            });
        }
    </script>
</body>
</html>