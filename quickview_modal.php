<!-- Quick View Modal Drawer -->
<div id="quickview-overlay" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 hidden transition-opacity duration-300"></div>

<div id="quickview-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl shadow-2xl max-w-3xl w-full mx-auto relative overflow-hidden transform transition-all duration-300 scale-95 opacity-0 border border-slate-100 max-h-[90vh] flex flex-col md:flex-row">
        <!-- Close Button -->
        <button id="close-quickview-btn" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 bg-slate-100 rounded-full w-9 h-9 flex items-center justify-center z-10 transition">
            <i class="fas fa-times text-lg"></i>
        </button>

        <!-- Product Image Preview -->
        <div class="w-full md:w-1/2 bg-slate-50 p-6 flex items-center justify-center relative">
            <img id="qv-image" src="" alt="Product Image" class="max-h-80 md:max-h-96 w-full object-cover rounded-2xl shadow-sm">
        </div>

        <!-- Product Info & Actions -->
        <div class="w-full md:w-1/2 p-6 md:p-8 flex flex-col justify-between overflow-y-auto">
            <div>
                <span id="qv-brand" class="text-xs font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-md">Brand</span>
                <h2 id="qv-title" class="text-xl md:text-2xl font-extrabold text-slate-900 mt-2 mb-2 leading-snug">Product Title</h2>
                
                <div class="flex items-center gap-2 mb-4">
                    <div id="qv-stars" class="inline-flex items-center text-amber-400 text-xs"></div>
                    <span id="qv-reviews" class="text-xs text-slate-400 font-medium">(12 reviews)</span>
                </div>

                <div class="flex items-baseline gap-3 mb-4">
                    <span id="qv-price" class="text-2xl font-black text-slate-900">₹0.00</span>
                    <span id="qv-mrp" class="text-sm text-slate-400 line-through">₹0.00</span>
                </div>

                <p id="qv-desc" class="text-xs text-slate-600 leading-relaxed mb-5 line-clamp-3">Product Description...</p>

                <!-- Size Selector -->
                <div class="mb-4">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Select Size:</label>
                    <div id="qv-sizes" class="flex flex-wrap gap-2">
                        <button type="button" class="qv-size-pill px-3 py-1.5 border rounded-lg text-xs font-bold border-indigo-600 bg-indigo-50 text-indigo-600">S</button>
                        <button type="button" class="qv-size-pill px-3 py-1.5 border rounded-lg text-xs font-bold border-slate-200 text-slate-700 hover:border-indigo-600">M</button>
                        <button type="button" class="qv-size-pill px-3 py-1.5 border rounded-lg text-xs font-bold border-slate-200 text-slate-700 hover:border-indigo-600">L</button>
                        <button type="button" class="qv-size-pill px-3 py-1.5 border rounded-lg text-xs font-bold border-slate-200 text-slate-700 hover:border-indigo-600">XL</button>
                    </div>
                </div>

                <!-- Quantity Selector -->
                <div class="mb-6">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Quantity:</label>
                    <div class="flex items-center space-x-3">
                        <div class="flex items-center border border-slate-200 rounded-xl bg-slate-50 p-1">
                            <button type="button" onclick="qvChangeQty(-1)" class="w-8 h-8 rounded-lg bg-white text-slate-700 font-bold hover:bg-slate-100 transition shadow-sm">-</button>
                            <span id="qv-qty" class="w-10 text-center font-extrabold text-slate-800 text-sm">1</span>
                            <button type="button" onclick="qvChangeQty(1)" class="w-8 h-8 rounded-lg bg-white text-slate-700 font-bold hover:bg-slate-100 transition shadow-sm">+</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add to Cart CTA -->
            <button id="qv-add-cart-btn" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-sm py-3.5 rounded-xl shadow-lg transition flex items-center justify-center gap-2">
                <i class="fas fa-shopping-cart"></i>
                <span>Add to Shopping Cart</span>
            </button>
        </div>
    </div>
</div>
