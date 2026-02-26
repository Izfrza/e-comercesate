<?php
require_once 'config.php';
$conn = getConnection();

// Check categories
$result = $conn->query('SELECT id, name FROM categories ORDER BY id');
echo 'Categories:' . PHP_EOL;
while ($row = $result->fetch_assoc()) {
    echo $row['id'] . ': ' . $row['name'] . PHP_EOL;
}

// Check products with categories
$result = $conn->query('SELECT p.name, p.category_id, c.name as cat_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.is_available = 1 ORDER BY p.category_id, p.name');
echo PHP_EOL . 'Products by category:' . PHP_EOL;
while ($row = $result->fetch_assoc()) {
    echo $row['name'] . ' -> Category ' . $row['category_id'] . ' (' . $row['cat_name'] . ')' . PHP_EOL;
}
$conn->close();
?>