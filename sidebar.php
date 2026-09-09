<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar flex-shrink-0 flex flex-col justify-between p-4">
    <div>
        <div class="px-4 py-4 mb-6 border-b border-slate-700 flex items-center space-x-3">
            <i class="fas fa-shield-alt text-2xl text-indigo-400"></i>
            <span class="text-xl font-bold text-white tracking-wide">AA Mart Admin</span>
        </div>
        <nav class="space-y-1">
            <a href="index.php" class="<?= $current_page === 'index.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="categories.php" class="<?= $current_page === 'categories.php' ? 'active' : '' ?>">
                <i class="fas fa-tags"></i> Categories
            </a>
            <a href="products.php" class="<?= $current_page === 'products.php' ? 'active' : '' ?>">
                <i class="fas fa-box"></i> Products
            </a>
            <a href="orders.php" class="<?= $current_page === 'orders.php' ? 'active' : '' ?>">
                <i class="fas fa-shopping-cart"></i> Orders
            </a>
            <a href="users.php" class="<?= $current_page === 'users.php' ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Users
            </a>
            <a href="reviews.php" class="<?= $current_page === 'reviews.php' ? 'active' : '' ?>">
                <i class="fas fa-star"></i> Reviews
            </a>
            <a href="coupons.php" class="<?= $current_page === 'coupons.php' ? 'active' : '' ?>">
                <i class="fas fa-ticket-alt"></i> Coupons
            </a>
            <a href="messages.php" class="<?= $current_page === 'messages.php' ? 'active' : '' ?>">
                <i class="fas fa-envelope"></i> Messages
            </a>
            <a href="settings.php" class="<?= $current_page === 'settings.php' ? 'active' : '' ?>">
                <i class="fas fa-cog"></i> Settings
            </a>
        </nav>
    </div>
    <div class="px-4 py-3 border-t border-slate-700 text-sm">
        <a href="logout.php" class="text-red-400 hover:text-red-300 flex items-center space-x-2">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>
