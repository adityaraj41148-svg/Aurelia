<?php
require_once __DIR__ . '/admin-auth.php';
$db = getDB();
$coupons = $db->query("SELECT * FROM coupons ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coupons & Discounts | Admin Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-body min-h-screen flex">
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
                <a href="admin-orders.php" class="admin-nav-link"><i class="fas fa-shopping-cart w-5"></i> Orders</a>
                <a href="admin-categories.php" class="admin-nav-link"><i class="fas fa-th-large w-5"></i> Categories</a>
                <a href="admin-coupons.php" class="admin-nav-link active"><i class="fas fa-ticket-alt w-5"></i> Coupons</a>
                <a href="admin-reviews.php" class="admin-nav-link"><i class="fas fa-star w-5"></i> Reviews</a>
                <a href="admin-analytics.php" class="admin-nav-link"><i class="fas fa-chart-line w-5"></i> Analytics</a>
                <a href="admin-settings.php" class="admin-nav-link"><i class="fas fa-cog w-5"></i> Settings</a>
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
                <h1 class="text-2xl font-bold text-slate-100">PROMOTIONAL COUPONS</h1>
                <p class="text-xs text-slate-400 mt-1">Manage promotional discount codes and order thresholds.</p>
            </div>
        </div>
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Min Order</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($coupons)): ?>
                        <tr><td colspan="5" class="text-center py-6 text-slate-500">No active promo coupons.</td></tr>
                    <?php else: ?>
                        <?php foreach ($coupons as $cp): ?>
                            <tr>
                                <td class="font-mono font-bold text-amber-400"><?= htmlspecialchars($cp['code']) ?></td>
                                <td class="uppercase text-slate-300 text-xs"><?= htmlspecialchars($cp['discount_type']) ?></td>
                                <td class="font-mono text-xs font-bold text-slate-100"><?= $cp['discount_type'] === 'percentage' ? $cp['discount_value'].'%' : '₹'.$cp['discount_value'] ?></td>
                                <td class="font-mono text-xs text-slate-400">₹<?= number_format((float)$cp['min_order_amount'], 2) ?></td>
                                <td>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border <?= $cp['status'] === 'active' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' : 'bg-rose-500/10 text-rose-400 border-rose-500/30' ?>">
                                        <?= strtoupper($cp['status']) ?>
                                    </span>
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
