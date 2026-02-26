<?php
require_once 'config.php';
$conn = getConnection();

// Get products
$sql = 'SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_available = 1 ORDER BY p.created_at DESC';
$result = $conn->query($sql);

echo 'Products HTML preview:' . PHP_EOL;
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="col-md-6 col-lg-4 menu-card-wrapper" data-category="' . (string)$row['category_id'] . '">' . PHP_EOL;
        echo '  <div class="menu-card">' . PHP_EOL;
        echo '    <h5>' . htmlspecialchars($row['name']) . '</h5>' . PHP_EOL;
        echo '    <p>Category: ' . htmlspecialchars($row['category_name']) . ' (ID: ' . $row['category_id'] . ')</p>' . PHP_EOL;
        echo '  </div>' . PHP_EOL;
        echo '</div>' . PHP_EOL;
    }
}
$conn->close();
?>