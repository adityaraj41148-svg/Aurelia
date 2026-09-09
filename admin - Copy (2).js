/**
 * AURELIA ADMIN PORTAL
 * Master Management Controller Script
 */

document.addEventListener('DOMContentLoaded', () => {
    const isLoginPage = window.location.pathname.endsWith('admin-login.html');
    if (!isLoginPage) {
        checkAdminAuth();
    }
});

function checkAdminAuth() {
    const session = localStorage.getItem('aurelia_admin_session_2026');
    const isLoginPage = window.location.pathname.endsWith('admin-login.html');

    if (!session && !isLoginPage) {
        window.location.href = 'admin-login.html';
    }
}

function handleAdminLogin(event) {
    if (event) event.preventDefault();
    const email = document.getElementById('admin-email')?.value.trim();
    const password = document.getElementById('admin-password')?.value.trim();
    const errorEl = document.getElementById('admin-login-error');

    if (email === 'admin@aurelia.com' && password === 'admin123') {
        const sessionData = {
            role: 'admin',
            email: email,
            loginTime: new Date().toISOString()
        };
        localStorage.setItem('aurelia_admin_session_2026', JSON.stringify(sessionData));
        window.location.href = 'admin-dashboard.html';
    } else {
        if (errorEl) {
            errorEl.textContent = '✕ Invalid credentials. Use demo: admin@aurelia.com / admin123';
            errorEl.classList.remove('hidden');
        }
    }
}

function handleAdminLogout() {
    localStorage.removeItem('aurelia_admin_session_2026');
    window.location.href = 'admin-login.html';
}

// ----------------------------------------------------
// DASHBOARD STATS CALCULATOR
// ----------------------------------------------------
function getAdminStats() {
    const products = JSON.parse(localStorage.getItem('aurelia_products_2026') || '[]');
    const customers = JSON.parse(localStorage.getItem('aurelia_customers_2026') || '[]');
    const orders = JSON.parse(localStorage.getItem('aurelia_orders_2026') || '[]');

    const totalRevenue = orders.reduce((sum, o) => sum + (o.total || 0), 0);
    const lowStock = products.filter(p => (p.stock || 10) <= 10);

    return {
        totalProducts: products.length,
        totalCustomers: customers.length,
        totalOrders: orders.length,
        totalRevenue: totalRevenue,
        lowStock: lowStock
    };
}

// ----------------------------------------------------
// ADMIN DASHBOARD OVERVIEW RENDERER
// ----------------------------------------------------
function renderAdminDashboard() {
    const stats = getAdminStats();

    const revEl = document.getElementById('stat-revenue');
    const ordEl = document.getElementById('stat-orders');
    const custEl = document.getElementById('stat-customers');
    const prodEl = document.getElementById('stat-products');

    if (revEl) revEl.textContent = `₹${stats.totalRevenue.toLocaleString('en-IN')}`;
    if (ordEl) ordEl.textContent = stats.totalOrders;
    if (custEl) custEl.textContent = stats.totalCustomers;
    if (prodEl) prodEl.textContent = stats.totalProducts;

    // Render Recent Orders Table
    const orders = JSON.parse(localStorage.getItem('aurelia_orders_2026') || '[]');
    const recentOrdersContainer = document.getElementById('recent-orders-rows');

    if (recentOrdersContainer) {
        if (orders.length === 0) {
            recentOrdersContainer.innerHTML = `<tr><td colspan="6" class="text-center py-6 text-slate-500">No orders placed yet.</td></tr>`;
        } else {
            recentOrdersContainer.innerHTML = orders.slice(0, 5).map(o => `
                <tr>
                    <td class="font-mono font-bold text-amber-400">${o.id}</td>
                    <td>${o.customerName}</td>
                    <td>${o.items ? o.items.length : 1} Items</td>
                    <td class="font-mono">₹${o.total.toLocaleString('en-IN')}</td>
                    <td><span class="px-2.5 py-1 rounded-full text-[10px] font-bold ${getStatusBadgeClass(o.status)}">${o.status}</span></td>
                    <td>${o.orderDate}</td>
                </tr>
            `).join('');
        }
    }
}

function getStatusBadgeClass(status) {
    switch ((status || '').toUpperCase()) {
        case 'PENDING': return 'status-badge-pending';
        case 'CONFIRMED': return 'status-badge-confirmed';
        case 'SHIPPED': return 'status-badge-shipped';
        case 'DELIVERED': return 'status-badge-delivered';
        case 'CANCELLED': return 'status-badge-cancelled';
        default: return 'status-badge-pending';
    }
}

