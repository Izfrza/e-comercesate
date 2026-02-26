<?php
require_once 'config.php';

$error = '';
$success = '';

// Check if user is already logged in
if (isLoggedIn()) {
    redirect(isAdmin() ? 'dashboard.php' : 'index.php');
}

// Handle forgot password form submission via WhatsApp
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = sanitize($_POST['phone']);
    
    if (empty($phone)) {
        $error = 'Nomor WhatsApp harus diisi!';
    } else {
        $conn = getConnection();
        
        // Check if phone number exists
        $stmt = $conn->prepare("SELECT id, name, phone FROM users WHERE phone = ?");
        $stmt->bind_param("s", $phone);
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
            
            // Create WhatsApp message
            $wa_message = "Halo Admin Sate Madura,%0ASaya *{$user['name']}* ingin reset password.%0ANomor saya: {$user['phone']}%0AToken reset: {$token}%0A%0APlease help me reset my password.";
            
            $success = 'Data ditemukan! Anda akan diarahkan ke WhatsApp untuk meminta reset password.';
            
            // Redirect to WhatsApp after a short delay
            echo '<script>
                setTimeout(function() {
                    window.location.href = "https://wa.me/' . WA_BUSINESS_NUMBER . '?text=' . $wa_message . '";
                }, 2000);
            </script>';
            
        } else {
            $error = 'Nomor WhatsApp tidak terdaftar!';
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
    <title>Lupa Password (WhatsApp) - Sate Madura</title>
    
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
                <i class="fab fa-whatsapp fa-3x mb-3"></i>
                <h2>Lupa Password</h2>
                <p>Reset via WhatsApp</p>
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
                        <div class="spinner-border text-warning" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Mengalihkan ke WhatsApp...</p>
                    </div>
                <?php endif; ?>
                
                <?php if (!$success): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Masukkan nomor WhatsApp yang terdaftar. Anda akan diarahkan ke chat WhatsApp kami untuk meminta reset password.
                    </div>
                    
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <div class="mb-4">
                            <label for="phone" class="form-label">Nomor WhatsApp Terdaftar</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fab fa-whatsapp text-success"></i>
                                </span>
                                <input type="tel" class="form-control border-start-0" id="phone" name="phone" placeholder="Contoh: 081234567890" required>
                            </div>
                            <div class="form-text">Masukkan nomor WhatsApp yang Anda gunakan saat mendaftar.</div>
                            <div class="invalid-feedback">Nomor WhatsApp harus diisi!</div>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100 py-3">
                            <i class="fab fa-whatsapp me-2"></i>Kirim via WhatsApp
                        </button>
                    </form>
                <?php endif; ?>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <p class="mb-2">Atau gunakan:</p>
                    <a href="forgot_password_gmail.php" class="btn btn-outline-primary">
                        <i class="fas fa-envelope me-2"></i>Reset via Gmail
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
