<?php
require_once 'config.php';

$conn = getConnection();

echo "Checking database...\n";

// Check tables
$result = $conn->query("SHOW TABLES");
echo "Tables:\n";
while ($row = $result->fetch_array()) {
    echo "- " . $row[0] . "\n";
}

// Check users
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$row = $result->fetch_assoc();
echo "\nUsers count: " . $row['count'] . "\n";

// Check categories
$result = $conn->query("SELECT COUNT(*) as count FROM categories");
$row = $result->fetch_assoc();
echo "Categories count: " . $row['count'] . "\n";

// Check products
$result = $conn->query("SELECT COUNT(*) as count FROM products");
$row = $result->fetch_assoc();
echo "Products count: " . $row['count'] . "\n";

$conn->close();
?>