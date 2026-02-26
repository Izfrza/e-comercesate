<?php
require_once 'config.php';

$error = '';
$success = '';
$email_sent = false;

// Check if user is already logged in
if (isLoggedIn()) {
    redirect(isAdmin() ? 'dashboard.php' : 'index.php');
}

// Handle forgot password form submission via Gmail
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    
    if (empty($email)) {
        $error = 'Email harus diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        $conn = getConnection();
        
        // Check if email exists
        $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expire = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Update user with reset token
            $update_stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expire = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $token, $expire, $user['id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            // Create reset link
            $reset_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['REQUEST_URI']) . "/reset_password.php?token=" . $token;
            
            // In production, send email via SMTP
            // For demo, we'll show the reset link and simulate success
            $email_sent = true;
            $success = 'Link reset password telah dikirim ke email Anda!';
            
            // For testing purposes, show the reset link
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_token'] = $token;
            
        } else {
            $error = 'Email tidak terdaftar!';
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
    <title>Lupa Password (Gmail) - Sate Madura</title>
    
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
            <div class="auth-header" style="background: linear-gradient(135deg, #4285F4 0%, #EA4335 100%);">
                <i class="fas fa-envelope fa-3x mb-3"></i>
                <h2>Lupa Password</h2>
                <p>Reset via Gmail</p>
            </div>
            <div class="auth-body">
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
                    
                    <div class="text-center mt-4">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Demo Mode:</strong> Untuk testing, klik link di bawah ini untuk reset password.
                            <?php if (isset($_SESSION['reset_token'])): ?>
                                <hr>
                                <a href="reset_password.php?token=<?php echo $_SESSION['reset_token']; ?>" class="btn btn-warning">
                                    <i class="fas fa-key me-2"></i>Klik untuk Reset Password
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!$success): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Masukkan email yang terdaftar. Link reset password akan dikirim ke email Anda.
                    </div>
                    
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <div class="mb-4">
                            <label for="email" class="form-label">Email Terdaftar</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-envelope text-danger"></i>
                                </span>
                                <input type="email" class="form-control border-start-0" id="email" name="email" placeholder="contoh@email.com" required>
                            </div>
                            <div class="form-text">Masukkan email yang Anda gunakan saat mendaftar.</div>
                            <div class="invalid-feedback">Email tidak valid!</div>
                        </div>
                        
                        <button type="submit" class="btn btn-danger w-100 py-3" style="background: linear-gradient(135deg, #4285F4 0%, #EA4335 100%); border: none;">
                            <i class="fas fa-paper-plane me-2"></i>Kirim Link Reset
                        </button>
                    </form>
                <?php endif; ?>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <p class="mb-2">Atau gunakan:</p>
                    <a href="forgot_password.php" class="btn btn-success">
                        <i class="fab fa-whatsapp me-2"></i>Reset via WhatsApp
                    </a>
                </div>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <p class="mb-0">Ingat password? <a href="login.php" class="text-warning fw-bold">Login</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
