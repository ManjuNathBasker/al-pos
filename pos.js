function posApp() {
    return {
        // ── State ──
        activeCategory: 'all',
        activeCategoryName: 'All Products',
        searchQuery: '',
        gridView: true,
        isLoading: false,
        isCheckingOut: false,
        showDiscount: false,
        showBillingModal: false,
        // CHANGE #6: Added new modal states
        showOrderCompleted: false,
        showBillModal: false,
        
        customer: {
            name: '',
            phone: '',
            address: '',
        },
        payments: { cash: 0, card: 0, upi: 0 },
        useWallet: false,

        get totalPaid() {
            let cash = parseFloat(this.payments.cash) || 0;
            let card = parseFloat(this.payments.card) || 0;
            let upi = parseFloat(this.payments.upi) || 0;
            return cash + card + upi + this.walletAmount;
        },

        get walletAmount() {
            if (!this.useWallet) return 0;
            return Math.min(this.customer.wallet_balance || 0, this.grandTotal);
        },

        recalcCash() {
            let others = (parseFloat(this.payments.upi) || 0) + (parseFloat(this.payments.card) || 0) + this.walletAmount;
            if (others >= this.grandTotal) {
                this.payments.cash = 0;
            } else {
                this.payments.cash = (this.grandTotal - others).toFixed(2);
            }
        },

        async fetchCustomer() {
            if (this.customer.phone.length >= 7) {
                try {
                    let res = await fetch('/pos/customer?phone=' + this.customer.phone);
                    let data = await res.json();
                    if(data && data.success) {
                        this.customer.name = data.customer.name;
                        this.customer.wallet_balance = parseFloat(data.customer.wallet_balance);
                        if(this.useWallet) this.recalcCash();
                    }
                } catch(e) {}
            }
        },

        get paymentDifference() {
            return parseFloat((this.grandTotal - this.totalPaid).toFixed(2));
        },

        get canSubmitOrder() {
            // Always require name and phone regardless of payment status
            if (!this.customer.name || !this.customer.phone) return false;
            return true;
        },

        get paymentRemaining() {
            return this.grandTotal - this.totalPaid;
        },

        discountPercent: 0,
        orderNote: '',
        lastOrderId: '',
        lastOrderTotal: 0,
        // CHANGE #7: Store order details for bill
        lastOrderItems: [],
        lastOrderCustomer: { name: '', phone: '' },
        lastOrderSubtotal: 0,
        lastOrderDiscount: 0,
        lastOrderDiscountPercent: 0,
        lastOrderTax: 0,
        
        currentTime: '',
        toasts: [],
        toastCounter: 0,

        allProducts: JSON.parse('[{\u0022id\u0022:10,\u0022name\u0022:\u0022Almonds 30g\u0022,\u0022price\u0022:3.49,\u0022image\u0022:\u0022products\\\/lEwishKXWGJ1bcxZEci4z5h4ULb3XYmG5BO89Rv0.jpg\u0022,\u0022sku\u0022:\u0022SNK-004\u0022,\u0022category_id\u0022:\u00222\u0022},{\u0022id\u0022:12,\u0022name\u0022:\u0022Blueberry Muffin\u0022,\u0022price\u0022:3.75,\u0022image\u0022:null,\u0022sku\u0022:\u0022BKY-002\u0022,\u0022category_id\u0022:\u00223\u0022},{\u0022id\u0022:11,\u0022name\u0022:\u0022Butter Croissant\u0022,\u0022price\u0022:3.5,\u0022image\u0022:null,\u0022sku\u0022:\u0022BKY-001\u0022,\u0022category_id\u0022:\u00223\u0022},{\u0022id\u0022:2,\u0022name\u0022:\u0022Caffe Latte\u0022,\u0022price\u0022:4.75,\u0022image\u0022:null,\u0022sku\u0022:\u0022BVR-002\u0022,\u0022category_id\u0022:\u00221\u0022},{\u0022id\u0022:16,\u0022name\u0022:\u0022Cheese Slice Pack\u0022,\u0022price\u0022:3.99,\u0022image\u0022:null,\u0022sku\u0022:\u0022DRY-002\u0022,\u0022category_id\u0022:\u00224\u0022},{\u0022id\u0022:14,\u0022name\u0022:\u0022Cinnamon Roll\u0022,\u0022price\u0022:4.25,\u0022image\u0022:null,\u0022sku\u0022:\u0022BKY-004\u0022,\u0022category_id\u0022:\u00223\u0022},{\u0022id\u0022:3,\u0022name\u0022:\u0022Cold Brew\u0022,\u0022price\u0022:5,\u0022image\u0022:null,\u0022sku\u0022:\u0022BVR-003\u0022,\u0022category_id\u0022:\u00221\u0022},{\u0022id\u0022:20,\u0022name\u0022:\u0022Earbuds Basic\u0022,\u0022price\u0022:19.99,\u0022image\u0022:null,\u0022sku\u0022:\u0022ELC-003\u0022,\u0022category_id\u0022:\u00225\u0022},{\u0022id\u0022:1,\u0022name\u0022:\u0022Espresso Shot\u0022,\u0022price\u0022:2.5,\u0022image\u0022:null,\u0022sku\u0022:\u0022BVR-001\u0022,\u0022category_id\u0022:\u00221\u0022},{\u0022id\u0022:8,\u0022name\u0022:\u0022Granola Bar\u0022,\u0022price\u0022:2.49,\u0022image\u0022:null,\u0022sku\u0022:\u0022SNK-002\u0022,\u0022category_id\u0022:\u00222\u0022},{\u0022id\u0022:15,\u0022name\u0022:\u0022Greek Yogurt\u0022,\u0022price\u0022:4.5,\u0022image\u0022:null,\u0022sku\u0022:\u0022DRY-001\u0022,\u0022category_id\u0022:\u00224\u0022},{\u0022id\u0022:4,\u0022name\u0022:\u0022Matcha Latte\u0022,\u0022price\u0022:5.5,\u0022image\u0022:null,\u0022sku\u0022:\u0022BVR-004\u0022,\u0022category_id\u0022:\u00221\u0022},{\u0022id\u0022:5,\u0022name\u0022:\u0022Orange Juice\u0022,\u0022price\u0022:3.25,\u0022image\u0022:null,\u0022sku\u0022:\u0022BVR-005\u0022,\u0022category_id\u0022:\u00221\u0022},{\u0022id\u0022:9,\u0022name\u0022:\u0022Popcorn (Salted)\u0022,\u0022price\u0022:2.99,\u0022image\u0022:null,\u0022sku\u0022:\u0022SNK-003\u0022,\u0022category_id\u0022:\u00222\u0022},{\u0022id\u0022:19,\u0022name\u0022:\u0022Power Bank 10k\u0022,\u0022price\u0022:29.99,\u0022image\u0022:null,\u0022sku\u0022:\u0022ELC-002\u0022,\u0022category_id\u0022:\u00225\u0022},{\u0022id\u0022:13,\u0022name\u0022:\u0022Sourdough Slice\u0022,\u0022price\u0022:4,\u0022image\u0022:null,\u0022sku\u0022:\u0022BKY-003\u0022,\u0022category_id\u0022:\u00223\u0022},{\u0022id\u0022:6,\u0022name\u0022:\u0022Sparkling Water\u0022,\u0022price\u0022:1.99,\u0022image\u0022:null,\u0022sku\u0022:\u0022BVR-006\u0022,\u0022category_id\u0022:\u00221\u0022},{\u0022id\u0022:7,\u0022name\u0022:\u0022Trail Mix\u0022,\u0022price\u0022:3.99,\u0022image\u0022:null,\u0022sku\u0022:\u0022SNK-001\u0022,\u0022category_id\u0022:\u00222\u0022},{\u0022id\u0022:18,\u0022name\u0022:\u0022USB-C Cable 1m\u0022,\u0022price\u0022:12.99,\u0022image\u0022:null,\u0022sku\u0022:\u0022ELC-001\u0022,\u0022category_id\u0022:\u00225\u0022},{\u0022id\u0022:17,\u0022name\u0022:\u0022Whole Milk 500ml\u0022,\u0022price\u0022:2.25,\u0022image\u0022:null,\u0022sku\u0022:\u0022DRY-003\u0022,\u0022category_id\u0022:\u00224\u0022}]'),
        categoryMap: JSON.parse('{\u00223\u0022:\u0022Bakery\u0022,\u00221\u0022:\u0022Beverages\u0022,\u00224\u0022:\u0022Dairy\u0022,\u00225\u0022:\u0022Electronics\u0022,\u00222\u0022:\u0022Snacks\u0022}'),
        cart: (function(raw) {
            // Normalize: if backend returns array or empty, convert to keyed object
            if (!raw || Array.isArray(raw)) return {};
            // If keys are not product IDs (e.g. sequential), re-key by id
            const result = {};
            Object.values(raw).forEach(item => {
                if (item && item.id) result[String(item.id)] = item;
            });
            return result;
        })([]),
        filteredProducts: [],

        get cartItems() { return Object.values(this.cart); },
        get totalQty() { return this.cartItems.reduce((sum, i) => sum + i.qty, 0); },
        get cartSubtotal() { return this.cartItems.reduce((sum, i) => sum + (i.price * i.qty), 0); },
        get discountAmount() { return this.cartSubtotal * (parseFloat(this.discountPercent) || 0) / 100; },
        get taxAmount() { return (this.cartSubtotal - this.discountAmount) * 0.08; },
        get grandTotal() { return this.cartSubtotal - this.discountAmount + this.taxAmount; },

        init() {
            this.filteredProducts = Array.isArray(this.allProducts) ? [...this.allProducts] : [];
            this.setActiveCategoryName();
            this.startClock();
        },

        startClock() {
            const tick = () => {
                this.currentTime = new Date().toLocaleTimeString('en-US', {
                    hour: '2-digit', minute: '2-digit', second: '2-digit'
                });
            };
            tick();
            setInterval(tick, 1000);
        },

        filterCategory(categoryId) {
            this.activeCategory = String(categoryId);
            this.searchQuery = '';
            this.setActiveCategoryName();
            this.applyFilters();
        },

        setActiveCategoryName() {
            this.activeCategoryName = (this.activeCategory === 'all') 
                ? 'All Products' 
                : (this.categoryMap[this.activeCategory] || 'Unknown');
        },

        filterProducts() { this.applyFilters(); },

        applyFilters() {
            let list = [...this.allProducts];
            if (this.activeCategory !== 'all') {
                list = list.filter(p => String(p.category_id) === this.activeCategory);
            }
            if (this.searchQuery.trim()) {
                const q = this.searchQuery.toLowerCase().trim();
                list = list.filter(p => 
                    p.name.toLowerCase().includes(q) || 
                    (p.sku && p.sku.toLowerCase().includes(q))
                );
            }
            this.filteredProducts = list;
        },

        isInCart(productId) { return !!this.cart[String(productId)]; },

        addToCart(product) {
            const key = String(product.id);
            if (this.cart[key]) {
                this.cart[key].qty++;
            } else {
                this.cart = {
                    ...this.cart,
                    [key]: {
                        id: product.id,
                        name: product.name,
                        price: parseFloat(product.price),
                        image: product.image || null,
                        sku: product.sku || null,
                        qty: 1,
                    }
                };
            }
            this.showToast(product.name + ' added');
            this.syncToBackend('add', { product_id: product.id });
        },

        updateQty(productId, action) {
            const key = String(productId);
            if (!this.cart[key]) return;
            if (action === 'increment') {
                this.cart[key].qty++;
            } else {
                this.cart[key].qty--;
                if (this.cart[key].qty <= 0) {
                    const updated = { ...this.cart };
                    delete updated[key];
                    this.cart = updated;
                }
            }
            this.syncToBackend('update', { product_id: productId, action });
        },

        removeFromCart(productId) {
            const key = String(productId);
            const name = this.cart[key] ? this.cart[key].name : 'Item';
            const updated = { ...this.cart };
            delete updated[key];
            this.cart = updated;
            this.showToast(name + ' removed');
            this.syncToBackend('remove', { product_id: productId });
        },

        clearCart() {
            this.cart = {};
            this.discountPercent = 0;
            this.orderNote = '';
            this.showToast('Cart cleared');
            this.syncToBackend('clear', {});
        },

        syncToBackend(action, payload) {
            const urls = {
                add: 'http://127.0.0.1:8001/pos/cart/add',
                update: 'http://127.0.0.1:8001/pos/cart/update',
                remove: 'http://127.0.0.1:8001/pos/cart/remove',
                clear: 'http://127.0.0.1:8001/pos/cart/clear',
            };
            fetch(urls[action], {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            }).catch(() => {});
        },

        checkout() {
            if (this.cartItems.length === 0) {
                this.showToast('Your cart is empty!', 'error');
                return;
            }
            this.showBillingModal = true;
            this.payments.cash = this.grandTotal.toFixed(2);
            this.payments.upi = 0;
            this.payments.card = 0;
            this.useWallet = false;
        },

        // CHANGE #8: Updated confirmOrder function
        async confirmOrder() {
            if (!this.customer.name || !this.customer.phone) {
                this.showToast('Name and Phone are required', 'error');
                return;
            }
            
            this.isCheckingOut = true;

            // Snapshot cart items BEFORE clearing, for the bill
            const snapshotItems = [...this.cartItems];
            const snapshotSubtotal = this.cartSubtotal;
            const snapshotDiscount = this.discountAmount;
            const snapshotDiscountPercent = this.discountPercent;
            const snapshotTax = this.taxAmount;
            const snapshotTotal = this.grandTotal;
            const snapshotCustomer = { name: this.customer.name, phone: this.customer.phone };

            try {
                const res = await fetch('http://127.0.0.1:8001/pos/checkout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        discount_percent: this.discountPercent,
                        note: this.orderNote,
                        total: this.grandTotal,
                        cart: this.cart,
                        customer_name: this.customer.name,
                        customer_phone: this.customer.phone,
                        billing_address: this.customer.address,
                        payment_details: this.payments, use_wallet: this.useWallet, wallet_amount: this.walletAmount
                    }),
                });

                const data = await res.json();
                
                if (data.success) {
                    // Store receipt data from backend response
                    this.lastOrderId = data.order_id;
                    this.lastOrderTotal = data.total || snapshotTotal;
                } else {
                    this.showToast(data.message || 'Checkout failed', 'error');
                    this.isCheckingOut = false;
                    return;
                }
            } catch (e) {
                // Network error — still show a local order ID so the bill works
                this.lastOrderId = 'LOCAL-' + Date.now();
                this.lastOrderTotal = snapshotTotal;
            }

            // Store all receipt data from snapshot
            this.lastOrderItems = snapshotItems;
            this.lastOrderCustomer = snapshotCustomer;
            this.lastOrderSubtotal = snapshotSubtotal;
            this.lastOrderDiscount = snapshotDiscount;
            this.lastOrderDiscountPercent = snapshotDiscountPercent;
            this.lastOrderTax = snapshotTax;
            
            // Hide billing modal and clear cart
            this.showBillingModal = false;
            this.cart = {};
            this.customer = { name: '', phone: '', address: '' };
            this.payments = { cash: 0, card: 0, qr: 0 };
            this.discountPercent = 0;
            this.orderNote = '';

            // Sync cart clear to backend silently
            this.syncToBackend('clear', {});

            // Show order completed modal
            this.showOrderCompleted = true;
            this.isCheckingOut = false;
        },

        // CHANGE #9: Handle order completed modal close
        handleOrderCompleted() {
            this.showOrderCompleted = false;
        },

        // CHANGE #10: Print bill function
        printBill() {
            const printWindow = window.open('', '_blank', 'width=400,height=600');
            const receiptHTML = document.getElementById('receipt-container').innerHTML;
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <title>Receipt</title>
                    <style>
                        body { margin: 0; padding: 10px; font-family: 'Courier New', monospace; font-size: 12px; background: #fff; color: #000; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { padding: 4px 2px; }
                        .receipt-container { width: 100%; max-width: 300px; margin: 0 auto; }
                    </style>
                </head>
                <body>
                    <div class="receipt-container">${receiptHTML}</div>
                    <script>window.onload = function(){ window.print(); setTimeout(()=>window.close(), 500); }<\/script>
                </body>
                </html>
            `);
            printWindow.document.close();
        },

        // CHANGE #11: Share on WhatsApp function
        shareOnWhatsApp() {
            const phoneNumber = this.lastOrderCustomer.phone.replace(/\D/g, '');
            const message = `Order #${this.lastOrderId}\nTotal: $${this.lastOrderTotal.toFixed(2)}\n\nThank you for your purchase!`;
            const whatsappURL = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
            window.open(whatsappURL, '_blank');
        },

        // CHANGE #12: Start new order function
        startNewOrder() {
            this.showBillModal = false;
            this.showOrderCompleted = false;
        },

        showToast(message, type = 'success') {
            const id = ++this.toastCounter;
            this.toasts.push({ id, message, type });
            setTimeout(() => {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }, 3000);
        },
    };
}
