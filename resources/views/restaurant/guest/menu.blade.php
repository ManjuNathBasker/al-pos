<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $table->name }} | Digital Menu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .glass { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 pb-24" x-data="guestApp()" x-cloak>
    <!-- Header -->
    <header class="sticky top-0 z-40 glass border-b border-white px-6 py-4 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black text-slate-800">CENTRAL PLAZA</h1>
            <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest">{{ $table->name }} • {{ $table->section->name }}</p>
        </div>
        <div class="flex gap-3">
            <button @click="showTracking = true" class="p-2 bg-indigo-50 rounded-full text-indigo-600 relative">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <template x-if="activeOrder">
                    <span class="absolute -top-1 -right-1 w-3 h-3 bg-emerald-500 rounded-full border-2 border-white"></span>
                </template>
            </button>
        </div>
    </header>

    <!-- Categories Scroll -->
    <div class="sticky top-[73px] z-30 glass py-4 px-6 overflow-x-auto no-scrollbar flex gap-3 border-b border-slate-100">
        <button @click="activeCategory = 'all'" :class="activeCategory === 'all' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'bg-white text-slate-500 border border-slate-200'" class="whitespace-nowrap px-4 py-2 rounded-full text-sm font-bold transition-all">
            All Items
        </button>
        @foreach($categories as $category)
            <button @click="activeCategory = '{{ $category->id }}'" :class="activeCategory === '{{ $category->id }}' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'bg-white text-slate-500 border border-slate-200'" class="whitespace-nowrap px-4 py-2 rounded-full text-sm font-bold transition-all">
                {{ $category->name }}
            </button>
        @endforeach
    </div>

    <!-- Menu Items -->
    <main class="px-6 py-8 space-y-10">
        @foreach($categories as $category)
            <section x-show="activeCategory === 'all' || activeCategory === '{{ $category->id }}'">
                <h2 class="text-2xl font-black text-slate-800 mb-6">{{ $category->name }}</h2>
                <div class="grid grid-cols-1 gap-6">
                    @foreach($products->where('category_id', $category->id) as $product)
                        <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 flex gap-4 hover:shadow-md transition-shadow">
                            <div class="w-24 h-24 bg-slate-100 rounded-2xl flex-shrink-0 overflow-hidden">
                                @if($product->image)
                                    <img src="{{ str_starts_with($product->image, 'http') ? $product->image : asset('storage/' . $product->image) }}" class="w-full h-full object-cover" onerror="this.src='https://placehold.co/400x400?text=Food'">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-300">
                                        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 flex flex-col justify-between">
                                <div>
                                    <h3 class="font-bold text-slate-800 leading-tight">{{ $product->name }}</h3>
                                    <p class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $product->description }}</p>
                                </div>
                                <div class="flex items-center justify-between mt-2">
                                    <span class="font-black text-lg text-slate-800">₹{{ number_format($product->price, 2) }}</span>
                                    
                                    <div class="flex items-center gap-2">
                                        <template x-if="isInCart({{ $product->id }})">
                                            <div class="flex items-center gap-3 bg-indigo-50 rounded-full px-2 py-1">
                                                <button @click="updateQty({{ $product->id }}, -1)" class="w-8 h-8 flex items-center justify-center text-indigo-600 font-bold">-</button>
                                                <span class="text-sm font-bold text-indigo-600" x-text="getQty({{ $product->id }})"></span>
                                                <button @click="updateQty({{ $product->id }}, 1)" class="w-8 h-8 flex items-center justify-center text-indigo-600 font-bold">+</button>
                                            </div>
                                        </template>
                                        <template x-if="!isInCart({{ $product->id }})">
                                            <button @click="addToCart({ id: {{ $product->id }}, name: '{{ $product->name }}', price: {{ $product->price }} })" class="w-10 h-10 bg-indigo-600 text-white rounded-full flex items-center justify-center shadow-lg shadow-indigo-100 active:scale-95 transition-transform">
                                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                </svg>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach
    </main>

    <!-- Floating Cart Bar -->
    <div x-show="cart.length > 0" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0" class="fixed bottom-6 left-6 right-6 z-50">
        <button @click="showCheckout = true" class="w-full bg-slate-900 text-white rounded-3xl py-4 px-8 flex items-center justify-between shadow-2xl active:scale-[0.98] transition-transform">
            <div class="flex items-center gap-4">
                <div class="relative">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 11-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span class="absolute -top-2 -right-2 bg-indigo-500 text-white text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center ring-4 ring-slate-900" x-text="cartCount"></span>
                </div>
                <div class="text-left">
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-widest">VIEW ORDER</span>
                    <span class="text-base font-black" x-text="'₹' + cartTotal.toFixed(2)"></span>
                </div>
            </div>
            <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>

    <!-- Checkout Modal -->
    <div x-show="showCheckout" class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center p-4 sm:p-6" x-cloak>
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showCheckout = false"></div>
        <div class="relative w-full max-w-lg bg-white rounded-t-[3rem] sm:rounded-[3rem] overflow-hidden shadow-2xl" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-full sm:translate-y-10 sm:opacity-0" x-transition:enter-end="translate-y-0 sm:translate-y-0 sm:opacity-100">
            <div class="px-8 pt-10 pb-6 text-center border-b border-slate-50">
                <h3 class="text-2xl font-black text-slate-800">Review Your Order</h3>
                <p class="text-sm text-slate-400 mt-1">Check your items before sending to kitchen</p>
            </div>
            
            <div class="px-8 py-6 max-h-[50vh] overflow-y-auto space-y-4">
                <template x-for="item in cart" :key="item.id">
                    <div class="flex items-center justify-between bg-slate-50 p-4 rounded-3xl border border-slate-100">
                        <div>
                            <p class="font-bold text-slate-800" x-text="item.name"></p>
                            <p class="text-xs text-slate-400" x-text="'₹' + item.price.toFixed(2) + ' x ' + item.qty"></p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button @click="updateQty(item.id, -1)" class="w-8 h-8 bg-white border border-slate-200 rounded-full flex items-center justify-center text-slate-600 font-bold">-</button>
                            <span class="text-sm font-bold text-slate-800" x-text="item.qty"></span>
                            <button @click="updateQty(item.id, 1)" class="w-8 h-8 bg-white border border-slate-200 rounded-full flex items-center justify-center text-slate-600 font-bold">+</button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="px-8 pt-4 pb-10 bg-white">
                <div class="flex items-center justify-between mb-6">
                    <span class="text-lg font-bold text-slate-400">Total Amount</span>
                    <span class="text-3xl font-black text-slate-900" x-text="'₹' + cartTotal.toFixed(2)"></span>
                </div>
                <button @click="placeOrder()" :disabled="isPlacing" class="w-full bg-indigo-600 text-white rounded-3xl py-5 font-black text-lg shadow-xl shadow-indigo-100 active:scale-[0.98] transition-all disabled:opacity-50">
                    <span x-show="!isPlacing">CONFIRM ORDER</span>
                    <span x-show="isPlacing">PLACING ORDER...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Order Tracking Modal -->
    <div x-show="showTracking" class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center p-0 sm:p-6" x-cloak>
        <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-md" @click="showTracking = false"></div>
        <div class="relative w-full max-w-lg bg-white rounded-t-[2.5rem] sm:rounded-[2.5rem] overflow-hidden shadow-2xl transition-all" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0">
            <!-- Indicator for mobile swipe -->
            <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mt-4 mb-2 sm:hidden"></div>
            
            <div class="px-8 pt-6 pb-6 bg-white border-b border-slate-50">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h3 class="text-2xl font-black text-slate-800">Order Status</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em] mt-1">Real-time updates</p>
                    </div>
                    <button @click="showTracking = false" class="p-3 bg-slate-50 rounded-2xl text-slate-400">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                
                <template x-if="activeOrder">
                    <div class="relative overflow-hidden bg-slate-900 rounded-[2rem] p-6 text-white shadow-xl shadow-slate-200">
                        <!-- Abstract background shape -->
                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-indigo-500/20 rounded-full blur-2xl"></div>
                        
                        <div class="relative flex items-center gap-5">
                            <div class="w-16 h-16 rounded-2xl flex items-center justify-center bg-white/10 backdrop-blur-sm border border-white/10">
                                <template x-if="activeOrder.kitchen_status === 'pending'">
                                    <svg class="w-8 h-8 text-amber-400 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </template>
                                <template x-if="activeOrder.kitchen_status === 'preparing'">
                                    <svg class="w-8 h-8 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                </template>
                                <template x-if="activeOrder.kitchen_status === 'ready'">
                                    <svg class="w-8 h-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </template>
                                <template x-if="activeOrder.kitchen_status === 'served'">
                                    <svg class="w-8 h-8 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </template>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1" x-text="activeOrder.kitchen_status === 'pending' ? 'WAITING' : 'CURRENT STATUS'"></p>
                                <p class="text-xl font-black uppercase tracking-wide" x-text="activeOrder.kitchen_status"></p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="px-8 py-8 bg-white max-h-[50vh] overflow-y-auto space-y-6">
                <template x-if="activeOrder">
                    <div>
                        <div class="space-y-6 mb-8">
                            <template x-for="item in activeOrder.items" :key="item.id">
                                <div class="flex justify-between items-center group">
                                    <div class="flex items-center gap-4">
                                        <div class="w-2 h-2 rounded-full bg-slate-200 group-hover:bg-indigo-400 transition-colors"></div>
                                        <div>
                                            <p class="font-bold text-slate-800" x-text="item.product_name"></p>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest" x-text="'QTY: ' + item.quantity"></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <span class="font-black text-slate-900" x-text="'₹' + parseFloat(item.subtotal).toFixed(2)"></span>
                                        <template x-if="item.kitchen_status === 'pending'">
                                            <button @click="removeItem(item.id)" class="p-2 bg-red-50 text-red-500 rounded-xl hover:bg-red-100 transition-colors">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </template>
                                        <template x-if="item.kitchen_status !== 'pending'">
                                            <span class="text-[8px] font-black uppercase tracking-tighter px-2 py-1 bg-indigo-50 text-indigo-600 rounded-lg" x-text="item.kitchen_status"></span>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <template x-if="activeOrder.kitchen_status === 'pending'">
                            <button @click="cancelOrder()" class="w-full py-4 bg-slate-50 text-red-500 font-bold rounded-2xl border border-red-100 hover:bg-red-50 transition-colors flex items-center justify-center gap-2 mb-4">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                CANCEL ENTIRE ORDER
                            </button>
                        </template>
                        
                        <p class="text-center text-[10px] font-bold text-slate-300 uppercase tracking-widest">
                            Orders cannot be modified after kitchen acceptance
                        </p>
                    </div>
                </template>
                
                <template x-if="!activeOrder">
                    <div class="text-center py-12">
                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 11-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                        </div>
                        <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">No active orders</p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
        function guestApp() {
            return {
                activeCategory: 'all',
                cart: [],
                showCheckout: false,
                showTracking: false,
                isPlacing: false,
                activeOrder: @json($activeOrder),
                token: '{{ $table->qr_token }}',

                get cartCount() { return this.cart.reduce((sum, item) => sum + item.qty, 0); },
                get cartTotal() { return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0); },

                isInCart(id) { return this.cart.some(i => i.id === id); },
                getQty(id) { 
                    const item = this.cart.find(i => i.id === id);
                    return item ? item.qty : 0;
                },

                addToCart(product) {
                    this.cart.push({ ...product, qty: 1 });
                },

                updateQty(id, delta) {
                    const item = this.cart.find(i => i.id === id);
                    if (item) {
                        item.qty += delta;
                        if (item.qty <= 0) {
                            this.cart = this.cart.filter(i => i.id !== id);
                        }
                    }
                },

                async placeOrder() {
                    this.isPlacing = true;
                    try {
                        const res = await fetch(`/menu/${this.token}/order`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ cart: this.cart })
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.cart = [];
                            this.showCheckout = false;
                            this.refreshStatus();
                            this.showTracking = true;
                        } else {
                            alert(data.message || 'Failed to place order');
                        }
                    } catch (e) {
                        alert('Error placing order');
                    } finally {
                        this.isPlacing = false;
                    }
                },

                async refreshStatus() {
                    try {
                        const res = await fetch(`/menu/${this.token}/status`);
                        const data = await res.json();
                        if (data.success) {
                            this.activeOrder = {
                                status: data.status,
                                kitchen_status: data.kitchen_status,
                                items: data.items
                            };
                        } else {
                            this.activeOrder = null;
                        }
                    } catch (e) {}
                },

                async removeItem(itemId) {
                    if (!confirm('Are you sure you want to remove this item?')) return;
                    try {
                        const res = await fetch(`/menu/${this.token}/item/${itemId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.refreshStatus();
                        } else {
                            alert(data.message || 'Failed to remove item');
                        }
                    } catch (e) {
                        alert('Error removing item');
                    }
                },

                async cancelOrder() {
                    if (!confirm('Are you sure you want to cancel the ENTIRE order?')) return;
                    try {
                        const res = await fetch(`/menu/${this.token}/cancel`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.activeOrder = null;
                            this.showTracking = false;
                            alert('Order cancelled successfully');
                        } else {
                            alert(data.message || 'Failed to cancel order');
                        }
                    } catch (e) {
                        alert('Error cancelling order');
                    }
                },

                init() {
                    // Refresh status every 10 seconds
                    setInterval(() => this.refreshStatus(), 10000);
                }
            }
        }
    </script>
</body>
</html>
