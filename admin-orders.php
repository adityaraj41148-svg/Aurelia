<?php
/**
 * AURELIA Admin Portal - Order Management & Status Workflow
 */

require_once __DIR__ . '/admin-auth.php';

$db = getDB();
$error_message = '';
$success_message = '';

// Process Order Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $new_status = strtolower(trim($_POST['order_status'] ?? ''));

    $valid_statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];

    if ($order_id > 0 && in_array($new_status, $valid_statuses)) {
        try {
            $stmt = $db->prepare("UPDATE orders SET order_status = :status, updated_at = NOW() WHERE id = :id");
            $stmt->execute(['status' => $new_status, 'id' => $order_id]);
            $success_message = 'Order status updated to "' . strtoupper($new_status) . '" successfully!';
        } catch (Exception $e) {
            $error_message = 'Failed to update order status: ' . $e->getMessage();
        }
    } else {
        $error_message = 'Invalid status selected for order.';
    }
}

// Search and Filter
$search = trim($_GET['search'] ?? '');
$status_filter = strtolower(trim($_GET['status'] ?? ''));

$query = "SELECT o.*, u.name as user_name, u.email as user_email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE 1=1";
$params = [];

if (!empty($status_filter) && $status_filter !== 'all') {
    $query .= " AND o.order_status = :status";
    $params['status'] = $status_filter;
}

if (!empty($search)) {
    $query .= " AND (o.order_number LIKE :search OR o.shipping_name LIKE :search OR u.name LIKE :search OR o.shipping_email LIKE :search)";
    $params['search'] = "%$search%";
}

$query .= " ORDER BY o.id DESC";
$stmt_orders = $db->prepare($query);
$stmt_orders->execute($params);
$orders = $stmt_orders->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management | Admin Portal</title>
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
                <a href="admin-products.php" class="admin-nav-link"><i class="fas fa-box w-5"></i> Products</a>
                <a href="admin-orders.php" class="admin-nav-link active"><i class="fas fa-shopping-cart w-5"></i> Orders</a>
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
                <h1 class="text-2xl font-bold text-slate-100">ORDER MANAGEMENT</h1>
                <p class="text-xs text-slate-400 mt-1">View all customer orders, item breakdowns, and update fulfillment statuses.</p>
            </div>
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
        <form method="GET" action="admin-orders.php" class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-[#0f172a] p-4 border border-[#1e293b] rounded-2xl">
            <div class="relative w-full sm:w-80">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search order ID, customer name, email..." class="w-full bg-[#020617] border border-[#1e293b] rounded-xl px-3.5 py-2 text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-amber-400">
                <button type="submit" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-amber-400 text-xs"><i class="fas fa-search"></i></button>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto">
                <select name="status" onchange="this.form.submit()" class="bg-[#020617] border border-[#1e293b] text-slate-200 text-xs font-semibold rounded-xl px-3 py-2 focus:outline-none focus:border-amber-400 cursor-pointer">
                    <option value="all">Filter: All Statuses</option>
                    <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>PENDING</option>
                    <option value="confirmed" <?= $status_filter === 'confirmed' ? 'selected' : '' ?>>CONFIRMED</option>
                    <option value="processing" <?= $status_filter === 'processing' ? 'selected' : '' ?>>PROCESSING</option>
                    <option value="shipped" <?= $status_filter === 'shipped' ? 'selected' : '' ?>>SHIPPED</option>
                    <option value="delivered" <?= $status_filter === 'delivered' ? 'selected' : '' ?>>DELIVERED</option>
                    <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>CANCELLED</option>
                </select>
                <?php if (!empty($search) || ($status_filter !== '' && $status_filter !== 'all')): ?>
                    <a href="admin-orders.php" class="px-3 py-2 bg-slate-800 text-slate-300 text-xs rounded-xl hover:bg-slate-700">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Orders Table -->
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Items & Address</th>
                        <th>Total Paid</th>
                        <th>Payment</th>
                        <th>Status Workflow</th>
                        <th>Order Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-6 text-slate-500">No orders found matching criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $ord): ?>
                            <?php
                            // Fetch items for this order
                            $stmt_items = $db->prepare("SELECT oi.*, p.name as product_name, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = :oid");
                            $stmt_items->execute(['oid' => $ord['id']]);
                            $items = $stmt_items->fetchAll();
                            ?>
                            <tr>
                                <td class="font-mono font-bold text-amber-400">
                                    <?= htmlspecialchars($ord['order_number']) ?>
                                </td>
                                <td>
                                    <p class="font-bold text-slate-100"><?= htmlspecialchars($ord['shipping_name'] ?: $ord['user_name'] ?: 'Guest') ?></p>
                                    <p class="text-[10px] text-slate-400"><?= htmlspecialchars($ord['shipping_email'] ?: $ord['user_email'] ?: 'N/A') ?></p>
                                    <p class="text-[10px] text-slate-400 font-mono"><?= htmlspecialchars($ord['shipping_phone'] ?? 'N/A') ?></p>
                                </td>
                                <td>
                                    <div class="text-xs space-y-1">
                                        <p class="font-bold text-slate-200"><?= count($items) ?> Item(s):</p>
                                        <ul class="text-[11px] text-slate-400 space-y-0.5 max-w-[200px] truncate">
                                            <?php foreach ($items as $it): ?>
                                                <li class="truncate">• <?= htmlspecialchars($it['product_name'] ?? 'Product') ?> (x<?= $it['quantity'] ?>)</li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </td>
                                <td class="font-mono font-bold text-slate-100 text-sm">
                                    ₹<?= number_format((float)$ord['grand_total'], 2) ?>
                                </td>
                                <td>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-800 text-slate-300 border border-slate-700">
                                        <?= htmlspecialchars($ord['payment_method']) ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" action="admin-orders.php" class="flex items-center gap-2">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                                        <select name="order_status" onchange="this.form.submit()" class="bg-[#020617] border border-[#1e293b] text-xs font-bold rounded-xl px-2.5 py-1.5 focus:outline-none focus:border-amber-400 cursor-pointer text-amber-400">
                                            <option value="pending" <?= strtolower($ord['order_status']) === 'pending' ? 'selected' : '' ?>>PENDING</option>
                                            <option value="confirmed" <?= strtolower($ord['order_status']) === 'confirmed' ? 'selected' : '' ?>>CONFIRMED</option>
                                            <option value="processing" <?= strtolower($ord['order_status']) === 'processing' ? 'selected' : '' ?>>PROCESSING</option>
                                            <option value="shipped" <?= strtolower($ord['order_status']) === 'shipped' ? 'selected' : '' ?>>SHIPPED</option>
                                            <option value="delivered" <?= strtolower($ord['order_status']) === 'delivered' ? 'selected' : '' ?>>DELIVERED</option>
                                            <option value="cancelled" <?= strtolower($ord['order_status']) === 'cancelled' ? 'selected' : '' ?>>CANCELLED</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="text-slate-400 text-xs">
                                    <?= date('d M Y, h:i A', strtotime($ord['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>
