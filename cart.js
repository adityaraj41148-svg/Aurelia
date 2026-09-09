/**
 * AA MART — STYLE. CHOICE. EVERYTHING.
 * Advanced Shopping Cart, Wishlist, Checkout & Coupon Engine
 */

let cart = JSON.parse(localStorage.getItem('aamart_cart') || '[]');
let wishlist = JSON.parse(localStorage.getItem('aamart_wishlist') || '[]');
let appliedCoupon = JSON.parse(localStorage.getItem('aamart_coupon') || 'null');

function saveCart() {
    localStorage.setItem('aamart_cart', JSON.stringify(cart));
    updateCartUI();
}

function saveWishlist() {
    localStorage.setItem('aamart_wishlist', JSON.stringify(wishlist));
    updateWishlistUI();
}

function saveCoupon(couponObj) {
    appliedCoupon = couponObj;
    if (couponObj) {
        localStorage.setItem('aamart_coupon', JSON.stringify(couponObj));
    } else {
        localStorage.removeItem('aamart_coupon');
    }
    updateCartUI();
}

// ----------------------------------------------------
// CART CORE OPERATIONS
// ----------------------------------------------------
function addToCart(product, selectedSize = null, selectedColor = null, showDrawer = false) {
    if (typeof product === 'number' || typeof product === 'string') {
        const found = PRODUCTS.find(p => p.id === parseInt(product, 10));
        if (found) product = found;
        else return;
    }

    if (!selectedSize && product.sizes && product.sizes.length > 0 && product.sizes[0] !== 'Free Size' && product.sizes[0] !== 'Standard' && product.sizes[0] !== 'Adjustable') {
        selectedSize = product.sizes[0];
    }

    const sizeKey = selectedSize || 'Standard';
    const colorKey = selectedColor || (product.colors ? product.colors[0] : 'Standard');

    const existingIndex = cart.findIndex(item => item.id === product.id && item.size === sizeKey && item.color === colorKey);

    if (existingIndex > -1) {
        cart[existingIndex].quantity += 1;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            price: parseFloat(product.price),
            originalPrice: parseFloat(product.originalPrice || product.price),
            image: product.image,
            size: sizeKey,
            color: colorKey,
            quantity: 1
        });
    }

    saveCart();
    if (typeof showToast === 'function') {
        showToast(`✓ Added "${product.name}" (${sizeKey}) to Cart!`, 'fa-shopping-bag');
    }

    if (showDrawer) {
        toggleCartDrawer(true);
    }
}

function buyNowProduct(productId, selectedSize = null) {
    const product = PRODUCTS.find(p => p.id === parseInt(productId, 10));
    if (!product) return;
    addToCart(product, selectedSize, null, false);
    toggleCartDrawer(true);
}

function changeQuantity(id, size, color, delta) {
    const itemIndex = cart.findIndex(i => i.id === id && i.size === size && i.color === color);
    if (itemIndex === -1) return;

    cart[itemIndex].quantity += delta;
    if (cart[itemIndex].quantity <= 0) {
        cart.splice(itemIndex, 1);
        if (typeof showToast === 'function') showToast('✓ Item removed from cart', 'fa-trash');
    }
    saveCart();
}

function removeFromCart(id, size, color) {
    cart = cart.filter(i => !(i.id === id && i.size === size && i.color === color));
    saveCart();
    if (typeof showToast === 'function') {
        showToast('✓ Item removed from cart', 'fa-trash');
    }
}

function moveToWishlist(id, size, color) {
    const item = cart.find(i => i.id === id && i.size === size && i.color === color);
    if (item) {
        const prod = PRODUCTS.find(p => p.id === id);
        if (prod) toggleWishlist(null, prod);
        removeFromCart(id, size, color);
    }
}

// ----------------------------------------------------
// WISHLIST OPERATIONS
// ----------------------------------------------------
function toggleWishlist(btnEl, product) {
    let prodObj = product;
    if (typeof product === 'number' || typeof product === 'string') {
        prodObj = PRODUCTS.find(p => p.id === parseInt(product, 10));
    }
    if (!prodObj) return;

    const existsIndex = wishlist.findIndex(w => w.id === prodObj.id);

    if (existsIndex > -1) {
        wishlist.splice(existsIndex, 1);
        if (btnEl) {
            const icon = btnEl.querySelector('i');
            if (icon) icon.className = 'far fa-heart text-slate-700';
        }
        if (typeof showToast === 'function') showToast(`❤️ Removed "${prodObj.name}" from Wishlist`, 'fa-heart-broken');
    } else {
        wishlist.push({
            id: prodObj.id,
            name: prodObj.name,
            price: prodObj.price,
            originalPrice: prodObj.originalPrice,
            image: prodObj.image
        });
        if (btnEl) {
            const icon = btnEl.querySelector('i');
            if (icon) icon.className = 'fas fa-heart text-rose-500';
        }
        if (typeof showToast === 'function') showToast(`❤️ Added "${prodObj.name}" to Wishlist!`, 'fa-heart');
    }

    saveWishlist();
}

