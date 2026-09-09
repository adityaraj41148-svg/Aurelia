<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$admin_title = "Manage Coupons - AA Mart Admin";
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $action = $_POST['action'] ?? '';
        if ($action === 'create') {
            $code = strtoupper(sanitize($_POST['code'] ?? ''));
            $discount_value = (float)($_POST['discount_value'] ?? 0);
            $type = $_POST['discount_type'] ?? 'percentage';

            if ($code && $discount_value > 0) {
                $stmt = $db->prepare("INSERT INTO coupons (code, discount_type, discount_value, status) VALUES (:code, :type, :val, 'active')");
                $stmt->execute(['code' => $code, 'type' => $type, 'val' => $discount_value]);
                $msg = "Coupon added!";
            }
        } elseif ($action === 'delete') {
            $cid = (int)($_POST['id'] ?? 0);
            $stmt = $db->prepare("DELETE FROM coupons WHERE id = :id");
            $stmt->execute(['id' => $cid]);
            $msg = "Coupon deleted!";
        }
    }
}

$coupons = $db->query("SELECT * FROM coupons ORDER BY id DESC")->fetchAll();
?>

<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<div class="flex-1 flex flex-col min-w-0">
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <main class="p-8 flex-1">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Manage Promotional Coupons</h2>

        <?php if ($msg): ?><div class="p-4 mb-6 bg-emerald-50 text-emerald-700 rounded-lg"><?= sanitize($msg) ?></div><?php endif; ?>

        <!-- Create Coupon Form -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 mb-8 max-w-xl">
            <h3 class="font-bold text-gray-800 text-lg mb-4">Add Discount Coupon</h3>
            <form action="coupons.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Coupon Code *</label>
                    <input type="text" name="code" placeholder="SAVE20" required class="w-full px-3 py-2 border rounded-lg outline-none text-sm focus:ring-2 focus:ring-indigo-500 uppercase">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Discount Type</label>
                    <select name="discount_type" class="w-full px-3 py-2 border rounded-lg outline-none text-sm bg-white">
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed Amount (₹)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Discount Value *</label>
                    <input type="number" step="0.01" name="discount_value" required class="w-full px-3 py-2 border rounded-lg outline-none text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-indigo-600 text-white font-semibold py-2 rounded-lg hover:bg-indigo-700 transition text-sm">Save Coupon</button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden max-w-3xl">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3">Code</th>
                        <th class="px-6 py-3">Type</th>
                        <th class="px-6 py-3">Value</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($coupons as $c): ?>
                        <tr>
                            <td class="px-6 py-4 font-bold text-indigo-600"><?= sanitize($c['code']) ?></td>
                            <td class="px-6 py-4 uppercase text-xs font-semibold"><?= sanitize($c['discount_type']) ?></td>
                            <td class="px-6 py-4 font-bold text-gray-800"><?= $c['discount_type'] === 'percentage' ? $c['discount_value'] . '%' : format_price($c['discount_value']) ?></td>
                            <td class="px-6 py-4"><span class="px-2.5 py-1 rounded-full text-xs font-semibold uppercase bg-emerald-100 text-emerald-700"><?= sanitize($c['status']) ?></span></td>
                            <td class="px-6 py-4">
                                <form action="coupons.php" method="POST" onsubmit="return confirm('Delete coupon?');">
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
