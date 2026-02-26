<?php
require_once 'config.php';

$error = '';
$success = false;
$token_valid = false;

// Check if user is already logged in
if (isLoggedIn()) {
    redirect(isAdmin() ? 'dashboard.php' : 'index.php');
}

// Check if token is provided
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = sanitize($_GET['token']);
    
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT id, name, email, reset_token_expire FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Check if token is expired
        $expire_time = strtotime($user['reset_token_expire']);
        $current_time = time();
        
        if ($expire_time > $current_time) {
            $token_valid = true;
            $user_id = $user['id'];
        } else {
            $error = 'Link reset password sudah expired! Silakan minta link baru.';
        }
    } else {
        $error = 'Token tidak valid!';
    }
    
    $stmt->close();
    $conn->close();
} else {
    $error = 'Token tidak ditemukan!';
}

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valid) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($password) || empty($confirm_password)) {
        $error = 'Password harus diisi!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak sama!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        // Hash new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update password and clear reset token
        $conn = getConnection();
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expire = NULL WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($stmt->execute()) {
            $success = true;
            $error = '';
        } else {
            $error = 'Terjadi kesalahan! Silakan coba lagi.';
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Sate Madura</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-key fa-3x mb-3"></i>
                <h2>Reset Password</h2>
                <p>Masukkan password baru Anda</p>
            </div>
            <div class="auth-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle fa-2x d-block text-center mb-3"></i>
                        <h5 class="text-center">Password Berhasil Diubah!</h5>
                        <p class="text-center mb-0">Silakan login dengan password baru Anda.</p>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="login.php" class="btn btn-warning w-100 py-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Login Sekarang
                        </a>
                    </div>
                <?php elseif ($token_valid): ?>
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password Baru</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-lock text-muted"></i>
                                </span>
                                <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="Minimal 6 karakter" required minlength="6">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">Password minimal 6 karakter!</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-lock text-muted"></i>
                                </span>
                                <input type="password" class="form-control border-start-0" id="confirm_password" name="confirm_password" placeholder="Masukkan kembali password" required>
                            </div>
                            <div class="invalid-feedback">Konfirmasi password harus sama!</div>
                        </div>
                        
                        <button type="submit" class="btn btn-warning w-100 py-3">
                            <i class="fas fa-save me-2"></i>Simpan Password Baru
                        </button>
                    </form>
                <?php else: ?>
                    <div class="text-center mt-4">
                        <a href="forgot_password.php" class="btn btn-warning">
                            <i class="fas fa-redo me-2"></i>Minta Link Baru
                        </a>
                    </div>
                <?php endif; ?>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <p class="mb-0">Ingat password? <a href="login.php" class="text-warning fw-bold">Login</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword')?.addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>
