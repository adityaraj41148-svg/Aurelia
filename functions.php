<?php

/**
 * Global Helper Functions & Security Utilities
 */

require_once __DIR__ . '/config.php';

// Output Escaping for XSS Prevention
function sanitize(?string $data): string
{
    if ($data === null) return '';
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Format Price to ₹XX.XX
function format_price(float $amount): string
{
    return '₹' . number_format($amount, 2);
}

// Product Image Fallback & Smart Category Matcher
function get_product_image(?string $image_url, ?string $product_name = ''): string
{
    if (!empty($image_url) && trim($image_url) !== '') {
        return sanitize($image_url);
    }

    $name = strtolower($product_name ?? '');

    if (strpos($name, 'headphone') !== false) return 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=900&q=80';
    if (strpos($name, 'smartwatch') !== false || strpos($name, 'watch') !== false) return 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=900&q=80';
    if (strpos($name, 'keyboard') !== false) return 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=900&q=80';
    if (strpos($name, 'webcam') !== false) return 'https://images.unsplash.com/photo-1587826080692-f439cd0b70da?auto=format&fit=crop&w=900&q=80';
    if (strpos($name, 'speaker') !== false) return 'https://images.unsplash.com/photo-1545454675-3531b543be5d?auto=format&fit=crop&w=900&q=80';
    if (strpos($name, 'mouse') !== false) return 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?auto=format&fit=crop&w=900&q=80';
    if (strpos($name, 'stand') !== false || strpos($name, 'laptop') !== false) return 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?auto=format&fit=crop&w=900&q=80';
    if (strpos($name, 'hub') !== false) return 'https://images.unsplash.com/photo-1625842268584-8f3296236761?auto=format&fit=crop&w=900&q=80';
    if (strpos($name, 'camera') !== false) return 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=900&q=80';
    if (strpos($name, 'charger') !== false) return 'https://images.unsplash.com/photo-1622445268121-8a16f88574d6?auto=format&fit=crop&w=900&q=80';
    if (strpos($name, 'ssd') !== false) return 'https://images.unsplash.com/photo-1597872200969-2b65d56bd16b?auto=format&fit=crop&w=900&q=80';
    if (strpos($name, 'lamp') !== false) return 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=900&q=80';
    if (strpos($name, 'backpack') !== false) return 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=900&q=80';
    if (strpos($name, 'tracker') !== false || strpos($name, 'fitness') !== false) return 'https://images.unsplash.com/photo-1575311373937-040b8e1fd5b6?auto=format&fit=crop&w=900&q=80';
    if (strpos($name, 'earbud') !== false || strpos($name, 'airpod') !== false) return 'https://images.unsplash.com/photo-1606220588913-b3aacb4d2f46?auto=format&fit=crop&w=900&q=80';
    if (strpos($name, 'drone') !== false) return 'https://images.unsplash.com/photo-1527977966376-1c8408f9f108?auto=format&fit=crop&w=900&q=80';
    if (strpos($name, 'shoe') !== false) return 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=900&q=80';
    if (strpos($name, 'phone') !== false || strpos($name, 'mobile') !== false) return 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=900&q=80';
    if (strpos($name, 'bank') !== false) return 'https://images.unsplash.com/photo-1609592424109-dd9892f1b177?auto=format&fit=crop&w=900&q=80';

    return BASE_URL . 'assets/images/placeholder.svg';
}

// Redirect Helper
function redirect(string $url): void
{
    header("Location: " . $url);
    exit;
}

// CSRF Token Generator
function generate_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF Token
function verify_csrf_token(?string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

// Flash Message Set & Get
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message
    ];
}

function get_flash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Generate URL slug from title
function slugify(string $text): string
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text ?: 'n-a');
}

// Calculate discount percentage
function calculate_discount_percent(float $price, ?float $mrp): int
{
    if (!$mrp || $mrp <= $price) return 0;
    return (int)round((($mrp - $price) / $mrp) * 100);
}

// Render Star Ratings HTML
function render_stars(float $rating = 4.5): string
{
    $full = floor($rating);
    $half = ($rating - $full) >= 0.5;
    $empty = 5 - $full - ($half ? 1 : 0);

    $html = '<div class="inline-flex items-center text-amber-400 text-xs gap-0.5">';
    for ($i = 0; $i < $full; $i++) {
        $html .= '<i class="fas fa-star"></i>';
    }
    if ($half) {
        $html .= '<i class="fas fa-star-half-alt"></i>';
    }
    for ($i = 0; $i < $empty; $i++) {
        $html .= '<i class="far fa-star text-gray-300"></i>';
    }
    $html .= '</div>';
    return $html;
}

