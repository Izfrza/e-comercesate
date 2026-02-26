<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}
?>

<?php include 'header.php'; ?>

<div class="section-padding">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Keranjang Belanja</h2>
            <p class="section-subtitle">Periksa kembali pesanan Anda</p>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-body">
                        <div id="cart-items">
                            <!-- Cart items will be loaded via JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Ringkasan Pesanan</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span id="cart-subtotal">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Biaya Pengiriman</span>
                            <span>Rp 0</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total</strong>
                            <strong id="cart-total" class="text-warning">Rp 0</strong>
                        </div>
                        
                        <form method="POST" action="checkout.php" id="checkout-form">
                            <input type="hidden" name="cart_data" id="cart-data">
                            <button type="submit" class="btn btn-warning w-100 py-3" id="checkout-btn">
                                <i class="fas fa-credit-card me-2"></i>Lanjut ke Pembayaran
                            </button>
                        </form>
                        
                        <a href="index.php#menu" class="btn btn-outline-dark w-100 mt-3">
                            <i class="fas fa-arrow-left me-2"></i>Lanjut Belanja
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Disable checkout button if cart is empty
document.addEventListener('DOMContentLoaded', function() {
    const cart = JSON.parse(localStorage.getItem('sateCart')) || [];
    const checkoutBtn = document.getElementById('checkout-btn');
    const cartDataInput = document.getElementById('cart-data');
    
    if (cart.length === 0) {
        checkoutBtn.classList.add('disabled');
        checkoutBtn.style.pointerEvents = 'none';
        checkoutBtn.innerHTML = '<i class="fas fa-shopping-cart me-2"></i>Keranjang Kosong';
    } else {
        // Populate cart data
        cartDataInput.value = JSON.stringify(cart);
    }
});
</script>

<?php include 'footer.php'; ?>
