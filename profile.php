<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$conn = getConnection();

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    
    // Check if updating password
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($name) || empty($phone)) {
        $error = 'Nama dan nomor WhatsApp harus diisi!';
    } elseif (!empty($new_password)) {
        if ($new_password !== $confirm_password) {
            $error = 'Password dan konfirmasi password tidak sama!';
        } elseif (strlen($new_password) < 6) {
            $error = 'Password minimal 6 karakter!';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, address = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $phone, $address, $hashed_password, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $_SESSION['name'] = $name;
                $success = 'Profil dan password berhasil diperbarui!';
            } else {
                $error = 'Gagal memperbarui profil!';
            }
            $stmt->close();
        }
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $phone, $address, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $_SESSION['name'] = $name;
            $success = 'Profil berhasil diperbarui!';
        } else {
            $error = 'Gagal memperbarui profil!';
        }
        $stmt->close();
    }
}

$conn->close();
?>

<?php include 'header.php'; ?>

<div class="section-padding">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Profil Saya</h2>
            <p class="section-subtitle">Kelola informasi akun Anda</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Informasi Profil</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                <small class="text-muted">Email tidak dapat diubah</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Nomor WhatsApp</label>
                                <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <textarea class="form-control" name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                            </div>
                            
                            <hr>
                            
                            <h6 class="mb-3">Ubah Password (Opsional)</h6>
                            
                            <div class="mb-3">
                                <label class="form-label">Password Baru</label>
                                <input type="password" class="form-control" name="new_password" placeholder="Minimal 6 karakter">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control" name="confirm_password" placeholder="Masukkan kembali password">
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                                </button>
                                <a href="index.php" class="btn btn-outline-dark">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
