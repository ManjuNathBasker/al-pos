<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $table->name }} | Waiter Order Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .glass-nav { background: rgba(255, 255, 255, 0.92); backdrop-filter: blur(12px); }
        [x-cloak] { display: none !important; }
        .brand-accent { background-color: #F5703E; }
        .brand-text { color: #F5703E; }
        .brand-border { border-color: #F5703E; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 pb-28 antialiased min-h-screen" x-data="guestApp()" x-init="init()" x-cloak>

    {{-- ════════════════════════════════════════════════════════════
         TOP APP HEADER
    ════════════════════════════════════════════════════════════ --}}
    <header class="sticky top-0 z-40 glass-nav border-b border-gray-200/80 px-4 sm:px-6 py-3.5 flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-3">
            <a href="{{ route('waiter.index') }}" class="w-9 h-9 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 flex items-center justify-center transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg text-white font-black text-xs flex items-center justify-center shadow-xs" style="background-color: #F5703E;">
                        {{ $table->name }}
                    </span>
                    <h1 class="text-base font-black text-gray-900 tracking-tight">{{ $table->name }} <span class="text-xs font-semibold text-gray-400">({{ $table->section->name }})</span></h1>
                </div>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="text-[9px] font-extrabold px-2 py-0.5 rounded-full uppercase tracking-wider"
                          :class="{
                              'bg-emerald-50 text-emerald-700 border border-emerald-200': tableData.status === 'available',
                              'bg-orange-50 text-brand-700 border border-brand-200': tableData.status === 'occupied',
                              'bg-blue-50 text-blue-700 border border-blue-200': tableData.status === 'reserved',
                              'bg-gray-100 text-gray-700 border border-gray-200': tableData.status === 'cleaning'
                          }"
                          x-text="tableData.status"></span>
                    <button @click="openStatusModal()" class="text-xs font-bold text-gray-400 hover:text-gray-700 flex items-center gap-1">
                        <svg class="w-3 h-3 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                        <span>Status</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Order Tracker Trigger --}}
        <div class="flex items-center gap-2">
            <button @click="showTracking = true"
                    class="px-3.5 py-2 rounded-2xl bg-white border border-gray-200 hover:bg-gray-50 text-gray-800 text-xs font-black shadow-xs flex items-center gap-2 relative transition-all">
                <svg class="w-4 h-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>Live Kitchen</span>
                <template x-if="activeOrder">
                    <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full animate-ping"></span>
                </template>
            </button>
        </div>
    </header>

    {{-- ════════════════════════════════════════════════════════════
         CATEGORIES SCROLL TABS
    ════════════════════════════════════════════════════════════ --}}
    <div class="sticky top-[61px] z-30 glass-nav py-3 px-4 sm:px-6 overflow-x-auto no-scrollbar flex items-center gap-2 border-b border-gray-200/80">
        <button @click="activeCategory = 'all'"
            :class="activeCategory === 'all' ? 'text-white shadow-md shadow-brand-500/20' : 'bg-white text-gray-600 border border-gray-200/90 hover:bg-gray-100'"
            :style="activeCategory === 'all' ? 'background-color: #F5703E;' : ''"
            class="whitespace-nowrap px-4 py-2 rounded-xl text-xs font-black transition-all">
            🍽️ All Items
        </button>
        @foreach($categories as $category)
            @if($products->filter(fn($p) => (string)$p->category_id === (string)$category->id)->count() > 0)
            <button @click="activeCategory = '{{ $category->id }}'"
                :class="String(activeCategory) === String('{{ $category->id }}') ? 'text-white shadow-md shadow-brand-500/20' : 'bg-white text-gray-600 border border-gray-200/90 hover:bg-gray-100'"
                :style="String(activeCategory) === String('{{ $category->id }}') ? 'background-color: #F5703E;' : ''"
                class="whitespace-nowrap px-4 py-2 rounded-xl text-xs font-black transition-all">
                {{ $category->name }}
            </button>
            @endif
        @endforeach

        @php
            $catIds = array_map('strval', $categories->pluck('id')->toArray());
            $uncatCount = $products->filter(fn($p) => empty($p->category_id) || !in_array((string)$p->category_id, $catIds))->count();
        @endphp
        @if($uncatCount > 0)
            <button @click="activeCategory = 'uncategorized'"
                :class="activeCategory === 'uncategorized' ? 'text-white shadow-md shadow-brand-500/20' : 'bg-white text-gray-600 border border-gray-200/90 hover:bg-gray-100'"
                :style="activeCategory === 'uncategorized' ? 'background-color: #F5703E;' : ''"
                class="whitespace-nowrap px-4 py-2 rounded-xl text-xs font-black transition-all">
                📦 General Items
            </button>
        @endif
    </div>

    {{-- ════════════════════════════════════════════════════════════
         MENU PRODUCTS GRID
    ════════════════════════════════════════════════════════════ --}}
    <main class="px-4 sm:px-6 py-6 space-y-8 max-w-7xl mx-auto">
        @php
            $hasDisplayedAnyProduct = false;
        @endphp

        {{-- Categories with assigned products --}}
        @foreach($categories as $category)
            @php
                $catProducts = $products->filter(fn($p) => (string)$p->category_id === (string)$category->id);
            @endphp
            @if($catProducts->count() > 0)
                @php $hasDisplayedAnyProduct = true; @endphp
                <section x-show="activeCategory === 'all' || String(activeCategory) === String('{{ $category->id }}')" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-black text-gray-900 tracking-tight flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full" style="background-color: #F5703E;"></span>
                            {{ $category->name }}
                        </h2>
                        <span class="text-xs font-bold text-gray-400">{{ $catProducts->count() }} items</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($catProducts as $product)
                            <div class="bg-white rounded-3xl p-4 shadow-sm border border-gray-200/80 flex flex-col justify-between hover:shadow-md transition-all">
                                <div class="flex gap-3.5">
                                    {{-- Product Thumbnail --}}
                                    <div class="w-20 h-20 bg-gray-100 rounded-2xl flex-shrink-0 overflow-hidden relative border border-gray-100">
                                        @if($product->image)
                                            <img src="{{ str_starts_with($product->image, 'http') ? $product->image : asset('storage/' . $product->image) }}" class="w-full h-full object-cover" onerror="this.src='https://placehold.co/400x400?text=Food'">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                                <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Product Info --}}
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-bold text-gray-900 text-sm leading-snug line-clamp-1">{{ $product->name }}</h3>
                                        <p class="text-xs text-gray-400 mt-0.5 line-clamp-2">{{ $product->description ?: 'Delicious freshly prepared dish' }}</p>
                                        <span class="text-xs font-bold text-gray-400 mt-1 block">SKU: {{ $product->sku ?: 'ITM-'.$product->id }}</span>
                                    </div>
                                </div>

                                {{-- Bottom Price & Stepper Action --}}
                                <div class="flex items-center justify-between pt-3 mt-3 border-t border-gray-100">
                                    <span class="font-black text-base text-gray-900">@currency($product->price)</span>

                                    <div class="flex items-center gap-2">
                                        <template x-if="isInCart({{ $product->id }})">
                                            <div class="flex items-center gap-2 bg-orange-50 border border-brand-200 rounded-xl px-2 py-1">
                                                <button @click="updateQty({{ $product->id }}, -1)"
                                                        class="w-7 h-7 flex items-center justify-center bg-white rounded-lg text-brand-700 font-black text-sm shadow-xs hover:bg-orange-100 transition-colors">-</button>
                                                <span class="text-xs font-black text-brand-900 px-1" x-text="getQty({{ $product->id }})"></span>
                                                <button @click="updateQty({{ $product->id }}, 1)"
                                                        class="w-7 h-7 flex items-center justify-center bg-white rounded-lg text-brand-700 font-black text-sm shadow-xs hover:bg-orange-100 transition-colors">+</button>
                                            </div>
                                        </template>
                                        <template x-if="!isInCart({{ $product->id }})">
                                            <button @click="addToCart({ id: {{ $product->id }}, name: '{{ $product->name }}', price: {{ $product->price }} })"
                                                    class="px-3.5 py-1.5 text-white rounded-xl flex items-center gap-1.5 text-xs font-black shadow-md shadow-brand-500/20 active:scale-95 transition-all"
                                                    style="background-color: #F5703E;">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                </svg>
                                                <span>Add</span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach

        {{-- Uncategorized Products Section --}}
        @php
            $uncategorizedProducts = $products->filter(function($p) use ($catIds) {
                return empty($p->category_id) || !in_array((string)$p->category_id, $catIds);
            });
        @endphp
        @if($uncategorizedProducts->count() > 0)
            @php $hasDisplayedAnyProduct = true; @endphp
            <section x-show="activeCategory === 'all' || activeCategory === 'uncategorized'" class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-black text-gray-900 tracking-tight flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full" style="background-color: #F5703E;"></span>
                        General Items
                    </h2>
                    <span class="text-xs font-bold text-gray-400">{{ $uncategorizedProducts->count() }} items</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($uncategorizedProducts as $product)
                        <div class="bg-white rounded-3xl p-4 shadow-sm border border-gray-200/80 flex flex-col justify-between hover:shadow-md transition-all">
                            <div class="flex gap-3.5">
                                {{-- Product Thumbnail --}}
                                <div class="w-20 h-20 bg-gray-100 rounded-2xl flex-shrink-0 overflow-hidden relative border border-gray-100">
                                    @if($product->image)
                                        <img src="{{ str_starts_with($product->image, 'http') ? $product->image : asset('storage/' . $product->image) }}" class="w-full h-full object-cover" onerror="this.src='https://placehold.co/400x400?text=Food'">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                                            <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                {{-- Product Info --}}
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-gray-900 text-sm leading-snug line-clamp-1">{{ $product->name }}</h3>
                                    <p class="text-xs text-gray-400 mt-0.5 line-clamp-2">{{ $product->description ?: 'Delicious freshly prepared dish' }}</p>
                                    <span class="text-xs font-bold text-gray-400 mt-1 block">SKU: {{ $product->sku ?: 'ITM-'.$product->id }}</span>
                                </div>
                            </div>

                            {{-- Bottom Price & Stepper Action --}}
                            <div class="flex items-center justify-between pt-3 mt-3 border-t border-gray-100">
                                <span class="font-black text-base text-gray-900">@currency($product->price)</span>

                                <div class="flex items-center gap-2">
                                    <template x-if="isInCart({{ $product->id }})">
                                        <div class="flex items-center gap-2 bg-orange-50 border border-brand-200 rounded-xl px-2 py-1">
                                            <button @click="updateQty({{ $product->id }}, -1)"
                                                    class="w-7 h-7 flex items-center justify-center bg-white rounded-lg text-brand-700 font-black text-sm shadow-xs hover:bg-orange-100 transition-colors">-</button>
                                            <span class="text-xs font-black text-brand-900 px-1" x-text="getQty({{ $product->id }})"></span>
                                            <button @click="updateQty({{ $product->id }}, 1)"
                                                    class="w-7 h-7 flex items-center justify-center bg-white rounded-lg text-brand-700 font-black text-sm shadow-xs hover:bg-orange-100 transition-colors">+</button>
                                        </div>
                                    </template>
                                    <template x-if="!isInCart({{ $product->id }})">
                                        <button @click="addToCart({ id: {{ $product->id }}, name: '{{ $product->name }}', price: {{ $product->price }} })"
                                                class="px-3.5 py-1.5 text-white rounded-xl flex items-center gap-1.5 text-xs font-black shadow-md shadow-brand-500/20 active:scale-95 transition-all"
                                                style="background-color: #F5703E;">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            <span>Add</span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Fallback: If no products were displayed but products exist --}}
        @if(!$hasDisplayedAnyProduct && $products->count() > 0)
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-black text-gray-900 tracking-tight flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full" style="background-color: #F5703E;"></span>
                        Menu Items
                    </h2>
                    <span class="text-xs font-bold text-gray-400">{{ $products->count() }} items</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($products as $product)
                        <div class="bg-white rounded-3xl p-4 shadow-sm border border-gray-200/80 flex flex-col justify-between hover:shadow-md transition-all">
                            <div class="flex gap-3.5">
                                {{-- Product Thumbnail --}}
                                <div class="w-20 h-20 bg-gray-100 rounded-2xl flex-shrink-0 overflow-hidden relative border border-gray-100">
                                    @if($product->image)
                                        <img src="{{ str_starts_with($product->image, 'http') ? $product->image : asset('storage/' . $product->image) }}" class="w-full h-full object-cover" onerror="this.src='https://placehold.co/400x400?text=Food'">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                                            <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                {{-- Product Info --}}
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-gray-900 text-sm leading-snug line-clamp-1">{{ $product->name }}</h3>
                                    <p class="text-xs text-gray-400 mt-0.5 line-clamp-2">{{ $product->description ?: 'Delicious freshly prepared dish' }}</p>
                                    <span class="text-xs font-bold text-gray-400 mt-1 block">SKU: {{ $product->sku ?: 'ITM-'.$product->id }}</span>
                                </div>
                            </div>

                            {{-- Bottom Price & Stepper Action --}}
                            <div class="flex items-center justify-between pt-3 mt-3 border-t border-gray-100">
                                <span class="font-black text-base text-gray-900">@currency($product->price)</span>

                                <div class="flex items-center gap-2">
                                    <template x-if="isInCart({{ $product->id }})">
                                        <div class="flex items-center gap-2 bg-orange-50 border border-brand-200 rounded-xl px-2 py-1">
                                            <button @click="updateQty({{ $product->id }}, -1)"
                                                    class="w-7 h-7 flex items-center justify-center bg-white rounded-lg text-brand-700 font-black text-sm shadow-xs hover:bg-orange-100 transition-colors">-</button>
                                            <span class="text-xs font-black text-brand-900 px-1" x-text="getQty({{ $product->id }})"></span>
                                            <button @click="updateQty({{ $product->id }}, 1)"
                                                    class="w-7 h-7 flex items-center justify-center bg-white rounded-lg text-brand-700 font-black text-sm shadow-xs hover:bg-orange-100 transition-colors">+</button>
                                        </div>
                                    </template>
                                    <template x-if="!isInCart({{ $product->id }})">
                                        <button @click="addToCart({ id: {{ $product->id }}, name: '{{ $product->name }}', price: {{ $product->price }} })"
                                                class="px-3.5 py-1.5 text-white rounded-xl flex items-center gap-1.5 text-xs font-black shadow-md shadow-brand-500/20 active:scale-95 transition-all"
                                                style="background-color: #F5703E;">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            <span>Add</span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @elseif($products->isEmpty())
            <div class="bg-white rounded-3xl p-12 text-center border border-gray-200/80 shadow-sm space-y-3">
                <div class="w-16 h-16 bg-orange-50 text-brand-500 rounded-2xl flex items-center justify-center mx-auto text-2xl border border-orange-100">
                    🍽️
                </div>
                <h3 class="text-base font-black text-gray-900">No Active Products Found</h3>
                <p class="text-xs text-gray-400 max-w-sm mx-auto font-medium">There are currently no active products in your menu. Please add or activate products under Admin → Products.</p>
            </div>
        @endif
    </main>

    {{-- ════════════════════════════════════════════════════════════
         FLOATING BOTTOM CART STRIP
    ════════════════════════════════════════════════════════════ --}}
    <div x-show="cart.length > 0"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         class="fixed bottom-5 left-4 right-4 sm:left-auto sm:right-6 sm:w-96 z-50">
        <button @click="showCheckout = true"
                class="w-full bg-gray-900 hover:bg-black text-white rounded-3xl py-3.5 px-5 flex items-center justify-between shadow-2xl active:scale-[0.98] transition-all border border-gray-800">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-white" style="background-color: #F5703E;">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 11-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <span class="absolute -top-1.5 -right-1.5 text-white text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center ring-2 ring-gray-900"
                          style="background-color: #F5703E;"
                          x-text="cartCount"></span>
                </div>
                <div class="text-left">
                    <span class="block text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">ORDER SUMMARY</span>
                    <span class="text-base font-black text-white" x-text="formatCurrency(cartTotal)"></span>
                </div>
            </div>
            <div class="flex items-center gap-1.5 text-xs font-black text-orange-400">
                <span>Review & Send</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </button>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         CHECKOUT & ORDER CONFIRMATION MODAL
    ════════════════════════════════════════════════════════════ --}}
    <div x-show="showCheckout" class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center p-4 sm:p-6" x-cloak>
        <div class="fixed inset-0 bg-gray-950/60 backdrop-blur-sm" @click="showCheckout = false"></div>
        <div class="relative w-full max-w-lg bg-white rounded-3xl overflow-hidden shadow-2xl border border-gray-100"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="translate-y-full sm:translate-y-8 sm:opacity-0"
             x-transition:enter-end="translate-y-0 sm:translate-y-0 sm:opacity-100">

            {{-- Modal Header --}}
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-black text-gray-900">Review Table Order</h3>
                    <p class="text-xs text-gray-400 font-semibold mt-0.5">Send selected items to kitchen KDS station</p>
                </div>
                <button @click="showCheckout = false" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 flex items-center justify-center text-xs font-bold transition-colors">
                    ✕
                </button>
            </div>

            {{-- Cart Items --}}
            <div class="px-6 py-5 max-h-[50vh] overflow-y-auto space-y-3">
                <template x-for="item in cart" :key="item.id">
                    <div class="flex items-center justify-between bg-gray-50 p-3.5 rounded-2xl border border-gray-200/70">
                        <div>
                            <p class="font-bold text-gray-900 text-xs" x-text="item.name"></p>
                            <p class="text-[11px] text-gray-400 font-semibold mt-0.5" x-text="formatCurrency(item.price) + ' × ' + item.qty"></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="updateQty(item.id, -1)" class="w-7 h-7 bg-white border border-gray-200 rounded-lg flex items-center justify-center text-gray-700 font-bold text-xs shadow-xs hover:bg-gray-100">-</button>
                            <span class="text-xs font-black text-gray-900 w-5 text-center" x-text="item.qty"></span>
                            <button @click="updateQty(item.id, 1)" class="w-7 h-7 bg-white border border-gray-200 rounded-lg flex items-center justify-center text-gray-700 font-bold text-xs shadow-xs hover:bg-gray-100">+</button>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Footer Summary & Confirm Button --}}
            <div class="px-6 py-5 bg-gray-50 border-t border-gray-100 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Bill Amount</span>
                    <span class="text-2xl font-black text-gray-900" x-text="formatCurrency(cartTotal)"></span>
                </div>
                <button @click="placeOrder()" :disabled="isPlacing"
                        class="w-full text-white rounded-2xl py-3.5 font-black text-sm shadow-xl shadow-brand-500/25 active:scale-[0.98] transition-all disabled:opacity-50 flex items-center justify-center gap-2"
                        style="background-color: #F5703E;">
                    <span x-show="!isPlacing">🚀 SEND ORDER TO KITCHEN</span>
                    <span x-show="isPlacing">SENDING TO KITCHEN...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         LIVE KITCHEN TRACKING DRAWER
    ════════════════════════════════════════════════════════════ --}}
    <div x-show="showTracking" class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center p-0 sm:p-6" x-cloak>
        <div class="fixed inset-0 bg-gray-950/70 backdrop-blur-sm" @click="showTracking = false"></div>
        <div class="relative w-full max-w-lg bg-white rounded-t-3xl sm:rounded-3xl overflow-hidden shadow-2xl transition-all border border-gray-100"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0">

            <div class="px-6 py-5 bg-white border-b border-gray-100 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-black text-gray-900">Table Orders & Kitchen Status</h3>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-0.5">Real-time KDS synchronization</p>
                </div>
                <button @click="showTracking = false" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 flex items-center justify-center text-xs font-bold">
                    ✕
                </button>
            </div>

            <div class="p-6 max-h-[60vh] overflow-y-auto space-y-5">
                <template x-if="activeOrder">
                    <div class="space-y-4">
                        {{-- Status Banner --}}
                        <div class="p-4 rounded-2xl text-white shadow-md flex items-center justify-between"
                             style="background-color: #F5703E;">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-white">
                                    <template x-if="activeOrder.kitchen_status === 'pending'">
                                        <svg class="w-5 h-5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </template>
                                    <template x-if="activeOrder.kitchen_status === 'preparing'">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                    </template>
                                    <template x-if="activeOrder.kitchen_status === 'ready'">
                                        <svg class="w-5 h-5 text-emerald-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    </template>
                                    <template x-if="activeOrder.kitchen_status === 'served'">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </template>
                                </div>
                                <div>
                                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-orange-100">Kitchen State</p>
                                    <p class="text-base font-black uppercase tracking-wide" x-text="activeOrder.kitchen_status"></p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-white/20 text-white"
                                  x-text="activeOrder.status"></span>
                        </div>

                        {{-- Item list --}}
                        <div class="space-y-2.5">
                            <template x-for="item in activeOrder.items" :key="item.id">
                                <div class="flex justify-between items-center p-3 rounded-2xl bg-gray-50 border border-gray-200/70">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                                        <div>
                                            <p class="font-bold text-gray-900 text-xs" x-text="item.product_name"></p>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider" x-text="'QTY: ' + item.quantity"></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="font-black text-xs text-gray-900" x-text="formatCurrency(item.subtotal)"></span>
                                        <template x-if="item.kitchen_status === 'pending'">
                                            <button @click="removeItem(item.id)" class="p-1.5 bg-red-50 text-red-500 rounded-lg hover:bg-red-100 transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Actions: Cancel or Complete or Print KOT --}}
                        <div class="pt-3 border-t border-gray-100 space-y-2">
                            <template x-if="activeOrder">
                                <button @click="printActiveOrderKOT()" type="button" class="w-full py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-extrabold rounded-2xl border border-indigo-200 transition-colors flex items-center justify-center gap-2 text-xs shadow-sm active:scale-95">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    <span>Print KOT Slip</span>
                                </button>
                            </template>

                            <template x-if="activeOrder && activeOrder.kitchen_status === 'pending'">
                                <button @click="cancelOrder()" class="w-full py-3 bg-red-50 hover:bg-red-100 text-red-600 font-bold rounded-2xl border border-red-200 transition-colors flex items-center justify-center gap-2 text-xs">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    <span>Cancel Entire Order</span>
                                </button>
                            </template>

                            <template x-if="activeOrder && activeOrder.status !== 'closed'">
                                <button @click="completeOrder()" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl shadow-lg transition-colors flex items-center justify-center gap-2 text-xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    <span>Complete & Free Table</span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="!activeOrder">
                    <div class="text-center py-10">
                        <div class="w-16 h-16 bg-gray-100 rounded-3xl flex items-center justify-center mx-auto mb-3 text-2xl">🍽️</div>
                        <p class="text-gray-700 font-bold text-sm">No active order for this table</p>
                        <p class="text-xs text-gray-400 mt-0.5">Select menu items and click Send to Kitchen</p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         UPDATE TABLE STATUS MODAL
    ════════════════════════════════════════════════════════════ --}}
    <div x-show="isStatusModalOpen" class="fixed inset-0 z-[70] flex items-center justify-center p-4" style="display: none;">
        <div class="fixed inset-0 bg-gray-950/60 backdrop-blur-sm" @click="isStatusModalOpen = false"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden border border-gray-100" @click.stop>
            <div class="p-5 border-b border-gray-100">
                <h3 class="text-base font-black text-gray-900">Update Table Status</h3>
                <p class="text-xs text-gray-400 mt-0.5">Change real-time occupancy state</p>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Status</label>
                    <select x-model="modalData.status" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-xs font-bold text-gray-800 focus:outline-none focus:border-brand-500">
                        <option value="available">Available</option>
                        <option value="occupied">Occupied</option>
                        <option value="reserved">Reserved</option>
                        <option value="cleaning">Cleaning</option>
                    </select>
                </div>

                <template x-if="modalData.status === 'reserved'">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Customer Name (Optional)</label>
                            <input type="text" x-model="modalData.customer_name" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-bold text-gray-800 focus:outline-none focus:border-brand-500" placeholder="e.g. John Doe">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Phone Number (Optional)</label>
                            <input type="text" x-model="modalData.customer_phone" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-bold text-gray-800 focus:outline-none focus:border-brand-500" placeholder="e.g. 9876543210">
                        </div>
                    </div>
                </template>
            </div>
            <div class="p-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-2.5">
                <button @click="isStatusModalOpen = false" class="px-4 py-2 text-xs font-bold text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-100">Cancel</button>
                <button @click="saveStatus()" class="px-4 py-2 text-xs font-black text-white rounded-xl shadow-md shadow-brand-500/20" style="background-color: #F5703E;" :disabled="isSavingStatus" x-text="isSavingStatus ? 'Saving...' : 'Save Changes'"></button>
            </div>
        </div>
    </div>

    <script>
        function guestApp() {
            return {
                currency: {!! json_encode(current_currency_config()) !!},
                formatCurrency(val) {
                    const num = parseFloat(val) || 0;
                    const isNegative = num < 0;
                    const absNum = Math.abs(num);
                    const d = typeof this.currency.decimal_places === 'number' ? this.currency.decimal_places : 2;
                    const formatted = absNum.toLocaleString('en-US', { minimumFractionDigits: d, maximumFractionDigits: d });
                    const res = this.currency.symbol_position === 'after' ? `${formatted} ${this.currency.symbol}` : `${this.currency.symbol}${formatted}`;
                    return isNegative ? `-${res}` : res;
                },
                activeCategory: 'all',
                cart: [],
                showCheckout: false,
                showTracking: false,
                isPlacing: false,
                activeOrder: @json($activeOrder),
                tableId: '{{ $table->id }}',
                tableData: {
                    status: '{{ $table->status }}',
                    customer_name: '{{ addslashes($table->customer_name ?? '') }}',
                    customer_phone: '{{ addslashes($table->customer_phone ?? '') }}'
                },
                isStatusModalOpen: false,
                isSavingStatus: false,
                modalData: {
                    status: '',
                    customer_name: '',
                    customer_phone: ''
                },

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
                        const res = await fetch(`/waiter/order/${this.tableId}`, {
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
                        const res = await fetch(`/waiter/order/${this.tableId}/status`);
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
                        const res = await fetch(`/waiter/order/${this.tableId}/item/${itemId}`, {
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
                        const res = await fetch(`/waiter/order/${this.tableId}/cancel`, {
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

                async completeOrder() {
                    if (!confirm('Mark order as COMPLETED and free this table?')) return;
                    try {
                        const res = await fetch(`/waiter/order/${this.tableId}/complete`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            alert('Order completed successfully!');
                            window.location.href = '/waiter';
                        } else {
                            alert(data.message || 'Failed to complete order');
                        }
                    } catch (e) {
                        alert('Error completing order');
                    }
                },

                openStatusModal() {
                    this.modalData = {
                        status: this.tableData.status,
                        customer_name: this.tableData.customer_name,
                        customer_phone: this.tableData.customer_phone
                    };
                    this.isStatusModalOpen = true;
                },

                async saveStatus() {
                    this.isSavingStatus = true;
                    try {
                        const res = await fetch(`/tables/${this.tableId}/status`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                status: this.modalData.status,
                                customer_name: this.modalData.customer_name,
                                customer_phone: this.modalData.customer_phone,
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.tableData.status = this.modalData.status;
                            this.tableData.customer_name = this.modalData.customer_name;
                            this.tableData.customer_phone = this.modalData.customer_phone;
                            this.isStatusModalOpen = false;
                        } else {
                            alert(data.message || 'Error updating status');
                        }
                    } catch (e) {
                        alert('Network error updating status');
                    } finally {
                        this.isSavingStatus = false;
                    }
                },

                printActiveOrderKOT() {
                    if (!this.activeOrder) return;
                    const items = (this.activeOrder.items || []).map(i => ({
                        name: i.product_name,
                        qty: i.quantity,
                        note: i.note
                    }));
                    printKOTSlip({
                        service_type: this.activeOrder.service_type || 'dine_in',
                        kot_id: 'KOT-' + this.activeOrder.id,
                        order_number: this.activeOrder.order_number || ('#ORD-' + this.activeOrder.id),
                        table_name: '{{ $table->name }}',
                        time: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}),
                        items: items
                    });
                },

                init() {
                    setInterval(() => this.refreshStatus(), 10000);
                }
            };
        }

        function printKOTSlip(data) {
            const win = window.open('', '_blank', 'width=400,height=600');
            const itemsHtml = (data.items || []).map(item => `
                <tr style="border-bottom: 1px dashed #ccc;">
                    <td style="padding: 4px 0; font-size: 14px; font-weight: bold; width: 40px; vertical-align: top;">${item.qty || item.quantity}x</td>
                    <td style="padding: 4px 0; font-size: 13px; font-weight: bold; vertical-align: top;">
                        ${item.name || item.product_name}
                        ${item.note ? `<div style="font-size: 11px; font-weight: normal; color: #333; font-style: italic;">Note: ${item.note}</div>` : ''}
                    </td>
                </tr>
            `).join('');

            const html = `<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>KOT Print Slip</title>
    <style>
        body { margin: 0; padding: 10px; font-family: 'Courier New', monospace; font-size: 12px; background: #fff; color: #000; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px 2px; }
        .kot-container { width: 100%; max-width: 300px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="kot-container">
        <div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 6px; margin-bottom: 8px;">
            <h2 style="margin: 0; font-size: 18px; font-weight: bold; text-transform: uppercase;">*** KITCHEN TICKET (KOT) ***</h2>
            <div style="font-size: 14px; font-weight: bold; margin-top: 4px; background: #000; color: #fff; padding: 3px 8px; display: inline-block; border-radius: 4px;">
                SERVICE: ${(data.service_type || 'DINE-IN').toUpperCase().replace('_', ' ')}
            </div>
        </div>

        <table style="width: 100%; font-size: 11px; margin-bottom: 8px; border-bottom: 1px dashed #000; padding-bottom: 6px;">
            <tr>
                <td><strong>KOT ID:</strong> ${data.kot_id || 'KOT-' + (data.order_id || '')}</td>
                <td style="text-align: right;"><strong>ORDER #:</strong> ${data.order_number || ('#ORD-' + (data.order_id || ''))}</td>
            </tr>
            <tr>
                <td><strong>TABLE:</strong> ${data.table_name || 'N/A'}</td>
                <td style="text-align: right;"><strong>TIME:</strong> ${data.time || new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</td>
            </tr>
        </table>

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
            <thead>
                <tr style="border-bottom: 1px solid #000;">
                    <th style="text-align: left; padding: 4px 0; font-size: 11px;">QTY</th>
                    <th style="text-align: left; padding: 4px 0; font-size: 11px;">ITEM</th>
                </tr>
            </thead>
            <tbody>
                ${itemsHtml}
            </tbody>
        </table>

        <div style="text-align: center; border-top: 2px solid #000; padding-top: 8px; font-size: 10px; font-weight: bold;">
            *** END OF KOT SLIP ***
        </div>
    </div>
    <script>
        window.onload = function() {
            window.print();
            setTimeout(function() { window.close(); }, 500);
        };
    <\/script>
</body>
</html>`;

            win.document.write(html);
            win.document.close();
        }
    </script>
</body>
</html>