// ----------------------------------------------------
// CUSTOMERS MANAGEMENT MODULE
// ----------------------------------------------------
function renderAdminCustomers() {
    const container = document.getElementById('admin-customers-rows');
    if (!container) return;

    let customers = JSON.parse(localStorage.getItem('aurelia_customers_2026') || '[]');
    const searchVal = (document.getElementById('cust-search-input')?.value || '').toLowerCase();
    const statusVal = document.getElementById('cust-status-filter')?.value || 'all';

    if (searchVal) {
        customers = customers.filter(c =>
            c.name.toLowerCase().includes(searchVal) ||
            c.email.toLowerCase().includes(searchVal) ||
            c.mobile.includes(searchVal) ||
            c.id.toLowerCase().includes(searchVal)
        );
    }

    if (statusVal !== 'all') {
        customers = customers.filter(c => c.status === statusVal);
    }

    if (customers.length === 0) {
        container.innerHTML = `<tr><td colspan="7" class="text-center py-8 text-slate-500">No customers found matching search criteria.</td></tr>`;
        return;
    }

    container.innerHTML = customers.map(c => `
        <tr>
            <td class="font-mono font-bold text-amber-400">${c.id}</td>
            <td class="font-semibold text-slate-100">${c.name}</td>
            <td>${c.email}</td>
            <td>${c.mobile}</td>
            <td>${c.regDate || '2026-01-15'}</td>
            <td><span class="px-2.5 py-1 rounded-full text-[10px] font-bold ${c.status === 'active' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30'}">${(c.status || 'active').toUpperCase()}</span></td>
            <td>
                <div class="flex gap-2">
                    <button onclick="openCustomerDetailsModal('${c.id}')" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs rounded-lg transition">View</button>
                    <button onclick="toggleCustomerStatus('${c.id}')" class="px-2.5 py-1 ${c.status === 'active' ? 'bg-rose-950/60 text-rose-400 border border-rose-500/30 hover:bg-rose-900' : 'bg-emerald-950/60 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-900'} text-xs rounded-lg transition">
                        ${c.status === 'active' ? 'Disable' : 'Enable'}
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function openCustomerDetailsModal(customerId) {
    const customers = JSON.parse(localStorage.getItem('aurelia_customers_2026') || '[]');
    const orders = JSON.parse(localStorage.getItem('aurelia_orders_2026') || '[]');
    const customer = customers.find(c => c.id === customerId);

    if (!customer) return;

    const custOrders = orders.filter(o => o.customerId === customerId || o.customerEmail === customer.email);
    const modal = document.getElementById('customer-modal');
    const content = document.getElementById('customer-modal-content');

    if (!modal || !content) return;

    content.innerHTML = `
        <div class="space-y-4 text-xs">
            <div class="border-b border-slate-800 pb-3 flex justify-between items-center">
                <div>
                    <h3 class="text-base font-bold text-slate-100">${customer.name}</h3>
                    <span class="text-amber-400 font-mono text-[11px]">${customer.id}</span>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold ${customer.status === 'active' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400'}">${(customer.status || 'active').toUpperCase()}</span>
            </div>

            <div class="grid grid-cols-2 gap-3 bg-slate-950 p-3 rounded-xl border border-slate-800">
                <div><span class="text-slate-500 block">Email:</span><strong class="text-slate-200">${customer.email}</strong></div>
                <div><span class="text-slate-500 block">Mobile:</span><strong class="text-slate-200">${customer.mobile}</strong></div>
                <div><span class="text-slate-500 block">Registration Date:</span><strong class="text-slate-200">${customer.regDate || '2026-01-15'}</strong></div>
                <div><span class="text-slate-500 block">Security:</span><strong class="text-emerald-400">🔒 Password Hashed (Hidden)</strong></div>
            </div>

            <div>
                <h4 class="font-bold text-slate-200 mb-2 uppercase text-[10px] tracking-wider text-amber-400">Order History (${custOrders.length})</h4>
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    ${custOrders.length === 0 ? '<p class="text-slate-500 italic">No orders placed by this customer.</p>' : custOrders.map(o => `
                        <div class="bg-slate-950 p-2.5 rounded-lg border border-slate-800 flex justify-between items-center">
                            <div>
                                <span class="font-mono font-bold text-amber-400 block">${o.id}</span>
                                <span class="text-slate-400 text-[10px]">${o.orderDate}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-mono text-slate-100 font-bold block">₹${o.total.toLocaleString('en-IN')}</span>
                                <span class="text-[10px] font-bold text-sky-400">${o.status}</span>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        </div>
    `;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeCustomerModal() {
    const modal = document.getElementById('customer-modal');
    if (modal) modal.classList.add('hidden'), modal.classList.remove('flex');
}

function toggleCustomerStatus(customerId) {
    let customers = JSON.parse(localStorage.getItem('aurelia_customers_2026') || '[]');
    const idx = customers.findIndex(c => c.id === customerId);
    if (idx > -1) {
        customers[idx].status = customers[idx].status === 'active' ? 'disabled' : 'active';
        localStorage.setItem('aurelia_customers_2026', JSON.stringify(customers));
        renderAdminCustomers();
    }
}

// ----------------------------------------------------
// ORDERS MANAGEMENT MODULE
// ----------------------------------------------------
function renderAdminOrders() {
    const container = document.getElementById('admin-orders-rows');
    if (!container) return;

    let orders = JSON.parse(localStorage.getItem('aurelia_orders_2026') || '[]');
    const searchVal = (document.getElementById('order-search-input')?.value || '').toLowerCase();
    const statusVal = document.getElementById('order-status-filter')?.value || 'all';

    if (searchVal) {
        orders = orders.filter(o =>
            o.id.toLowerCase().includes(searchVal) ||
            o.customerName.toLowerCase().includes(searchVal) ||
            o.customerEmail.toLowerCase().includes(searchVal)
        );
    }

    if (statusVal !== 'all') {
        orders = orders.filter(o => (o.status || '').toUpperCase() === statusVal.toUpperCase());
    }

    if (orders.length === 0) {
        container.innerHTML = `<tr><td colspan="7" class="text-center py-8 text-slate-500">No orders found.</td></tr>`;
        return;
    }

    container.innerHTML = orders.map((o, idx) => `
        <tr>
            <td class="font-mono font-bold text-amber-400">${o.id}</td>
            <td>
                <div class="font-bold text-slate-100">${o.customerName}</div>
                <div class="text-[10px] text-slate-400">${o.customerEmail}</div>
            </td>
            <td>${o.items ? o.items.length : 1} Items</td>
            <td class="font-mono font-bold text-slate-100">₹${o.total.toLocaleString('en-IN')}</td>
            <td><span class="text-slate-400 text-xs">${o.paymentMethod || 'UPI'}</span></td>
            <td>
                <select onchange="updateOrderStatus('${o.id}', this.value)" class="bg-slate-950 border border-slate-700 text-slate-200 text-xs font-bold rounded-lg px-2 py-1 focus:outline-none focus:border-amber-400 cursor-pointer">
                    <option value="PENDING" ${o.status === 'PENDING' ? 'selected' : ''}>PENDING</option>
                    <option value="CONFIRMED" ${o.status === 'CONFIRMED' ? 'selected' : ''}>CONFIRMED</option>
                    <option value="PACKED" ${o.status === 'PACKED' ? 'selected' : ''}>PACKED</option>
                    <option value="SHIPPED" ${o.status === 'SHIPPED' ? 'selected' : ''}>SHIPPED</option>
                    <option value="DELIVERED" ${o.status === 'DELIVERED' ? 'selected' : ''}>DELIVERED</option>
                    <option value="CANCELLED" ${o.status === 'CANCELLED' ? 'selected' : ''}>CANCELLED</option>
                </select>
            </td>
            <td>${o.orderDate}</td>
        </tr>
    `).join('');
}

function updateOrderStatus(orderId, newStatus) {
    let orders = JSON.parse(localStorage.getItem('aurelia_orders_2026') || '[]');
    const idx = orders.findIndex(o => o.id === orderId);

    if (idx > -1) {
        orders[idx].status = newStatus;
        localStorage.setItem('aurelia_orders_2026', JSON.stringify(orders));
        renderAdminOrders();
    }
}

// ----------------------------------------------------
// PRODUCTS MANAGEMENT MODULE (ADMIN CRUD)
// ----------------------------------------------------
function renderAdminProducts() {
    const container = document.getElementById('admin-products-rows');
    if (!container) return;

    let products = JSON.parse(localStorage.getItem('aurelia_products_2026') || '[]');
    const searchVal = (document.getElementById('prod-search-input')?.value || '').toLowerCase();
    const catVal = document.getElementById('prod-cat-filter')?.value || 'all';

    if (searchVal) {
        products = products.filter(p =>
            p.name.toLowerCase().includes(searchVal) ||
            p.brand.toLowerCase().includes(searchVal) ||
            p.id.toString().includes(searchVal)
        );
    }

    if (catVal !== 'all') {
        products = products.filter(p => p.category.toLowerCase() === catVal.toLowerCase());
    }

    if (products.length === 0) {
        container.innerHTML = `<tr><td colspan="7" class="text-center py-8 text-slate-500">No products found.</td></tr>`;
        return;
    }

    container.innerHTML = products.map(p => `
        <tr>
            <td>
                <img src="${p.image}" alt="${p.name}" class="w-10 h-12 object-cover rounded-lg bg-slate-950 border border-slate-800">
            </td>
            <td>
                <div class="font-bold text-slate-100 line-clamp-1">${p.name}</div>
                <span class="text-[10px] font-bold text-amber-400 uppercase">${p.brand}</span>
            </td>
            <td class="uppercase text-xs font-semibold text-slate-400">${p.category}</td>
            <td class="font-mono font-bold text-slate-100">₹${p.price.toLocaleString('en-IN')}</td>
            <td>
                <div class="flex items-center gap-2">
                    <span class="font-mono text-xs font-bold ${p.stock <= 10 ? 'text-rose-400' : 'text-emerald-400'}">${p.stock || 20}</span>
                    <button onclick="updateStockPrompt(${p.id})" class="text-[10px] text-slate-400 hover:text-amber-400 underline">Adjust</button>
                </div>
            </td>
            <td><span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">${p.badge || 'EDITORIAL'}</span></td>
            <td>
                <div class="flex gap-2">
                    <button onclick="openEditProductModal(${p.id})" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs rounded-lg transition">Edit</button>
                    <button onclick="deleteProduct(${p.id})" class="px-2.5 py-1 bg-rose-950/60 hover:bg-rose-900 text-rose-400 text-xs rounded-lg transition border border-rose-500/30">Delete</button>
                </div>
            </td>
        </tr>
    `).join('');
}

function updateStockPrompt(productId) {
    let products = JSON.parse(localStorage.getItem('aurelia_products_2026') || '[]');
    const idx = products.findIndex(p => p.id === productId);
    if (idx > -1) {
        const newStock = prompt(`Enter new stock quantity for "${products[idx].name}":`, products[idx].stock || 20);
        if (newStock !== null && !isNaN(parseInt(newStock, 10))) {
            products[idx].stock = Math.max(0, parseInt(newStock, 10));
            localStorage.setItem('aurelia_products_2026', JSON.stringify(products));
            renderAdminProducts();
        }
    }
}

function deleteProduct(productId) {
    if (!confirm('Are you sure you want to delete this product?')) return;
    let products = JSON.parse(localStorage.getItem('aurelia_products_2026') || '[]');
    products = products.filter(p => p.id !== productId);
    localStorage.setItem('aurelia_products_2026', JSON.stringify(products));
    renderAdminProducts();
}

function openAddProductModal() {
    const modal = document.getElementById('product-modal');
    if (modal) modal.classList.remove('hidden'), modal.classList.add('flex');
}

function closeProductModal() {
    const modal = document.getElementById('product-modal');
    if (modal) modal.classList.add('hidden'), modal.classList.remove('flex');
}

function handleSaveProduct(event) {
    if (event) event.preventDefault();
    let products = JSON.parse(localStorage.getItem('aurelia_products_2026') || '[]');

    const name = document.getElementById('prod-form-name').value.trim();
    const brand = document.getElementById('prod-form-brand').value.trim();
    const category = document.getElementById('prod-form-category').value.toLowerCase();
    const price = parseFloat(document.getElementById('prod-form-price').value);
    const originalPrice = parseFloat(document.getElementById('prod-form-orig-price').value) || price;
    const image = document.getElementById('prod-form-image').value.trim() || 'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?auto=format&fit=crop&w=800&q=80';
    const stock = parseInt(document.getElementById('prod-form-stock').value, 10) || 20;

    const newProd = {
        id: Date.now(),
        name,
        brand,
        category,
        price,
        originalPrice,
        oldPrice: originalPrice,
        discount: Math.round(((originalPrice - price) / originalPrice) * 100),
        rating: 5.0,
        reviews: 1,
        sizes: ["XS", "S", "M", "L", "XL"],
        colors: ["Default"],
        image,
        secondaryImage: image,
        description: "Handcrafted luxury editorial garment.",
        badge: "NEW",
        stock,
        isNew: true
    };

    products.unshift(newProd);
    localStorage.setItem('aurelia_products_2026', JSON.stringify(products));
    closeProductModal();
    renderAdminProducts();
}

// Global Expose
window.handleAdminLogin = handleAdminLogin;
window.handleAdminLogout = handleAdminLogout;
window.renderAdminDashboard = renderAdminDashboard;
window.renderAdminCustomers = renderAdminCustomers;
window.openCustomerDetailsModal = openCustomerDetailsModal;
window.closeCustomerModal = closeCustomerModal;
window.toggleCustomerStatus = toggleCustomerStatus;
window.renderAdminOrders = renderAdminOrders;
window.updateOrderStatus = updateOrderStatus;
window.renderAdminProducts = renderAdminProducts;
window.updateStockPrompt = updateStockPrompt;
window.deleteProduct = deleteProduct;
window.openAddProductModal = openAddProductModal;
window.closeProductModal = closeProductModal;
window.handleSaveProduct = handleSaveProduct;