// Render Premium Product Card Component HTML
function render_product_card(array $p): string
{
    $id = (int)($p['id'] ?? 0);
    $name = sanitize($p['name'] ?? $p['product_name'] ?? '');
    $brand = sanitize($p['brand'] ?? 'AA MART CRAFT');
    $price = (float)($p['price'] ?? $p['sale_price'] ?? $p['price_inr'] ?? 999.00);
    $mrp = (float)($p['mrp'] ?? $p['original_price'] ?? 1999.00);
    $discount_pct = calculate_discount_percent($price, $mrp);
    $img = get_product_image($p['image'] ?? '', $name);
    $rating = (float)($p['rating'] ?? 5.0);
    $reviews = (int)($p['review_count'] ?? $p['reviews'] ?? 19);
    $badge = sanitize($p['badge'] ?? '');
    $is_new = !empty($p['is_new']) || (!empty($badge) && strpos(strtoupper($badge), 'NEW') !== false);
    
    $badge_html = '';
    if ($is_new) {
        $badge_html = '<span class="absolute top-2.5 left-2.5 z-10 bg-purple-600 text-white font-black text-[10px] uppercase tracking-wider px-2.5 py-1 rounded-md shadow-md">NEW</span>';
    } elseif ($discount_pct > 0) {
        $badge_html = '<span class="absolute top-2.5 left-2.5 z-10 bg-red-600 text-white font-black text-[10px] uppercase tracking-wider px-2.5 py-1 rounded-md shadow-md">-' . $discount_pct . '% OFF</span>';
    } elseif (!empty($badge)) {
        $badge_html = '<span class="absolute top-2.5 left-2.5 z-10 bg-purple-600 text-white font-bold text-[10px] px-2 py-0.5 rounded-md border border-purple-200">' . $badge . '</span>';
    }

    return '
    <div class="product-card bg-white border border-slate-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition duration-300 flex flex-col justify-between space-y-3 relative group" data-product-id="' . $id . '">
        <!-- Top Badges & Wishlist -->
        ' . $badge_html . '
        
        <button type="button" onclick="toggleWishlist(this, ' . $id . ')" class="absolute top-2.5 right-2.5 z-10 w-8 h-8 rounded-full bg-white/80 backdrop-blur-md text-slate-700 flex items-center justify-center shadow-md hover:bg-white transition" aria-label="Toggle Wishlist">
            <i class="far fa-heart text-sm"></i>
        </button>

        <!-- Product Image Container -->
        <div class="w-full aspect-[4/3] bg-slate-50 rounded-xl overflow-hidden relative border border-slate-100 flex items-center justify-center cursor-pointer" onclick="openQuickView(' . $id . ')">
            <img src="' . $img . '" alt="' . $name . '" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        </div>

        <!-- Brand & Title -->
        <div>
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">' . $brand . '</span>
            <h3 class="text-base font-bold text-slate-900 mt-0.5 line-clamp-1 hover:text-purple-600 cursor-pointer" onclick="openQuickView(' . $id . ')">' . $name . '</h3>
        </div>

        <!-- Rating & Reviews -->
        <div class="flex items-center gap-1.5">
            ' . render_stars($rating) . '
            <span class="text-xs font-bold text-slate-700">' . number_format($rating, 1) . '</span>
            <span class="text-xs text-gray-400 font-medium">(' . $reviews . ' reviews)</span>
        </div>

        <!-- Price & Strikethrough Discount -->
        <div class="flex items-baseline gap-2">
            <span class="text-xl font-black text-slate-900">₹' . number_format($price, 2) . '</span>
            ' . ($mrp > $price ? '<span class="text-xs text-gray-400 line-through font-medium">₹' . number_format($mrp, 2) . '</span>' : '') . '
            ' . ($discount_pct > 0 ? '<span class="text-xs font-bold text-rose-500 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-100">' . $discount_pct . '% OFF</span>' : '') . '
        </div>

        <!-- Full-Width Light Purple Add to Cart Button -->
        <button type="button" onclick="addToCart(' . $id . ')" class="w-full bg-purple-100 hover:bg-purple-200 text-purple-700 font-bold text-sm py-3 px-4 rounded-xl transition duration-200 flex items-center justify-center gap-2 border border-purple-200 active:scale-[0.98]">
            <i class="fas fa-shopping-cart text-base"></i>
            <span>Add to Cart</span>
        </button>
    </div>';
}