// ----------------------------------------------------
// UI RENDERING & CALCULATIONS
// ----------------------------------------------------
function updateCartUI() {
    const cartBadge = document.getElementById('cart-count-badge');
    const floatCartBadge = document.getElementById('float-cart-count');
    const cartItemsEl = document.getElementById('cart-items');
    const cartSubtotalEl = document.getElementById('cart-subtotal');
    const cartDiscountEl = document.getElementById('cart-discount');
    const cartCouponDiscountEl = document.getElementById('cart-coupon-discount');
    const cartTotalEl = document.getElementById('cart-total');
    const freeShippingProgressEl = document.getElementById('free-shipping-progress');
    const freeShippingMsgEl = document.getElementById('free-shipping-msg');

    const totalCount = cart.reduce((sum, item) => sum + item.quantity, 0);
    const rawSubtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const originalSubtotal = cart.reduce((sum, item) => sum + (item.originalPrice * item.quantity), 0);
    const totalSavings = Math.max(0, originalSubtotal - rawSubtotal);

    // Coupon Calculation
    let couponDiscount = 0;
    if (appliedCoupon && COUPONS[appliedCoupon.code]) {
        const rule = COUPONS[appliedCoupon.code];
        if (rawSubtotal >= (rule.minSpend || 0)) {
            if (rule.type === 'flat') {
                couponDiscount = rule.value;
            } else if (rule.type === 'percent') {
                couponDiscount = Math.min(rule.maxDiscount || 99999, (rawSubtotal * rule.value) / 100);
            }
        } else {
            appliedCoupon = null;
            localStorage.removeItem('aamart_coupon');
        }
    }

    const finalTotal = Math.max(0, rawSubtotal - couponDiscount);

    // Update Badges
    [cartBadge, floatCartBadge].forEach(b => {
        if (b) {
            b.textContent = totalCount;
            if (totalCount > 0) b.classList.remove('hidden');
            else b.classList.add('hidden');
        }
    });

    // Free Shipping Progress (Threshold: ₹1,000)
    const shippingThreshold = 1000;
    if (freeShippingProgressEl && freeShippingMsgEl) {
        if (rawSubtotal >= shippingThreshold || cart.length === 0) {
            freeShippingProgressEl.style.width = cart.length === 0 ? '0%' : '100%';
            freeShippingMsgEl.innerHTML = cart.length === 0 ? 'Add ₹1,000 for FREE Delivery' : '🎉 You\'ve unlocked FREE Delivery!';
        } else {
            const diff = shippingThreshold - rawSubtotal;
            const pct = Math.min(100, (rawSubtotal / shippingThreshold) * 100);
            freeShippingProgressEl.style.width = `${pct}%`;
            freeShippingMsgEl.innerHTML = `Add <strong>₹${diff.toLocaleString('en-IN')}</strong> more for FREE Delivery`;
        }
    }

    // Summary Labels
    if (cartSubtotalEl) cartSubtotalEl.textContent = `₹${rawSubtotal.toLocaleString('en-IN')}`;
    if (cartDiscountEl) cartDiscountEl.textContent = `-₹${totalSavings.toLocaleString('en-IN')}`;
    if (cartCouponDiscountEl) cartCouponDiscountEl.textContent = `-₹${couponDiscount.toLocaleString('en-IN')}`;
    if (cartTotalEl) cartTotalEl.textContent = `₹${finalTotal.toLocaleString('en-IN')}`;

    // Render Items
    if (cartItemsEl) {
        if (cart.length === 0) {
            cartItemsEl.innerHTML = `
                <div class="flex flex-col items-center justify-center py-12 text-center space-y-3">
                    <div class="w-20 h-20 rounded-full bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600 text-3xl">
                        🛍️
                    </div>
                    <h4 class="font-bold text-slate-900 text-base">Your cart is empty!</h4>
                    <p class="text-xs text-slate-500 max-w-xs leading-relaxed">Discover top fashion, craft, footwear and deals from AA MART.</p>
                    <button onclick="toggleCartDrawer(false); window.location.hash='#products';" class="mt-2 inline-flex items-center space-x-2 bg-purple-600 text-white font-bold text-xs px-5 py-2.5 rounded-xl hover:bg-purple-700 transition shadow-md">
                        <span>EXPLORE COLLECTION</span>
                    </button>
                </div>
            `;
        } else {
            cartItemsEl.innerHTML = cart.map(item => `
                <div class="flex gap-3 p-3 rounded-2xl bg-white border border-slate-100 shadow-sm relative group">
                    <img src="${item.image}" alt="${item.name}" class="w-16 h-20 object-cover rounded-xl border border-slate-100">
                    <div class="flex-grow min-w-0 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start">
                                <h4 class="font-bold text-xs text-slate-900 truncate pr-2">${item.name}</h4>
                                <button onclick="removeFromCart(${item.id}, '${item.size}', '${item.color}')" class="text-slate-400 hover:text-rose-500 transition text-xs p-1" title="Remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="text-[10px] text-purple-600 font-medium mt-0.5">Size: ${item.size} • Color: ${item.color}</div>
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-xs font-black text-slate-900">₹${(item.price * item.quantity).toLocaleString('en-IN')}</span>
                            <div class="flex items-center space-x-1.5 bg-slate-50 border border-slate-200 rounded-lg px-2 py-0.5">
                                <button onclick="changeQuantity(${item.id}, '${item.size}', '${item.color}', -1)" class="text-slate-600 hover:text-purple-600 font-bold text-xs px-1">-</button>
                                <span class="text-xs font-bold text-slate-900 px-1">${item.quantity}</span>
                                <button onclick="changeQuantity(${item.id}, '${item.size}', '${item.color}', 1)" class="text-slate-600 hover:text-purple-600 font-bold text-xs px-1">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }
    }
}

function updateWishlistUI() {
    const wishlistBadge = document.getElementById('wishlist-count-badge');
    const wishlistItemsEl = document.getElementById('wishlist-items');

    if (wishlistBadge) {
        wishlistBadge.textContent = wishlist.length;
        if (wishlist.length > 0) wishlistBadge.classList.remove('hidden');
        else wishlistBadge.classList.add('hidden');
    }

    if (wishlistItemsEl) {
        if (wishlist.length === 0) {
            wishlistItemsEl.innerHTML = `
                <div class="flex flex-col items-center justify-center py-10 text-center space-y-2 text-slate-400">
                    <i class="fas fa-heart text-3xl text-slate-300"></i>
                    <p class="text-xs text-slate-500">Your wishlist is empty</p>
                </div>
            `;
        } else {
            wishlistItemsEl.innerHTML = wishlist.map(item => `
                <div class="flex items-center justify-between p-3 bg-white border border-slate-100 rounded-xl shadow-xs">
                    <div class="flex items-center gap-3">
                        <img src="${item.image}" alt="${item.name}" class="w-12 h-14 object-cover rounded-lg border border-slate-100">
                        <div>
                            <h4 class="font-bold text-xs text-slate-900 truncate max-w-[150px]">${item.name}</h4>
                            <span class="text-xs font-black text-purple-600">₹${item.price.toLocaleString('en-IN')}</span>
                        </div>
                    </div>
                    <button onclick="addToCart(${item.id}); toggleWishlist(null, ${item.id});" class="bg-purple-600 text-white font-bold text-[10px] px-3 py-1.5 rounded-lg hover:bg-purple-700 transition">
                        Move to Cart
                    </button>
                </div>
            `).join('');
        }
    }
}

// ----------------------------------------------------
// DRAWER TOGGLE LOGIC
// ----------------------------------------------------
function toggleCartDrawer(open = null) {
    const drawer = document.getElementById('cart-drawer');
    const overlay = document.getElementById('cart-drawer-overlay');
    if (!drawer) return;

    const isOpen = drawer.classList.contains('translate-x-0');
    const shouldOpen = open !== null ? open : !isOpen;

    if (shouldOpen) {
        drawer.classList.remove('hidden');
        if (overlay) overlay.classList.remove('hidden');
        requestAnimationFrame(() => {
            drawer.classList.remove('translate-x-full');
            drawer.classList.add('translate-x-0');
        });
    } else {
        drawer.classList.remove('translate-x-0');
        drawer.classList.add('translate-x-full');
        if (overlay) overlay.classList.add('hidden');
        setTimeout(() => drawer.classList.add('hidden'), 300);
    }
}

function toggleWishlistDrawer(open = null) {
    const drawer = document.getElementById('wishlist-drawer');
    const overlay = document.getElementById('wishlist-drawer-overlay');
    if (!drawer) return;

    const isOpen = drawer.classList.contains('translate-x-0');
    const shouldOpen = open !== null ? open : !isOpen;

    if (shouldOpen) {
        drawer.classList.remove('hidden');
        if (overlay) overlay.classList.remove('hidden');
        requestAnimationFrame(() => {
            drawer.classList.remove('translate-x-full');
            drawer.classList.add('translate-x-0');
        });
    } else {
        drawer.classList.remove('translate-x-0');
        drawer.classList.add('translate-x-full');
        if (overlay) overlay.classList.add('hidden');
        setTimeout(() => drawer.classList.add('hidden'), 300);
    }
}

function applyCouponCode(codeStr) {
    const code = (codeStr || '').trim().toUpperCase();
    if (!code) {
        if (typeof showToast === 'function') showToast('✕ Please enter a coupon code', 'fa-exclamation-circle');
        return;
    }

    const rule = COUPONS[code];
    if (!rule) {
        if (typeof showToast === 'function') showToast(`✕ Invalid coupon code "${code}"`, 'fa-times-circle');
        return;
    }

    const rawSubtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    if (rawSubtotal < rule.minSpend) {
        if (typeof showToast === 'function') showToast(`✕ Minimum spend of ₹${rule.minSpend.toLocaleString('en-IN')} required for ${code}`, 'fa-exclamation-circle');
        return;
    }

    saveCoupon(rule);
    if (typeof showToast === 'function') showToast(`✓ Coupon "${code}" applied successfully!`, 'fa-tag');
}

document.addEventListener('DOMContentLoaded', () => {
    updateCartUI();
    updateWishlistUI();
});
