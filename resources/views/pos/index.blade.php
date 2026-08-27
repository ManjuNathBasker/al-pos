<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>POS Terminal — {{ config('app.name') }}</title>

    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,600&display=swap" rel="stylesheet" />

    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#FFF5F0',
                            100: '#FFE8DC',
                            200: '#FFD0B8',
                            300: '#FFAF8A',
                            400: '#FF8554',
                            500: '#F5703E', // Primary Accent
                            600: '#E05520',
                            700: '#C04010',
                            800: '#9A3008',
                            900: '#7A2506',
                        },
                    },
                    boxShadow: {
                        'card': '0 2px 8px -1px rgba(0, 0, 0, 0.05), 0 1px 3px -1px rgba(0, 0, 0, 0.03)',
                        'card-hover': '0 12px 28px -4px rgba(245, 112, 62, 0.15), 0 4px 12px -2px rgba(0, 0, 0, 0.06)',
                        'panel-left': '2px 0 16px rgba(0, 0, 0, 0.04)',
                        'panel-right': '-2px 0 16px rgba(0, 0, 0, 0.04)',
                        'checkout': '0 8px 25px -4px rgba(245, 112, 62, 0.45), 0 4px 10px -2px rgba(245, 112, 62, 0.25)',
                    }
                },
            },
        };
    </script>

    <style>
        [x-cloak] { display: none !important; }

        *, *::before, *::after {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            box-sizing: border-box;
            user-select: none;
            -webkit-user-select: none;
        }

        input, textarea, select {
            user-select: auto;
            -webkit-user-select: auto;
        }

        html, body {
            height: 100%;
            width: 100%;
            overflow: hidden;
            background-color: #F6F7F9;
        }

        /* ── Numeric Tabular Figures ── */
        .price, .tabular {
            font-variant-numeric: tabular-nums;
        }

        /* ── Custom Scrollbars ── */
        .thin-scroll::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        .thin-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .thin-scroll::-webkit-scrollbar-thumb {
            background: #E2E4E8;
            border-radius: 99px;
        }
        .thin-scroll::-webkit-scrollbar-thumb:hover {
            background: #CBD0D8;
        }

        /* ── Sidebar Category Buttons ── */
        .sidebar-cat-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 9px 12px;
            border-radius: 12px;
            font-size: 12.5px;
            font-weight: 600;
            color: #4B5563;
            transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: left;
            cursor: pointer;
            border: 1px solid transparent;
            background: transparent;
        }
        .sidebar-cat-btn:hover {
            background: #FFF5F0;
            color: #F5703E;
            border-color: #FFE8DC;
        }
        .sidebar-cat-btn.active {
            background: #F5703E;
            color: #FFFFFF !important;
            border-color: #F5703E;
            box-shadow: 0 4px 14px rgba(245, 112, 62, 0.35);
        }
        .sidebar-cat-btn.active .cat-icon-box {
            background: rgba(255, 255, 255, 0.25);
            color: #FFFFFF;
        }
        .sidebar-cat-btn.active .cat-badge {
            background: rgba(255, 255, 255, 0.28);
            color: #FFFFFF;
        }

        .cat-icon-box {
            width: 30px;
            height: 30px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: #F3F4F6;
            color: #6B7280;
            font-size: 14px;
            transition: all 0.18s;
        }

        /* ── Service Mode Buttons ── */
        .service-mode-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 9px 4px;
            border-radius: 12px;
            border: 1.5px solid #F0F1F4;
            cursor: pointer;
            transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            color: #6B7280;
            background: #FAFAFB;
        }
        .service-mode-btn:hover {
            background: #FFF5F0;
            color: #F5703E;
            border-color: #FFE8DC;
        }
        .service-mode-btn.active {
            background: #F5703E;
            color: #FFFFFF;
            border-color: #F5703E;
            box-shadow: 0 4px 14px rgba(245, 112, 62, 0.38);
        }

        /* ── Product Cards ── */
        .restaurant-card {
            background: #FFFFFF;
            border-radius: 18px;
            overflow: hidden;
            border: 1.5px solid #EBECEF;
            cursor: pointer;
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s ease, border-color 0.2s ease;
            display: flex;
            flex-direction: column;
            position: relative;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
            height: 100%;
        }
        .restaurant-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 28px -4px rgba(245, 112, 62, 0.18), 0 4px 12px rgba(0, 0, 0, 0.05);
            border-color: #F5703E;
        }
        .restaurant-card:active {
            transform: translateY(-1px) scale(0.98);
        }
        .restaurant-card.in-cart {
            border-color: #F5703E;
            background: #FFFFFF;
            box-shadow: 0 0 0 2px rgba(245, 112, 62, 0.22), 0 4px 14px rgba(245, 112, 62, 0.12);
        }

        /* ── Product List View Cards ── */
        .restaurant-list-card {
            background: #FFFFFF;
            border-radius: 16px;
            border: 1.5px solid #EBECEF;
            cursor: pointer;
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            padding: 12px 16px !important;
            gap: 16px !important;
            position: relative;
            width: 100% !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
        }
        .restaurant-list-card:hover {
            transform: translateY(-2px);
            border-color: #F5703E;
            box-shadow: 0 8px 20px -3px rgba(245, 112, 62, 0.15), 0 3px 8px rgba(0, 0, 0, 0.04);
        }
        .restaurant-list-card:active {
            transform: translateY(0) scale(0.99);
        }
        .restaurant-list-card.in-cart {
            border-color: #F5703E;
            background: #FFFFFF;
            box-shadow: 0 0 0 2px rgba(245, 112, 62, 0.2), 0 4px 12px rgba(245, 112, 62, 0.08);
        }

        .card-img-wrap {
            position: relative;
            overflow: hidden;
            width: 100%;
            height: 140px;
            background: #FAFAFA;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
        }
        .card-img-wrap img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            transition: transform 0.3s ease;
        }
        .restaurant-card:hover .card-img-wrap img {
            transform: scale(1.06);
        }

        .card-quick-add {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(245, 112, 62, 0.2);
            backdrop-filter: blur(2px);
            opacity: 0;
            transition: opacity 0.2s ease;
            pointer-events: none;
        }
        .restaurant-card:hover .card-quick-add {
            opacity: 1;
        }
        .card-quick-add-btn {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #F5703E;
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 18px rgba(245, 112, 62, 0.65);
            transform: scale(0.75);
            transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .restaurant-card:hover .card-quick-add-btn {
            transform: scale(1);
        }

        /* ── Cart Quantity Badge ── */
        .cart-qty-pill {
            position: absolute;
            top: 8px;
            right: 8px;
            z-index: 10;
            min-width: 26px;
            height: 26px;
            border-radius: 99px;
            background: #F5703E;
            color: #FFFFFF;
            font-size: 11px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 7px;
            border: 2px solid #FFFFFF;
            box-shadow: 0 3px 10px rgba(245, 112, 62, 0.55);
            animation: bounceIn 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        @keyframes bounceIn {
            0% { transform: scale(0.4); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        /* ── Cart Item Card ── */
        .cart-item-row {
            background: #FFFFFF;
            border-radius: 14px;
            padding: 10px 12px;
            border: 1.5px solid #F0F1F4;
            transition: all 0.18s ease;
        }
        .cart-item-row:hover {
            border-color: #FFD0B8;
            box-shadow: 0 4px 12px rgba(245, 112, 62, 0.08);
        }

        /* ── Touch Stepper ── */
        .touch-stepper-btn {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 700;
            line-height: 1;
            transition: all 0.15s ease;
            background: #F3F4F6;
            color: #374151;
        }
        .touch-stepper-btn:hover {
            background: #F5703E;
            color: #FFFFFF;
        }
        .touch-stepper-btn:active {
            transform: scale(0.88);
        }

        /* ── Primary Checkout CTA ── */
        .btn-checkout-primary {
            background: linear-gradient(135deg, #F5703E 0%, #E05520 100%);
            color: #FFFFFF;
            font-weight: 800;
            border: none;
            cursor: pointer;
            border-radius: 16px;
            box-shadow: 0 8px 24px -4px rgba(245, 112, 62, 0.5), 0 4px 10px -2px rgba(245, 112, 62, 0.3);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-checkout-primary:hover:not(:disabled) {
            background: linear-gradient(135deg, #E05520 0%, #C04010 100%);
            box-shadow: 0 12px 28px -4px rgba(245, 112, 62, 0.65);
            transform: translateY(-2px);
        }
        .btn-checkout-primary:active:not(:disabled) {
            transform: translateY(0);
            box-shadow: 0 4px 12px rgba(245, 112, 62, 0.4);
        }
        .btn-checkout-primary:disabled {
            background: linear-gradient(135deg, #D1D5DB 0%, #9CA3AF 100%) !important;
            box-shadow: none !important;
            transform: none !important;
            cursor: not-allowed;
            opacity: 0.85;
        }

        /* ── Spinner ── */
        .spin-loader {
            animation: spinAnim 0.7s linear infinite;
        }
        @keyframes spinAnim {
            to { transform: rotate(360deg); }
        }

        /* ── Toast Animation ── */
        @keyframes toastSlideDown {
            from { opacity: 0; transform: translateY(-12px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .toast-animate {
            animation: toastSlideDown 0.22s ease-out;
        }

        /* ── Thermal Receipt Styles ── */
        @media print {
            body { background: #FFFFFF !important; }
            .no-print { display: none !important; }
            .receipt-container { page-break-after: always; margin: 0; padding: 0; }
        }
        .receipt-container {
            width: 80mm;
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.4;
            color: #000000;
            padding: 12px;
            background: #FFFFFF;
        }
    </style>
</head>

<body x-data="posApp()" x-init="init()" class="h-full flex flex-col antialiased">

{{-- ════════════════════════════════════════════════════════════
     REGISTER SESSION CHECK (MODAL IF CLOSED)
════════════════════════════════════════════════════════════ --}}
@php
    $openSession = \App\Models\RegisterSession::openForUser(auth()->id())->first();
    $defaultOpening = 0;
    $cashAccountBalance = 0;
    if (!$openSession) {
        $lastSession = \App\Models\RegisterSession::where('user_id', auth()->id())
            ->where('status', 'closed')->latest('closed_at')->first();
        if ($lastSession) { $defaultOpening = $lastSession->closing_amount_actual; }
        $cashAccount = \App\Models\Account::where('company_id', session('company_id'))
            ->where(function($q) { $q->where('account_name','like','%Cash%')->orWhere('account_code','1000'); })
            ->first();
        if ($cashAccount) { $cashAccountBalance = $cashAccount->calculateBalance(); $defaultOpening = $cashAccountBalance; }
    }
@endphp

{{-- ════════════════════════════════════════════════════════════
     OPEN REGISTER MODAL
════════════════════════════════════════════════════════════ --}}
@if(!$openSession)
<div class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-md">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden border border-gray-100">
        <div class="h-2 bg-gradient-to-r from-brand-500 to-amber-500"></div>
        <div class="p-7">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-5 bg-brand-50 text-brand-500 border border-brand-100">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7H6a2 2 0 00-2 2v9a2 2 0 002 2h9a2 2 0 002-2v-3M9 7h8m-8 0V5a2 2 0 012-2h2a2 2 0 012 2v2m-6 0h6"/>
                </svg>
            </div>
            <h2 class="text-xl font-extrabold text-gray-900 mb-1">Open Cash Register</h2>
            <p class="text-xs text-gray-500 mb-5">Enter your opening cash float to begin your shift.</p>

            <form action="{{ route('register-sessions.open') }}" method="POST">
                @csrf
                @if($cashAccountBalance > 0)
                <div class="flex items-center justify-between mb-2 px-3 py-1.5 rounded-xl bg-gray-50 border border-gray-100">
                    <span class="text-xs font-semibold text-gray-500">Cash Ledger</span>
                    <span class="text-xs font-bold text-brand-600">@currency($cashAccountBalance)</span>
                </div>
                @endif

                <div class="relative mb-5">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-xl">@currencySymbol</span>
                    <input type="number" name="opening_amount" step="0.01" min="0" required autofocus
                        class="w-full pl-10 pr-4 py-3.5 rounded-2xl border-2 border-gray-200 bg-gray-50 text-xl font-bold text-gray-900 outline-none focus:border-brand-500 focus:bg-white transition-all tabular"
                        placeholder="0.00" value="{{ number_format($defaultOpening, 2, '.', '') }}">
                </div>

                <div class="flex gap-2.5">
                    <a href="{{ route('dashboard') }}" class="flex-1 py-3 text-center font-semibold text-gray-500 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors text-xs">← Dashboard</a>
                    <button type="submit" class="flex-1 py-3 text-center font-bold text-white bg-brand-500 hover:bg-brand-600 rounded-xl transition-all shadow-lg shadow-brand-500/30 text-xs">
                        Open Shift →
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- ════════════════════════════════════════════════════════════
     CLOSE REGISTER MODAL
════════════════════════════════════════════════════════════ --}}
@if($openSession)
<div x-show="showCloseRegister" style="display:none;"
     class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-md"
     x-transition>
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden border border-gray-100">
        <div class="h-2 bg-red-500"></div>
        <div class="p-7">
            <h2 class="text-xl font-extrabold text-gray-900 mb-1">Close Cash Register</h2>
            <p class="text-xs text-gray-500 mb-4">Count physical cash in drawer and finalize shift.</p>

            <form action="{{ route('register-sessions.close', $openSession->id) }}" method="POST">
                @csrf
                <div class="bg-gray-50 rounded-2xl p-4 mb-4 space-y-2 border border-gray-100">
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Opening Amount</span>
                        <span class="font-bold text-gray-800">@currency($openSession->opening_amount)</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Expected Closing</span>
                        <span class="font-bold text-brand-600">@currency($openSession->calculateExpectedAmount())</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Actual Cash Counted (@currencySymbol)</label>
                    <input type="number" name="closing_amount_actual" step="0.01" min="0" required
                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-gray-50 text-lg font-bold text-gray-900 outline-none focus:border-red-500 focus:bg-white transition-all tabular"
                        placeholder="0.00">
                </div>

                <div class="mb-5">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Shift Notes (Optional)</label>
                    <textarea name="notes" rows="2" class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-xs text-gray-700 outline-none focus:border-red-500 resize-none transition-all" placeholder="Any discrepancies..."></textarea>
                </div>

                <div class="flex gap-2.5">
                    <button type="button" @click="showCloseRegister=false" class="flex-1 py-3 text-center font-semibold text-gray-500 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors text-xs">Cancel</button>
                    <button type="submit" class="flex-1 py-3 text-center font-bold text-white bg-red-500 hover:bg-red-600 rounded-xl transition-all shadow-lg shadow-red-500/20 text-xs">Close Shift</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- ════════════════════════════════════════════════════════════
     TOAST NOTIFICATIONS (Bottom-Left Floating Toast)
════════════════════════════════════════════════════════════ --}}
<div class="fixed bottom-6 left-6 z-[90] space-y-2 pointer-events-none w-auto max-w-sm">
    <template x-for="toast in toasts" :key="toast.id">
        <div class="toast-animate flex items-center gap-2.5 px-4 py-2.5 rounded-2xl shadow-2xl pointer-events-auto border backdrop-blur-md"
             :class="toast.type==='error'
                ? 'bg-red-900/95 border-red-700 text-white shadow-red-950/40'
                : 'bg-gray-900/95 border-gray-700 text-white shadow-black/30'">
            <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 text-xs"
                 :class="toast.type==='error' ? 'bg-red-500 text-white' : 'bg-brand-500 text-white'">
                <svg x-show="toast.type!=='error'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                <svg x-show="toast.type==='error'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <p class="text-xs font-semibold flex-1 truncate" x-text="toast.message"></p>
        </div>
    </template>
</div>

{{-- ════════════════════════════════════════════════════════════
     ORDER COMPLETED MODAL
════════════════════════════════════════════════════════════ --}}
<div x-show="showOrderCompleted" x-cloak
     class="fixed inset-0 z-[150] flex items-center justify-center bg-black/60 backdrop-blur-md"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     @click.self="handleOrderCompleted()">
    <div class="bg-white rounded-3xl p-8 shadow-2xl max-w-sm w-full mx-4 text-center border border-gray-100"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100">
        <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-5 bg-emerald-50 text-emerald-500 border border-emerald-100 shadow-lg shadow-emerald-500/10">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <div class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full inline-flex items-center gap-1.5 mb-3 border border-emerald-100">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            Payment Successful
        </div>
        <h2 class="text-2xl font-black text-gray-900 mb-1">Order Placed!</h2>
        <p class="text-xs text-gray-400 mb-2" x-text="'Order #' + lastOrderId"></p>
        <p class="text-4xl font-black price text-brand-500 mb-6" x-text="formatCurrency(lastOrderTotal)"></p>

        <div class="space-y-2">
            <button @click="showOrderCompleted=false; $nextTick(()=>showBillModal=true)"
                class="w-full btn-checkout-primary py-3.5 text-sm rounded-2xl flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                View & Print Receipt
            </button>
            <button @click="startNewOrder()" class="w-full py-2.5 text-xs font-bold text-gray-400 hover:text-gray-700 transition-colors">
                Start Next Order →
            </button>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════
     TABLES SELECTION MODAL (Dine-In)
════════════════════════════════════════════════════════════ --}}
<div x-show="showTablesModal" x-cloak
     class="fixed inset-0 z-[120] flex items-center justify-center bg-black/60 backdrop-blur-md"
     x-transition @click.self="showTablesModal=false">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden border border-gray-100">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-black text-gray-900">Active Dine-In Tables</h2>
                <p class="text-xs text-gray-400 mt-0.5">Select a table with an active session to load its cart</p>
            </div>
            <button @click="showTablesModal=false" class="w-8 h-8 rounded-xl bg-gray-100 flex items-center justify-center text-gray-400 hover:bg-gray-200 hover:text-gray-700 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 max-h-[60vh] overflow-y-auto thin-scroll">
            <template x-for="table in activeTablesList" :key="table.id">
                <button @click="loadTableOrder(table)"
                    class="p-4 bg-white border-2 border-gray-100 rounded-2xl text-left hover:border-brand-500 hover:shadow-lg hover:shadow-brand-500/10 transition-all active:scale-95 group">
                    <div class="w-10 h-10 rounded-xl mb-2.5 flex items-center justify-center bg-brand-50 text-brand-500 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <div class="text-sm font-bold text-gray-900 truncate" x-text="table.name"></div>
                    <div class="text-[10px] text-gray-400 mb-2 truncate" x-text="table.section ? table.section.name : 'Main Dining Area'"></div>
                    <div class="text-sm font-black price text-brand-500" x-text="formatCurrency(table.active_order.total_amount)"></div>
                </button>
            </template>
            <template x-if="activeTablesList.length===0">
                <div class="col-span-full py-16 text-center text-gray-400">
                    <div class="text-4xl mb-2">🍽️</div>
                    <p class="text-sm font-bold text-gray-600">No active tables found</p>
                    <p class="text-xs text-gray-400 mt-1">All tables are currently open and available</p>
                </div>
            </template>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════
     RECEIPT BILL MODAL
════════════════════════════════════════════════════════════ --}}
<div x-show="showBillModal" x-cloak
     class="fixed inset-0 z-[150] flex items-center justify-center bg-black/60 backdrop-blur-md no-print"
     x-transition @click.self="showBillModal=false">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full mx-4 max-h-[92vh] overflow-hidden flex flex-col border border-gray-100">
        <div class="flex-1 overflow-y-auto thin-scroll p-6">
            <div id="receipt-container" class="receipt-container bg-white p-2">
                <div style="text-align:center;margin-bottom:12px;">
                    <h2 style="margin:0;font-size:16px;text-transform:uppercase;font-weight:bold;">{{ config('app.name') }}</h2>
                    <p style="margin:2px 0;font-size:11px;">123 Supermarket St, Retail City</p>
                    <p style="margin:2px 0;font-size:11px;">Tel: +1 234 567 890</p>
                    <div style="margin:8px 0;border-top:1px dashed #000;border-bottom:1px dashed #000;padding:6px 0;text-align:center;">
                        <div style="font-size:15px;font-weight:bold;letter-spacing:0.5px;">TAX INVOICE / RECEIPT</div>
                        <div style="font-size:13px;font-weight:bold;margin-top:2px;" x-text="'ORDER #: ' + (lastOrderNumber || ('#ORD-' + String(lastOrderId || '').padStart(5, '0')))"></div>
                        <template x-if="lastOrderKotNumber">
                            <div style="font-size:11px;color:#333;margin-top:2px;" x-text="'KOT REF: ' + lastOrderKotNumber"></div>
                        </template>
                        <div style="font-size:12px;font-weight:bold;text-transform:uppercase;margin-top:4px;background:#f3f4f6;padding:3px 6px;display:inline-block;border-radius:4px;">
                            SERVICE MODE: <span x-text="formatServiceMode(lastOrderServiceType || serviceType)"></span>
                            <template x-if="lastOrderTableName"><span x-text="' (' + lastOrderTableName + ')'"></span></template>
                        </div>
                    </div>
                </div>
                <div style="margin-bottom:10px;font-size:11px;line-height:1.4;">
                    <div style="display:flex;justify-content:space-between;">
                        <span><strong>Date & Time:</strong></span>
                        <span x-text="new Date().toLocaleString('en-US')"></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span><strong>Cashier:</strong></span>
                        <span>{{ auth()->user()->name ?? 'Admin' }}</span>
                    </div>
                    <template x-if="lastOrderCustomer && lastOrderCustomer.name">
                        <div style="display:flex;justify-content:space-between;">
                            <span><strong>Customer:</strong></span>
                            <span x-text="lastOrderCustomer.name + (lastOrderCustomer.phone ? ' (' + lastOrderCustomer.phone + ')' : '')"></span>
                        </div>
                    </template>
                    <template x-if="lastOrderTableName">
                        <div style="display:flex;justify-content:space-between;">
                            <span><strong>Table / Seating:</strong></span>
                            <span x-text="lastOrderTableName"></span>
                        </div>
                    </template>
                    <template x-if="(lastOrderServiceType==='delivery' || serviceType==='delivery') && customer.address">
                        <div style="display:flex;justify-content:space-between;">
                            <span><strong>Delivery Address:</strong></span>
                            <span x-text="customer.address"></span>
                        </div>
                    </template>
                </div>
                <table style="width:100%;border-collapse:collapse;margin-bottom:10px;font-size:11px;">
                    <thead>
                        <tr style="border-bottom:1px solid #000;">
                            <th style="text-align:left;padding:4px 0;">Item</th>
                            <th style="text-align:center;padding:4px 0;">Qty</th>
                            <th style="text-align:right;padding:4px 0;">Price</th>
                            <th style="text-align:right;padding:4px 0;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="item in lastOrderItems" :key="item.id">
                            <tr style="border-bottom:1px dashed #eee;">
                                <td style="padding:4px 0;">
                                    <span x-text="item.name"></span>
                                    <template x-if="item.sku"><br><small x-text="'SKU: ' + item.sku" style="font-size:9px;color:#555;"></small></template>
                                </td>
                                <td style="text-align:center;padding:4px 0;" x-text="item.qty"></td>
                                <td style="text-align:right;padding:4px 0;" x-text="formatCurrency(item.price)"></td>
                                <td style="text-align:right;padding:4px 0;" x-text="formatCurrency(item.price*item.qty)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div style="border-top:1px dashed #000;padding-top:6px;font-size:11px;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:2px;"><span>Subtotal:</span><span x-text="formatCurrency(lastOrderSubtotal)"></span></div>
                    <template x-if="lastOrderDiscount>0">
                        <div style="display:flex;justify-content:space-between;margin-bottom:2px;"><span x-text="'Discount ('+(lastOrderDiscountPercent||0)+'%):'"></span><span x-text="'-' + formatCurrency(lastOrderDiscount)"></span></div>
                    </template>
                    <div style="display:flex;justify-content:space-between;margin-bottom:2px;"><span x-text="'Tax (' + taxRate + '%):'"></span><span x-text="formatCurrency(lastOrderTax)"></span></div>
                    <div style="display:flex;justify-content:space-between;font-weight:bold;font-size:14px;margin-top:6px;border-top:1px solid #000;padding-top:6px;">
                        <span>TOTAL AMOUNT:</span><span x-text="formatCurrency(lastOrderTotal)"></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:11px;font-weight:bold;margin-top:4px;color:#059669;">
                        <span>PAYMENT STATUS:</span><span>PAID</span>
                    </div>
                </div>
                <div style="text-align:center;margin-top:16px;font-size:10px;border-top:1px dashed #000;padding-top:10px;">
                    <p style="margin:0;font-weight:bold;">THANK YOU FOR YOUR VISIT!</p>
                    <p style="margin:4px 0 0;">Please keep this receipt for your records.</p>
                </div>
            </div>
        </div>
        <div class="p-4 bg-gray-50 border-t border-gray-100 flex gap-2">
            <button @click="printBill()" class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl bg-blue-50 text-blue-600 font-bold text-xs hover:bg-blue-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print
            </button>
            <button @click="shareOnWhatsApp()" class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl bg-emerald-50 text-emerald-600 font-bold text-xs hover:bg-emerald-100 transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.272-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.67-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.076 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg>
                WhatsApp
            </button>
            <button @click="startNewOrder()" class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl bg-gray-200 text-gray-700 font-bold text-xs hover:bg-gray-300 transition-colors">
                Done
            </button>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════
     MAIN 3-ZONE TABLET POS INTERFACE
════════════════════════════════════════════════════════════ --}}
<div id="pos-main" class="flex h-screen w-full overflow-hidden {{ !$openSession ? 'pointer-events-none opacity-40 blur-sm' : '' }}">

    {{-- ============================================================
         ZONE 1: COMPACT SERVICE & CATEGORIES SIDEBAR (210px)
    ============================================================ --}}
    <aside class="flex flex-col flex-shrink-0 bg-white border-r border-gray-200/80 shadow-panel-left z-20" style="width: 210px;">

        {{-- Brand & Header --}}
        <div class="flex items-center gap-3 px-4 py-3.5 border-b border-gray-100 flex-shrink-0">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 bg-gradient-to-br from-brand-500 to-brand-600 shadow-md shadow-brand-500/25">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <div class="text-xs font-black text-gray-900 tracking-tight truncate">{{ config('app.name') }}</div>
                <div class="text-[9px] font-bold text-brand-600 uppercase tracking-wider">Restaurant POS</div>
            </div>
        </div>

        {{-- Cashier / Shift Status Card --}}
        <div class="px-3.5 py-2.5 border-b border-gray-100 bg-gray-50/60 flex-shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl flex-shrink-0 flex items-center justify-center text-xs font-bold text-white uppercase bg-gradient-to-br from-brand-500 to-brand-600 shadow-sm">
                    {{ substr(auth()->user()->name ?? 'A', 0, 2) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs font-bold text-gray-800 truncate leading-tight">{{ auth()->user()->name ?? 'Admin' }}</div>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                        <span class="text-[9px] font-medium text-gray-400 capitalize">{{ auth()->user()->roles->first()->name ?? 'Cashier' }}</span>
                    </div>
                </div>
            </div>
            @if($openSession)
            <button @click="showCloseRegister=true"
                class="w-full mt-2 py-1.5 rounded-lg text-[9px] font-bold uppercase tracking-wider text-center text-red-600 bg-red-50 hover:bg-red-100 border border-red-200/60 transition-colors">
                × Close Register
            </button>
            @endif
        </div>

        {{-- Service Modes Switcher (2x2 Grid) --}}
        <div class="px-3 pt-3 pb-2.5 border-b border-gray-100 flex-shrink-0">
            <div class="text-[9px] font-extrabold text-gray-400 uppercase tracking-wider mb-2 px-1">Service Mode</div>
            <div class="grid grid-cols-2 gap-1.5">
                <button @click="serviceType='retail';loadedOrderId=null;loadedTableName=''"
                    :class="serviceType==='retail' ? 'active' : ''"
                    class="service-mode-btn">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7H6a2 2 0 00-2 2v9a2 2 0 002 2h9a2 2 0 002-2v-3M9 7h8m-8 0V5a2 2 0 012-2h2a2 2 0 012 2v2m-6 0h6"/></svg>
                    Counter
                </button>
                @php $activeCompany = \App\Models\Company::find(session('company_id')); @endphp
                @if($activeCompany && $activeCompany->isModuleEnabled('restaurant_mode'))
                <button @click="serviceType='dine_in';fetchActiveTables();"
                    :class="serviceType==='dine_in' ? 'active' : ''"
                    class="service-mode-btn">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    Dine-In
                </button>
                <button @click="serviceType='takeaway';loadedOrderId=null;loadedTableName=''"
                    :class="serviceType==='takeaway' ? 'active' : ''"
                    class="service-mode-btn">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    Takeaway
                </button>
                <button @click="serviceType='delivery';loadedOrderId=null;loadedTableName=''"
                    :class="serviceType==='delivery' ? 'active' : ''"
                    class="service-mode-btn">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Delivery
                </button>
                @endif
            </div>

            {{-- Dine-In Table Picker Pill --}}
            <template x-if="serviceType==='dine_in'">
                <button @click="fetchActiveTables()"
                    class="w-full flex items-center gap-2 mt-2 px-3 py-2 rounded-xl text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200 hover:bg-brand-100 transition-all text-left">
                    <span class="text-sm">🪑</span>
                    <span class="flex-1 truncate" x-text="loadedTableName ? 'Table: '+loadedTableName : 'Select Table...'"></span>
                    <svg class="w-3.5 h-3.5 text-brand-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </button>
            </template>
        </div>

        {{-- Categories Sidebar --}}
        <nav class="flex-1 overflow-y-auto thin-scroll px-3 py-3 space-y-1">
            {{-- Quick Access Section --}}
            <div class="mb-3 border-b border-gray-100 pb-2">
                <div class="text-[9px] font-extrabold text-gray-400 uppercase tracking-wider mb-2 px-1">Quick Access</div>
                <div class="space-y-1">
                    <button @click="selectQuickAccess('favorites')"
                        :class="activeQuickAccess==='favorites' ? 'active' : ''"
                        class="sidebar-cat-btn">
                        <span class="cat-icon-box">
                            <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                            </svg>
                        </span>
                        <span class="flex-1 truncate">Favorites</span>
                        <span class="cat-badge text-[10px] font-bold px-1.5 py-0.5 rounded-md"
                            :class="activeQuickAccess==='favorites' ? 'bg-white/30 text-white' : 'bg-orange-100 text-orange-700 font-extrabold'">
                            <span x-text="favoritesCount"></span>
                        </span>
                    </button>

                    <button @click="selectQuickAccess('active_orders')"
                        :class="activeQuickAccess==='active_orders' ? 'active' : ''"
                        class="sidebar-cat-btn">
                        <span class="cat-icon-box">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <span class="flex-1 truncate">Active Orders</span>
                        <span class="cat-badge text-[10px] font-bold px-1.5 py-0.5 rounded-md"
                            :class="activeQuickAccess==='active_orders' ? 'bg-white/30 text-white' : 'bg-red-100 text-red-700 font-extrabold'">
                            <span x-text="activeOrdersList.length"></span>
                        </span>
                    </button>
                </div>
            </div>

            <div class="text-[9px] font-extrabold text-gray-400 uppercase tracking-wider mb-2 px-1">Menu Categories</div>

            <button @click="filterCategory('all')" :class="activeCategory==='all' ? 'active' : ''" class="sidebar-cat-btn">
                <span class="cat-icon-box">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                </span>
                <span class="flex-1 truncate">All Items</span>
                <span class="cat-badge text-[10px] font-bold px-1.5 py-0.5 rounded-md"
                    :class="activeCategory==='all' ? 'bg-white/30 text-white' : 'bg-gray-100 text-gray-400'">
                    {{ $products->count() }}
                </span>
            </button>

            @foreach($categories as $category)
            <button @click="filterCategory('{{ $category->id }}')" :class="activeCategory==='{{ $category->id }}' ? 'active' : ''" class="sidebar-cat-btn">
                <span class="cat-icon-box">{{ $category->icon ?? '🍽️' }}</span>
                <span class="flex-1 truncate">{{ $category->name }}</span>
                <span class="cat-badge text-[10px] font-bold px-1.5 py-0.5 rounded-md"
                    :class="activeCategory==='{{ $category->id }}' ? 'bg-white/30 text-white' : 'bg-gray-100 text-gray-400'">
                    {{ $category->products->count() }}
                </span>
            </button>
            @endforeach
        </nav>

        {{-- Sidebar Footer --}}
        <div class="px-3.5 py-3 border-t border-gray-100 bg-gray-50/70 flex-shrink-0">
            <div class="text-center mb-2">
                <p class="text-[10px] font-semibold text-gray-500">{{ now()->format('D, d M Y') }}</p>
                <p class="text-xs font-mono font-bold text-gray-700 tabular mt-0.5" x-text="currentTime"></p>
            </div>
            <a href="{{ route('orders.index') }}" class="w-full flex items-center justify-center gap-1.5 py-2 rounded-xl text-[10px] font-bold uppercase tracking-wider text-gray-600 bg-white hover:bg-gray-100 border border-gray-200 shadow-sm transition-colors">
                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Order History
            </a>
        </div>
    </aside>

    {{-- ============================================================
         ZONE 2: PRODUCT BROWSING & MENU AREA (Flex-1)
    ============================================================ --}}
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden bg-[#F6F7F9]">

        {{-- Top Sub-Header Bar --}}
        <div class="flex-shrink-0 px-5 py-3.5 bg-white border-b border-gray-200/80 shadow-sm flex items-center gap-3.5 z-10">

            {{-- Category / Count Indicator --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                <span class="text-xs font-bold text-gray-900 truncate max-w-[140px]" x-text="activeCategoryName"></span>
                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-brand-50 text-brand-600 border border-brand-100" x-text="filteredProducts.length + ' items'"></span>
            </div>

            {{-- Search Bar --}}
            <div class="relative flex-1 max-w-md">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" x-model="searchQuery" @input="filterProducts"
                    placeholder="Search menu items by name or SKU..."
                    class="w-full pl-10 pr-9 py-2 rounded-xl text-xs font-semibold text-gray-800 bg-gray-100/80 border border-transparent focus:border-brand-500 focus:bg-white outline-none transition-all">
                <button x-show="searchQuery" @click="searchQuery='';filterProducts()"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Grid & Density Controls --}}
            <div class="flex items-center gap-2 flex-shrink-0 ml-auto">
                {{-- View Toggle --}}
                <div class="flex items-center bg-gray-100 p-1 rounded-xl gap-1">
                    <button @click="gridView=true"
                        class="p-1.5 rounded-lg transition-all"
                        :class="gridView ? 'bg-white text-brand-600 shadow-sm' : 'text-gray-400 hover:text-gray-700'"
                        title="Grid View">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    </button>
                    <button @click="gridView=false"
                        class="p-1.5 rounded-lg transition-all"
                        :class="!gridView ? 'bg-white text-brand-600 shadow-sm' : 'text-gray-400 hover:text-gray-700'"
                        title="List View">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    </button>
                </div>

                {{-- Column Count Selector (Grid Only) --}}
                <div x-show="gridView" class="hidden sm:flex items-center bg-gray-100 p-1 rounded-xl gap-1">
                    <span class="text-[9px] font-extrabold text-gray-400 px-1 uppercase">Cols</span>
                    <template x-for="c in [3, 4, 5]" :key="c">
                        <button @click="gridCols=c"
                            class="w-6 h-6 rounded-lg text-xs font-bold flex items-center justify-center transition-all"
                            :class="gridCols===c ? 'bg-brand-500 text-white shadow-sm' : 'text-gray-500 hover:text-gray-900'"
                            x-text="c"></button>
                    </template>
                </div>

                {{-- Reset Button --}}
                <button @click="resetFilters()"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-red-200 text-red-600 bg-red-50/60 hover:bg-red-100 font-extrabold text-xs transition-all shadow-sm active:scale-95 ml-1"
                    title="Reset All Filters">
                    <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>RESET</span>
                </button>
            </div>
        </div>

        {{-- Products Area --}}
        <div class="flex-1 overflow-y-auto thin-scroll p-5">

            {{-- Loading State --}}
            <div x-show="isLoading" class="flex flex-col items-center justify-center h-full gap-3 text-gray-400">
                <div class="relative w-12 h-12">
                    <div class="absolute inset-0 rounded-full border-4 border-gray-200"></div>
                    <div class="spin-loader absolute inset-0 rounded-full border-4 border-transparent border-top-brand-500" style="border-top-color: #F5703E;"></div>
                </div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Loading Menu...</p>
            </div>

            {{-- Empty Products State --}}
            <div x-show="!isLoading && filteredProducts.length===0"
                 class="flex flex-col items-center justify-center h-full gap-3 py-16 text-gray-400">
                <div class="w-16 h-16 rounded-3xl bg-white shadow-sm flex items-center justify-center text-3xl border border-gray-200">🔍</div>
                <p class="text-sm font-bold text-gray-700">No matching items</p>
                <p class="text-xs text-gray-400">Try adjusting your search query or category filter</p>
            </div>

            {{-- ── GRID VIEW (Responsive Columns) ── --}}
            <div x-show="!isLoading && filteredProducts.length>0 && gridView"
                 :class="{
                    'grid-cols-2 sm:grid-cols-3 xl:grid-cols-3': gridCols===3,
                    'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-4': gridCols===4,
                    'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5': gridCols===5
                 }"
                 class="grid gap-4">
                <template x-for="product in filteredProducts" :key="product.id">
                    <div @click="addToCart(product)"
                        :class="isInCart(product.id) ? 'restaurant-card pcard in-cart' : 'restaurant-card pcard'">

                        {{-- Food Image Area --}}
                        <div class="card-img-wrap">
                            {{-- Favorite Marking Heart Icon --}}
                            <button type="button" @click.stop="toggleFavorite(product.id)"
                                class="absolute top-2.5 right-2.5 z-10 w-7 h-7 rounded-full bg-white/90 backdrop-blur-sm flex items-center justify-center text-gray-400 hover:text-red-500 shadow-sm transition-all hover:scale-110"
                                :class="isFavorite(product.id) ? 'text-red-500 bg-white shadow-red-500/20' : ''"
                                :title="isFavorite(product.id) ? 'Remove from Favorites' : 'Add to Favorites'">
                                <svg class="w-4 h-4" :fill="isFavorite(product.id) ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </button>

                            <img x-show="product.image" :src="getImageUrl(product.image)" :alt="product.name" loading="lazy" onerror="this.style.display='none';" />
                            <div x-show="!product.image" class="w-full h-full flex flex-col items-center justify-center text-gray-300 bg-gray-100">
                                <svg class="w-10 h-10 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">No Image</span>
                            </div>

                            {{-- Active In-Cart Quantity Pill --}}
                            <template x-if="isInCart(product.id)">
                                <div class="cart-qty-pill tabular" x-text="cart[String(product.id)].qty"></div>
                            </template>

                            {{-- Hover Overlay "+ Add" --}}
                            <div class="card-quick-add">
                                <div class="card-quick-add-btn">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                                </div>
                            </div>
                        </div>

                        {{-- Card Details --}}
                        <div class="p-3.5 flex flex-col flex-1 justify-between bg-white">
                            <div>
                                <h3 class="text-xs font-bold text-gray-900 leading-snug line-clamp-2 mb-0.5" x-text="product.name"></h3>
                                <p class="text-[10px] font-semibold text-gray-400 truncate mb-2" x-text="product.sku ? 'SKU: ' + product.sku : 'Item #' + product.id"></p>
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                <span class="text-sm font-black price text-brand-500 tabular" x-text="formatCurrency(product.price)"></span>

                                <button type="button" class="w-7 h-7 rounded-full flex items-center justify-center transition-all"
                                     :class="isInCart(product.id) ? 'bg-emerald-500 text-white shadow-sm' : 'bg-brand-50 text-brand-600 hover:bg-brand-500 hover:text-white'">
                                    <svg x-show="!isInCart(product.id)" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    <svg x-show="isInCart(product.id)" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- ── LIST VIEW ── --}}
            <div x-show="!isLoading && filteredProducts.length>0 && !gridView" class="space-y-3">
                <template x-for="product in filteredProducts" :key="product.id">
                    <div @click="addToCart(product)"
                        :class="isInCart(product.id) ? 'restaurant-list-card pcard in-cart' : 'restaurant-list-card pcard'">

                        {{-- Food Thumbnail (56x56) --}}
                        <div class="w-14 h-14 rounded-xl overflow-hidden bg-gray-50 flex-shrink-0 relative border border-gray-100 flex items-center justify-center p-1">
                            <img x-show="product.image" :src="getImageUrl(product.image)" :alt="product.name" class="w-full h-full object-contain" onerror="this.style.display='none';" />
                            <div x-show="!product.image" class="w-full h-full flex flex-col items-center justify-center text-gray-300 bg-gray-100">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <template x-if="isInCart(product.id)">
                                <div class="cart-qty-pill text-[10px] top-0.5 right-0.5" style="min-width: 18px; height: 18px; padding: 0 4px;" x-text="cart[String(product.id)].qty"></div>
                            </template>
                        </div>

                        {{-- Details --}}
                        <div class="flex-1 min-w-0 pr-2">
                            <div class="flex items-center gap-2 mb-0.5">
                                <h3 class="text-sm font-bold text-gray-900 truncate" x-text="product.name"></h3>
                                <template x-if="isInCart(product.id)">
                                    <span class="text-[9px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full bg-brand-50 text-brand-600 border border-brand-200 flex-shrink-0">
                                        In Order · <span x-text="cart[String(product.id)].qty"></span>
                                    </span>
                                </template>
                            </div>
                            <p class="text-xs text-gray-400 font-semibold truncate" x-text="product.sku ? 'SKU: ' + product.sku : 'Item #' + product.id"></p>
                        </div>

                        {{-- Price & Action --}}
                        <div class="flex items-center gap-4 flex-shrink-0">
                            <div class="text-right">
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block">Price</span>
                                <span class="text-sm sm:text-base font-black price text-brand-500 tabular" x-text="formatCurrency(product.price)"></span>
                            </div>
                            <button type="button" class="w-9 h-9 rounded-xl flex items-center justify-center transition-all shadow-sm"
                                :class="isInCart(product.id) ? 'bg-emerald-500 text-white shadow-emerald-500/20' : 'bg-brand-50 text-brand-600 hover:bg-brand-500 hover:text-white border border-brand-200 hover:border-brand-500'">
                                <svg x-show="!isInCart(product.id)" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <svg x-show="isInCart(product.id)" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        </div>

                    </div>
                </template>
            </div>
        </div>

        {{-- ── LIVE ACTIVE ORDERS BOTTOM PANEL ── --}}
        <div id="active-orders-panel" x-show="showActiveOrdersPanel" x-cloak
             class="border-t-2 border-red-400 bg-white p-3.5 shadow-lg flex-shrink-0 z-10 transition-all">
            <div class="flex items-center justify-between mb-2.5">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-ping"></span>
                    <h3 class="text-xs font-black text-gray-900 tracking-tight flex items-center gap-2 uppercase">
                        LIVE ACTIVE ORDERS
                        <span class="text-[10px] font-black px-2 py-0.5 rounded-full bg-red-500 text-white tabular" x-text="activeOrdersList.length"></span>
                    </h3>
                    <span class="text-[10px] font-semibold text-gray-400 border-l border-gray-200 pl-2.5 hidden sm:inline">Sorted by duration (High to Low)</span>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('orders.index') }}" class="text-xs font-extrabold text-brand-600 hover:text-brand-700 flex items-center gap-1">
                        View All Orders
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <button @click="selectQuickAccess('active_orders')" type="button"
                        class="w-6 h-6 rounded-lg bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 hover:text-gray-800 transition-all text-xs font-bold"
                        title="Close Active Orders Panel">
                        ×
                    </button>
                </div>
            </div>

            {{-- Active Orders Carousel / Cards --}}
            <div class="flex items-center gap-2.5 overflow-x-auto thin-scroll pb-1">
                <template x-for="ord in activeOrdersList" :key="ord.id">
                    <div @click="loadActiveOrder(ord)"
                         class="flex-shrink-0 w-40 bg-white border-2 border-gray-100 hover:border-brand-500 rounded-2xl p-2.5 shadow-sm hover:shadow-md transition-all cursor-pointer group">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-black text-gray-900 truncate group-hover:text-brand-600" x-text="ord.order_number"></span>
                            <span class="text-[8px] font-extrabold px-1.5 py-0.5 rounded-md"
                                :class="{
                                    'bg-purple-100 text-purple-700': ord.service_type==='dine_in',
                                    'bg-blue-100 text-blue-700': ord.service_type==='retail'||ord.service_type==='counter',
                                    'bg-emerald-100 text-emerald-700': ord.service_type==='takeaway',
                                    'bg-amber-100 text-amber-700': ord.service_type==='delivery'
                                }"
                                x-text="ord.service_type_label"></span>
                        </div>
                        <div class="flex items-center justify-between text-[9px] text-gray-400 mb-1.5">
                            <div class="flex items-center gap-1">
                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span x-text="ord.time"></span>
                            </div>
                            <span class="text-[8px] font-black px-1.5 py-0.5 rounded"
                                :class="ord.payment_status==='paid' ? 'bg-emerald-100 text-emerald-700' : (ord.payment_status==='partial' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700')"
                                x-text="ord.payment_status_label"></span>
                        </div>
                        <div class="text-lg font-black text-gray-900 leading-tight mb-2 tabular" x-text="ord.duration"></div>

                        <div class="flex items-center justify-between pt-1 border-t border-gray-100">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full"
                                    :class="{
                                        'bg-red-500': ord.status==='preparing',
                                        'bg-amber-500': ord.status==='pending',
                                        'bg-emerald-500': ord.status==='ready'
                                    }"></span>
                                <span class="text-[9px] font-bold text-gray-700 capitalize" x-text="ord.status_label"></span>
                            </div>
                            <div class="flex items-center gap-1">
                                <button @click.stop="printOrderKOT(ord)" type="button" class="p-1 text-indigo-600 hover:bg-indigo-50 rounded" title="Print KOT Slip">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </button>
                                <span class="text-[9px] font-extrabold text-brand-600 group-hover:underline">Load &rarr;</span>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="activeOrdersList.length===0">
                    <div class="w-full py-3 text-center text-xs font-semibold text-gray-400">
                        No active orders right now
                    </div>
                </template>
            </div>

            {{-- Footer Auto Refresh Info --}}
            <div class="flex items-center justify-center gap-1.5 mt-2 text-[9px] font-semibold text-gray-400">
                <svg class="w-3 h-3 text-emerald-500 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Auto refresh every 5 seconds</span>
            </div>
        </div>
    </main>

    {{-- ============================================================
         ZONE 3: RIGHT PERSISTENT ORDER / CART PANEL (360px)
    ============================================================ --}}
    <aside class="flex flex-col flex-shrink-0 bg-white border-l border-gray-200/80 shadow-panel-right z-20" style="width: 360px;">

        {{-- Order Header --}}
        <div class="flex-shrink-0 px-5 py-4 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h2 class="text-sm font-black text-gray-900 uppercase tracking-tight">Order</h2>
                    <span class="text-[9px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full"
                        :class="serviceType==='dine_in' ? 'bg-amber-100 text-amber-800' : (serviceType==='takeaway' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600')"
                        x-text="serviceType.replace('_',' ')">
                    </span>
                    <template x-if="loadedOrderId">
                        <span class="text-[9px] font-extrabold px-2 py-0.5 rounded-full bg-brand-50 text-brand-700 border border-brand-200" x-text="'🪑 '+loadedTableName"></span>
                    </template>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-gray-400" x-text="totalQty + ' item' + (totalQty!==1?'s':'')"></span>
                    <button x-show="cartItems.length>0" @click="clearCart()"
                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors"
                        title="Clear Order">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Cart Items List --}}
        <div class="flex-1 overflow-y-auto thin-scroll px-4 py-3 space-y-2.5">

            {{-- Empty Cart Illustration --}}
            <template x-if="cartItems.length===0">
                <div class="flex flex-col items-center justify-center h-full py-16 text-center text-gray-400">
                    <div class="w-16 h-16 rounded-3xl bg-gray-50 flex items-center justify-center text-3xl mb-3 border border-gray-100">🛒</div>
                    <p class="text-xs font-bold text-gray-700">Order is empty</p>
                    <p class="text-[10px] text-gray-400 mt-1 max-w-[180px]">Select items from the menu to start building your order</p>
                </div>
            </template>

            {{-- Cart Item Rows --}}
            <template x-for="item in cartItems" :key="item.id">
                <div class="cart-item-row">
                    <div class="flex items-start gap-2.5">
                        {{-- Thumbnail --}}
                        <div class="w-10 h-10 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0 flex items-center justify-center">
                            <img x-show="item.image" :src="'storage/'+item.image" :alt="item.name" class="w-full h-full object-cover"/>
                            <svg x-show="!item.image" class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <h4 class="text-xs font-bold text-gray-900 truncate leading-snug" x-text="item.name"></h4>
                            <p class="text-[10px] font-semibold text-gray-400 tabular" x-text="formatCurrency(item.price)+' each'"></p>
                        </div>

                        {{-- Line Total --}}
                        <div class="text-right flex-shrink-0">
                            <span class="text-xs font-black price text-gray-900 tabular" x-text="formatCurrency(item.price*item.qty)"></span>
                        </div>
                    </div>

                    {{-- Stepper Controls --}}
                    <div class="flex items-center justify-between mt-2.5 pt-2 border-t border-gray-100">
                        <div class="flex items-center bg-gray-50 rounded-xl p-1 gap-1 border border-gray-100">
                            <button @click="updateQty(item.id, 'decrement')" class="touch-stepper-btn" title="Decrease">−</button>
                            <span class="w-7 text-center text-xs font-black text-gray-900 tabular" x-text="item.qty"></span>
                            <button @click="updateQty(item.id, 'increment')" class="touch-stepper-btn" title="Increase">+</button>
                        </div>

                        <button @click="removeFromCart(item.id)" class="text-[10px] font-bold text-gray-400 hover:text-red-500 px-2 py-1 rounded-lg transition-colors">
                            Remove
                        </button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Discount & Coupon Accordion --}}
        <div class="flex-shrink-0 px-4 py-2 border-t border-gray-100 bg-gray-50/50">
            <button @click="showDiscount=!showDiscount"
                class="w-full flex items-center justify-between py-2 px-3 rounded-xl transition-all"
                :class="showDiscount ? 'bg-brand-50 border border-brand-100' : 'bg-white border border-gray-200 hover:bg-gray-50'">
                <div class="flex items-center gap-2">
                    <svg class="w-3.5 h-3.5" :class="showDiscount ? 'text-brand-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    <span class="text-xs font-bold" :class="showDiscount ? 'text-brand-600' : 'text-gray-700'">Discount & Coupons</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-black text-red-500 price tabular" x-show="discountAmount>0" x-text="'-' + formatCurrency(discountAmount)"></span>
                    <svg class="w-3.5 h-3.5 text-gray-400 transition-transform" :class="showDiscount ? 'rotate-180 text-brand-500' : ''" fill="currentColor" viewBox="0 0 24 24"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                </div>
            </button>

            <div x-show="showDiscount" x-collapse class="mt-2.5 p-3 bg-white rounded-2xl border border-gray-200 space-y-2.5 shadow-sm">
                {{-- Type Switcher --}}
                <div class="flex bg-gray-100 p-1 rounded-xl gap-1">
                    <button @click="discountType='percent'" class="flex-1 py-1 text-[10px] font-extrabold uppercase rounded-lg transition-all" :class="discountType==='percent' ? 'bg-brand-500 text-white shadow-sm' : 'text-gray-500'">% Percentage</button>
                    <button @click="discountType='fixed'" class="flex-1 py-1 text-[10px] font-extrabold uppercase rounded-lg transition-all" :class="discountType==='fixed' ? 'bg-brand-500 text-white shadow-sm' : 'text-gray-500'"><span x-text="currency.symbol"></span> Fixed Amount</button>
                </div>

                {{-- Amount Input --}}
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-xs" x-text="discountType==='percent' ? '%' : currency.symbol"></span>
                    <input type="number" x-model="discountValue" :placeholder="discountType==='percent' ? '0' : '0.00'"
                        class="w-full pl-7 pr-8 py-2 rounded-xl text-xs font-bold text-gray-900 bg-gray-50 border border-gray-200 focus:border-brand-500 outline-none tabular">
                    <button x-show="discountValue>0" @click="discountValue=0" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Coupon Code --}}
                <div class="pt-2 border-t border-gray-100 flex gap-2">
                    <input type="text" x-model="couponCode" placeholder="COUPON CODE"
                        class="flex-1 px-3 py-1.5 rounded-xl text-[11px] font-mono font-bold uppercase text-gray-900 bg-gray-50 border border-gray-200 focus:border-brand-500 outline-none">
                    <button @click="applyCoupon()" class="px-3 py-1.5 bg-gray-900 text-white rounded-xl text-[10px] font-bold uppercase hover:bg-black transition-colors">
                        Apply
                    </button>
                </div>

                <template x-if="appliedCoupon">
                    <div class="flex items-center justify-between px-2.5 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 text-[10px] font-bold border border-emerald-200">
                        <span x-text="'✓ ' + appliedCoupon.code + ' Applied'"></span>
                        <button @click="appliedCoupon=null;couponCode=''" class="text-emerald-800 hover:underline">Remove</button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Order Summary & Totals --}}
        <div class="flex-shrink-0 px-5 py-3.5 border-t border-gray-100 bg-gray-50/80">
            <div class="space-y-1.5 text-xs font-semibold">
                <div class="flex justify-between text-gray-500">
                    <span>Subtotal</span>
                    <span class="text-gray-900 price tabular" x-text="formatCurrency(cartSubtotal)"></span>
                </div>
                <template x-if="discountAmount>0">
                    <div class="flex justify-between text-red-500">
                        <span>Discount</span>
                        <span class="price tabular" x-text="'-' + formatCurrency(discountAmount)"></span>
                    </div>
                </template>
                <div class="flex justify-between text-gray-500">
                    <span x-text="'Tax (' + taxRate + '%)'"></span>
                    <span class="text-gray-900 price tabular" x-text="formatCurrency(taxAmount)"></span>
                </div>
                <template x-if="cardServiceCharge>0">
                    <div class="flex justify-between text-gray-500">
                        <span>Card Service Charge</span>
                        <span class="text-gray-900 price tabular" x-text="'+' + formatCurrency(cardServiceCharge)"></span>
                    </div>
                </template>
            </div>

            {{-- Grand Total Display --}}
            <div class="flex items-center justify-between mt-3 pt-3 border-t-2 border-gray-200">
                <span class="text-sm font-black text-gray-900 uppercase tracking-tight">Total</span>
                <span class="text-3xl font-black price text-brand-500 tabular" x-text="formatCurrency(grandTotal)"></span>
            </div>
        </div>

        {{-- Primary Action / Checkout --}}
        <div class="flex-shrink-0 px-4 pb-4 pt-2">
            <button id="checkout-cart-btn" @click="checkout()" :disabled="cartItems.length===0"
                class="btn-checkout-primary checkout-btn w-full py-4 text-sm flex items-center justify-center gap-2.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span x-show="cartItems.length===0">Add items to order</span>
                <span x-show="cartItems.length>0" x-text="'Charge ' + formatCurrency(grandTotal) + ' →'"></span>
            </button>
        </div>
    </aside>

