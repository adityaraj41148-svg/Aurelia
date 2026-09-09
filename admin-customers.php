<?php
/**
 * AURELIA Admin Portal - Customer Management
 */

require_once __DIR__ . '/admin-auth.php';

$db = getDB();
$error_message = '';
$success_message = '';

// Toggle Customer Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    $user_id = (int)($_POST['user_id'] ?? 0);
    $current_status = $_POST['current_status'] ?? 'active';
    $new_status = ($current_status === 'active') ? 'inactive' : 'active';

    if ($user_id > 0) {
        try {
            $stmt = $db->prepare("UPDATE users SET status = :status WHERE id = :id");
            $stmt->execute(['status' => $new_status, 'id' => $user_id]);
            $success_message = 'Customer account status updated to "' . strtoupper($new_status) . '" successfully!';
        } catch (Exception $e) {
            $error_message = 'Failed to update customer status: ' . $e->getMessage();
        }
    }
}

// Search and Filter
$search = trim($_GET['search'] ?? '');
$status_filter = strtolower(trim($_GET['status'] ?? ''));

$query = "SELECT u.*, 
            COUNT(o.id) as total_orders, 
            COALESCE(SUM(o.grand_total), 0) as total_spend 
          FROM users u 
          LEFT JOIN orders o ON o.user_id = u.id AND o.order_status != 'cancelled'
          WHERE 1=1";
$params = [];

if (!empty($status_filter) && $status_filter !== 'all') {
    $query .= " AND u.status = :status";
    $params['status'] = $status_filter;
}

if (!empty($search)) {
    $query .= " AND (u.name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search OR u.id = :search_exact)";
    $params['search'] = "%$search%";
    $params['search_exact'] = is_numeric($search) ? (int)$search : 0;
}

$query .= " GROUP BY u.id ORDER BY u.id DESC";
$stmt_customers = $db->prepare($query);
$stmt_customers->execute($params);
$customers = $stmt_customers->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management | Admin Portal</title>
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
                <a href="admin-customers.php" class="admin-nav-link active"><i class="fas fa-users w-5"></i> Customers</a>
                <a href="admin-products.php" class="admin-nav-link"><i class="fas fa-box w-5"></i> Products</a>
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
                <h1 class="text-2xl font-bold text-slate-100">CUSTOMER MANAGEMENT</h1>
                <p class="text-xs text-slate-400 mt-1">Manage registered customer accounts, view order history, and toggle statuses.</p>
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
        <form method="GET" action="admin-customers.php" class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-[#0f172a] p-4 border border-[#1e293b] rounded-2xl">
            <div class="relative w-full sm:w-80">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search customer name, email, mobile..." class="w-full bg-[#020617] border border-[#1e293b] rounded-xl px-3.5 py-2 text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-amber-400">
                <button type="submit" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-amber-400 text-xs"><i class="fas fa-search"></i></button>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto">
                <select name="status" onchange="this.form.submit()" class="bg-[#020617] border border-[#1e293b] text-slate-200 text-xs font-semibold rounded-xl px-3 py-2 focus:outline-none focus:border-amber-400 cursor-pointer">
                    <option value="all">Filter: All Statuses</option>
                    <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Active Accounts</option>
                    <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Disabled Accounts</option>
                </select>
                <?php if (!empty($search) || ($status_filter !== '' && $status_filter !== 'all')): ?>
                    <a href="admin-customers.php" class="px-3 py-2 bg-slate-800 text-slate-300 text-xs rounded-xl hover:bg-slate-700">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Customers Table -->
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Customer ID</th>
                        <th>Full Name</th>
                        <th>Email & Phone</th>
                        <th>Total Orders</th>
                        <th>Total Spending</th>
                        <th>Reg. Date</th>
                        <th>Account Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($customers)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-6 text-slate-500">No registered customers found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($customers as $cust): ?>
                            <tr>
                                <td class="font-mono text-amber-400 font-bold">#<?= $cust['id'] ?></td>
                                <td class="font-bold text-slate-100"><?= htmlspecialchars($cust['name']) ?></td>
                                <td>
                                    <p class="text-slate-300 text-xs"><?= htmlspecialchars($cust['email']) ?></p>
                                    <p class="text-[10px] text-slate-400 font-mono"><?= htmlspecialchars($cust['phone'] ?: 'N/A') ?></p>
                                </td>
                                <td class="font-mono text-xs font-bold text-slate-200">
                                    <?= $cust['total_orders'] ?> Orders
                                </td>
                                <td class="font-mono font-bold text-emerald-400 text-xs">
                                    ₹<?= number_format((float)$cust['total_spend'], 2) ?>
                                </td>
                                <td class="text-slate-400 text-xs">
                                    <?= date('d M Y', strtotime($cust['created_at'])) ?>
                                </td>
                                <td>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border <?= $cust['status'] === 'active' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' : 'bg-rose-500/10 text-rose-400 border-rose-500/30' ?>">
                                        <?= strtoupper($cust['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" action="admin-customers.php" class="inline">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="user_id" value="<?= $cust['id'] ?>">
                                        <input type="hidden" name="current_status" value="<?= $cust['status'] ?>">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer <?= $cust['status'] === 'active' ? 'bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 border border-rose-500/30' : 'bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 border border-emerald-500/30' ?>">
                                            <?= $cust['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                        </button>
                                    </form>
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
