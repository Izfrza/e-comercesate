-- ============================================
-- Database untuk Sate Madura E-Commerce
-- ============================================
-- Import file ini ke MySQL/MariaDB
--
-- CARA IMPORT DI HOSTING:
-- 1. Login ke cPanel → phpMyAdmin
-- 2. Buat database baru (tanpa perlu menjalankan file ini)
-- 3. Klik database tersebut, lalu pilih menu "Import"
-- 4. Upload file ini
--
-- CATATAN: 
-- - Baris CREATE DATABASE dan USE sudah dihapus
-- - Pastikan database sudah ada sebelum import

-- ============================================
-- TABEL USERS (pelanggan & admin)
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    address TEXT,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_token_expire DATETIME DEFAULT NULL
);

-- ============================================
-- TABEL CATEGORIES (kategori menu)
-- ============================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- TABEL PRODUCTS (menu sate)
-- ============================================
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    image VARCHAR(255) DEFAULT 'default-food.jpg',
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================
-- TABEL ORDERS (pesanan)
-- ============================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('qris', 'cod') NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    order_status ENUM('pending', 'confirmed', 'processing', 'on_delivery', 'delivered', 'cancelled') DEFAULT 'pending',
    delivery_address TEXT,
    delivery_phone VARCHAR(20),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================
-- TABEL ORDER_ITEMS (detail pesanan)
-- ============================================
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL
);

-- ============================================
-- INSERT DATA DEFAULT
-- ============================================

-- Insert categories
INSERT INTO categories (name, description) VALUES 
('Sate Ayam', 'Sate ayam madura pilihan'),
('Sate Sapi', 'Sate sapi empuk berkualitas'),
('Sate Kambing', 'Sate kambing lezat'),
('Minuman', 'Berbagai minuman segar'),
('Pendamping', 'Lontong, nasi, dll');

-- Insert products
INSERT INTO products (category_id, name, description, price, stock, image) VALUES 
(1, 'Sate Ayam Special', 'Sate ayam madura dengan bumbu khas', 25000, 100, 'sate-ayam-special.jpg'),
(1, 'Sate Ayam Biasa', 'Sate ayam madura regular', 20000, 100, 'sate-ayam-biasa.jpg'),
(1, 'Sate Ayam Jumbo', 'Sate ayam ukuran besar', 35000, 50, 'sate-ayam-jumbo.jpg'),
(2, 'Sate Sapi Special', 'Sate sapi empuk premium', 35000, 80, 'sate-sapi-special.jpg'),
(2, 'Sate Sapi Biasa', 'Sate sapi regular', 30000, 80, 'sate-sapi-biasa.jpg'),
(3, 'Sate Kambing', 'Sate kambing empuk tidak berbau', 40000, 50, 'sate-kambing.jpg'),
(4, 'Es Teh Manis', 'Es teh segar', 5000, 200, 'es-teh.jpg'),
(4, 'Es Jeruk', 'Es jeruk segar', 7000, 200, 'es-jeruk.jpg'),
(4, 'Air Mineral', 'Air mineral 600ml', 4000, 200, 'air-mineral.jpg'),
(5, 'Lontong', 'Lontong pendamping sate', 3000, 100, 'lontong.jpg'),
(5, 'Nasi Putih', 'Nasi putih hangat', 3000, 100, 'nasi.jpg');

-- Insert admin user (password: admin123)
INSERT INTO users (name, email, phone, password, role) VALUES 
('Admin Sate Madura', 'admin@satemadura.com', '0895402357182', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample customer
INSERT INTO users (name, email, phone, password, address, role) VALUES 
('Pelanggan Demo', 'demo@example.com', '081234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jl. Contoh No. 123', 'customer');
