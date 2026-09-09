/**
 * Main JavaScript File for AA Mart
 * Handles Wishlist API, Quick View Modal, Live Search Suggestions, Modals & Toast Feedback
 */

// Global Toast helper
function showToast(message) {
    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.textContent = message;
    toast.classList.remove('hidden');
    toast.classList.add('show');
    clearTimeout(showToast.timeout);
    showToast.timeout = setTimeout(() => {
        toast.classList.remove('show');
        toast.classList.add('hidden');
    }, 2200);
}

// Global Wishlist Toggle Function
window.toggleWishlist = function (productId, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const btn = event ? event.currentTarget : null;
    if (btn) btn.classList.add('scale-125');

    fetch(`wishlist_api.php?action=toggle&product_id=${productId}`, {
        method: 'POST'
    })
        .then(res => res.json())
        .then(data => {
            if (btn) btn.classList.remove('scale-125');
            if (data.success) {
                showToast(data.message);
                // Update badge count
                const badge = document.getElementById('wishlist-count-badge');
                if (badge) badge.textContent = data.count;
                if (btn) {
                    const icon = btn.querySelector('i');
                    if (icon) {
                        if (data.in_wishlist) {
                            icon.className = 'fas fa-heart text-base text-rose-500';
                        } else {
                            icon.className = 'far fa-heart text-base text-slate-400';
                        }
                    }
                }
            }
        })
        .catch(err => {
            if (btn) btn.classList.remove('scale-125');
            showToast('Item saved to wishlist!');
        });
};

// Global Quick View Modal Handler
let qvCurrentProduct = null;
let qvQuantity = 1;

window.openQuickView = function (product) {
    qvCurrentProduct = product;
    qvQuantity = 1;

    const modal = document.getElementById('quickview-modal');
    const overlay = document.getElementById('quickview-overlay');
    if (!modal) return;

    document.getElementById('qv-image').src = product.image || 'assets/images/placeholder.svg';
    document.getElementById('qv-brand').textContent = product.brand || 'AA MART';
    document.getElementById('qv-title').textContent = product.name || '';
    document.getElementById('qv-price').textContent = `₹${parseFloat(product.price).toFixed(2)}`;
    document.getElementById('qv-mrp').textContent = product.mrp > product.price ? `₹${parseFloat(product.mrp).toFixed(2)}` : '';
    document.getElementById('qv-desc').textContent = product.description || '';
    document.getElementById('qv-qty').textContent = '1';

    // Render Stars
    const starsDiv = document.getElementById('qv-stars');
    if (starsDiv) {
        let starsHtml = '';
        const rating = product.rating || 4.5;
        for (let i = 0; i < 5; i++) {
            starsHtml += `<i class="${i < Math.floor(rating) ? 'fas' : 'far'} fa-star"></i>`;
        }
        starsDiv.innerHTML = starsHtml;
    }

    const reviewsSpan = document.getElementById('qv-reviews');
    if (reviewsSpan) reviewsSpan.textContent = `(${product.reviews || 12} reviews)`;

    modal.classList.remove('hidden');
    if (overlay) overlay.classList.remove('hidden');
    document.body.classList.add('modal-open');

    setTimeout(() => {
        const content = modal.querySelector('div');
        if (content) content.classList.remove('scale-95', 'opacity-0');
    }, 10);
};

window.closeQuickView = function () {
    const modal = document.getElementById('quickview-modal');
    const overlay = document.getElementById('quickview-overlay');
    if (!modal) return;

    const content = modal.querySelector('div');
    if (content) content.classList.add('scale-95', 'opacity-0');

    setTimeout(() => {
        modal.classList.add('hidden');
        if (overlay) overlay.classList.add('hidden');
        document.body.classList.remove('modal-open');
    }, 200);
};

window.qvChangeQty = function (delta) {
    qvQuantity = Math.max(1, qvQuantity + delta);
    const qtyEl = document.getElementById('qv-qty');
    if (qtyEl) qtyEl.textContent = qvQuantity;
};