</div>{{-- /pos-main --}}

{{-- ════════════════════════════════════════════════════════════
     CHECKOUT / BILLING MODAL
════════════════════════════════════════════════════════════ --}}
<div x-show="showBillingModal" x-cloak
     class="fixed inset-0 z-[120] flex items-center justify-center bg-black/60 backdrop-blur-md"
     x-transition>
    <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full mx-4 flex flex-col overflow-hidden border border-gray-100"
         style="max-height: 92vh;">

        {{-- Header --}}
        <div class="px-6 py-4.5 border-b border-gray-100 flex items-center justify-between flex-shrink-0 bg-white">
            <div>
                <h1 class="text-lg font-black text-gray-900">Order Checkout</h1>
                <p class="text-xs text-gray-400 mt-0.5">Select payment method and confirm customer receipt</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <span class="text-[10px] font-bold text-gray-400 uppercase">Grand Total</span>
                    <p class="text-2xl font-black price text-brand-500 tabular" x-text="formatCurrency(grandTotal)"></p>
                </div>
                <button @click="showBillingModal=false" class="w-8 h-8 rounded-xl bg-gray-100 flex items-center justify-center text-gray-400 hover:bg-gray-200 hover:text-gray-700 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Content --}}
        <div class="flex-1 overflow-y-auto thin-scroll p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Left: Order Summary Review --}}
                <div class="space-y-4">
                    <div>
                        <h2 class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400 mb-2">Order Items</h2>
                        <div class="rounded-2xl p-3 bg-gray-50 border border-gray-100 max-h-52 overflow-y-auto thin-scroll space-y-2">
                            <template x-for="item in cartItems" :key="item.id">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-gray-900 truncate" x-text="item.name"></p>
                                        <p class="text-[10px] text-gray-400 tabular" x-text="'Qty: ' + item.qty + ' × ' + formatCurrency(item.price)"></p>
                                    </div>
                                    <span class="text-xs font-black price text-gray-900 tabular" x-text="formatCurrency(item.price*item.qty)"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-2xl border border-gray-100 space-y-1.5 text-xs font-semibold">
                        <div class="flex justify-between text-gray-500"><span>Subtotal</span><span class="text-gray-900 price tabular" x-text="formatCurrency(cartSubtotal)"></span></div>
                        <template x-if="discountAmount>0">
                            <div class="flex justify-between text-red-500"><span>Discount</span><span class="price tabular" x-text="'-' + formatCurrency(discountAmount)"></span></div>
                        </template>
                        <div class="flex justify-between text-gray-500"><span x-text="'Tax (' + taxRate + '%)'"></span><span class="text-gray-900 price tabular" x-text="formatCurrency(taxAmount)"></span></div>
                        <template x-if="cardServiceCharge>0">
                            <div class="flex justify-between text-gray-500"><span>Card Service Charge</span><span class="text-gray-900 price tabular" x-text="'+' + formatCurrency(cardServiceCharge)"></span></div>
                        </template>
                        <div class="flex justify-between pt-2 mt-1 border-t border-gray-200 text-sm font-black">
                            <span class="text-gray-900">Total Due</span>
                            <span class="text-brand-500 price tabular" x-text="formatCurrency(grandTotal)"></span>
                        </div>
                    </div>
                </div>

                {{-- Right: Customer & Payment Details --}}
                <div class="space-y-4">
                    {{-- Customer Info --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h2 class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Customer Details</h2>
                            <button type="button" @click="customer.name='Walk-in Customer'; customer.phone='0000000000'; matchingCustomers=[]; showCustomerDropdown=false;"
                                class="text-[10px] font-bold text-brand-600 hover:text-brand-700 bg-brand-50 hover:bg-brand-100 px-2 py-0.5 rounded-md transition-colors">
                                ⚡ Walk-in Quick Fill
                            </button>
                        </div>
                        <div class="space-y-2 relative">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Customer Name (Optional)</label>
                                <input type="text" x-model="customer.name" @input.debounce.300ms="searchCustomers($event.target.value)" @focus="if(customer.name && customer.name.length >= 2) searchCustomers(customer.name)"
                                    class="w-full px-3 py-2 rounded-xl text-xs font-semibold text-gray-900 bg-gray-50 border border-gray-200 focus:border-brand-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Phone Number (Optional)</label>
                                <div class="relative">
                                    <input type="text" x-model="customer.phone" @input.debounce.300ms="searchCustomers($event.target.value)" @focus="if(customer.phone && customer.phone.length >= 2) searchCustomers(customer.phone)"
                                        class="w-full pl-3 pr-8 py-2 rounded-xl text-xs font-semibold text-gray-900 bg-gray-50 border border-gray-200 focus:border-brand-500 outline-none">
                                    <div class="absolute right-2.5 top-1/2 -translate-y-1/2" x-show="isCustomerLoading">
                                        <div class="spin-loader w-3.5 h-3.5 rounded-full border-2 border-gray-200 border-t-brand-500"></div>
                                    </div>
                                </div>
                            </div>

                            {{-- Autocomplete Dropdown --}}
                            <div x-show="showCustomerDropdown && matchingCustomers.length > 0" @click.outside="showCustomerDropdown = false"
                                class="absolute left-0 right-0 top-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden max-h-48 overflow-y-auto z-50">
                                <template x-for="c in matchingCustomers" :key="c.id">
                                    <button type="button" @click="selectCustomer(c)"
                                        class="w-full text-left px-3 py-2 text-xs hover:bg-brand-50 flex items-center justify-between border-b border-gray-100 last:border-0 transition-colors">
                                        <div>
                                            <span class="font-bold text-gray-800" x-text="c.name"></span>
                                            <span class="text-gray-400 ml-1.5" x-text="'(' + c.phone + ')'"></span>
                                        </div>
                                        <template x-if="c.wallet_balance > 0">
                                            <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded" x-text="formatCurrency(c.wallet_balance)"></span>
                                        </template>
                                    </button>
                                </template>
                            </div>

                            <div x-show="serviceType==='delivery'||customer.name">
                                <label class="block text-[10px] font-bold uppercase tracking-wider mb-1"
                                    :class="serviceType==='delivery' ? 'text-red-500' : 'text-gray-500'"
                                    x-text="serviceType==='delivery' ? 'Delivery Address (Required) *' : 'Address (Optional)'"></label>
                                <textarea x-model="customer.address" rows="2" placeholder="Street, building, suite..."
                                    class="w-full px-3 py-2 rounded-xl text-xs font-semibold text-gray-900 bg-gray-50 border border-gray-200 focus:border-brand-500 outline-none resize-none"></textarea>
                            </div>

                            {{-- Delivery Partner Selection --}}
                            <div x-show="serviceType==='delivery'" class="p-2.5 rounded-xl bg-blue-50/70 border border-blue-200 space-y-1.5">
                                <label class="block text-[10px] font-bold text-blue-900 uppercase tracking-wider">
                                    Delivery Partner
                                </label>
                                <select x-model="selectedDeliveryPartnerId" @change="recalcCash"
                                    class="w-full px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-900 bg-white border border-blue-200 focus:border-brand-500 outline-none">
                                    <option value="">-- Select Delivery Partner --</option>
                                    <template x-for="dp in deliveryPartners" :key="dp.id">
                                        <option :value="dp.id" x-text="dp.name + ' (' + dp.commission_percentage + '%)'"></option>
                                    </template>
                                </select>
                                <template x-if="selectedDeliveryPartner">
                                    <div class="flex justify-between items-center text-xs text-blue-800 pt-1 border-t border-blue-200/60">
                                        <span>Partner Commission (<span x-text="selectedDeliveryPartner.commission_percentage + '%'"></span>):</span>
                                        <span class="font-bold tabular" x-text="formatCurrency(deliveryCommissionAmount)"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Details --}}
                    <div>
                        <h2 class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400 mb-2">Payment Methods</h2>

                        {{-- Wallet Toggle --}}
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-gray-50 border border-gray-200 mb-2.5">
                            <div>
                                <p class="text-xs font-bold text-gray-800">Customer Wallet</p>
                                <p class="text-[10px] text-gray-400 tabular" x-text="'Balance: ' + formatCurrency(customer.wallet_balance||0)"></p>
                            </div>
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" x-model="useWallet" @change="recalcCash" class="w-4 h-4 rounded accent-brand-500">
                                <span class="text-xs font-bold text-gray-700">Use</span>
                            </label>
                        </div>

                        {{-- Split Payments List --}}
                        <div class="space-y-2">
                            <template x-for="(p, index) in splitPayments" :key="index">
                                <div class="p-2.5 rounded-xl bg-gray-50 border border-gray-200 space-y-2">
                                    <div class="flex gap-2">
                                        <div class="flex-1">
                                            <label class="text-[9px] font-bold text-gray-400 uppercase">Method</label>
                                            <select :value="p.method" @change="p.method=$event.target.value;p.card_id='';p.card_type_id='';p.offer_id='';p.offers=[];"
                                                class="w-full px-2 py-1.5 rounded-lg text-xs font-bold text-gray-800 bg-white border border-gray-200 outline-none">
                                                <template x-for="acc in paymentAccounts" :key="acc.id">
                                                    <option :value="acc.id" x-text="acc.account_name"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div class="flex-1">
                                            <label class="text-[9px] font-bold text-gray-400 uppercase">Amount (<span x-text="currency.symbol"></span>)</label>
                                            <input type="number" x-model="p.amount"
                                                @input="if(cards.some(c=>String(c.settlement_account_id)===String(p.method)))resolveOffersForSplitCard(index,p.card_id,p.amount)"
                                                class="w-full px-2 py-1.5 rounded-lg text-xs font-bold font-mono text-gray-900 bg-white border border-gray-200 outline-none tabular">
                                        </div>
                                        <button @click="removeSplit(index)" class="mt-auto w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors bg-white border border-gray-200">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>

                                    {{-- Card Selector & Card Type Selector if Card Account or Card Methods --}}
                                    <template x-if="cards.some(c=>String(c.settlement_account_id)===String(p.method)) || cardAccountIds.map(String).includes(String(p.method)) || paymentAccounts.some(acc=>String(acc.id)===String(p.method) && (acc.is_card_account || (acc.account_name && acc.account_name.toLowerCase().includes('card'))))">
                                        <div class="space-y-2 p-2 rounded-lg bg-white border border-gray-100">
                                            <template x-if="cards.some(c=>String(c.settlement_account_id)===String(p.method))">
                                                <div>
                                                    <label class="block text-[9px] font-bold text-gray-400 uppercase mb-0.5">Select Terminal Card</label>
                                                    <select x-model="p.card_id" @change="resolveOffersForSplitCard(index,p.card_id,p.amount)"
                                                        class="w-full px-2 py-1 rounded text-xs font-semibold text-gray-800 bg-gray-50 border border-gray-200 outline-none">
                                                        <option value="">Choose Card...</option>
                                                        <template x-for="card in cards.filter(c=>String(c.settlement_account_id)===String(p.method))" :key="card.id">
                                                            <option :value="card.id" x-text="card.bank_name+' - '+card.card_network+' ('+card.card_type+')'"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                            </template>

                                            <template x-if="cardTypes.length > 0">
                                                <div>
                                                    <label class="block text-[9px] font-bold text-gray-400 uppercase mb-0.5">Select Card Type (POS Commission)</label>
                                                    <select x-model="p.card_type_id"
                                                        class="w-full px-2 py-1 rounded text-xs font-semibold text-gray-800 bg-gray-50 border border-gray-200 outline-none">
                                                        <option value="">-- Select Card Type --</option>
                                                        <template x-for="ct in cardTypes" :key="ct.id">
                                                            <option :value="ct.id" x-text="ct.name + (ct.commission_value > 0 ? (' (' + ct.commission_value + (ct.commission_type === 'percentage' ? '%' : '') + ')') : '')"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <button @click="addSplit()" class="w-full py-2 rounded-xl text-xs font-bold text-brand-600 border border-dashed border-brand-300 hover:bg-brand-50 transition-colors">
                                + Add Payment Method
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex-shrink-0">
            {{-- Balance Status --}}
            <div class="flex items-center justify-between px-4 py-2.5 rounded-2xl mb-3 border"
                 :class="paymentDifference>0 ? 'bg-red-50 border-red-200 text-red-700' : 'bg-emerald-50 border-emerald-200 text-emerald-700'">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full" :class="paymentDifference>0 ? 'bg-red-500 animate-pulse' : 'bg-emerald-500'"></span>
                    <span class="text-xs font-bold" x-text="paymentDifference>0 ? 'Remaining Balance' : 'Payment Balanced'"></span>
                </div>
                <div class="text-right">
                    <span class="text-base font-black price tabular" x-text="formatCurrency(Math.abs(paymentDifference))"></span>
                </div>
            </div>

            <div class="flex gap-2.5">
                <button @click="showBillingModal=false" class="px-5 py-3.5 rounded-2xl text-xs font-bold text-gray-500 bg-gray-200 hover:bg-gray-300 transition-colors">
                    Cancel
                </button>
                <button @click="confirmOrder()" :disabled="!canSubmitOrder||isCheckingOut"
                    class="flex-1 btn-checkout-primary py-3.5 text-sm flex items-center justify-center gap-2">
                    <span x-show="!isCheckingOut">Confirm & Complete Order</span>
                    <div x-show="isCheckingOut" class="spin-loader w-4 h-4 rounded-full border-2 border-white/40 border-t-white"></div>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════
     ALPINE.JS CONTROLLER (100% Logic Preserved)
