<?php
/**
 * AURELIA Admin Portal - Product Catalog Management
 */

require_once __DIR__ . '/admin-auth.php';

$db = getDB();
$error_message = '';
$success_message = '';

// Handle POST actions for Product CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0);
        $brand = trim($_POST['brand'] ?? 'AURELIA STUDIO');
        $gender = $_POST['gender'] ?? 'Unisex';
        $price = (float)($_POST['price'] ?? 0);
        $sale_price = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
        $stock = (int)($_POST['stock'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $status = $_POST['status'] ?? 'active';

        if (empty($name) || $category_id <= 0 || $price <= 0) {
            $error_message = 'Please provide a valid product name, category, and price.';
        } else {
            if (empty($image)) {
                $image = 'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?auto=format&fit=crop&w=800&q=80';
            }
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-')) . '-' . time();

            try {
                $stmt = $db->prepare("INSERT INTO products (name, slug, category_id, brand, gender, price, sale_price, stock, description, image, status) VALUES (:name, :slug, :cid, :brand, :gender, :price, :sale_price, :stock, :desc, :image, :status)");
                $stmt->execute([
                    'name' => $name,
                    'slug' => $slug,
                    'cid' => $category_id,
                    'brand' => $brand,
                    'gender' => $gender,
                    'price' => $price,
                    'sale_price' => $sale_price,
                    'stock' => $stock,
                    'desc' => $description,
                    'image' => $image,
                    'status' => $status
                ]);
                $success_message = 'Product "' . htmlspecialchars($name) . '" added successfully!';
            } catch (Exception $e) {
                $error_message = 'Failed to add product: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0);
        $brand = trim($_POST['brand'] ?? 'AURELIA STUDIO');
        $gender = $_POST['gender'] ?? 'Unisex';
        $price = (float)($_POST['price'] ?? 0);
        $sale_price = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
        $stock = (int)($_POST['stock'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $status = $_POST['status'] ?? 'active';

        if ($id <= 0 || empty($name) || $category_id <= 0 || $price <= 0) {
            $error_message = 'Invalid product details provided for update.';
        } else {
            try {
                $stmt = $db->prepare("UPDATE products SET name = :name, category_id = :cid, brand = :brand, gender = :gender, price = :price, sale_price = :sale_price, stock = :stock, description = :desc, image = :image, status = :status WHERE id = :id");
                $stmt->execute([
                    'id' => $id,
                    'name' => $name,
                    'cid' => $category_id,
                    'brand' => $brand,
                    'gender' => $gender,
                    'price' => $price,
                    'sale_price' => $sale_price,
                    'stock' => $stock,
                    'desc' => $description,
                    'image' => $image,
                    'status' => $status
                ]);
                $success_message = 'Product updated successfully!';
            } catch (Exception $e) {
                $error_message = 'Failed to update product: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $db->prepare("DELETE FROM products WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $success_message = 'Product deleted successfully!';
            } catch (Exception $e) {
                $error_message = 'Failed to delete product: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'toggle_status') {
        $id = (int)($_POST['id'] ?? 0);
        $current_status = $_POST['current_status'] ?? 'active';
        $new_status = ($current_status === 'active') ? 'inactive' : 'active';
        if ($id > 0) {
            try {
                $stmt = $db->prepare("UPDATE products SET status = :status WHERE id = :id");
                $stmt->execute(['status' => $new_status, 'id' => $id]);
                $success_message = 'Product status updated to ' . strtoupper($new_status) . '!';
            } catch (Exception $e) {
                $error_message = 'Failed to update status: ' . $e->getMessage();
            }
        }
    }
}

// Fetch categories for filter & modal dropdown
$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Filtering & Search
$search = trim($_GET['search'] ?? '');
$cat_filter = (int)($_GET['category'] ?? 0);

$query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];

if ($cat_filter > 0) {
    $query .= " AND p.category_id = :cat_id";
    $params['cat_id'] = $cat_filter;
}

if (!empty($search)) {
    $query .= " AND (p.name LIKE :search OR p.brand LIKE :search OR p.id = :search_exact)";
    $params['search'] = "%$search%";
    $params['search_exact'] = is_numeric($search) ? (int)$search : 0;
}

$query .= " ORDER BY p.id DESC";
$stmt_products = $db->prepare($query);
$stmt_products->execute($params);
$products = $stmt_products->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Catalog Management | Admin Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-body min-h-screen flex">

    <!-- Sidebar Navigation -->
    <aside class="w-64 admin-sidebar p-6 flex flex-col justify-between shrink-0 hidden md:flex min-h-screen">
        <div class="space-y-8">
            <div class="flex items-center gap-3 border-b border-slate-800 pb-6">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-400 font-black text-xl flex items-center justify-center">A</div>
                <div>
                    <h2 class="font-bold text-slate-100 text-sm tracking-widest">A U R E L I A</h2>
                    <span class="text-[9px] font-bold text-amber-400 tracking-widest uppercase">ADMIN DASHBOARD</span>
                </div>
            </div>

            <nav class="space-y-1">
                <a href="admin-dashboard.php" class="admin-nav-link"><i class="fas fa-chart-pie w-5"></i> Dashboard</a>
                <a href="admin-customers.php" class="admin-nav-link"><i class="fas fa-users w-5"></i> Customers</a>
                <a href="admin-products.php" class="admin-nav-link active"><i class="fas fa-box w-5"></i> Products</a>
                <a href="admin-orders.php" class="admin-nav-link"><i class="fas fa-shopping-cart w-5"></i> Orders</a>
                <a href="admin-categories.html" class="admin-nav-link"><i class="fas fa-th-large w-5"></i> Categories</a>
                <a href="admin-coupons.html" class="admin-nav-link"><i class="fas fa-ticket-alt w-5"></i> Coupons</a>
                <a href="admin-reviews.html" class="admin-nav-link"><i class="fas fa-star w-5"></i> Reviews</a>
                <a href="admin-analytics.html" class="admin-nav-link"><i class="fas fa-chart-line w-5"></i> Analytics</a>
                <a href="admin-settings.html" class="admin-nav-link"><i class="fas fa-cog w-5"></i> Settings</a>
            </nav>
        </div>

        <div class="pt-6 border-t border-slate-800">
            <a href="admin-logout.php" class="w-full text-left admin-nav-link text-rose-400 hover:bg-rose-500/10 flex items-center gap-2">
                <i class="fas fa-sign-out-alt w-5"></i> Admin Logout
            </a>
        </div>
    </aside>

    <main class="flex-grow p-6 md:p-10 space-y-8 overflow-x-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-800 pb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-100">PRODUCT MANAGEMENT</h1>
                <p class="text-xs text-slate-400 mt-1">Add, edit, delete products and adjust inventory stock levels.</p>
            </div>
            <button onclick="openAddModal()" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs rounded-xl transition flex items-center gap-2 cursor-pointer">
                <i class="fas fa-plus"></i> ADD NEW PRODUCT
            </button>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="p-3 bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs rounded-xl font-medium flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="p-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs rounded-xl font-medium flex items-center gap-2">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>

        <!-- Search & Filter Controls -->
        <form method="GET" action="admin-products.php" class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-[#0f172a] p-4 border border-[#1e293b] rounded-2xl">
            <div class="relative w-full sm:w-80">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search product name, brand, ID..." class="w-full bg-[#020617] border border-[#1e293b] rounded-xl px-3.5 py-2 text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-amber-400">
                <button type="submit" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-amber-400 text-xs"><i class="fas fa-search"></i></button>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto">
                <select name="category" onchange="this.form.submit()" class="bg-[#020617] border border-[#1e293b] text-slate-200 text-xs font-semibold rounded-xl px-3 py-2 focus:outline-none focus:border-amber-400 cursor-pointer">
                    <option value="0">Filter: All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat_filter == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($search) || $cat_filter > 0): ?>
                    <a href="admin-products.php" class="px-3 py-2 bg-slate-800 text-slate-300 text-xs rounded-xl hover:bg-slate-700">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Products Table -->
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product & Brand</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock Qty</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-6 text-slate-500">No products found matching criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $prod): ?>
                            <tr>
                                <td>
                                    <img src="<?= htmlspecialchars($prod['image']) ?>" alt="Product" class="w-10 h-12 object-cover rounded-lg border border-slate-800 bg-slate-900" onerror="this.src='https://images.unsplash.com/photo-1539109136881-3be0616acf4b?auto=format&fit=crop&w=800&q=80'">
                                </td>
                                <td>
                                    <p class="font-bold text-slate-100"><?= htmlspecialchars($prod['name']) ?></p>
                                    <p class="text-[10px] text-slate-400 uppercase font-mono"><?= htmlspecialchars($prod['brand'] ?? 'AURELIA STUDIO') ?> • ID #<?= $prod['id'] ?></p>
                                </td>
                                <td class="text-slate-300 text-xs">
                                    <span class="px-2 py-1 bg-slate-800 border border-slate-700 rounded-lg"><?= htmlspecialchars($prod['category_name'] ?? 'General') ?></span>
                                </td>
                                <td class="font-mono font-bold text-slate-100">
                                    ₹<?= number_format((float)$prod['price'], 2) ?>
                                    <?php if (!empty($prod['sale_price']) && $prod['sale_price'] < $prod['price']): ?>
                                        <span class="block text-[10px] text-amber-400 font-normal">Sale: ₹<?= number_format((float)$prod['sale_price'], 2) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="font-mono font-bold <?= $prod['stock'] <= 10 ? 'text-rose-400' : 'text-emerald-400' ?>">
                                        <?= $prod['stock'] ?> units
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" action="admin-products.php" class="inline">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="id" value="<?= $prod['id'] ?>">
                                        <input type="hidden" name="current_status" value="<?= $prod['status'] ?>">
                                        <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-bold border transition cursor-pointer <?= $prod['status'] === 'active' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/30 hover:bg-rose-500/20' ?>">
                                            <?= strtoupper($prod['status']) ?>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <button onclick='openEditModal(<?= json_encode($prod) ?>)' class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-amber-400 text-xs font-bold rounded-lg transition cursor-pointer" title="Edit Product">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form method="POST" action="admin-products.php" onsubmit="return confirm('Are you sure you want to delete this product?');" class="inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $prod['id'] ?>">
                                            <button type="submit" class="px-2.5 py-1.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 text-xs font-bold rounded-lg border border-rose-500/30 transition cursor-pointer" title="Delete Product">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Product Add / Edit Modal -->
    <div id="product-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs">
        <div class="bg-[#0f172a] border border-[#1e293b] p-6 max-w-lg w-full rounded-2xl relative space-y-4 shadow-2xl overflow-y-auto max-h-[90vh]">
            <div class="flex justify-between items-center border-b border-slate-800 pb-3">
                <h3 id="modal-title" class="text-sm font-bold text-slate-100 uppercase tracking-wider">ADD NEW PRODUCT</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-100 text-lg cursor-pointer"><i class="fas fa-times"></i></button>
            </div>

            <form method="POST" action="admin-products.php" class="space-y-4 text-xs">
                <input type="hidden" name="action" id="form-action" value="add">
                <input type="hidden" name="id" id="prod-id" value="">

                <div>
                    <label class="block font-bold text-slate-300 mb-1 uppercase text-[10px]">PRODUCT NAME</label>
                    <input type="text" name="name" id="prod-name" required placeholder="e.g. Linen Draped Shirt" class="w-full bg-[#020617] border border-[#1e293b] rounded-xl px-3 py-2.5 text-slate-100 focus:outline-none focus:border-amber-400">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-300 mb-1 uppercase text-[10px]">CATEGORY</label>
                        <select name="category_id" id="prod-category" required class="w-full bg-[#020617] border border-[#1e293b] rounded-xl px-3 py-2.5 text-slate-100 focus:outline-none focus:border-amber-400">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 mb-1 uppercase text-[10px]">BRAND</label>
                        <input type="text" name="brand" id="prod-brand" value="AURELIA STUDIO" class="w-full bg-[#020617] border border-[#1e293b] rounded-xl px-3 py-2.5 text-slate-100 focus:outline-none focus:border-amber-400">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-slate-300 mb-1 uppercase text-[10px]">PRICE (₹)</label>
                        <input type="number" step="0.01" name="price" id="prod-price" required placeholder="4990.00" class="w-full bg-[#020617] border border-[#1e293b] rounded-xl px-3 py-2.5 text-slate-100 focus:outline-none focus:border-amber-400">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 mb-1 uppercase text-[10px]">SALE PRICE (₹)</label>
                        <input type="number" step="0.01" name="sale_price" id="prod-sale-price" placeholder="Optional" class="w-full bg-[#020617] border border-[#1e293b] rounded-xl px-3 py-2.5 text-slate-100 focus:outline-none focus:border-amber-400">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 mb-1 uppercase text-[10px]">STOCK QTY</label>
                        <input type="number" name="stock" id="prod-stock" required value="25" class="w-full bg-[#020617] border border-[#1e293b] rounded-xl px-3 py-2.5 text-slate-100 focus:outline-none focus:border-amber-400">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-300 mb-1 uppercase text-[10px]">GENDER</label>
                        <select name="gender" id="prod-gender" class="w-full bg-[#020617] border border-[#1e293b] rounded-xl px-3 py-2.5 text-slate-100 focus:outline-none focus:border-amber-400">
                            <option value="Unisex">Unisex</option>
                            <option value="Women">Women</option>
                            <option value="Men">Men</option>
                            <option value="Kids">Kids</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-300 mb-1 uppercase text-[10px]">STATUS</label>
                        <select name="status" id="prod-status" class="w-full bg-[#020617] border border-[#1e293b] rounded-xl px-3 py-2.5 text-slate-100 focus:outline-none focus:border-amber-400">
                            <option value="active">Active (Available)</option>
                            <option value="inactive">Inactive (Unavailable)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-300 mb-1 uppercase text-[10px]">IMAGE URL</label>
                    <input type="text" name="image" id="prod-image" placeholder="https://images.unsplash.com/..." class="w-full bg-[#020617] border border-[#1e293b] rounded-xl px-3 py-2.5 text-slate-100 focus:outline-none focus:border-amber-400">
                </div>

                <div>
                    <label class="block font-bold text-slate-300 mb-1 uppercase text-[10px]">DESCRIPTION</label>
                    <textarea name="description" id="prod-desc" rows="3" placeholder="Product details..." class="w-full bg-[#020617] border border-[#1e293b] rounded-xl px-3 py-2.5 text-slate-100 focus:outline-none focus:border-amber-400"></textarea>
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <button type="button" onclick="closeModal()" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold rounded-xl cursor-pointer">Cancel</button>
                    <button type="submit" id="form-submit-btn" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl uppercase tracking-wider cursor-pointer">SAVE PRODUCT</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('modal-title').textContent = 'ADD NEW PRODUCT';
            document.getElementById('form-action').value = 'add';
            document.getElementById('prod-id').value = '';
            document.getElementById('prod-name').value = '';
            document.getElementById('prod-brand').value = 'AURELIA STUDIO';
            document.getElementById('prod-price').value = '';
            document.getElementById('prod-sale-price').value = '';
            document.getElementById('prod-stock').value = '25';
            document.getElementById('prod-image').value = '';
            document.getElementById('prod-desc').value = '';
            document.getElementById('product-modal').classList.remove('hidden');
            document.getElementById('product-modal').classList.add('flex');
        }

        function openEditModal(prod) {
            document.getElementById('modal-title').textContent = 'EDIT PRODUCT #' + prod.id;
            document.getElementById('form-action').value = 'edit';
            document.getElementById('prod-id').value = prod.id;
            document.getElementById('prod-name').value = prod.name;
            document.getElementById('prod-category').value = prod.category_id;
            document.getElementById('prod-brand').value = prod.brand || 'AURELIA STUDIO';
            document.getElementById('prod-price').value = prod.price;
            document.getElementById('prod-sale-price').value = prod.sale_price || '';
            document.getElementById('prod-stock').value = prod.stock;
            document.getElementById('prod-gender').value = prod.gender || 'Unisex';
            document.getElementById('prod-status').value = prod.status || 'active';
            document.getElementById('prod-image').value = prod.image || '';
            document.getElementById('prod-desc').value = prod.description || '';
            document.getElementById('product-modal').classList.remove('hidden');
            document.getElementById('product-modal').classList.add('flex');
        }

        function closeModal() {
            document.getElementById('product-modal').classList.add('hidden');
            document.getElementById('product-modal').classList.remove('flex');
        }
    </script>
</body>
</html>
