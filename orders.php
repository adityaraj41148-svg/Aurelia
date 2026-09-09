<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$admin_title = "Manage Orders - AA Mart Admin";
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $oid = (int)($_POST['order_id'] ?? 0);
        $status = sanitize($_POST['order_status'] ?? 'pending');
        $stmt = $db->prepare("UPDATE orders SET order_status = :status WHERE id = :id");
        $stmt->execute(['status' => $status, 'id' => $oid]);
        $msg = "Order status updated!";
    }
}

$orders = $db->query("SELECT * FROM orders ORDER BY id DESC")->fetchAll();
?>

<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<div class="flex-1 flex flex-col min-w-0">
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <main class="p-8 flex-1">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Manage Customer Orders</h2>

        <?php if ($msg): ?><div class="p-4 mb-6 bg-emerald-50 text-emerald-700 rounded-lg"><?= sanitize($msg) ?></div><?php endif; ?>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3">Order #</th>
                        <th class="px-6 py-3">Customer Details</th>
                        <th class="px-6 py-3">Total Amount</th>
                        <th class="px-6 py-3">Payment</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Update Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td class="px-6 py-4 font-bold text-indigo-600"><?= sanitize($o['order_number']) ?></td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-800"><?= sanitize($o['shipping_name']) ?></div>
                                <div class="text-xs text-gray-400"><?= sanitize($o['shipping_email']) ?> | <?= sanitize($o['shipping_phone']) ?></div>
                                <div class="text-xs text-gray-500 mt-1"><?= sanitize($o['shipping_address']) ?></div>
                            </td>
                            <td class="px-6 py-4 font-bold text-gray-800"><?= format_price($o['grand_total']) ?></td>
                            <td class="px-6 py-4 text-xs font-semibold"><?= sanitize($o['payment_method']) ?></td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold uppercase bg-amber-100 text-amber-700">
                                    <?= sanitize($o['order_status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <form action="orders.php" method="POST" class="flex items-center space-x-2">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                    <select name="order_status" class="px-2 py-1 border rounded text-xs outline-none bg-white">
                                        <option value="pending" <?= $o['order_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="processing" <?= $o['order_status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                        <option value="shipped" <?= $o['order_status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                        <option value="delivered" <?= $o['order_status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                        <option value="cancelled" <?= $o['order_status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-700">Update</button>
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
