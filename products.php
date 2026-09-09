<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$admin_title = "Manage Products - AA Mart Admin";
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$msg = '';
$error = '';

// Handle Product Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "CSRF error.";
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'create') {
            $name = sanitize($_POST['name'] ?? '');
            $category_id = (int)($_POST['category_id'] ?? 0);
            $price = (float)($_POST['price'] ?? 0);
            $stock = (int)($_POST['stock'] ?? 0);
            $description = sanitize($_POST['description'] ?? '');
            $image = sanitize($_POST['image'] ?? '');
            $slug = slugify($name);

            if ($name && $category_id > 0 && $price > 0) {
                $stmt = $db->prepare("INSERT INTO products (category_id, name, slug, description, price, stock, image, status) VALUES (:cat, :name, :slug, :desc, :price, :stock, :image, 'active')");
                $stmt->execute([
                    'cat' => $category_id,
                    'name' => $name,
                    'slug' => $slug,
                    'desc' => $description,
                    'price' => $price,
                    'stock' => $stock,
                    'image' => $image ?: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=900&q=80'
                ]);
                $msg = "Product added successfully!";
            } else {
                $error = "Please fill in all required product fields.";
            }
        } elseif ($action === 'delete') {
            $pid = (int)($_POST['id'] ?? 0);
            if ($pid > 0) {
                $stmt = $db->prepare("DELETE FROM products WHERE id = :id");
                $stmt->execute(['id' => $pid]);
                $msg = "Product deleted.";
            }
        }
    }
}

// Fetch All Products & Categories
$products = $db->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC")->fetchAll();
$categories = $db->query("SELECT * FROM categories WHERE status = 'active'")->fetchAll();
?>

<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<div class="flex-1 flex flex-col min-w-0">
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <main class="p-8 flex-1">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Manage Products</h2>
                <p class="text-sm text-gray-500">Add, view, and manage store product inventory.</p>
            </div>
        </div>

        <?php if ($msg): ?><div class="p-4 mb-6 bg-emerald-50 text-emerald-700 rounded-lg"><?= sanitize($msg) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="p-4 mb-6 bg-red-50 text-red-700 rounded-lg"><?= sanitize($error) ?></div><?php endif; ?>

        <!-- Add Product Form Card -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 mb-8">
            <h3 class="font-bold text-gray-800 text-lg mb-4">Add New Product</h3>
            <form action="products.php" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Product Title *</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border rounded-lg outline-none text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Category *</label>
                    <select name="category_id" required class="w-full px-3 py-2 border rounded-lg outline-none text-sm focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= sanitize($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Price (₹) *</label>
                    <input type="number" step="0.01" name="price" required class="w-full px-3 py-2 border rounded-lg outline-none text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Stock Quantity</label>
                    <input type="number" name="stock" value="50" class="w-full px-3 py-2 border rounded-lg outline-none text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Image URL</label>
                    <input type="url" name="image" placeholder="https://..." class="w-full px-3 py-2 border rounded-lg outline-none text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full px-3 py-2 border rounded-lg outline-none text-sm focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
                <div class="md:col-span-3">
                    <button type="submit" class="bg-indigo-600 text-white font-semibold px-6 py-2 rounded-lg hover:bg-indigo-700 transition text-sm">Save Product</button>
                </div>
            </form>
        </div>

        <!-- Products List Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3">Product</th>
                        <th class="px-6 py-3">Category</th>
                        <th class="px-6 py-3">Price</th>
                        <th class="px-6 py-3">Stock</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td class="px-6 py-4 flex items-center space-x-3">
                                <img src="<?= get_product_image($p['image'], $p['name']) ?>" alt="<?= sanitize($p['name']) ?>" loading="lazy" onerror="this.onerror=null; this.src='../assets/images/placeholder.svg';" class="w-10 h-10 object-cover rounded-md border bg-gray-50">
                                <span class="font-bold text-gray-800"><?= sanitize($p['name']) ?></span>
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-indigo-600"><?= sanitize($p['category_name'] ?? 'General') ?></td>
                            <td class="px-6 py-4 font-semibold text-gray-800"><?= format_price($p['price']) ?></td>
                            <td class="px-6 py-4"><?= $p['stock'] ?></td>
                            <td class="px-6 py-4">
                                <form action="products.php" method="POST" onsubmit="return confirm('Delete this product?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-800 font-medium text-xs"><i class="fas fa-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
