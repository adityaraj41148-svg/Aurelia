<?php
// AURELIA — WEAR THE MOMENT.
// Modular Footer Include
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
    <!-- FOOTER -->
    <footer class="bg-[#0f0e0d] text-[#e6e0d4] py-16 text-xs">
        <div class="container mx-auto px-6 max-w-7xl space-y-12">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-8">
                
                <div class="lg:col-span-2 space-y-4">
                    <h4 class="font-serif-title text-2xl font-normal tracking-widest text-white">A U R E L I A</h4>
                    <p class="text-[11px] text-[#8c857b] font-light leading-relaxed max-w-sm">AURELIA is built around considered design, exceptional materials and effortless silhouettes. Wear the moment.</p>
                </div>

                <div class="space-y-3">
                    <h5 class="text-[10px] font-bold text-[#c5a059] uppercase tracking-editorial">SHOP</h5>
                    <ul class="space-y-2 text-[#e6e0d4]/80">
                        <li><a href="#products" onclick="filterProductCategory('women')" class="hover:text-white">Women</a></li>
                        <li><a href="#products" onclick="filterProductCategory('men')" class="hover:text-white">Men</a></li>
                        <li><a href="#products" onclick="filterProductCategory('kids')" class="hover:text-white">Kids</a></li>
                        <li><a href="#products" onclick="filterProductCategory('all')" class="hover:text-white">Collections</a></li>
                    </ul>
                </div>

                <div class="space-y-3">
                    <h5 class="text-[10px] font-bold text-[#c5a059] uppercase tracking-editorial">CLIENT SERVICES</h5>
                    <ul class="space-y-2 text-[#e6e0d4]/80">
                        <li><a href="#" class="hover:text-white">Contact Us</a></li>
                        <li><a href="#" class="hover:text-white">Shipping & Returns</a></li>
                        <li><a href="#" class="hover:text-white">Size Guide</a></li>
                        <li><a href="#" class="hover:text-white">Authenticity Guarantee</a></li>
                    </ul>
                </div>

                <div class="space-y-3">
                    <h5 class="text-[10px] font-bold text-[#c5a059] uppercase tracking-editorial">JOIN THE JOURNAL</h5>
                    <p class="text-[11px] text-[#8c857b]">Subscribe for early collection access.</p>
                    <form onsubmit="handleNewsletterSubmit(event)" class="space-y-2">
                        <input type="email" required placeholder="Enter your email..." class="w-full bg-[#1a1816] border border-[#57534e]/40 px-3 py-2 text-xs text-white focus:outline-none focus:border-[#c5a059]">
                        <button type="submit" class="w-full bg-[#c5a059] hover:bg-[#b08a43] text-[#0f0e0d] font-bold text-xs py-2 tracking-editorial uppercase transition cursor-pointer">
                            SUBSCRIBE →
                        </button>
                    </form>
                </div>

            </div>

            <div class="border-t border-[#1a1816] pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-[#8c857b]">
                <p>&copy; 2026 AURELIA. WEAR THE MOMENT. All Rights Reserved.</p>
                <div class="flex gap-4">
                    <a href="#" class="hover:text-white">Instagram</a>
                    <a href="#" class="hover:text-white">Pinterest</a>
                    <a href="#" class="hover:text-white">Journal</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- JS Scripts -->
    <script src="<?= $baseUrl ?>products.js"></script>
    <script src="<?= $baseUrl ?>script.js"></script>
</body>
</html>