<?php
/**
 * AURELIA Admin Portal - Dashboard Overview
 */

require_once __DIR__ . '/admin-auth.php';

$db = getDB();

// 1. Calculate Metrics dynamically from MySQL Database
$total_revenue = $db->query("SELECT COALESCE(SUM(grand_total), 0) FROM orders WHERE order_status != 'cancelled'")->fetchColumn();
$total_orders  = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_customers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_products = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();

// 2. Fetch Recent Orders (Limit 5)
$stmt_orders = $db->query("SELECT o.*, u.name as customer_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.id DESC LIMIT 5");
$recent_orders = $stmt_orders->fetchAll();

// 3. Fetch Recent Customers (Limit 5)
$stmt_customers = $db->query("SELECT * FROM users ORDER BY id DESC LIMIT 5");
$recent_customers = $stmt_customers->fetchAll();

// 4. Fetch Low Stock Products (stock <= 10)
$stmt_low_stock = $db->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.stock <= 10 ORDER BY p.stock ASC LIMIT 5");
$low_stock_products = $stmt_low_stock->fetchAll();

// 5. Order Status Breakdown
$stmt_order_stats = $db->query("SELECT order_status, COUNT(*) as count FROM orders GROUP BY order_status");
$order_status_counts = $stmt_order_stats->fetchAll(PDO::FETCH_KEY_PAIR);