════════════════════════════════════════════════════════════ --}}
<script>
function posApp() {
    return {
        // ── State ──
        currency: {!! json_encode($currencyConfig ?? current_currency_config()) !!},
        formatCurrency(val) {
            const num = parseFloat(val) || 0;
            const isNegative = num < 0;
            const absNum = Math.abs(num);
            const d = typeof this.currency.decimal_places === 'number' ? this.currency.decimal_places : 2;
            const formatted = absNum.toLocaleString('en-US', { minimumFractionDigits: d, maximumFractionDigits: d });
            const res = this.currency.symbol_position === 'after' ? `${formatted} ${this.currency.symbol}` : `${this.currency.symbol}${formatted}`;
            return isNegative ? `-${res}` : res;
        },
        showCloseRegister: false,
        activeCategory: 'all',
        activeCategoryName: 'All Items',
        searchQuery: '',
        gridView: true,
        gridCols: 4,
        isLoading: false,
        isCheckingOut: false,
        showDiscount: false,
        showBillingModal: false,
        showOrderCompleted: false,
        showBillModal: false,
        showTablesModal: false,
        activeTablesList: [],
        favorites: JSON.parse(localStorage.getItem('pos_favorites') || '[]'),
        activeQuickAccess: null,
        showActiveOrdersPanel: false,

        selectQuickAccess(type) {
            if (type === 'active_orders') {
                this.showActiveOrdersPanel = !this.showActiveOrdersPanel;
                if (this.showActiveOrdersPanel) {
                    this.activeQuickAccess = 'active_orders';
                    this.fetchActiveOrders();
                } else {
                    if (this.activeQuickAccess === 'active_orders') {
                        this.activeQuickAccess = null;
                    }
                }
                this.applyFilters();
                return;
            }

            if (this.activeQuickAccess === type) {
                this.activeQuickAccess = null;
                this.setActiveCategoryName();
                this.applyFilters();
                return;
            }
            this.activeQuickAccess = type;
            if (type === 'favorites') {
                this.activeCategory = 'all';
                this.setActiveCategoryName();
            }
            this.applyFilters();
        },
        activeOrdersList: [],
        loadedOrderId: null,
        loadedTableName: '',
        loadedOrderTotal: 0,
        serviceType: 'retail',
        isSplit: true,
        splitPayments: [],
        cards: [],
        cardDetails: {},
        cardOffers: {},
        isCustomerLoading: false,

        customer: { name: '', phone: '', address: '' },
        paymentAccounts: {!! json_encode($paymentAccounts ?? []) !!},
        cardTypes: {!! json_encode($cardTypes ?? []) !!},
        cardAccountIds: {!! json_encode($cardAccountIds ?? []) !!},
        selectedCardTypeId: '',
        payments: {},
        useWallet: false,

        get totalPaid() {
            if (this.isSplit) {
                return this.splitPayments.reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0) + this.walletAmount;
            }
            let sum = 0;
            for (let acc of this.paymentAccounts) { sum += parseFloat(this.payments[acc.id]) || 0; }
            return sum + this.walletAmount;
        },

        get walletAmount() {
            if (!this.useWallet) return 0;
            return Math.min(this.customer.wallet_balance || 0, this.grandTotal);
        },

        recalcDynamicCash(changedAccountId) {
            if (this.paymentAccounts.length === 0) return;
            let primaryAccountId = this.paymentAccounts[0].id;
            if (changedAccountId === primaryAccountId) return;
            let others = this.walletAmount;
            for (let acc of this.paymentAccounts) {
                if (acc.id !== primaryAccountId) others += (parseFloat(this.payments[acc.id]) || 0);
            }
            this.payments[primaryAccountId] = others >= this.grandTotal ? 0 : (this.grandTotal - others).toFixed(2);
        },

        recalcCash() {
            if (this.isSplit) {
                if (this.splitPayments.length > 0) {
                    let otherSplitSum = 0;
                    for (let i = 1; i < this.splitPayments.length; i++) otherSplitSum += parseFloat(this.splitPayments[i].amount) || 0;
                    let remaining = this.grandTotal - this.walletAmount - otherSplitSum;
                    this.splitPayments[0].amount = Math.max(0, remaining).toFixed(2);
                }
            } else {
                if (this.paymentAccounts.length === 0) return;
                this.recalcDynamicCash(this.paymentAccounts[0].id);
            }
        },

        taxRate: {{ $companyTaxPercentage ?? 8.0 }},
        deliveryPartners: {!! json_encode($deliveryPartners ?? []) !!},
        selectedDeliveryPartnerId: '',
        matchingCustomers: [],
        showCustomerDropdown: false,

        get selectedDeliveryPartner() {
            return this.deliveryPartners.find(dp => dp.id == this.selectedDeliveryPartnerId) || null;
        },

        get deliveryCommissionAmount() {
            if (this.serviceType !== 'delivery' || !this.selectedDeliveryPartner) return 0;
            return parseFloat((this.grandTotal * (parseFloat(this.selectedDeliveryPartner.commission_percentage) / 100)).toFixed(2));
        },

        async searchCustomers(term) {
            let q = (term !== undefined ? term : (this.customer.phone || this.customer.name || '')).trim();
            if (!q || q.length < 2) {
                this.matchingCustomers = [];
                this.showCustomerDropdown = false;
                return;
            }
            this.isCustomerLoading = true;
            try {
                let res = await fetch('/pos/customer?query=' + encodeURIComponent(q));
                let data = await res.json();
                if (data && data.success && data.customers && data.customers.length > 0) {
                    this.matchingCustomers = data.customers;
                    this.showCustomerDropdown = true;
                } else {
                    this.matchingCustomers = [];
                    this.showCustomerDropdown = false;
                }
            } catch(e) {}
            this.isCustomerLoading = false;
        },

        selectCustomer(c) {
            this.customer = {
                id: c.id,
                name: c.name || '',
                phone: c.phone || '',
                address: c.address || '',
                wallet_balance: parseFloat(c.wallet_balance || 0)
            };
            this.matchingCustomers = [];
            this.showCustomerDropdown = false;
            if (this.useWallet) this.recalcCash();
        },

        async fetchCustomer() {
            if (this.customer.phone && this.customer.phone.length >= 2) {
                this.searchCustomers(this.customer.phone);
            }
        },

        get paymentDifference() { return parseFloat((this.grandTotal - this.totalPaid).toFixed(2)); },
        get canSubmitOrder() { return true; },
        get paymentRemaining() { return this.grandTotal - this.totalPaid; },

        discountType: 'percent',
        discountValue: 0,
        couponCode: '',
        appliedCoupon: null,
        orderNote: '',
        lastOrderId: '',
        lastOrderNumber: '',
        lastOrderKotNumber: '',
        lastOrderTableName: '',
        lastOrderTotal: 0,
        lastOrderItems: [],
        lastOrderCustomer: { name: '', phone: '' },
        lastOrderSubtotal: 0,
        lastOrderDiscount: 0,
        lastOrderDiscountPercent: 0,
        lastOrderTax: 0,
        lastOrderServiceType: 'counter',
        loadedOrderPaymentStatus: null,
        loadedOrderStatus: null,
        discountPercent: 0,
        currentTime: '',
        toasts: [],
        toastCounter: 0,

        allProducts: {{ Js::from($products->map(fn($p) => ['id'=>$p->id,'name'=>$p->name,'price'=>(float)$p->price,'image'=>$p->image??null,'sku'=>$p->sku??null,'category_id'=>(string)$p->category_id])->values()) }},
        categoryMap: {{ Js::from($categories->pluck('name','id')) }},
        cart: (function(raw) {
            if (!raw || Array.isArray(raw)) return {};
            const result = {};
            Object.values(raw).forEach(item => { if (item && item.id) result[String(item.id)] = item; });
            return result;
        })({{ Js::from($cart) }}),
        filteredProducts: [],

        get cartItems() { return Object.values(this.cart); },
        get totalQty() { return this.cartItems.reduce((sum, i) => sum + i.qty, 0); },
        get cartSubtotal() { return this.cartItems.reduce((sum, i) => sum + (i.price * i.qty), 0); },

        get cardDiscount() {
            let d = 0;
            if (!this.isSplit) {
                for (const [accountId, amount] of Object.entries(this.payments)) {
                    const details = this.cardDetails[accountId];
                    if (details && details.card_id) {
                        const offers = this.cardOffers[accountId] || [];
                        const sel = offers.find(o => String(o.offer.id) === String(details.offer_id));
                        d += sel ? parseFloat(sel.discount) : 0;
                    }
                }
            } else {
                this.splitPayments.forEach(p => {
                    if (p.card_id) {
                        const offers = p.offers || [];
                        const sel = offers.find(o => String(o.offer.id) === String(p.offer_id));
                        d += sel ? parseFloat(sel.discount) : 0;
                    }
                });
            }
            return d;
        },

        get cardServiceCharge() {
            let sc = 0;
            if (!this.isSplit) {
                for (const [accountId, amount] of Object.entries(this.payments)) {
                    const details = this.cardDetails[accountId];
                    if (details && details.card_id) {
                        const card = this.cards.find(c => String(c.id) === String(details.card_id));
                        if (card) {
                            const offers = this.cardOffers[accountId] || [];
                            const sel = offers.find(o => String(o.offer.id) === String(details.offer_id));
                            const offAmt = sel ? parseFloat(sel.discount) : 0;
                            sc += Math.max(0, parseFloat(amount) - offAmt) * (parseFloat(card.service_charge) / 100);
                        }
                    }
                }
            } else {
                this.splitPayments.forEach(p => {
                    if (p.card_id) {
                        const card = this.cards.find(c => String(c.id) === String(p.card_id));
                        if (card) {
                            const offers = p.offers || [];
                            const sel = offers.find(o => String(o.offer.id) === String(p.offer_id));
                            const offAmt = sel ? parseFloat(sel.discount) : 0;
                            sc += Math.max(0, parseFloat(p.amount) - offAmt) * (parseFloat(card.service_charge) / 100);
                        }
                    }
                });
            }
            return sc;
        },

        get discountAmount() {
            let total = this.cartSubtotal;
            let manual = this.discountType === 'percent'
                ? total * (parseFloat(this.discountValue) || 0) / 100
                : parseFloat(this.discountValue) || 0;
            let coupon = 0;
            if (this.appliedCoupon) {
                coupon = this.appliedCoupon.type === 'percent'
                    ? total * (parseFloat(this.appliedCoupon.value) || 0) / 100
                    : parseFloat(this.appliedCoupon.value) || 0;
            }
            return manual + coupon + this.cardDiscount;
        },

        get taxAmount() { return Math.max(0, this.cartSubtotal - this.discountAmount) * (this.taxRate / 100); },
        get grandTotal() { return Math.max(0, this.cartSubtotal - this.discountAmount + this.taxAmount + this.cardServiceCharge); },

        recalcTotal() { /* triggers Alpine computed re-evaluation */ },

        get favoriteProducts() {
            if (!Array.isArray(this.allProducts)) return [];
            return this.allProducts.filter(p => this.favorites.includes(Number(p.id)) || this.favorites.includes(String(p.id)));
        },

        get favoritesCount() {
            return this.favoriteProducts.length;
        },

        cleanOrphanFavorites() {
            if (!Array.isArray(this.allProducts)) return;
            const validIds = this.allProducts.map(p => Number(p.id));
            this.favorites = (this.favorites || []).map(f => Number(f)).filter(fId => validIds.includes(fId));
            localStorage.setItem('pos_favorites', JSON.stringify(this.favorites));
        },

        getImageUrl(path) {
            if (!path) return '';
            if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('data:')) return path;
            let cleanPath = path.replace(/^\/?(storage\/)?/, '');
            return '/storage/' + cleanPath;
        },

        isFavorite(productId) {
            return this.favorites.includes(Number(productId)) || this.favorites.includes(String(productId));
        },

        toggleFavorite(productId) {
            const id = Number(productId);
            if (this.isFavorite(id)) {
                this.favorites = this.favorites.filter(fId => Number(fId) !== id);
                this.showToast('Removed from favorites');
            } else {
                this.favorites.push(id);
                this.showToast('Added to favorites');
            }
            localStorage.setItem('pos_favorites', JSON.stringify(this.favorites));
            if (this.activeQuickAccess === 'favorites') {
                this.setActiveCategoryName();
                this.applyFilters();
            }
        },


        formatServiceMode(type) {
            if (!type) return 'Counter';
            const t = String(type).toLowerCase().replace(/_/g, '-');
            if (t === 'dine-in') return 'Dine-In';
            if (t === 'takeaway' || t === 'pickup') return 'Takeaway';
            if (t === 'delivery') return 'Delivery';
            return 'Counter';
        },

        resetFilters() {
            this.cart = {};
            this.loadedOrderId = null;
            this.loadedOrderPaymentStatus = null;
            this.loadedOrderStatus = null;
            this.loadedTableName = '';
            this.loadedOrderTotal = 0;
            this.customer = { name: '', phone: '', address: '' };
            this.discountValue = 0;
            this.discountType = 'percent';
            this.appliedCoupon = null;
            this.couponCode = '';
            this.orderNote = '';
            this.useWallet = false;
            this.walletAmount = 0;
            this.searchQuery = '';
            this.activeCategory = 'all';
            this.activeQuickAccess = null;
            this.serviceType = 'retail';
            this.gridView = true;
            this.gridCols = 4;
            this.syncToBackend('clear', {});
            this.setActiveCategoryName();
            this.applyFilters();
            this.showToast('POS reset to default');
        },

        async loadActiveOrder(ord) {
            const orderId = ord.id;
            try {
                const res = await fetch('{{ route("pos.load-order") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ order_id: orderId })
                });
                const data = await res.json();
                if (data.success) {
                    this.cart = data.cart;
                    this.loadedOrderId = data.order.id;
                    this.lastOrderId = data.order.id;
                    this.lastOrderNumber = data.order.order_number || ord.order_number || ('#ORD-' + String(data.order.id).padStart(5, '0'));
                    this.lastOrderKotNumber = data.order.kot_number || ('KOT-' + data.order.id);
                    this.lastOrderServiceType = data.order.service_type || ord.service_type || 'dine_in';
                    this.lastOrderTableName = data.order.table ? data.order.table.name : (ord.service_type === 'dine_in' ? 'Dine In' : '');
                    this.loadedOrderPaymentStatus = data.order.payment_status || ord.payment_status;
                    this.loadedOrderStatus = data.order.status || ord.status;
                    this.loadedTableName = this.lastOrderTableName;
                    this.loadedOrderTotal = data.order.total_amount;
                    this.serviceType = this.lastOrderServiceType;
                    this.customer.name = data.order.customer ? data.order.customer.name : (data.order.customer_name || '');
                    this.customer.phone = data.order.customer ? data.order.customer.phone : (data.order.customer_phone || '');
                    this.customer.address = data.order.customer ? (data.order.customer.address || '') : (data.order.billing_address || '');
                    this.discountValue = data.order.discount_value || 0;
                    this.discountType = data.order.discount_type || 'percent';
                    this.orderNote = data.order.note || '';
                    this.showToast('Loaded Order ' + (ord.order_number || ('#' + ord.id)));
                    if (this.customer.phone) this.fetchCustomer();
                } else {
                    this.showToast(data.message || 'Failed to load order', 'error');
                }
            } catch(e) { this.showToast('Failed to load order', 'error'); }
        },

        async fetchActiveOrders() {
            try {
                const res = await fetch('{{ route("pos.active-orders") }}');
                const data = await res.json();
                if (data.success && Array.isArray(data.orders)) {
                    this.activeOrdersList = data.orders;
                }
            } catch(e) { console.error('Failed to fetch active orders', e); }
        },

        async init() {
            this.filteredProducts = Array.isArray(this.allProducts) ? [...this.allProducts] : [];
            this.cleanOrphanFavorites();
            this.setActiveCategoryName();
            this.startClock();
            await this.fetchCards();
            await this.fetchActiveOrders();
            setInterval(() => { this.fetchActiveOrders(); }, 5000);
        },

        async fetchCards() {
            try {
                const res = await fetch('/api/cards');
                this.cards = await res.json();
            } catch(e) { console.error('Failed to fetch cards', e); }
        },

        async resolveOffersForCard(accountId, cardId, amount) {
            if (!this.cardDetails[accountId]) this.cardDetails[accountId] = { card_id:'', offer_id:'' };
            if (!cardId) { this.cardDetails[accountId].card_id=''; this.cardDetails[accountId].offer_id=''; this.cardOffers[accountId]=[]; return; }
            try {
                const res = await fetch('/api/pos/resolve-offers', {
                    method:'POST',
                    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},
                    body:JSON.stringify({card_id:cardId,subtotal:parseFloat(amount)||0,cart:Object.values(this.cart),customer_id:this.customer.id||null,branch_id:this.branchId||null})
                });
                const data = await res.json();
                if (data.success && data.offers) {
                    this.cardOffers[accountId] = data.offers;
                    this.cardDetails[accountId].offer_id = data.offers.length>0 ? data.offers[0].offer.id : '';
                } else { this.cardOffers[accountId]=[]; this.cardDetails[accountId].offer_id=''; }
            } catch(e) { this.cardOffers[accountId]=[]; this.cardDetails[accountId].offer_id=''; }
        },

        async resolveOffersForSplitCard(index, cardId, amount) {
            let p = this.splitPayments[index];
            if (!p) return;
            if (!cardId) { p.card_id=''; p.offer_id=''; p.offers=[]; return; }
            try {
                const res = await fetch('/api/pos/resolve-offers', {
                    method:'POST',
                    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},
                    body:JSON.stringify({card_id:cardId,subtotal:parseFloat(amount)||0,cart:Object.values(this.cart),customer_id:this.customer.id||null,branch_id:this.branchId||null})
                });
                const data = await res.json();
                if (data.success && data.offers) {
                    p.offers = data.offers;
                    p.offer_id = data.offers.length>0 ? data.offers[0].offer.id : '';
                } else { p.offers=[]; p.offer_id=''; }
            } catch(e) { p.offers=[]; p.offer_id=''; }
        },

        startClock() {
            const tick = () => { this.currentTime = new Date().toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit',second:'2-digit'}); };
            tick(); setInterval(tick, 1000);
        },

        filterCategory(categoryId) {
            this.activeQuickAccess = null;
            this.activeCategory = String(categoryId);
            this.searchQuery = '';
            this.setActiveCategoryName();
            this.applyFilters();
        },

        setActiveCategoryName() {
            if (this.activeQuickAccess === 'favorites') {
                this.activeCategoryName = 'Favorites (' + this.favoritesCount + ')';
            } else {
                this.activeCategoryName = this.activeCategory==='all' ? 'All Items' : (this.categoryMap[this.activeCategory]||'Unknown');
            }
        },

        filterProducts() { this.applyFilters(); },

        applyFilters() {
            let list = [...this.allProducts];
            if (this.activeQuickAccess === 'favorites') {
                list = list.filter(p => this.favorites.includes(Number(p.id)));
            } else if (this.activeCategory !== 'all') {
                list = list.filter(p => String(p.category_id) === this.activeCategory);
            }
            if (this.searchQuery.trim()) {
                const q = this.searchQuery.toLowerCase().trim();
                list = list.filter(p => p.name.toLowerCase().includes(q) || (p.sku && p.sku.toLowerCase().includes(q)));
            }
            this.filteredProducts = list;
        },

        isInCart(productId) { return !(!this.cart[String(productId)]); },

        addToCart(product) {
            const key = String(product.id);
            if (this.cart[key]) {
                this.cart[key].qty++;
            } else {
                this.cart = { ...this.cart, [key]: { id:product.id, name:product.name, price:parseFloat(product.price), image:product.image||null, sku:product.sku||null, qty:1 } };
            }
            this.showToast(product.name + ' added to order');
            this.syncToBackend('add', { product_id:product.id });
        },

        updateQty(productId, action) {
            const key = String(productId);
            if (!this.cart[key]) return;
            if (action==='increment') {
                this.cart[key].qty++;
            } else {
                this.cart[key].qty--;
                if (this.cart[key].qty <= 0) { const u={...this.cart}; delete u[key]; this.cart=u; }
            }
            this.syncToBackend('update', { product_id:productId, action });
        },

        removeFromCart(productId) {
            const key = String(productId);
            const name = this.cart[key] ? this.cart[key].name : 'Item';
            const updated = {...this.cart}; delete updated[key]; this.cart = updated;
            this.showToast(name + ' removed');
            this.syncToBackend('remove', { product_id:productId });
        },

        clearCart() {
            this.cart = {}; this.discountValue=0; this.appliedCoupon=null; this.couponCode=''; this.orderNote='';
            this.showToast('Order cleared');
            this.syncToBackend('clear', {});
        },

        syncToBackend(action, data) {
            fetch('/pos/cart/'+action, {
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
                body:JSON.stringify(data)
            }).catch(()=>{});
        },

        async fetchActiveTables() {
            this.showTablesModal = true;
            try {
                const res = await fetch('{{ route("pos.active-tables") }}');
                const data = await res.json();
                if (data.success) this.activeTablesList = data.tables;
            } catch(e) { this.showToast('Failed to fetch tables','error'); }
        },

        async loadTableOrder(table) {
            if (!table || !table.active_order) {
                this.showToast('No active order for this table', 'error');
                return;
            }
            const ord = {
                id: table.active_order.id,
                order_number: table.active_order.order_number,
                service_type: 'dine_in',
                service_type_label: 'Dine In',
                table_name: table.name,
                status: table.active_order.status,
                payment_status: table.active_order.payment_status
            };
            this.showTablesModal = false;
            await this.loadActiveOrder(ord);
        },

        initPayments() {
            this.payments={}; this.cardDetails={}; this.cardOffers={}; this.selectedCardTypeId='';
            let cashAcc = this.paymentAccounts.find(acc => acc.account_name.toLowerCase().includes('cash'));
            let defaultAccId = cashAcc ? cashAcc.id : (this.paymentAccounts[0]?.id||'');
            if (this.paymentAccounts.length>0 && defaultAccId) this.payments[defaultAccId] = this.grandTotal.toFixed(2);
            this.isSplit = true;
            if (defaultAccId) {
                this.splitPayments = [{ method:defaultAccId, amount:(this.grandTotal-this.walletAmount).toFixed(2), card_id:'', card_type_id:'', offer_id:'', offers:[] }];
            } else { this.splitPayments=[]; }
            this.paymentAccounts.forEach(acc => {
                if (this.cards.some(c => String(c.settlement_account_id)===String(acc.id))) this.cardDetails[acc.id]={card_id:'',offer_id:''};
            });
        },

        checkout() {
            if (this.cartItems.length===0) { this.showToast('Your cart is empty!','error'); return; }
            if (this.loadedOrderPaymentStatus==='paid' || this.loadedOrderStatus==='paid') {
                this.showToast('Order is ALREADY PAID! Showing receipt bill.');
                this.lastOrderId = this.loadedOrderId;
                this.lastOrderItems = [...this.cartItems];
                this.lastOrderSubtotal = this.cartSubtotal;
                this.lastOrderDiscount = this.discountAmount;
                this.lastOrderDiscountPercent = this.discountValue;
                this.lastOrderTax = this.taxAmount;
                this.lastOrderTotal = this.grandTotal;
                this.lastOrderCustomer = { name: this.customer.name, phone: this.customer.phone };
                this.lastOrderServiceType = this.serviceType;
                this.showBillModal = true;
                return;
            }
            if (!this.customer.name) this.customer.name = 'Walk-in Customer';
            if (!this.customer.phone) this.customer.phone = '0000000000';
            this.showBillingModal = true;
            this.initPayments();
            this.useWallet = false;
        },

        async confirmOrder() {
            if (this.serviceType==='delivery') {
                if (!this.customer.address) { this.showToast('Delivery Address is required for Delivery orders','error'); return; }
            }
            this.isCheckingOut = true;
            const snap = { items:[...this.cartItems], subtotal:this.cartSubtotal, discount:this.discountAmount, discountPct:this.discountValue, tax:this.taxAmount, total:this.grandTotal, customer:{name:this.customer.name,phone:this.customer.phone} };
            let selectedCardTypeId = this.selectedCardTypeId || null;
            if (this.isSplit) {
                const spWithCardType = this.splitPayments.find(p => p.card_type_id);
                if (spWithCardType) selectedCardTypeId = spWithCardType.card_type_id;
            }
            const cardDetailsPayload = {};
            if (!this.isSplit) {
                for (const [accountId,details] of Object.entries(this.cardDetails)) {
                    if (details.card_id || selectedCardTypeId) cardDetailsPayload[accountId]={card_id:details.card_id||null,offer_id:details.offer_id||null,card_type_id:selectedCardTypeId};
                }
            }
            const splitPaymentsPayload = this.splitPayments.map(p => {
                const mapped = {method:p.method,amount:parseFloat(p.amount)||0,card_type_id:p.card_type_id||null};
                if (p.card_id || p.card_type_id) mapped.card_details = {card_id:p.card_id||null,card_type_id:p.card_type_id||null,offer_id:p.offer_id||null};
                return mapped;
            });
            try {
                const res = await fetch('{{ route("pos.checkout") }}', {
                    method:'POST',
                    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},
                    body:JSON.stringify({
                        service_type:this.serviceType, discount_percent:this.discountValue, discount_type:this.discountType,
                        subtotal:this.cartSubtotal, tax_amount:this.taxAmount, coupon_id:this.appliedCoupon?this.appliedCoupon.id:null,
                        note:this.orderNote, total:this.grandTotal, cart:this.cartItems, order_id:this.loadedOrderId,
                        customer_name:this.customer.name, customer_phone:this.customer.phone, billing_address:this.customer.address,
                        delivery_partner_id: this.serviceType === 'delivery' ? (this.selectedDeliveryPartnerId || null) : null,
                        card_type_id: selectedCardTypeId || null,
                        payment_details:this.payments, card_details:cardDetailsPayload, use_wallet:this.useWallet,
                        wallet_amount:this.walletAmount, is_split:this.isSplit, split_payments:splitPaymentsPayload
                    })
                });
                const data = await res.json();
                if (data.success) {
                    this.lastOrderId = data.order_id;
                    this.lastOrderNumber = data.order_number || data.order_id;
                    this.lastOrderKotNumber = data.kot_number || (data.order_id ? 'KOT-' + data.order_id : '');
                    this.lastOrderServiceType = this.serviceType;
                    this.lastOrderTableName = this.loadedTableName;
                    this.lastOrderTotal = data.total || snap.total;
                } else {
                    this.showToast(data.message||'Checkout failed','error');
                    this.isCheckingOut=false;
                    return;
                }
            } catch(e) {
                this.lastOrderId = 'LOCAL-' + Date.now();
                this.lastOrderNumber = '#ORD-LOCAL';
                this.lastOrderServiceType = this.serviceType;
                this.lastOrderTotal = snap.total;
            }
            this.lastOrderItems=snap.items; this.lastOrderCustomer=snap.customer; this.lastOrderSubtotal=snap.subtotal;
            this.lastOrderDiscount=snap.discount; this.lastOrderDiscountPercent=snap.discountPct; this.lastOrderTax=snap.tax;
            this.showBillingModal=false;
            this.cart={}; this.customer={name:'',phone:'',address:''}; this.payments={cash:0,card:0,upi:0};
            this.discountValue=0; this.appliedCoupon=null; this.couponCode=''; this.orderNote='';
            this.loadedOrderId=null; this.loadedTableName=''; this.loadedOrderTotal=0;
            this.syncToBackend('clear',{});
            this.showOrderCompleted=true;
            this.isCheckingOut=false;
        },

        handleOrderCompleted() { this.showOrderCompleted=false; },

        printBill() {
            const win = window.open('','_blank','width=400,height=600');
            const html = document.getElementById('receipt-container').innerHTML;
            win.document.write(`<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Receipt</title><style>body{margin:0;padding:10px;font-family:'Courier New',monospace;font-size:12px;background:#fff;color:#000;}table{width:100%;border-collapse:collapse;}th,td{padding:4px 2px;}.receipt-container{width:100%;max-width:300px;margin:0 auto;}</style></head><body><div class="receipt-container">${html}</div><script>window.onload=function(){window.print();setTimeout(()=>window.close(),500);}<\/script></body></html>`);
            win.document.close();
        },

        shareOnWhatsApp() {
            const ph = this.lastOrderCustomer.phone.replace(/\D/g,'');
            const msg = `Order #${this.lastOrderId}\nTotal: ${this.formatCurrency(this.lastOrderTotal)}\n\nThank you for your visit!`;
            window.open(`https://wa.me/${ph}?text=${encodeURIComponent(msg)}`,'_blank');
        },

        startNewOrder() { this.showBillModal=false; this.showOrderCompleted=false; },

        toggleSplit() {
            this.isSplit=!this.isSplit;
            if (this.isSplit && this.splitPayments.length===0) this.addSplit();
        },

        addSplit() {
            let cashAcc = this.paymentAccounts.find(acc => acc.account_name.toLowerCase().includes('cash'));
            let defaultAccId = cashAcc ? cashAcc.id : (this.paymentAccounts[0]?.id||'');
            this.splitPayments.push({method:defaultAccId,amount:'0.00',card_id:'',card_type_id:'',offer_id:'',offers:[]});
        },

        removeSplit(index) { this.splitPayments.splice(index,1); },

        async applyCoupon() {
            if (!this.couponCode.trim()) return;
            try {
                const res = await fetch('{{ route("pos.validate-coupon") }}', {
                    method:'POST',
                    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},
                    body:JSON.stringify({code:this.couponCode.toUpperCase()})
                });
                const data = await res.json();
                if (data.success) { this.appliedCoupon=data.coupon; this.showToast('Coupon applied: '+data.coupon.code); }
                else { this.showToast(data.message||'Invalid coupon','error'); }
            } catch(e) { this.showToast('Failed to validate coupon','error'); }
        },

        async printOrderKOT(ord) {
            const orderId = ord.id || (ord.active_order ? ord.active_order.id : null);
            if (!orderId) return;
            try {
                const res = await fetch('{{ route("pos.load-order") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: JSON.stringify({ order_id: orderId })
                });
                const data = await res.json();
                if (data.success && data.order) {
                    const items = (Object.values(data.cart) || []).map(i => ({ name: i.name, qty: i.qty, note: i.note }));
                    printKOTSlip({
                        service_type: data.order.service_type || ord.service_type || 'counter',
                        kot_id: 'KOT-' + data.order.id,
                        order_number: data.order.order_number || ord.order_number || ('#ORD-' + data.order.id),
                        table_name: data.order.table ? data.order.table.name : (ord.name || ord.service_type_label || 'COUNTER'),
                        time: ord.time || new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}),
                        items: items
                    });
                } else {
                    this.showToast(data.message || 'Failed to fetch KOT data', 'error');
                }
            } catch(e) { this.showToast('Failed to fetch KOT data', 'error'); }
        },

        showToast(message, type='success') {
            const id = ++this.toastCounter;
            this.toasts.push({id,message,type});
            setTimeout(()=>{ this.toasts=this.toasts.filter(t=>t.id!==id); },3000);
        },
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
                SERVICE: ${(data.service_type || 'COUNTER').toUpperCase().replace('_', ' ')}
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