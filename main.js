/* Custom JavaScript - Sate Madura E-Commerce */

// Cart functionality
let cart = JSON.parse(localStorage.getItem('sateCart')) || [];

// Update cart count in navbar
function updateCartCount() {
    const count = cart.reduce((total, item) => total + item.quantity, 0);
    const cartCountElements = document.querySelectorAll('#cart-count');
    cartCountElements.forEach(el => el.textContent = count);
}

// Add item to cart
function addToCart(productId, name, price, image) {
    const existingItem = cart.find(item => item.id === productId);
    
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({
            id: productId,
            name: name,
            price: price,
            image: image,
            quantity: 1
        });
    }
    
    localStorage.setItem('sateCart', JSON.stringify(cart));
    updateCartCount();
    showToast('Item added to cart!', 'success');
}

// Remove item from cart
function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    localStorage.setItem('sateCart', JSON.stringify(cart));
    updateCartCount();
    renderCart();
}

// Update item quantity
function updateQuantity(productId, quantity) {
    if (quantity < 1) {
        removeFromCart(productId);
        return;
    }
    
    const item = cart.find(item => item.id === productId);
    if (item) {
        item.quantity = quantity;
        localStorage.setItem('sateCart', JSON.stringify(cart));
        renderCart();
    }
}

// Calculate cart total
function calculateTotal() {
    return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
}

// Render cart items
function renderCart() {
    const cartItems = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');
    const cartSubtotal = document.getElementById('cart-subtotal');
    
    if (!cartItems) return;
    
    if (cart.length === 0) {
        cartItems.innerHTML = '<div class="text-center py-5"><i class="fas fa-shopping-cart fa-4x text-muted"></i><p class="mt-3">Keranjang Anda kosong</p></div>';
        if (cartTotal) cartTotal.innerHTML = 'Rp 0';
        if (cartSubtotal) cartSubtotal.innerHTML = 'Rp 0';
        return;
    }
    
    let html = '';
    cart.forEach(item => {
        const itemImage = item.image && item.image !== 'default-food.jpg' 
            ? `assets/images/${item.image}`
            : 'assets/images/default-food.jpg';
        
        html += `
            <div class="cart-item d-flex align-items-center">
                <div class="cart-image me-3">
                    <img src="${itemImage}" alt="${item.name}" onerror="this.src='assets/images/default-food.jpg'">
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1">${item.name}</h6>
                    <p class="text-warning mb-2">Rp ${formatNumber(item.price)}</p>
                </div>
                <div class="d-flex align-items-center me-3">
                    <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${item.id}, ${item.quantity - 1})">
                        <i class="fas fa-minus"></i>
                    </button>
                    <span class="mx-2">${item.quantity}</span>
                    <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${item.id}, ${item.quantity + 1})">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="text-end">
                    <p class="fw-bold mb-0">Rp ${formatNumber(item.price * item.quantity)}</p>
                    <button class="btn btn-sm btn-danger" onclick="removeFromCart(${item.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    cartItems.innerHTML = html;
    if (cartTotal) cartTotal.innerHTML = 'Rp ' + formatNumber(calculateTotal());
    if (cartSubtotal) cartSubtotal.innerHTML = 'Rp ' + formatNumber(calculateTotal());
}

// Render checkout items
function renderCheckoutItems() {
    const checkoutItems = document.getElementById('checkout-items');
    const checkoutSubtotal = document.getElementById('checkout-subtotal');
    const checkoutTotal = document.getElementById('checkout-total');
    
    if (!checkoutItems) return;
    
    if (cart.length === 0) {
        checkoutItems.innerHTML = '<div class="text-center py-3"><p class="text-muted">Keranjang kosong</p></div>';
        return;
    }
    
    let html = '';
    cart.forEach(item => {
        const itemImage = item.image && item.image !== 'default-food.jpg' 
            ? `https://images.unsplash.com/photo-1564676713077-4e3a1c2b5f8d?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80`
            : 'https://images.unsplash.com/photo-1564676713077-4e3a1c2b5f8d?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80';
        
        html += `
            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                <div class="cart-image me-3" style="width: 60px; height: 60px;">
                    <img src="${itemImage}" alt="${item.name}" class="img-fluid rounded" onerror="this.src='https://via.placeholder.com/60?text=No+Image'">
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1" style="font-size: 0.9rem;">${item.name}</h6>
                    <p class="text-muted mb-0" style="font-size: 0.85rem;">Rp ${formatNumber(item.price)} x ${item.quantity}</p>
                </div>
                <div class="text-end">
                    <strong>Rp ${formatNumber(item.price * item.quantity)}</strong>
                </div>
            </div>
        `;
    });
    
    checkoutItems.innerHTML = html;
    if (checkoutSubtotal) checkoutSubtotal.innerHTML = 'Rp ' + formatNumber(calculateTotal());
    if (checkoutTotal) checkoutTotal.innerHTML = 'Rp ' + formatNumber(calculateTotal());
}

// Format number with thousand separator
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Show toast notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast-notification toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Show loading spinner
function showLoading() {
    const spinner = document.createElement('div');
    spinner.className = 'spinner-overlay';
    spinner.innerHTML = '<div class="spinner-border text-warning" role="status"><span class="visually-hidden">Loading...</span></div>';
    document.body.appendChild(spinner);
}

// Hide loading spinner
function hideLoading() {
    const spinner = document.querySelector('.spinner-overlay');
    if (spinner) {
        spinner.remove();
    }
}

// Confirm deleteinner) spinner
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

// Search functionality
function searchProducts() {
    const searchInput = document.getElementById('search-input');
    const filter = searchInput.value.toUpperCase();
    const menuCards = document.querySelectorAll('.menu-card');
    
    menuCards.forEach(card => {
        const title = card.querySelector('.menu-title').textContent;
        const description = card.querySelector('.menu-description').textContent;
        
        if (title.toUpperCase().indexOf(filter) > -1 || description.toUpperCase().indexOf(filter) > -1) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

// Filter by category
function filterByCategory(categoryId) {
    const menuCards = document.querySelectorAll('.menu-card-wrapper');
    
    menuCards.forEach(card => {
        if (categoryId === 'all' || card.dataset.category === categoryId) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

// WhatsApp redirect
function redirectToWhatsApp(phone, message) {
    const encodedMessage = encodeURIComponent(message);
    window.open(`https://wa.me/${phone}?text=${encodedMessage}`, '_blank');
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Copied to clipboard!', 'success');
    });
}

// Initialize on document ready
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
    
    // Initialize cart page if on cart page
    if (document.getElementById('cart-items')) {
        renderCart();
    }
    
    // Initialize checkout page if on checkout page
    if (document.getElementById('checkout-items')) {
        renderCheckoutItems();
    }
    
    // Search functionality
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('keyup', searchProducts);
    }
    
    // Delay category filter initialization to ensure DOM is fully ready
    setTimeout(function() {
        initializeCategoryFilter();
    }, 100);
});

// Initialize category filter
function initializeCategoryFilter() {
    const categoryButtons = document.querySelectorAll('.category-filter');
    const menuCards = document.querySelectorAll('.menu-card-wrapper');
    
    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = this.dataset.category;
            filterByCategory(categoryId);
            
            // Update active state
            categoryButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Form validation
(function() {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