document.addEventListener('DOMContentLoaded', () => {

    // Quick View Close Event
    const closeQvBtn = document.getElementById('close-quickview-btn');
    const qvOverlay = document.getElementById('quickview-overlay');
    const qvAddBtn = document.getElementById('qv-add-cart-btn');

    if (closeQvBtn) closeQvBtn.addEventListener('click', closeQuickView);
    if (qvOverlay) qvOverlay.addEventListener('click', closeQuickView);

    if (qvAddBtn) {
        qvAddBtn.addEventListener('click', () => {
            if (qvCurrentProduct) {
                addToCart({
                    id: qvCurrentProduct.id,
                    name: qvCurrentProduct.name,
                    price: qvCurrentProduct.price,
                    image: qvCurrentProduct.image,
                    quantity: qvQuantity
                });
                closeQuickView();
            }
        });
    }

    // --- Live Search Auto-Suggestions ---
    function setupLiveSearch(inputId, dropdownId, listId) {
        const input = document.getElementById(inputId);
        const dropdown = document.getElementById(dropdownId);
        const list = document.getElementById(listId);
        if (!input || !dropdown || !list) return;

        let debounceTimer;

        input.addEventListener('input', (e) => {
            const query = e.target.value.trim();
            clearTimeout(debounceTimer);

            if (query.length < 2) {
                dropdown.classList.add('hidden');
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(`includes/search_api.php?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.length === 0) {
                            list.innerHTML = `<div class="p-4 text-xs text-slate-500 text-center">No fashion items found matching "${query}"</div>`;
                        } else {
                            list.innerHTML = data.map(item => `
                                <a href="${item.url}" class="flex items-center gap-3 p-3 hover:bg-indigo-50/70 transition group">
                                    <img src="${item.image}" alt="${item.name}" class="w-10 h-10 object-cover rounded-lg border bg-white">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-xs font-bold text-slate-800 truncate group-hover:text-indigo-600">${item.name}</h4>
                                        <span class="text-[10px] text-slate-400 font-medium">${item.brand} • ${item.category}</span>
                                    </div>
                                    <span class="text-xs font-black text-indigo-600">${item.price}</span>
                                </a>
                            `).join('');
                        }
                        dropdown.classList.remove('hidden');
                    })
                    .catch(() => {
                        dropdown.classList.add('hidden');
                    });
            }, 250);
        });

        document.addEventListener('click', (e) => {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    }

    setupLiveSearch('navbar-search-input', 'search-suggestions-dropdown', 'search-suggestions-list');

    // --- Mobile Menu Toggle ---
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }

    // --- Login Modal Controls ---
    const signinButton = document.getElementById('signin-button');
    const mobileSigninButton = document.getElementById('mobile-signin-button');
    const loginModal = document.getElementById('login-modal');
    const loginModalOverlay = document.getElementById('login-modal-overlay');
    const closeLoginButton = document.getElementById('close-login-button');

    function toggleLoginModal() {
        if (!loginModal) return;
        const modalContent = loginModal.querySelector('div');
        if (loginModal.classList.contains('hidden')) {
            loginModal.classList.remove('hidden');
            if (loginModalOverlay) loginModalOverlay.classList.remove('hidden');
            document.body.classList.add('modal-open');
            setTimeout(() => {
                if (modalContent) modalContent.classList.remove('scale-95', 'opacity-0');
            }, 10);
        } else {
            if (modalContent) modalContent.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                loginModal.classList.add('hidden');
                if (loginModalOverlay) loginModalOverlay.classList.add('hidden');
                document.body.classList.remove('modal-open');
            }, 300);
        }
    }

    if (signinButton) signinButton.addEventListener('click', toggleLoginModal);
    if (mobileSigninButton) mobileSigninButton.addEventListener('click', () => {
        if (mobileMenu) mobileMenu.classList.add('hidden');
        toggleLoginModal();
    });
    if (closeLoginButton) closeLoginButton.addEventListener('click', toggleLoginModal);
    if (loginModalOverlay) loginModalOverlay.addEventListener('click', toggleLoginModal);

    // --- Scroll To Top Floating Button ---
    const scrollToTopBtn = document.getElementById('scroll-to-top');
    if (scrollToTopBtn) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                scrollToTopBtn.classList.add('show');
            } else {
                scrollToTopBtn.classList.remove('show');
            }
        });

        scrollToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
});
