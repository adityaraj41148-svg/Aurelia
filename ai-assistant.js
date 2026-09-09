/**
 * AJIO FASHIONATION — ONAM EDIT 2026
 * Onam AI Stylist Chatbot Engine (Rule-based JavaScript AI)
 */

document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('chatbot-toggle');
    const closeBtn = document.getElementById('chatbot-close');
    const panel = document.getElementById('chatbot-panel');
    const input = document.getElementById('chatbot-input');
    const sendBtn = document.getElementById('chatbot-send');
    const messagesContainer = document.getElementById('chatbot-messages');

    if (!toggleBtn || !panel) return;

    toggleBtn.addEventListener('click', () => {
        panel.classList.toggle('hidden');
        panel.classList.toggle('chat-open');
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            panel.classList.add('hidden');
            panel.classList.remove('chat-open');
        });
    }

    // Quick suggestion chip listeners
    document.querySelectorAll('.chat-chip-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const prompt = btn.getAttribute('data-prompt');
            if (prompt) {
                handleUserMessage(prompt);
            }
        });
    });

    if (sendBtn && input) {
        sendBtn.addEventListener('click', () => {
            const text = input.value.trim();
            if (text) {
                handleUserMessage(text);
                input.value = '';
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                const text = input.value.trim();
                if (text) {
                    handleUserMessage(text);
                    input.value = '';
                }
            }
        });
    }

    function appendMessage(sender, htmlContent) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `chat-msg ${sender}`;
        
        const bubble = document.createElement('div');
        bubble.className = `chat-bubble ${sender === 'user' ? 'bg-amber-500 text-slate-950 font-medium ml-auto' : 'bg-slate-900 border border-slate-800 text-slate-200'} p-3 rounded-2xl text-xs leading-relaxed max-w-[85%]`;
        bubble.innerHTML = htmlContent;

        msgDiv.appendChild(bubble);
        messagesContainer.appendChild(msgDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function handleUserMessage(query) {
        appendMessage('user', escapeHTML(query));

        // Generate Bot Response
        setTimeout(() => {
            const reply = generateStylistReply(query.toLowerCase());
            appendMessage('bot', reply);
        }, 400);
    }

    function escapeHTML(str) {
        return str.replace(/[&<>'"]/g, 
            tag => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[tag] || tag)
        );
    }

    function generateStylistReply(q) {
        if (q.includes('under') || q.includes('3000') || q.includes('budget') || q.includes('cheap')) {
            const affordable = PRODUCTS.filter(p => p.price <= 3000).slice(0, 3);
            let response = "Here are our top festive picks under ₹3,000:<br><div class='mt-2 space-y-2'>";
            affordable.forEach(p => {
                response += `
                    <div class='flex items-center gap-2 bg-slate-950 p-2 rounded-xl border border-slate-800'>
                        <img src='${p.image}' class='w-10 h-12 object-cover rounded'>
                        <div class='flex-grow min-w-0'>
                            <div class='font-bold text-white truncate text-[11px]'>${p.name}</div>
                            <div class='text-amber-400 font-mono font-bold text-xs'>₹${p.price.toLocaleString()}</div>
                        </div>
                        <button onclick='addToCart(${p.id}, null, null, true)' class='bg-amber-500 text-slate-950 font-bold text-[10px] px-2 py-1 rounded'>Add</button>
                    </div>
                `;
            });
            response += "</div>";
            return response;
        }

        if (q.includes('wear') || q.includes('suggest') || q.includes('outfit')) {
            return "For traditional Onam celebrations, we recommend a classic <strong>Kerala Kasavu Saree</strong> for women paired with temple jewellery, or a <strong>Royal Silk Kurta & Kasavu Mundu</strong> set for men.";
        }

        if (q.includes('women') || q.includes('saree') || q.includes('anarkali') || q.includes('lehenga')) {
            return "Showing our graceful Women's Kasavu Collection! We feature tissue silk sarees, brocade Anarkali suits, and temple zari lehengas with up to 50% discount.";
        }

        if (q.includes('men') || q.includes('kurta') || q.includes('mundu') || q.includes('jacket')) {
            return "Explore Flying Machine & Heritage Weaves Men's festive wear including raw silk kurtas, double dhoti Mundus, and jacquard Nehru jackets.";
        }

        if (q.includes('family') || q.includes('kid') || q.includes('matching')) {
            return "Try our <strong>Build Your Onam Family Look</strong> feature! We offer coordinated Kasavu sarees, men's silk kurtas, and kids' Pattu Pavadai sets.";
        }

        if (q.includes('deal') || q.includes('coupon') || q.includes('offer')) {
            return "Current Best Offer: Use coupon <strong>ONAM2026</strong> at checkout for flat ₹500 cashback on orders above ₹2,999, or <strong>ICICI10</strong> for 10% instant bank discount!";
        }

        if (q.includes('cart')) {
            const count = (typeof cart !== 'undefined') ? cart.length : 0;
            return `You currently have <strong>${count} items</strong> in your shopping cart. Click the cart icon at the top or bottom floating button to review.`;
        }

        return "I am Onam AI Stylist! I can help you choose outfits, recommend budget fashion under ₹3,000, or apply discount coupons. Try asking 'Suggest men outfit' or 'Show sarees under ₹3000'.";
    }
});
