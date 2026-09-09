<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$admin_title = "Manage Reviews - AA Mart Admin";
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $rid = (int)($_POST['id'] ?? 0);
        if ($_POST['action'] === 'delete') {
            $stmt = $db->prepare("DELETE FROM reviews WHERE id = :id");
            $stmt->execute(['id' => $rid]);
            $msg = "Review deleted.";
        }
    }
}

$reviews = $db->query("SELECT r.*, p.name as product_name FROM reviews r LEFT JOIN products p ON r.product_id = p.id ORDER BY r.id DESC")->fetchAll();
?>

<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<div class="flex-1 flex flex-col min-w-0">
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <main class="p-8 flex-1">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Customer Product Reviews</h2>

        <?php if ($msg): ?><div class="p-4 mb-6 bg-emerald-50 text-emerald-700 rounded-lg"><?= sanitize($msg) ?></div><?php endif; ?>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3">Product</th>
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3">Rating</th>
                        <th class="px-6 py-3">Review Comment</th>
                        <th class="px-6 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($reviews as $r): ?>
                        <tr>
                            <td class="px-6 py-4 font-bold text-indigo-600"><?= sanitize($r['product_name'] ?? 'General Store') ?></td>
                            <td class="px-6 py-4 font-semibold text-gray-800"><?= sanitize($r['customer_name']) ?></td>
                            <td class="px-6 py-4 text-amber-400 font-bold"><?= $r['rating'] ?> Stars</td>
                            <td class="px-6 py-4 max-w-xs text-xs text-gray-600"><?= sanitize($r['comment']) ?></td>
                            <td class="px-6 py-4">
                                <form action="reviews.php" method="POST" onsubmit="return confirm('Delete review?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
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
