<?php
$page_title = "Shopping Cart - AA Mart";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="container mx-auto px-4 sm:px-6 py-12 min-h-[70vh] flex flex-col justify-center">
    <!-- Header Title -->
    <div id="cart-header-title" class="text-center mb-8 hidden">
        <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Your Shopping Cart</h1>
        <p class="text-slate-500 text-xs mt-1">Review your items, apply promo coupons, and proceed to checkout</p>
    </div>

    <!-- Empty Cart Section -->
    <div id="empty-cart-container" class="max-w-xl mx-auto w-full text-center bg-white rounded-3xl p-8 sm:p-12 shadow-xl border border-slate-100">
        <div class="relative w-48 h-48 mx-auto mb-6 flex items-center justify-center bg-indigo-50 rounded-full text-indigo-500 text-6xl">
            <i class="fas fa-shopping-bag"></i>
        </div>
        <h2 class="text-2xl font-black text-slate-800 mb-2">Your shopping cart is empty</h2>
        <p class="text-slate-500 text-xs sm:text-sm max-w-sm mx-auto mb-8">
            Explore our curated fashion collections for Men, Women, and Kids and fill your cart with styles!
        </p>
        <a href="category.php" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-sm px-8 py-3.5 rounded-2xl shadow-lg transition">
            <i class="fas fa-shopping-cart"></i>
            <span>Browse Fashion Catalog</span>
        </a>
    </div>

    <!-- Active Cart Items Section -->
    <div id="active-cart-container" class="max-w-5xl mx-auto w-full hidden">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <!-- Left Column: Product List (8 cols) -->
            <div class="lg:col-span-8 bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-4">
                <div class="flex justify-between items-center pb-4 border-b border-slate-100">
                    <h2 class="text-lg font-black text-slate-800">Cart Products (<span id="cart-item-count-badge">0</span>)</h2>
                    <button onclick="clearCart()" class="text-xs text-rose-500 hover:text-rose-700 font-bold transition flex items-center gap-1">
                        <i class="fas fa-trash-alt"></i>
                        <span>Clear Cart</span>
                    </button>
                </div>

                <!-- Free Shipping Progress Bar -->
                <div class="p-3.5 bg-emerald-50 rounded-2xl border border-emerald-100 text-xs">
                    <div id="free-shipping-msg" class="font-bold text-emerald-800 flex items-center gap-1.5 mb-1.5">
                        <i class="fas fa-truck text-emerald-600"></i> Free Delivery Progress
                    </div>
                    <div class="w-full bg-emerald-200 rounded-full h-2 overflow-hidden">
                        <div id="free-shipping-bar" class="bg-emerald-600 h-full rounded-full transition-all duration-500" style="width: 100%"></div>
                    </div>
                </div>

                <div id="full-cart-page-items" class="space-y-4 divide-y divide-slate-100">
                    <!-- Rendered dynamically by JS -->
                </div>

                <div class="mt-6 border-t border-slate-100 pt-4 flex justify-between items-center">
                    <a href="category.php" class="inline-flex items-center gap-2 text-indigo-600 font-bold text-xs hover:text-indigo-800 transition">
                        <i class="fas fa-arrow-left"></i>
                        <span>Continue Shopping</span>
                    </a>
                </div>
            </div>

            <!-- Right Column: Coupon & Order Summary (4 cols) -->
            <div class="lg:col-span-4 space-y-6">

                <!-- Coupon Code Form -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-700 mb-3 flex items-center gap-1.5">
                        <i class="fas fa-ticket-alt text-amber-500"></i> Apply Promo Coupon
                    </h3>
                    <div class="flex gap-2">
                        <input type="text" id="coupon-code-input" placeholder="e.g. WELCOME10" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono uppercase font-bold outline-none focus:ring-2 focus:ring-indigo-500">
                        <button type="button" onclick="applyCoupon()" class="bg-slate-900 hover:bg-indigo-600 text-white font-bold text-xs px-4 py-2 rounded-xl transition">Apply</button>
                    </div>
                    <div id="coupon-message" class="text-[11px] font-bold mt-2 hidden"></div>
                    <div class="text-[10px] text-slate-400 mt-2">Available Coupons: <strong class="text-slate-600 font-mono">WELCOME10</strong> (10% Off), <strong class="text-slate-600 font-mono">FLAT500</strong> (₹500 Off), <strong class="text-slate-600 font-mono">ONAM2026</strong> (₹300 Off)</div>
                </div>

                <!-- Order Summary Box -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 space-y-4">
                    <h3 class="text-base font-black text-slate-900 pb-3 border-b border-slate-100">Order Summary</h3>

                    <div class="space-y-2.5 text-xs text-slate-600">
                        <div class="flex justify-between">
                            <span>Subtotal</span>
                            <span id="summary-subtotal" class="font-bold text-slate-800">₹0.00</span>
                        </div>
                        <div id="summary-discount-row" class="flex justify-between text-rose-600 font-bold hidden">
                            <span>Coupon Discount</span>
                            <span id="summary-discount">-₹0.00</span>
                        </div>
                        <div class="flex justify-between text-emerald-600 font-bold">
                            <span>Delivery Charge</span>
                            <span>FREE</span>
                        </div>
                        <div class="flex justify-between border-t border-slate-100 pt-3 text-base font-black text-slate-900">
                            <span>Final Total</span>
                            <span id="cart-page-total" class="text-indigo-600">₹0.00</span>
                        </div>
                    </div>

                    <a href="checkout.php" class="w-full bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-extrabold text-sm py-4 rounded-2xl shadow-lg transition flex items-center justify-center gap-2">
                        <span>Proceed to Checkout</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

            </div>
        </div>
    </div>

    <script>
        let appliedCoupon = null;
        let couponDiscount = 0;

        function renderCartPage() {
            const emptyContainer = document.getElementById('empty-cart-container');
            const activeContainer = document.getElementById('active-cart-container');
            const titleHeader = document.getElementById('cart-header-title');
            const itemsDiv = document.getElementById('full-cart-page-items');
            const totalEl = document.getElementById('cart-page-total');
            const subtotalEl = document.getElementById('summary-subtotal');
            const discountRow = document.getElementById('summary-discount-row');
            const discountEl = document.getElementById('summary-discount');
            const countBadge = document.getElementById('cart-item-count-badge');
            const shippingBar = document.getElementById('free-shipping-bar');
            const shippingMsg = document.getElementById('free-shipping-msg');

            const cart = JSON.parse(localStorage.getItem('aamart_cart') || '[]');

            if (cart.length === 0) {
                emptyContainer.classList.remove('hidden');
                activeContainer.classList.add('hidden');
                titleHeader.classList.add('hidden');
            } else {
                emptyContainer.classList.add('hidden');
                activeContainer.classList.remove('hidden');
                titleHeader.classList.remove('hidden');

                let subtotal = 0;
                let totalItemsCount = 0;

                itemsDiv.innerHTML = cart.map(item => {
                    const itemSubtotal = item.price * item.quantity;
                    subtotal += itemSubtotal;
                    totalItemsCount += item.quantity;

                    return `
                        <div class="flex flex-col sm:flex-row items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-100 gap-4 transition">
                            <div class="flex items-center gap-4 w-full sm:w-auto">
                                <img src="${item.image || 'assets/images/placeholder.svg'}" alt="${item.name}" loading="lazy" onerror="this.onerror=null; this.src='assets/images/placeholder.svg';" class="w-16 h-16 object-cover rounded-xl shadow-sm border bg-white">
                                <div>
                                    <h3 class="font-bold text-slate-800 text-xs sm:text-sm leading-snug">${item.name}</h3>
                                    <p class="text-xs text-indigo-600 font-extrabold mt-0.5">₹${item.price.toFixed(2)}</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between sm:justify-end w-full sm:w-auto gap-6">
                                <div class="flex items-center space-x-2 bg-white px-2.5 py-1 rounded-xl border border-slate-200 shadow-sm">
                                    <button onclick="changeQuantity(${item.id}, -1); renderCartPage();" class="w-6 h-6 flex items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 font-bold text-xs transition">-</button>
                                    <span class="font-extrabold text-slate-800 min-w-6 text-center text-xs">${item.quantity}</span>
                                    <button onclick="changeQuantity(${item.id}, 1); renderCartPage();" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 font-bold text-xs transition">+</button>
                                </div>
                                <span class="font-black text-slate-900 text-sm min-w-20 text-right">₹${itemSubtotal.toFixed(2)}</span>
                                <button onclick="removeFromCart(${item.id}); renderCartPage();" class="text-rose-400 hover:text-rose-600 p-2 text-base transition" title="Remove item">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    `;
                }).join('');

                // Free Shipping Threshold (₹999)
                const freeThreshold = 999;
                const freePct = Math.min(100, (subtotal / freeThreshold) * 100);
                if (shippingBar) shippingBar.style.width = freePct + '%';
                if (shippingMsg) {
                    if (subtotal >= freeThreshold) {
                        shippingMsg.innerHTML = `<i class="fas fa-check-circle text-emerald-600 mr-1"></i> You unlocked FREE Shipping!`;
                    } else {
                        shippingMsg.innerHTML = `<i class="fas fa-truck text-emerald-600 mr-1"></i> Add ₹${(freeThreshold - subtotal).toFixed(2)} more for FREE Shipping!`;
                    }
                }

                // Coupon calculation
                if (appliedCoupon === 'WELCOME10') {
                    couponDiscount = subtotal * 0.10;
                } else if (appliedCoupon === 'FLAT500') {
                    couponDiscount = Math.min(500, subtotal);
                } else if (appliedCoupon === 'ONAM2026') {
                    couponDiscount = Math.min(300, subtotal);
                } else {
                    couponDiscount = 0;
                }

                const finalTotal = Math.max(0, subtotal - couponDiscount);

                if (countBadge) countBadge.textContent = totalItemsCount;
                if (subtotalEl) subtotalEl.textContent = `₹${subtotal.toFixed(2)}`;
                if (totalEl) totalEl.textContent = `₹${finalTotal.toFixed(2)}`;

                if (couponDiscount > 0) {
                    discountRow.classList.remove('hidden');
                    discountEl.textContent = `-₹${couponDiscount.toFixed(2)}`;
                } else {
                    discountRow.classList.add('hidden');
                }
            }
        }

        function applyCoupon() {
            const input = document.getElementById('coupon-code-input');
            const msg = document.getElementById('coupon-message');
            const code = input.value.trim().toUpperCase();

            if (code === 'WELCOME10' || code === 'FLAT500' || code === 'ONAM2026' || code === 'ICICI10') {
                appliedCoupon = code;
                msg.className = 'text-[11px] font-bold mt-2 text-emerald-600';
                msg.textContent = `Coupon "${code}" applied successfully!`;
                msg.classList.remove('hidden');
            } else {
                appliedCoupon = null;
                msg.className = 'text-[11px] font-bold mt-2 text-rose-600';
                msg.textContent = 'Invalid coupon code. Try WELCOME10 or FLAT500.';
                msg.classList.remove('hidden');
            }
            renderCartPage();
        }

        function clearCart() {
            if (confirm('Are you sure you want to clear your shopping cart?')) {
                localStorage.removeItem('aamart_cart');
                if (typeof updateCartUI === 'function') updateCartUI();
                renderCartPage();
            }
        }

        document.addEventListener('DOMContentLoaded', renderCartPage);
    </script>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>