// 6. Category Product Breakdown
$stmt_cat_stats = $db->query("SELECT c.name, COUNT(p.id) as count FROM categories c LEFT JOIN products p ON p.category_id = c.id GROUP BY c.id");
$category_counts = $stmt_cat_stats->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | AURELIA</title>
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
                <a href="admin-dashboard.php" class="admin-nav-link active"><i class="fas fa-chart-pie w-5"></i> Dashboard</a>
                <a href="admin-customers.php" class="admin-nav-link"><i class="fas fa-users w-5"></i> Customers</a>
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
            <div class="px-3 py-2 mb-3 bg-slate-900 border border-slate-800 rounded-xl text-xs text-slate-300">
                <p class="font-bold text-slate-100"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></p>
                <p class="text-[10px] text-slate-400"><?= htmlspecialchars($_SESSION['admin_email'] ?? '') ?></p>
            </div>
            <a href="admin-logout.php" class="w-full text-left admin-nav-link text-rose-400 hover:bg-rose-500/10 flex items-center gap-2">
                <i class="fas fa-sign-out-alt w-5"></i> Admin Logout
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-grow p-6 md:p-10 space-y-8 overflow-x-hidden">
        <!-- Top Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-800 pb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-100">DASHBOARD OVERVIEW</h1>
                <p class="text-xs text-slate-400 mt-1">Real calculated store statistics from database.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="index.php" target="_blank" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl transition flex items-center gap-2">
                    <i class="fas fa-external-link-alt"></i> View Storefront
                </a>
            </div>
        </div>

        <!-- Calculated Stat Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="admin-stat-card">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">TOTAL REVENUE</span>
                        <h3 class="text-2xl font-bold font-mono text-slate-100 mt-1">₹<?= number_format((float)$total_revenue, 2) ?></h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 flex items-center justify-center text-lg"><i class="fas fa-wallet"></i></div>
                </div>
            </div>

            <div class="admin-stat-card">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">TOTAL ORDERS</span>
                        <h3 class="text-2xl font-bold font-mono text-slate-100 mt-1"><?= number_format($total_orders) ?></h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-400 flex items-center justify-center text-lg"><i class="fas fa-shopping-bag"></i></div>
                </div>
            </div>

            <div class="admin-stat-card">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">TOTAL CUSTOMERS</span>
                        <h3 class="text-2xl font-bold font-mono text-slate-100 mt-1"><?= number_format($total_customers) ?></h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-sky-500/10 border border-sky-500/30 text-sky-400 flex items-center justify-center text-lg"><i class="fas fa-users"></i></div>
                </div>
            </div>

            <div class="admin-stat-card">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">TOTAL PRODUCTS</span>
                        <h3 class="text-2xl font-bold font-mono text-slate-100 mt-1"><?= number_format($total_products) ?></h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/30 text-purple-400 flex items-center justify-center text-lg"><i class="fas fa-box"></i></div>
                </div>
            </div>
        </div>

        <!-- Recent Orders & Statistics Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Recent Orders (2 Columns) -->
            <div class="lg:col-span-2 space-y-4">
                <div class="flex justify-between items-center">
                    <h2 class="text-sm font-bold text-slate-200 uppercase tracking-wider">RECENT ORDERS</h2>
                    <a href="admin-orders.php" class="text-xs text-amber-400 hover:underline">View All Orders →</a>
                </div>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_orders)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-6 text-slate-500">No orders placed yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_orders as $ord): ?>
                                    <?php
                                    $st = strtolower($ord['order_status']);
                                    $badge = 'bg-amber-500/10 text-amber-400 border-amber-500/30';
                                    if ($st === 'delivered') $badge = 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30';
                                    elseif ($st === 'shipped') $badge = 'bg-sky-500/10 text-sky-400 border-sky-500/30';
                                    elseif ($st === 'cancelled') $badge = 'bg-rose-500/10 text-rose-400 border-rose-500/30';
                                    ?>
                                    <tr>
                                        <td class="font-mono font-bold text-amber-400"><?= htmlspecialchars($ord['order_number']) ?></td>
                                        <td class="text-slate-200"><?= htmlspecialchars($ord['shipping_name'] ?? $ord['customer_name'] ?? 'Guest Customer') ?></td>
                                        <td class="font-mono">₹<?= number_format((float)$ord['grand_total'], 2) ?></td>
                                        <td><span class="px-2.5 py-1 rounded-full text-[10px] font-bold border <?= $badge ?>"><?= strtoupper($ord['order_status']) ?></span></td>
                                        <td class="text-slate-400 text-xs"><?= date('d M Y', strtotime($ord['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Order Workflow & Category Distribution (1 Column) -->
            <div class="space-y-6">
                <!-- Order Status breakdown -->
                <div class="bg-[#0f172a] border border-[#1e293b] rounded-2xl p-5 space-y-4">
                    <h3 class="text-xs font-bold text-slate-200 uppercase tracking-wider">ORDER STATUS BREAKDOWN</h3>
                    <div class="space-y-2">
                        <?php
                        $possible_statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
                        foreach ($possible_statuses as $st):
                            $count = $order_status_counts[$st] ?? 0;
                            $pct = $total_orders > 0 ? round(($count / $total_orders) * 100) : 0;
                        ?>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-400 uppercase font-semibold"><?= $st ?></span>
                                <span class="font-mono font-bold text-slate-200"><?= $count ?> (<?= $pct ?>%)</span>
                            </div>
                            <div class="w-full bg-slate-900 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-amber-400 h-1.5 rounded-full" style="width: <?= $pct ?>%"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Low Stock Alert -->
                <div class="bg-[#0f172a] border border-[#1e293b] rounded-2xl p-5 space-y-3">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xs font-bold text-rose-400 uppercase tracking-wider"><i class="fas fa-exclamation-triangle mr-1"></i> LOW STOCK ALERTS</h3>
                        <a href="admin-products.php" class="text-[10px] text-amber-400 hover:underline">Manage</a>
                    </div>
                    <?php if (empty($low_stock_products)): ?>
                        <p class="text-xs text-slate-500">All products have sufficient stock levels.</p>
                    <?php else: ?>
                        <div class="space-y-2">
                            <?php foreach ($low_stock_products as $lp): ?>
                                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-950/60 border border-slate-800 text-xs">
                                    <div class="truncate max-w-[160px]">
                                        <p class="font-bold text-slate-200 truncate"><?= htmlspecialchars($lp['name']) ?></p>
                                        <p class="text-[10px] text-slate-400"><?= htmlspecialchars($lp['brand'] ?? 'AURELIA') ?></p>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30 font-mono">
                                        <?= $lp['stock'] ?> Left
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Customers Grid -->
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <h2 class="text-sm font-bold text-slate-200 uppercase tracking-wider">RECENTLY REGISTERED CUSTOMERS</h2>
                <a href="admin-customers.php" class="text-xs text-amber-400 hover:underline">View All Customers →</a>
            </div>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer Name</th>
                            <th>Email Address</th>
                            <th>Mobile</th>
                            <th>Status</th>
                            <th>Registered Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_customers)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-6 text-slate-500">No registered customers found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_customers as $cust): ?>
                                <tr>
                                    <td class="font-mono text-amber-400 font-bold">#<?= $cust['id'] ?></td>
                                    <td class="font-bold text-slate-200"><?= htmlspecialchars($cust['name']) ?></td>
                                    <td class="text-slate-300"><?= htmlspecialchars($cust['email']) ?></td>
                                    <td class="text-slate-400 font-mono"><?= htmlspecialchars($cust['phone'] ?: 'N/A') ?></td>
                                    <td>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border <?= $cust['status'] === 'active' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' : 'bg-rose-500/10 text-rose-400 border-rose-500/30' ?>">
                                            <?= strtoupper($cust['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-slate-400 text-xs"><?= date('d M Y', strtotime($cust['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>
