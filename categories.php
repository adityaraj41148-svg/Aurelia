<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$admin_title = "Manage Categories - AA Mart Admin";
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "CSRF validation error.";
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'create') {
            $name = sanitize($_POST['name'] ?? '');
            $icon = sanitize($_POST['icon'] ?? 'fa-folder');
            $slug = slugify($name);

            if ($name) {
                $stmt = $db->prepare("INSERT INTO categories (name, slug, icon, status) VALUES (:name, :slug, :icon, 'active')");
                $stmt->execute(['name' => $name, 'slug' => $slug, 'icon' => $icon]);
                $msg = "Category added successfully!";
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $db->prepare("DELETE FROM categories WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $msg = "Category deleted.";
            }
        }
    }
}

$categories = $db->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll();
?>

<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<div class="flex-1 flex flex-col min-w-0">
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <main class="p-8 flex-1">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Manage Categories</h2>

        <?php if ($msg): ?><div class="p-4 mb-6 bg-emerald-50 text-emerald-700 rounded-lg"><?= sanitize($msg) ?></div><?php endif; ?>

        <!-- Add Category -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 mb-8 max-w-xl">
            <h3 class="font-bold text-gray-800 text-lg mb-4">Add Category</h3>
            <form action="categories.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Category Name *</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border rounded-lg outline-none text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">FontAwesome Icon Class</label>
                    <input type="text" name="icon" value="fa-tag" class="w-full px-3 py-2 border rounded-lg outline-none text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="submit" class="bg-indigo-600 text-white font-semibold px-6 py-2 rounded-lg hover:bg-indigo-700 transition text-sm">Save Category</button>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden max-w-3xl">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3">Icon</th>
                        <th class="px-6 py-3">Category Name</th>
                        <th class="px-6 py-3">Slug</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($categories as $c): ?>
                        <tr>
                            <td class="px-6 py-4 text-indigo-600"><i class="fas <?= sanitize($c['icon']) ?> text-lg"></i></td>
                            <td class="px-6 py-4 font-bold text-gray-800"><?= sanitize($c['name']) ?></td>
                            <td class="px-6 py-4 text-gray-400"><?= sanitize($c['slug']) ?></td>
                            <td class="px-6 py-4">
                                <form action="categories.php" method="POST" onsubmit="return confirm('Delete category?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium"><i class="fas fa-trash"></i> Delete</button>
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
