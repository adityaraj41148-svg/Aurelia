<?php
// AURELIA — WEAR THE MOMENT.
// Modular Navigation Header Include
if (file_exists(__DIR__ . '/auth.php')) require_once __DIR__ . '/auth.php';
if (file_exists(__DIR__ . '/functions.php')) require_once __DIR__ . '/functions.php';
$baseUrl = defined('BASE_URL') ? BASE_URL : 'index.php';
?>

<!-- Announcement Bar -->
<div class="bg-[#0f0e0d] text-[#e6e0d4] px-4 py-2 text-center text-[10px] sm:text-xs font-medium tracking-editorial uppercase shadow-sm">
    <div class="container mx-auto max-w-7xl flex items-center justify-between">
        <div class="mx-auto flex items-center gap-3">
            <span>✨ COMPLIMENTARY EXPRESS SHIPPING ON ORDERS ABOVE ₹4,990</span>
            <span class="hidden md:inline">• EASY 7-DAY RETURNS</span>
            <span class="bg-[#c5a059] text-[#0f0e0d] font-bold px-2 py-0.5 rounded-sm ml-2">PROMO CODE: AURELIA10</span>
        </div>
    </div>
</div>

<!-- Ultra-Clean Navigation Header -->
<header id="main-header" class="sticky top-0 nav-header z-40 px-4 sm:px-8 py-4">
    <div class="container mx-auto max-w-7xl flex items-center justify-between gap-4">
        
        <!-- Left: Brand Logo -->
        <a href="<?= $baseUrl ?>" class="flex flex-col group cursor-pointer">
            <span class="font-serif-title font-bold text-2xl sm:text-3xl tracking-widest text-[#0f0e0d] group-hover:text-[#c5a059] transition duration-300">
                A U R E L I A
            </span>
            <span class="text-[8px] font-bold text-[#8c857b] tracking-editorial uppercase leading-none">
                WEAR THE MOMENT.
            </span>
        </a>

        <!-- Center Navigation Links -->
        <nav class="hidden lg:flex items-center gap-8 text-xs font-semibold uppercase tracking-editorial text-[#0f0e0d]">
            <button type="button" onclick="filterProductCategory('women')" class="link-underline hover:text-[#c5a059] transition cursor-pointer">Women</button>
            <button type="button" onclick="filterProductCategory('men')" class="link-underline hover:text-[#c5a059] transition cursor-pointer">Men</button>
            <button type="button" onclick="filterProductCategory('kids')" class="link-underline hover:text-[#c5a059] transition cursor-pointer">Kids</button>
            <button type="button" onclick="filterProductCategory('all')" class="link-underline hover:text-[#c5a059] transition cursor-pointer">Collections</button>
            <button type="button" onclick="filterProductCategory('new')" class="text-[#c5a059] font-bold link-underline hover:text-[#0f0e0d] transition cursor-pointer flex items-center gap-1.5">
                <span>New Arrivals</span> <span class="w-1.5 h-1.5 rounded-full bg-[#c5a059] animate-pulse"></span>
            </button>
        </nav>

        <!-- Right Action Icons -->
        <div class="flex items-center gap-5 text-[#0f0e0d]">
            
            <!-- Search Button Trigger -->
            <button type="button" onclick="toggleSearchOverlay(true)" class="p-1 hover:text-[#c5a059] transition cursor-pointer flex items-center gap-2 text-xs font-medium" aria-label="Search">
                <i class="fas fa-search text-base"></i>
                <span class="hidden xl:inline tracking-widest uppercase">Search</span>
            </button>

            <!-- User Account Button -->
            <button type="button" onclick="toggleAccountDrawer(true)" class="p-1 hover:text-[#c5a059] transition cursor-pointer" aria-label="Account">
                <i class="far fa-user text-base"></i>
            </button>

            <!-- Wishlist Button -->
            <button type="button" onclick="toggleWishlistDrawer(true)" class="relative p-1 hover:text-[#c5a059] transition cursor-pointer" aria-label="Wishlist">
                <i class="far fa-heart text-base"></i>
                <span id="wishlist-count-badge" class="absolute -top-1.5 -right-2 bg-[#c5a059] text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center">0</span>
            </button>

            <!-- Cart Button -->
            <button type="button" onclick="toggleCartDrawer(true)" class="relative p-1 hover:text-[#c5a059] transition cursor-pointer" aria-label="Shopping Bag">
                <i class="fas fa-shopping-bag text-base"></i>
                <span id="cart-count-badge" class="absolute -top-1.5 -right-2 bg-[#0f0e0d] text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center">0</span>
            </button>

            <!-- Mobile Hamburger Button -->
            <button type="button" onclick="toggleMobileMenu()" class="lg:hidden p-1 hover:text-[#c5a059] transition cursor-pointer" aria-label="Menu">
                <i class="fas fa-bars text-lg"></i>
            </button>
        </div>
    </div>

    <!-- Mobile Drawer Navigation -->
    <div id="mobile-menu" class="hidden lg:hidden pt-4 pb-2 border-t border-[#e6e1d6] mt-3 space-y-3 text-xs font-semibold uppercase tracking-editorial text-[#0f0e0d]">
        <button onclick="toggleMobileMenu(); filterProductCategory('women');" class="block w-full text-left py-1 hover:text-[#c5a059]">Women's Collection</button>
        <button onclick="toggleMobileMenu(); filterProductCategory('men');" class="block w-full text-left py-1 hover:text-[#c5a059]">Men's Collection</button>
        <button onclick="toggleMobileMenu(); filterProductCategory('kids');" class="block w-full text-left py-1 hover:text-[#c5a059]">Junior Collection</button>
        <button onclick="toggleMobileMenu(); filterProductCategory('all');" class="block w-full text-left py-1 hover:text-[#c5a059]">All Collections</button>
        <button onclick="toggleMobileMenu(); filterProductCategory('new');" class="block w-full text-left py-1 text-[#c5a059]">New Arrivals 2026</button>
    </div>
</header>