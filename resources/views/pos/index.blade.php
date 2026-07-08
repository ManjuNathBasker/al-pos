<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>POS Terminal — {{ config('app.name') }}</title>

    {{-- Google Fonts: DM Sans + DM Mono --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet" />

    {{-- Tailwind CSS CDN (replace with compiled asset in production) --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['DM Sans', 'sans-serif'],
                        mono: ['DM Mono', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        },
                    },
                    boxShadow: {
                        'card': '0 1px 3px 0 rgb(0 0 0 / 0.04), 0 1px 2px -1px rgb(0 0 0 / 0.04)',
                        'card-hover': '0 10px 25px -5px rgb(0 0 0 / 0.08), 0 8px 10px -6px rgb(0 0 0 / 0.04)',
                        'sidebar': '4px 0 24px -2px rgb(0 0 0 / 0.06)',
                        'cart': '-4px 0 24px -2px rgb(0 0 0 / 0.06)',
                    },
                    animation: {
                        'slide-in-right': 'slideInRight 0.25s ease-out',
                        'fade-in': 'fadeIn 0.2s ease-out',
                        'bounce-in': 'bounceIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)',
                        'shake': 'shake 0.4s ease-in-out',
                    },
                    keyframes: {
                        slideInRight: {
                            '0%': {
                                opacity: '0',
                                transform: 'translateX(12px)'
                            },
                            '100%': {
                                opacity: '1',
                                transform: 'translateX(0)'
                            },
                        },
                        fadeIn: {
                            '0%': {
                                opacity: '0',
                                transform: 'translateY(6px)'
                            },
                            '100%': {
                                opacity: '1',
                                transform: 'translateY(0)'
                            },
                        },
                        bounceIn: {
                            '0%': {
                                opacity: '0',
                                transform: 'scale(0.8)'
                            },
                            '100%': {
                                opacity: '1',
                                transform: 'scale(1)'
                            },
                        },
                        shake: {
                            '0%, 100%': {
                                transform: 'translateX(0)'
                            },
                            '20%, 60%': {
                                transform: 'translateX(-4px)'
                            },
                            '40%, 80%': {
                                transform: 'translateX(4px)'
                            },
                        },
                    },
                },
            },
        };
    </script>

    <style>
        * {
            font-family: 'DM Sans', sans-serif;
        }

        body {
            background: #f8f7ff;
        }

        /* Scrollbar styling */
        .styled-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .styled-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .styled-scroll::-webkit-scrollbar-thumb {
            background: #c7d2fe;
            border-radius: 99px;
        }

        .styled-scroll::-webkit-scrollbar-thumb:hover {
            background: #a5b4fc;
        }

        /* Product card add ripple */
        .product-card {
            position: relative;
            overflow: hidden;
        }

        .product-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at center, rgba(99, 102, 241, 0.12) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
        }

        .product-card:active::after {
            opacity: 1;
        }

        /* Toast */
        .toast-enter {
            animation: slideInRight 0.3s ease-out;
        }

        .toast-leave {
            animation: fadeOut 0.3s ease-in forwards;
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }

            to {
                opacity: 0;
                transform: translateX(16px);
            }
        }

        /* Category active glow */
        .cat-active {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            box-shadow: 0 4px 14px -3px rgba(99, 102, 241, 0.5);
        }

        /* Checkout button gradient */
        .checkout-btn {
            background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
            box-shadow: 0 4px 18px -4px rgba(99, 102, 241, 0.55);
            transition: all 0.2s ease;
        }

        .checkout-btn:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
            box-shadow: 0 6px 22px -4px rgba(99, 102, 241, 0.65);
            transform: translateY(-1px);
        }

        .checkout-btn:active {
            transform: translateY(0);
        }

        /* Quantity button */
        .qty-btn {
            transition: all 0.15s ease;
        }

        .qty-btn:hover {
            background: #6366f1;
            color: white;
        }

        .qty-btn:active {
            transform: scale(0.9);
        }

        /* Badge pulse */
        @keyframes badgePop {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.4);
            }

            100% {
                transform: scale(1);
            }
        }

        .badge-pop {
            animation: badgePop 0.3s ease-out;
        }

        /* Product card hover lift */
        .product-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .product-card:hover {
            transform: translateY(-3px);
        }

        /* Loading spinner */
        .spin {
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Category sidebar dot indicator */
        .cat-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
            transition: all 0.2s;
        }

        /* Price tag style */
        .price-tag {
            font-family: 'DM Mono', monospace;
            letter-spacing: -0.02em;
        }

        /* CHANGE #1: Receipt print styles */
        @media print {
            body {
                background: white;
            }
            .no-print {
                display: none !important;
            }
            .receipt-container {
                page-break-after: always;
                margin: 0;
                padding: 0;
            }
        }

        .receipt-container {
            width: 80mm;
            font-family: 'DM Mono', monospace;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            padding: 10px;
            background: #fff;
        }
    </style>
</head>

<body class="h-screen overflow-hidden text-slate-800" x-data="posApp()" x-init="init()">

    @php
        $openSession = \App\Models\RegisterSession::openForUser(auth()->id())->first();
    @endphp

    @if(!$openSession)
    {{-- Blocking Modal for Opening Register --}}
    <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8 border border-slate-100">
            <div class="w-16 h-16 bg-indigo-100 rounded-2xl flex items-center justify-center mb-6 mx-auto">
                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-slate-800 text-center mb-2">Open Register</h2>
            <p class="text-sm text-slate-500 text-center mb-8">Please enter the starting cash amount to open your shift.</p>
            
            <form action="{{ route('register-sessions.open') }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Opening Cash Amount ($)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-slate-400 font-bold">$</span>
                        </div>
                        <input type="number" name="opening_amount" step="0.01" min="0" required autofocus
                            class="w-full pl-10 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-lg font-bold text-slate-800 focus:ring-2 focus:ring-indigo-600 outline-none transition-shadow"
                            placeholder="0.00">
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('dashboard') }}" class="flex-1 py-4 text-center font-bold text-slate-600 bg-slate-100 rounded-2xl hover:bg-slate-200 transition-colors">Back</a>
                    <button type="submit" class="flex-1 py-4 text-center font-bold text-white bg-indigo-600 rounded-2xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all">Open Shift</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if($openSession)
    {{-- Close Register Modal --}}
    <div x-show="showCloseRegister" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm" x-transition>
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8 border border-slate-100">
            <h2 class="text-2xl font-bold text-slate-800 mb-2">Close Register</h2>
            <p class="text-sm text-slate-500 mb-6">Enter the actual cash counted in your drawer.</p>
            
            <form action="{{ route('register-sessions.close', $openSession->id) }}" method="POST">
                @csrf
                
                <div class="bg-slate-50 p-4 rounded-2xl mb-6">
                    <div class="flex justify-between mb-2">
                        <span class="text-sm text-slate-600">Opening Amount:</span>
                        <span class="text-sm font-bold">${{ number_format($openSession->opening_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-600">Expected Closing:</span>
                        <span class="text-sm font-bold text-indigo-600">${{ number_format($openSession->calculateExpectedAmount(), 2) }}</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Actual Cash Counted ($)</label>
                    <input type="number" name="closing_amount_actual" step="0.01" min="0" required
                        class="w-full px-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-lg font-bold text-slate-800 focus:ring-2 focus:ring-red-500 outline-none transition-all"
                        placeholder="0.00">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Notes / Discrepancy Reason</label>
                    <textarea name="notes" rows="2" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm text-slate-800 focus:ring-2 focus:ring-red-500 outline-none transition-all" placeholder="Optional notes..."></textarea>
                </div>
                
                <div class="flex gap-3">
                    <button type="button" @click="showCloseRegister = false" class="flex-1 py-4 text-center font-bold text-slate-600 bg-slate-100 rounded-2xl hover:bg-slate-200 transition-colors">Cancel</button>
                    <button type="submit" class="flex-1 py-4 text-center font-bold text-white bg-red-600 rounded-2xl hover:bg-red-700 shadow-lg shadow-red-200 transition-all">Close Shift</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- POS Interface begins here. -->
    {{-- ===================================================================
     GLOBAL TOAST NOTIFICATION
=================================================================== --}}
    <div class="fixed top-4 right-4 z-50 space-y-2" aria-live="polite">
        <template x-for="toast in toasts" :key="toast.id">
            <div
                class="toast-enter flex items-center gap-3 bg-white border border-slate-100 rounded-xl px-4 py-3 shadow-lg min-w-[260px] max-w-xs"
                :class="toast.type === 'error' ? 'border-red-100 bg-red-50' : ''">
                <div
                    class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                    :class="toast.type === 'error' ? 'bg-red-100 text-red-500' : 'bg-brand-100 text-brand-600'">
                    <svg x-show="toast.type !== 'error'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg x-show="toast.type === 'error'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-slate-700" x-text="toast.message"></p>
            </div>
        </template>
    </div>

    {{-- ===================================================================
     CHANGE #2: ORDER COMPLETED SUCCESS MODAL (shown before bill)
=================================================================== --}}
    <div
        x-show="showOrderCompleted"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm"
        @click.self="handleOrderCompleted()">
        <div
            x-show="showOrderCompleted"
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            class="bg-white rounded-3xl p-8 shadow-2xl max-w-sm w-full mx-4 text-center">
            <div class="w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-5">
                <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h2 class="text-2xl font-700 text-slate-800 mb-1 font-bold">Order Completed!</h2>
            <p class="text-slate-500 text-sm mb-1" x-text="'Order #' + lastOrderId"></p>
            <p class="text-3xl font-bold text-brand-600 price-tag mb-6" x-text="'$' + lastOrderTotal.toFixed(2)"></p>
            <button
                @click="showOrderCompleted = false; $nextTick(() => showBillModal = true)"
                class="w-full checkout-btn text-white font-semibold py-3 rounded-xl">
                View Bill
            </button>
        </div>
    </div>

    {{-- ===================================================================
     TABLE SELECTION MODAL
=================================================================== --}}
    <div
        x-show="showTablesModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm"
        @click.self="showTablesModal = false"
        x-cloak>
        <div
            x-show="showTablesModal"
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            class="bg-white rounded-[2.5rem] p-8 shadow-2xl max-w-4xl w-full mx-4 overflow-hidden border border-slate-100">
            
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-2xl font-black text-slate-800">Dine-in Tables</h2>
                    <p class="text-sm text-slate-400">Select an occupied table to process payment</p>
                </div>
                <button @click="showTablesModal = false" class="p-3 bg-slate-50 rounded-2xl text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 overflow-y-auto max-h-[60vh] p-1">
                <template x-for="table in activeTablesList" :key="table.id">
                    <button 
                        @click="loadTableOrder(table)"
                        class="relative group p-5 bg-white border-2 border-slate-100 rounded-3xl text-left hover:border-brand-500 hover:shadow-xl transition-all active:scale-[0.98]">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-12 h-12 bg-brand-50 rounded-2xl flex items-center justify-center text-brand-600 group-hover:bg-brand-600 group-hover:text-white transition-colors">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-widest bg-emerald-100 text-emerald-600 px-2 py-1 rounded-lg">Occupied</span>
                        </div>
                        <h3 class="font-bold text-slate-800" x-text="table.name"></h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest" x-text="table.section.name"></p>
                        
                        <div class="mt-4 pt-4 border-t border-slate-50">
                            <p class="text-xs font-bold text-slate-400 mb-1 uppercase tracking-tight">Active Order</p>
                            <p class="text-sm font-black text-brand-600 price-tag" x-text="'₹' + parseFloat(table.active_order.total_amount).toFixed(2)"></p>
                        </div>
                    </button>
                </template>
                
                <template x-if="activeTablesList.length === 0">
                    <div class="col-span-full py-12 text-center">
                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        </div>
                        <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">No occupied tables</p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- ===================================================================
     CHANGE #3: BILL DISPLAY MODAL (shown after order completion)
=================================================================== --}}
    <div
        x-show="showBillModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm"
        @click.self="showBillModal = false">
        <div
            x-show="showBillModal"
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            class="bg-white rounded-3xl p-8 shadow-2xl max-w-2xl w-full mx-4 max-h-screen overflow-y-auto">
            
            <!-- Bill Content -->
            <div id="receipt-container" class="receipt-container bg-white p-6 mb-6 border border-slate-200 rounded-lg">
                <!-- Header -->
                <div style="text-align: center; margin-bottom: 10px;">
                    <h2 style="margin: 0; font-size: 16px; text-transform: uppercase;">{{ config('app.name') }}</h2>
                    <p style="margin: 2px 0;">123 Supermarket St, Retail City</p>
                    <p style="margin: 2px 0;">Tel: +1 234 567 890</p>
                    <p style="margin: 5px 0; border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 3px 0;">
                        RECEIPT: #<span x-text="lastOrderId"></span>
                    </p>
                </div>

                <!-- Info Section -->
                <div style="margin-bottom: 10px;">
                    <p style="margin: 0;" x-text="'Date: ' + new Date().toLocaleString('en-US')"></p>
                    <p style="margin: 0;">Cashier: {{ auth()->user()->name ?? 'Admin' }}</p>
                    <template x-if="lastOrderCustomer.name">
                        <p style="margin: 0;" x-text="'Customer: ' + lastOrderCustomer.name"></p>
                    </template>
                </div>

                <!-- Items Table -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
                    <thead>
                        <tr style="border-bottom: 1px solid #000;">
                            <th style="text-align: left; padding: 5px 0;">Item</th>
                            <th style="text-align: center;">Qty</th>
                            <th style="text-align: right;">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="item in lastOrderItems" :key="item.id">
                            <tr>
                                <td style="padding: 5px 0;">
                                    <span x-text="item.name"></span><br>
                                    <small x-text="'SKU: ' + (item.sku || 'N/A')" style="font-size: 10px;"></small>
                                </td>
                                <td style="text-align: center;" x-text="item.qty"></td>
                                <td style="text-align: right;" x-text="(item.price * item.qty).toFixed(2)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <!-- Totals -->
                <div style="border-top: 1px dashed #000; padding-top: 5px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span>Subtotal:</span>
                        <span x-text="lastOrderSubtotal.toFixed(2)"></span>
                    </div>
                    <template x-if="lastOrderDiscount > 0">
                        <div style="display: flex; justify-content: space-between;">
                            <span x-text="'Discount (' + lastOrderDiscountPercent + '%):'"></span>
                            <span x-text="'-' + lastOrderDiscount.toFixed(2)"></span>
                        </div>
                    </template>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Tax (8%):</span>
                        <span x-text="lastOrderTax.toFixed(2)"></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 14px; margin-top: 5px;">
                        <span>GRAND TOTAL:</span>
                        <span x-text="'$' + lastOrderTotal.toFixed(2)"></span>
                    </div>
                </div>

                <!-- Footer -->
                <div style="text-align: center; margin-top: 20px; font-size: 10px;">
                    <p style="margin: 0;">THANK YOU FOR SHOPPING WITH US!</p>
                    <p style="margin: 0;">Please keep this receipt for returns.</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 flex-wrap justify-center">
                <!-- CHANGE #4: Print Button -->
                <button
                    @click="printBill()"
                    class="flex items-center gap-2 px-6 py-3 bg-blue-50 text-blue-600 font-semibold rounded-xl hover:bg-blue-100 transition-all no-print">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print Bill
                </button>

                <!-- CHANGE #5: WhatsApp Share Button -->
                <button
                    @click="shareOnWhatsApp()"
                    class="flex items-center gap-2 px-6 py-3 bg-green-50 text-green-600 font-semibold rounded-xl hover:bg-green-100 transition-all no-print">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.272-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.67-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.076 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421-7.403h-.004a9.87 9.87 0 00-4.782 1.176l-.342-.544C5.486 3.45 10.528.5 15.848.5c4.716 0 8.977 2.85 10.88 7.06l-2.35.761a8.21 8.21 0 00-1.922-2.82c-2.354-1.94-5.79-3.031-9.397-3.031z"/>
                    </svg>
                    Share on WhatsApp
                </button>

                <!-- Close/New Order Button -->
                <button
                    @click="startNewOrder()"
                    class="flex items-center gap-2 px-6 py-3 bg-slate-100 text-slate-700 font-semibold rounded-xl hover:bg-slate-200 transition-all no-print">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Order
                </button>
            </div>
        </div>
    </div>

    {{-- REST OF YOUR EXISTING CODE REMAINS THE SAME UNTIL THE SCRIPT SECTION --}}
    {{-- ===================================================================
     MAIN LAYOUT: Sidebar | Products | Cart
=================================================================== --}}
    <div class="flex h-screen overflow-hidden w-full {{ !$openSession ? 'pointer-events-none blur-sm' : '' }}">
        {{-- ===============================================================
         LEFT: CATEGORY SIDEBAR
    =============================================================== --}}
        <aside class="w-[220px] flex-shrink-0 bg-white shadow-sidebar flex flex-col z-10">

            {{-- Logo / Brand & Exit --}}
            <div class="px-5 py-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="flex items-center justify-center">
                        <img src="{{ asset('/assets/images/al-pos.png') }}" alt="{{ config('app.name') }}" class="w-10 h-10 object-contain">
                    </div>
                    <span class="text-sm font-bold text-slate-800">{{ config('app.name') }}</span>
                </div>
                <a href="{{ route('orders.index') }}" class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Exit POS">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </a>
            </div>

            {{-- Cashier Info --}}
            <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/60">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center text-brand-600 font-bold text-xs flex-shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'Admin', 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-slate-700 truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                        <p class="text-[10px] text-slate-400">Cashier</p>
                    </div>
                    <div class="ml-auto w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0" title="Online"></div>
                </div>
                @if($openSession)
                <div class="mt-3">
                    <button @click="showCloseRegister = true" class="w-full py-2 bg-white border border-red-200 text-red-600 rounded-xl text-xs font-bold hover:bg-red-50 transition-colors flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        Close Register
                    </button>
                </div>
                @endif
            </div>

            {{-- Category List --}}
            <nav class="flex-1 overflow-y-auto styled-scroll px-3 pb-4 space-y-0.5">
                {{-- Service Type Switcher --}}
                <div class="grid grid-cols-2 gap-2 mb-4">
                    <button @click="serviceType = 'retail'; loadedOrderId = null; loadedTableName = ''" 
                            :class="serviceType === 'retail' ? 'bg-indigo-600 text-white shadow-indigo-200' : 'bg-slate-50 text-slate-600 hover:bg-slate-100'"
                            class="flex flex-col items-center justify-center p-3 rounded-2xl transition-all border border-transparent shadow-sm">
                        <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                        <span class="text-[10px] font-black uppercase tracking-tighter">Counter</span>
                    </button>
                    
                    @php $activeCompany = \App\Models\Company::find(session('company_id')); @endphp
                    @if($activeCompany && $activeCompany->isModuleEnabled('restaurant_mode'))
                    <button @click="serviceType = 'dine_in'; fetchActiveTables();" 
                            :class="serviceType === 'dine_in' ? 'bg-emerald-600 text-white shadow-emerald-200' : 'bg-slate-50 text-slate-600 hover:bg-slate-100'"
                            class="flex flex-col items-center justify-center p-3 rounded-2xl transition-all border border-transparent shadow-sm">
                        <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        <span class="text-[10px] font-black uppercase tracking-tighter">Dine-in</span>
                    </button>

                    <button @click="serviceType = 'takeaway'; loadedOrderId = null; loadedTableName = ''" 
                            :class="serviceType === 'takeaway' ? 'bg-amber-500 text-white shadow-amber-200' : 'bg-slate-50 text-slate-600 hover:bg-slate-100'"
                            class="flex flex-col items-center justify-center p-3 rounded-2xl transition-all border border-transparent shadow-sm">
                        <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                        <span class="text-[10px] font-black uppercase tracking-tighter">Takeaway</span>
                    </button>

                    <button @click="serviceType = 'delivery'; loadedOrderId = null; loadedTableName = ''" 
                            :class="serviceType === 'delivery' ? 'bg-rose-500 text-white shadow-rose-200' : 'bg-slate-50 text-slate-600 hover:bg-slate-100'"
                            class="flex flex-col items-center justify-center p-3 rounded-2xl transition-all border border-transparent shadow-sm">
                        <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                        <span class="text-[10px] font-black uppercase tracking-tighter">Delivery</span>
                    </button>
                    @endif
                </div>

                <template x-if="serviceType === 'dine_in'">
                    <button @click="fetchActiveTables()" class="w-full flex items-center gap-3 px-3 py-3 mb-2 rounded-xl text-sm font-bold transition-all duration-200 text-left bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-100">
                         <span class="w-7 h-7 rounded-lg bg-emerald-500 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        </span>
                        <span class="flex-1" x-text="loadedTableName ? 'Table: ' + loadedTableName : 'Select Table'"></span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </button>
                </template>

                {{-- Divider --}}
                <div class="px-5 pt-2 pb-2">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Categories</p>
                </div>

                {{-- All Products --}}
                <button
                    @click="filterCategory('all')"
                    :class="activeCategory === 'all' ? 'cat-active text-white' : 'text-slate-600 hover:bg-brand-50 hover:text-brand-700'"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 text-left">
                    <span
                        class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 transition-colors"
                        :class="activeCategory === 'all' ? 'bg-white/20' : 'bg-slate-100 text-slate-500'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </span>
                    <span class="flex-1">All Products</span>
                    <span
                        class="text-[10px] font-semibold px-1.5 py-0.5 rounded-md"
                        :class="activeCategory === 'all' ? 'bg-white/25 text-white' : 'bg-slate-100 text-slate-500'">
                        {{ $products->count() }}
                    </span>
                </button>

                {{-- Dynamic Categories --}}
                @foreach($categories as $category)
                <button
                    @click="filterCategory('{{ $category->id }}')"
                    :class="activeCategory === '{{ $category->id }}' ? 'cat-active text-white' : 'text-slate-600 hover:bg-brand-50 hover:text-brand-700'"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 text-left">
                    <span
                        class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 transition-colors text-base"
                        :class="activeCategory === '{{ $category->id }}' ? 'bg-white/20' : 'bg-slate-100'">
                        {{ $category->icon ?? '📦' }}
                    </span>
                    <span class="flex-1 truncate">{{ $category->name }}</span>
                    <span
                        class="text-[10px] font-semibold px-1.5 py-0.5 rounded-md"
                        :class="activeCategory === '{{ $category->id }}' ? 'bg-white/25 text-white' : 'bg-slate-100 text-slate-500'">
                        {{ $category->products->count() }}
                    </span>
                </button>
                @endforeach

            </nav>

            {{-- Sidebar Footer --}}
            <div class="px-4 py-4 border-t border-slate-100 space-y-4">
                {{-- User Profile --}}
                <div class="flex items-center gap-3 px-2">
                    <div class="w-9 h-9 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-bold text-sm uppercase">
                        {{ substr(auth()->user()->name, 0, 2) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-slate-800 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-slate-500 truncate">{{ auth()->user()->roles->first()->name ?? 'Staff' }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-slate-400 hover:text-red-500 transition-colors" title="Logout">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>

                <div class="space-y-1">
                    <p class="text-[10px] text-slate-400 text-center font-mono">
                        {{ now()->format('D, d M Y') }}
                    </p>
                    <p class="text-[10px] text-slate-400 text-center font-mono" x-text="currentTime"></p>
                </div>
            </div>
        </aside>

        {{-- ===============================================================
         CENTER: PRODUCT GRID
    =============================================================== --}}
        <main class="flex-1 flex flex-col min-w-0 overflow-hidden">

            {{-- Top Bar --}}
            <header class="bg-white/80 backdrop-blur-sm border-b border-slate-100 px-6 py-4 flex items-center gap-4 flex-shrink-0">

                {{-- Search --}}
                <div class="relative flex-1 max-w-md">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        type="text"
                        x-model="searchQuery"
                        @input="filterProducts"
                        placeholder="Search products, SKU…"
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-200 focus:border-brand-400 transition-all" />
                    <button
                        x-show="searchQuery"
                        @click="searchQuery = ''; filterProducts()"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Active Category Pill --}}
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400 font-medium">Showing:</span>
                    <span x-text="activeCategoryName" class="text-xs font-semibold bg-brand-50 text-brand-700 px-3 py-1.5 rounded-lg"></span>
                </div>

                {{-- Product count --}}
                <div class="ml-auto text-sm text-slate-400">
                    <span class="font-semibold text-slate-600" x-text="filteredProducts.length"></span> items
                </div>

                {{-- Layout Controls --}}
                <div class="flex items-center gap-4">
                    {{-- Grid Column Selector (only visible in grid view) --}}
                    <div x-show="gridView" class="hidden sm:flex items-center gap-1.5 bg-slate-100/50 p-1 rounded-lg border border-slate-200/50">
                        <span class="text-[10px] font-bold text-slate-400 uppercase px-2">Cols</span>
                        <template x-for="c in [2,3,4,5,6]" :key="c">
                            <button @click="gridCols = c" 
                                    :class="gridCols === c ? 'bg-white text-brand-600 shadow-sm' : 'text-slate-400 hover:text-slate-600'"
                                    class="w-6 h-6 flex items-center justify-center rounded text-[11px] font-bold transition-all"
                                    x-text="c"></button>
                        </template>
                    </div>

                    <div class="flex items-center bg-slate-100 rounded-lg p-0.5 gap-0.5">
                        <button
                            @click="gridView = true"
                            :class="gridView ? 'bg-white text-brand-600 shadow-card' : 'text-slate-400 hover:text-slate-600'"
                            class="p-1.5 rounded-md transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </button>
                        <button
                            @click="gridView = false"
                            :class="!gridView ? 'bg-white text-brand-600 shadow-card' : 'text-slate-400 hover:text-slate-600'"
                            class="p-1.5 rounded-md transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                        </button>
                    </div>

                </div>
            </header>

            {{-- Sub-header with Static Store Display --}}
            <div class="bg-white border-b border-slate-100 px-6 py-3 flex items-center justify-between flex-shrink-0 z-40 relative">
                <div class="flex items-center gap-3">
                    @php
                        $currentCompany = \App\Models\Company::find(session('company_id'));
                        $initials = $currentCompany ? strtoupper(substr($currentCompany->name, 0, 1)) : 'C';
                    @endphp
                    <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-base shadow-sm">
                        {{ $initials }}
                    </div>
                    <div class="flex flex-col items-start leading-tight">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Store Context</span>
                        <span class="text-sm font-bold text-slate-700">{{ $currentCompany->name ?? 'Default Store' }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-4 text-xs text-slate-400 font-medium">
                    <span x-text="serviceType.toUpperCase()"></span>
                </div>
            </div>

            {{-- Product Grid Area --}}
            <div class="flex-1 overflow-y-auto styled-scroll p-5">

                {{-- Loading state --}}
                <div x-show="isLoading" class="flex flex-col items-center justify-center h-full gap-3 text-slate-400">
                    <svg class="spin w-8 h-8 text-brand-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <p class="text-sm font-medium">Loading products…</p>
                </div>

                {{-- Empty state --}}
                <div x-show="!isLoading && filteredProducts.length === 0" class="flex flex-col items-center justify-center h-full gap-3 text-slate-400">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium">No products found</p>
                </div>

                {{-- Product Grid --}}
                <div x-show="!isLoading && filteredProducts.length > 0" 
                     :class="gridView ? 'grid gap-4' : 'flex flex-col gap-3'"
                     :style="gridView ? `grid-template-columns: repeat(${gridCols}, minmax(0, 1fr))` : ''">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <button @click="addToCart(product)" 
                                :class="gridView ? 'flex-col' : 'flex-row items-center h-28'"
                                class="product-card group relative flex overflow-hidden rounded-2xl border border-slate-100 bg-white hover:border-brand-300 hover:shadow-card-hover transition-all active:scale-[0.98]">
                            
                            {{-- Image Area --}}
                            <div :class="gridView ? 'aspect-[4/3] w-full' : 'w-40 h-full flex-shrink-0'"
                                 class="relative overflow-hidden bg-slate-50 border-r border-slate-50">
                                <img x-show="product.image" 
                                     :src="'storage/' + product.image" 
                                     :alt="product.name" 
                                     class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
                                <div x-show="!product.image" class="flex h-full w-full items-center justify-center">
                                     <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                     </svg>
                                </div>

                                {{-- In Cart Badge Overlay --}}
                                <template x-if="isInCart(product.id)">
                                    <div class="absolute right-2 top-2 z-10 animate-bounce-in">
                                        <span class="flex h-7 min-w-[28px] items-center justify-center rounded-full bg-emerald-500 px-1.5 text-[11px] font-black text-white shadow-lg border-2 border-white">
                                            <span x-text="cart[String(product.id)].qty"></span>
                                        </span>
                                    </div>
                                </template>
                                
                                {{-- Quick Add Overlay --}}
                                <div class="absolute inset-0 bg-brand-900/0 group-hover:bg-brand-900/10 transition-colors duration-300 flex items-center justify-center">
                                    <div class="w-10 h-10 rounded-full bg-white text-brand-600 shadow-xl flex items-center justify-center opacity-0 scale-50 group-hover:opacity-100 group-hover:scale-100 transition-all duration-300">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            {{-- Product Info Area --}}
                            <div class="flex-1 flex flex-col p-3 text-left justify-between h-full">
                                <div class="flex flex-col">
                                    <h3 :class="gridView ? 'text-[13px]' : 'text-base'" 
                                        class="font-bold text-slate-800 line-clamp-1 group-hover:text-brand-600 transition-colors mb-0.5" x-text="product.name"></h3>
                                    <p class="text-[10px] font-medium text-slate-400 uppercase tracking-tight" x-text="product.sku || 'NO SKU'"></p>
                                    <template x-if="!gridView">
                                        <p class="mt-2 text-xs text-slate-500 line-clamp-2" x-text="product.description || 'No description available for this item.'"></p>
                                    </template>
                                </div>
                                <div class="mt-2.5 flex items-center justify-between">
                                    <span :class="gridView ? 'text-base' : 'text-xl'" 
                                          class="font-black text-brand-600 price-tag" x-text="'$' + parseFloat(product.price).toFixed(2)"></span>
                                    <div class="text-[10px] font-bold text-slate-300 uppercase tracking-widest group-hover:text-brand-400 transition-colors" x-text="gridView ? 'Select' : 'Add to Order'"></div>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
        </main>

        {{-- ===============================================================
         RIGHT: SHOPPING CART SIDEBAR
    =============================================================== --}}
        <aside class="w-[340px] flex-shrink-0 bg-white shadow-cart flex flex-col z-10 overflow-hidden">

            {{-- Cart Header --}}
            <div class="px-6 py-5 border-b border-slate-100 flex-shrink-0 bg-white">
                <div class="flex flex-col gap-1">
                    <div class="flex items-center justify-between">
                        <div class="flex items-baseline gap-2">
                            <h2 class="text-lg font-bold text-slate-800">Order</h2>
                            <p class="text-[10px] text-slate-400 font-mono uppercase tracking-widest">{{ date('Y-m-d H:i') }}</p>
                        </div>
                        <template x-if="loadedOrderId">
                             <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black bg-brand-100 text-brand-700 uppercase tracking-tighter" x-text="'Table: ' + loadedTableName"></span>
                        </template>
                    </div>
                    <template x-if="loadedOrderId">
                        <div class="flex items-center justify-between mt-1 pt-1 border-t border-slate-50">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Original Total</span>
                            <span class="text-xs font-black text-slate-600" x-text="'$' + parseFloat(loadedOrderTotal).toFixed(2)"></span>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Items Section --}}
            <div class="flex-1 overflow-y-auto styled-scroll px-4 py-4 space-y-2">
                <template x-if="cartItems.length === 0">
                    <div class="flex flex-col items-center justify-center h-full gap-3 text-slate-400">
                        <svg class="w-12 h-12 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <p class="text-sm font-medium">Cart is empty</p>
                    </div>
                </template>

                <template x-for="item in cartItems" :key="item.id">
                    <div class="flex gap-4 p-3.5 bg-white border border-slate-100 rounded-2xl transition-all hover:border-brand-200 hover:shadow-sm">
                        <div class="w-16 h-16 rounded-xl bg-slate-50 flex-shrink-0 flex items-center justify-center overflow-hidden border border-slate-100">
                            <img x-show="item.image" :src="'storage/' + item.image" :alt="item.name" class="w-full h-full object-cover" />
                            <svg x-show="!item.image" class="w-8 h-8 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0 flex flex-col justify-between">
                            <div>
                                <p class="text-[13px] font-bold text-slate-800 truncate" x-text="item.name"></p>
                                <p class="text-[11px] font-medium text-slate-400" x-text="'$' + parseFloat(item.price).toFixed(2) + ' / unit'"></p>
                            </div>
                            <p class="text-[14px] font-black text-brand-600 price-tag" x-text="'$' + (item.price * item.qty).toFixed(2)"></p>
                        </div>
                        <div class="flex flex-col items-end justify-between py-0.5">
                            <button @click="removeFromCart(item.id)" class="w-6 h-6 flex items-center justify-center text-slate-300 hover:text-red-500 transition-colors rounded-lg hover:bg-red-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                            <div class="flex items-center gap-2 bg-slate-50 rounded-xl p-1 border border-slate-100">
                                <button @click="updateQty(item.id, 'decrement')" class="w-6 h-6 flex items-center justify-center text-slate-600 hover:bg-white hover:text-brand-600 rounded-lg transition-all shadow-sm active:scale-90 font-black">−</button>
                                <span class="w-5 text-center font-bold text-[12px] text-slate-800" x-text="item.qty"></span>
                                <button @click="updateQty(item.id, 'increment')" class="w-6 h-6 flex items-center justify-center text-slate-600 hover:bg-white hover:text-brand-600 rounded-lg transition-all shadow-sm active:scale-90 font-black">+</button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Divider --}}
            <div class="px-4 py-3 border-t border-b border-slate-100 flex-shrink-0 space-y-3">

                {{-- Discount --}}
                <div @click="showDiscount = !showDiscount" class="flex items-center justify-between cursor-pointer hover:bg-slate-50 -mx-2 px-2 py-2 rounded-lg transition-colors">
                    <span class="text-sm font-medium text-slate-700">Discount</span>
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-bold text-red-600 price-tag" x-text="discountAmount > 0 ? '-$' + discountAmount.toFixed(2) : '-'" />
                        <svg class="w-4 h-4 text-slate-400 transition-transform" :class="showDiscount ? 'rotate-180' : ''">
                            <path fill="currentColor" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                        </svg>
                    </div>
                </div>

                <template x-if="showDiscount">
                    <div class="space-y-3 bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <!-- Manual Discount Type Toggle -->
                        <div class="flex items-center gap-2 mb-2">
                            <button @click="discountType = 'percent'" :class="discountType === 'percent' ? 'bg-brand-600 text-white' : 'bg-white text-slate-600'" class="flex-1 py-1.5 rounded-lg text-xs font-bold transition-all border border-slate-200">PERCENT (%)</button>
                            <button @click="discountType = 'fixed'" :class="discountType === 'fixed' ? 'bg-brand-600 text-white' : 'bg-white text-slate-600'" class="flex-1 py-1.5 rounded-lg text-xs font-bold transition-all border border-slate-200">FIXED (-)</button>
                        </div>
                        
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-mono text-sm" x-text="discountType === 'percent' ? '%' : '$'"></span>
                                <input type="number" x-model="discountValue" :placeholder="discountType === 'percent' ? '0%' : '0.00'" class="w-full pl-8 pr-3 py-2 bg-white border border-slate-200 rounded-lg focus:border-brand-400 outline-none text-sm font-mono" />
                            </div>
                            <button @click="discountValue = 0" class="p-2 text-slate-400 hover:text-red-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>

                        <!-- Coupon Code Section -->
                        <div class="pt-2 border-t border-slate-200">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Apply Coupon</label>
                            <div class="flex gap-2">
                                <input type="text" x-model="couponCode" placeholder="COUPON10" class="flex-1 px-3 py-2 bg-white border border-slate-200 rounded-lg focus:border-brand-400 outline-none text-sm font-mono uppercase" />
                                <button @click="applyCoupon()" class="px-3 py-2 bg-slate-800 text-white text-xs font-bold rounded-lg hover:bg-slate-900 transition-colors">APPLY</button>
                            </div>
                            <template x-if="appliedCoupon">
                                <div class="mt-2 flex items-center justify-between bg-emerald-50 text-emerald-700 px-2 py-1.5 rounded-lg text-[11px] font-bold">
                                    <span x-text="'Applied: ' + appliedCoupon.code"></span>
                                    <button @click="appliedCoupon = null; couponCode = ''" class="text-emerald-600 hover:text-emerald-800 uppercase">Remove</button>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Subtotal Row --}}
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-slate-600">Subtotal</span>
                    <span class="text-sm font-bold text-slate-800 price-tag" x-text="'$' + cartSubtotal.toFixed(2)" />
                </div>

                {{-- Tax Row --}}
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-slate-600">Tax <span class="text-xs text-slate-400 font-normal">(8%)</span></span>
                    <span class="text-sm font-bold text-slate-800 price-tag" x-text="'$' + taxAmount.toFixed(2)" />
                </div>

                {{-- Grand Total --}}
                <div class="flex items-center justify-between pt-2 border-t border-slate-200">
                    <span class="text-base font-bold text-slate-800">Total</span>
                    <span class="text-2xl font-bold text-brand-600 price-tag" x-text="'$' + grandTotal.toFixed(2)" />
                </div>
            </div>

            {{-- Cart Actions --}}
            <div class="px-6 pb-6 pt-4 flex-shrink-0 space-y-2">
                <button @click="checkout()" :disabled="cartItems.length === 0" class="w-full checkout-btn text-white font-bold text-lg rounded-2xl disabled:opacity-40 disabled:grayscale transition-all flex items-center justify-center gap-3 py-4">
                    <span>Checkout</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </button>
                <button @click="clearCart()" :disabled="cartItems.length === 0" class="w-full px-6 py-4 bg-slate-50 text-slate-600 font-bold rounded-2xl hover:bg-slate-100 hover:text-slate-800 transition-all disabled:opacity-40">
                    Clear Cart
                </button>
            </div>
        </aside>
    </div>

    {{-- ===================================================================
     BILLING FORM MODAL (Customer Details)
=================================================================== --}}
    <div x-show="showBillingModal" 
         class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm"
         x-cloak x-transition>
        <div class="bg-white rounded-[2rem] shadow-2xl max-w-3xl w-full mx-4 overflow-hidden border border-slate-100">
            
            {{-- Header Section --}}
            <div class="px-8 py-6 border-b border-slate-50 flex justify-between items-end bg-white">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 mb-1">Order Summary</h1>
                    <p class="text-sm text-slate-500">Fill in customer details to complete the order</p>
                </div>
            </div>

            {{-- Content Section --}}
            <div class="px-8 py-8 overflow-y-auto max-h-[calc(100vh-300px)]">
                <div class="grid grid-cols-2 gap-8">

                    {{-- Left: Items & Amounts --}}
                    <div class="space-y-6">
                        <div>
                            <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-600 mb-3">Order Items</h2>
                            <div class="space-y-2 bg-slate-50 p-4 rounded-xl max-h-48 overflow-y-auto">
                                <template x-for="item in cartItems" :key="item.id">
                                    <div class="flex justify-between text-sm">
                                        <div>
                                            <p class="font-medium text-slate-800" x-text="item.name" />
                                            <p class="text-xs text-slate-500" x-text="'Qty: ' + item.qty" />
                                        </div>
                                        <p class="font-mono font-bold text-slate-800" x-text="'$' + (item.price * item.qty).toFixed(2)" />
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="border-t-2 border-slate-200 pt-4 space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-slate-600">Subtotal:</span>
                                <span class="font-mono font-bold text-slate-800" x-text="'$' + cartSubtotal.toFixed(2)" />
                            </div>
                            <template x-if="discountAmount > 0">
                                <div class="flex justify-between">
                                    <span class="text-sm text-slate-600">
                                        Discount
                                        <template x-if="appliedCoupon">
                                            <span class="text-[10px] bg-emerald-100 text-emerald-700 px-1 rounded ml-1" x-text="appliedCoupon.code"></span>
                                        </template>
                                        :
                                    </span>
                                    <span class="font-mono font-bold text-red-600" x-text="'-$' + discountAmount.toFixed(2)" />
                                </div>
                            </template>
                            <div class="flex justify-between">
                                <span class="text-sm text-slate-600">Tax (8%):</span>
                                <span class="font-mono font-bold text-slate-800" x-text="'$' + taxAmount.toFixed(2)" />
                            </div>
                            <template x-if="cardServiceCharge > 0">
                                <div class="flex justify-between">
                                    <span class="text-sm text-slate-600">Card Srv Charge:</span>
                                    <span class="font-mono font-bold text-slate-800" x-text="'+$' + cardServiceCharge.toFixed(2)" />
                                </div>
                            </template>
                            <div class="flex justify-between border-t border-slate-200 pt-2">
                                <span class="font-bold text-slate-800">Grand Total:</span>
                                <span class="text-2xl font-bold text-brand-600 price-tag" x-text="'$' + grandTotal.toFixed(2)" />
                            </div>
                        </div>
                    </div>

                    {{-- Right: Customer & Payment Form --}}
                    <div class="space-y-6">
                        <!-- Customer Info Section -->
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Customer Name</label>
                                    <input type="text" x-model="customer.name" placeholder="Walk-in Customer" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:bg-white focus:border-brand-400 outline-none text-sm font-bold transition-all" />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Phone Number</label>
                                    <div class="relative">
                                        <input type="text" x-model="customer.phone" @input="fetchCustomer()" placeholder="9988776655" class="w-full pl-4 pr-10 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:bg-white focus:border-brand-400 outline-none text-sm font-bold transition-all" />
                                        <div class="absolute right-3 top-1/2 -translate-y-1/2" x-show="isCustomerLoading">
                                            <svg class="animate-spin h-4 w-4 text-brand-500" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div x-show="serviceType === 'delivery' || customer.name">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5" :class="serviceType === 'delivery' ? 'text-rose-500' : ''">
                                    <span x-text="serviceType === 'delivery' ? 'Delivery Address (Required)' : 'Customer Address'"></span>
                                </label>
                                <textarea x-model="customer.address" rows="2" placeholder="Street, Building, Floor..." class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl focus:bg-white focus:border-brand-400 outline-none text-sm font-bold transition-all"></textarea>
                            </div>
                        </div>

                        {{-- Payment Methods Header --}}
                        <div class="border-t border-slate-100 pt-4 flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-slate-700">Payment Method</h3>
                        </div>

                        {{-- Payment Methods --}}
                        <div class="space-y-4">
                            <!-- Wallet UI -->
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-slate-700">Wallet Balance</p>
                                    <p class="text-xs text-slate-500" x-text="'Available: $' + (customer.wallet_balance || 0).toFixed(2)"></p>
                                </div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="useWallet" @change="recalcCash" class="w-5 h-5 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                    <span class="text-sm font-semibold text-slate-700">Use Wallet</span>
                                </label>
                            </div>

                            {{-- Split Payments List --}}
                            <div class="space-y-3">
                                <template x-for="(p, index) in splitPayments" :key="index">
                                    <div class="space-y-2 bg-slate-50 p-3 rounded-xl border border-slate-200">
                                        <div class="flex items-end gap-2">
                                            <div class="flex-1 space-y-1">
                                                <label class="text-[10px] font-black uppercase text-slate-400">Method</label>
                                                <select x-model="p.method" @change="p.card_id = ''; p.offer_id = ''; p.offers = [];" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold focus:border-brand-500 outline-none">
                                                    <template x-for="acc in paymentAccounts" :key="acc.id">
                                                        <option :value="acc.id" x-text="acc.account_name"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div class="flex-1 space-y-1">
                                                <label class="text-[10px] font-black uppercase text-slate-400">Amount</label>
                                                <input type="number" x-model="p.amount" @input="if(cards.some(c => String(c.settlement_account_id) === String(p.method))) resolveOffersForSplitCard(index, p.card_id, p.amount)" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold focus:border-brand-500 outline-none font-mono" />
                                            </div>
                                            <button @click="removeSplit(index)" class="p-2 text-slate-300 hover:text-red-500">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>

                                        <!-- Split Card Selection -->
                                        <template x-if="cards.some(c => String(c.settlement_account_id) === String(p.method))">
                                            <div class="mt-2 space-y-2 p-2 bg-white rounded-lg border border-slate-150">
                                                <div>
                                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Select Card</label>
                                                    <select x-model="p.card_id" 
                                                            @change="resolveOffersForSplitCard(index, p.card_id, p.amount)" 
                                                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold focus:border-brand-500 outline-none">
                                                        <option value="">Select Card</option>
                                                        <template x-for="card in cards.filter(c => String(c.settlement_account_id) === String(p.method))" :key="card.id">
                                                            <option :value="card.id" x-text="card.bank_name + ' - ' + card.card_network + ' (' + card.card_type + ')'"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                                
                                                <template x-if="p.card_id && p.offers && p.offers.length > 0">
                                                    <div>
                                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Available Offer</label>
                                                        <select x-model="p.offer_id" 
                                                                @change="recalcTotal()" 
                                                                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold focus:border-brand-500 outline-none">
                                                            <template x-for="offerItem in p.offers" :key="offerItem.offer.id">
                                                                <option :value="offerItem.offer.id" x-text="offerItem.offer.name + ' (Discount: $' + parseFloat(offerItem.discount).toFixed(2) + ')'"></option>
                                                            </template>
                                                        </select>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <button @click="addSplit()" class="w-full py-2 border-2 border-dashed border-slate-200 rounded-xl text-xs font-bold text-slate-400 hover:border-brand-300 hover:text-brand-500 transition-all">+ Add Payment</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer with Amount Status --}}
            <div class="px-8 pb-8 pt-4 bg-white">
                <div class="flex items-center justify-between px-6 py-4 rounded-[1.25rem] mb-6 transition-all duration-300" 
                     :class="paymentDifference > 0 ? 'bg-red-50 border-red-100' : 'bg-emerald-50 border-emerald-100'">
                    <div class="flex items-center gap-3">
                        <div class="w-2.5 h-2.5 rounded-full" :class="paymentDifference > 0 ? 'bg-red-500 animate-pulse' : 'bg-emerald-500'"></div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest leading-none" 
                               :class="paymentDifference > 0 ? 'text-red-400' : 'text-emerald-400'"
                               x-text="paymentDifference > 0 ? 'Balance to Store' : 'Payment Status'"></p>
                            <p class="text-sm font-bold mt-1" 
                               :class="paymentDifference > 0 ? 'text-red-700' : 'text-emerald-700'"
                               x-text="paymentDifference > 0 ? 'Remaining will be saved to credit' : 'Ready to checkout'"></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-bold uppercase opacity-60" 
                           :class="paymentDifference > 0 ? 'text-red-700' : 'text-emerald-700'" 
                           x-text="paymentDifference > 0 ? 'Remaining' : 'Change Due'"></p>
                        <p class="text-2xl font-black font-mono leading-none mt-1" 
                           :class="paymentDifference > 0 ? 'text-red-700' : 'text-emerald-700'" 
                           x-text="'$' + Math.abs(paymentDifference).toFixed(2)"></p>
                    </div>
                </div>

                {{-- Main Buttons --}}
                <div class="flex gap-4">
                    <button @click="showBillingModal = false" 
                            class="px-8 py-4 bg-slate-50 text-slate-500 font-bold rounded-2xl hover:bg-slate-100 hover:text-slate-700 transition-all">
                        Cancel
                    </button>
                    <button @click="confirmOrder()" 
                            :disabled="!canSubmitOrder || isCheckingOut"
                            class="flex-1 checkout-btn text-white font-bold text-lg rounded-2xl disabled:opacity-40 disabled:grayscale transition-all flex items-center justify-center gap-3 py-4">
                        <span x-show="!isCheckingOut">Confirm & Complete</span>
                        <svg x-show="isCheckingOut" class="spin w-6 h-6" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
<script>
function posApp() {
    return {
        // ── State ──
        showCloseRegister: false,
        activeCategory: 'all',
        activeCategoryName: 'All Products',
        searchQuery: '',
        gridView: true,
        gridCols: 4,
        isLoading: false,
        isCheckingOut: false,
        showDiscount: false,
        showBillingModal: false,
        // CHANGE #6: Added new modal states
        showOrderCompleted: false,
        showBillModal: false,
        showTablesModal: false,
        activeTablesList: [],
        loadedOrderId: null,
        loadedTableName: '',
        loadedOrderTotal: 0,
        serviceType: 'retail', // retail (counter), dine_in, takeaway, delivery
        isSplit: true,
        splitPayments: [],
        cards: [],
        cardDetails: {},
        cardOffers: {},
        
        customer: {
            name: '',
            phone: '',
            address: '',
        },
        paymentAccounts: {!! json_encode($paymentAccounts ?? []) !!},
        payments: {},
        useWallet: false,

        get totalPaid() {
            if (this.isSplit) {
                return this.splitPayments.reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0) + this.walletAmount;
            }
            let sum = 0;
            for (let acc of this.paymentAccounts) {
                sum += parseFloat(this.payments[acc.id]) || 0;
            }
            return sum + this.walletAmount;
        },

        get walletAmount() {
            if (!this.useWallet) return 0;
            return Math.min(this.customer.wallet_balance || 0, this.grandTotal);
        },

        recalcDynamicCash(changedAccountId) {
            // Find the primary account (we'll just use the first account in the list)
            if (this.paymentAccounts.length === 0) return;
            let primaryAccountId = this.paymentAccounts[0].id;
            
            if (changedAccountId === primaryAccountId) return; // Don't auto-adjust the one that user just typed in
            
            let others = this.walletAmount;
            for (let acc of this.paymentAccounts) {
                if (acc.id !== primaryAccountId) {
                    others += (parseFloat(this.payments[acc.id]) || 0);
                }
            }
            
            if (others >= this.grandTotal) {
                this.payments[primaryAccountId] = 0;
            } else {
                this.payments[primaryAccountId] = (this.grandTotal - others).toFixed(2);
            }
        },

        recalcCash() {
            if (this.isSplit) {
                if (this.splitPayments.length > 0) {
                    let otherSplitSum = 0;
                    for (let i = 1; i < this.splitPayments.length; i++) {
                        otherSplitSum += parseFloat(this.splitPayments[i].amount) || 0;
                    }
                    let remaining = this.grandTotal - this.walletAmount - otherSplitSum;
                    this.splitPayments[0].amount = Math.max(0, remaining).toFixed(2);
                }
            } else {
                if (this.paymentAccounts.length === 0) return;
                let primaryAccountId = this.paymentAccounts[0].id;
                this.recalcDynamicCash(primaryAccountId);
            }
        },

        async fetchCustomer() {
            if (this.customer.phone.length >= 7) {
                try {
                    let res = await fetch('/pos/customer?phone=' + encodeURIComponent(this.customer.phone));
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

        discountType: 'percent', // 'percent' or 'fixed'
        discountValue: 0,
        couponCode: '',
        appliedCoupon: null,
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
        discountPercent: 0,
        
        currentTime: '',
        toasts: [],
        toastCounter: 0,

        allProducts: {{ Js::from($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'price' => (float) $p->price, 'image' => $p->image ?? null, 'sku' => $p->sku ?? null, 'category_id' => (string) $p->category_id])->values()) }},
        categoryMap: {{ Js::from($categories->pluck('name', 'id')) }},
        cart: (function(raw) {
            // Normalize: if backend returns array or empty, convert to keyed object
            if (!raw || Array.isArray(raw)) return {};
            // If keys are not product IDs (e.g. sequential), re-key by id
            const result = {};
            Object.values(raw).forEach(item => {
                if (item && item.id) result[String(item.id)] = item;
            });
            return result;
        })({{ Js::from($cart) }}),
        filteredProducts: [],

        get cartItems() { return Object.values(this.cart); },
        get totalQty() { return this.cartItems.reduce((sum, i) => sum + i.qty, 0); },
        get cartSubtotal() { return this.cartItems.reduce((sum, i) => sum + (i.price * i.qty), 0); },
        
        get cardDiscount() {
            let cardDiscount = 0;
            if (!this.isSplit) {
                for (const [accountId, amount] of Object.entries(this.payments)) {
                    const details = this.cardDetails[accountId];
                    if (details && details.card_id) {
                        const offers = this.cardOffers[accountId] || [];
                        const selectedOffer = offers.find(o => String(o.offer.id) === String(details.offer_id));
                        cardDiscount += selectedOffer ? parseFloat(selectedOffer.discount) : 0;
                    }
                }
            } else {
                this.splitPayments.forEach(p => {
                    if (p.card_id) {
                        const offers = p.offers || [];
                        const selectedOffer = offers.find(o => String(o.offer.id) === String(p.offer_id));
                        cardDiscount += selectedOffer ? parseFloat(selectedOffer.discount) : 0;
                    }
                });
            }
            return cardDiscount;
        },

        get cardServiceCharge() {
            let cardServiceCharge = 0;
            if (!this.isSplit) {
                for (const [accountId, amount] of Object.entries(this.payments)) {
                    const details = this.cardDetails[accountId];
                    if (details && details.card_id) {
                        const card = this.cards.find(c => String(c.id) === String(details.card_id));
                        if (card) {
                            const offers = this.cardOffers[accountId] || [];
                            const selectedOffer = offers.find(o => String(o.offer.id) === String(details.offer_id));
                            const offAmt = selectedOffer ? parseFloat(selectedOffer.discount) : 0;
                            const taxableBase = Math.max(0, parseFloat(amount) - offAmt);
                            cardServiceCharge += taxableBase * (parseFloat(card.service_charge) / 100);
                        }
                    }
                }
            } else {
                this.splitPayments.forEach(p => {
                    if (p.card_id) {
                        const card = this.cards.find(c => String(c.id) === String(p.card_id));
                        if (card) {
                            const offers = p.offers || [];
                            const selectedOffer = offers.find(o => String(o.offer.id) === String(p.offer_id));
                            const offAmt = selectedOffer ? parseFloat(selectedOffer.discount) : 0;
                            const taxableBase = Math.max(0, parseFloat(p.amount) - offAmt);
                            cardServiceCharge += taxableBase * (parseFloat(card.service_charge) / 100);
                        }
                    }
                });
            }
            return cardServiceCharge;
        },

        get discountAmount() { 
            let total = this.cartSubtotal;
            let manualDiscount = 0;
            let couponDiscount = 0;

            // Manual Discount
            if (this.discountType === 'percent') {
                manualDiscount = total * (parseFloat(this.discountValue) || 0) / 100;
            } else {
                manualDiscount = parseFloat(this.discountValue) || 0;
            }

            // Coupon Discount
            if (this.appliedCoupon) {
                if (this.appliedCoupon.type === 'percent') {
                    couponDiscount = total * (parseFloat(this.appliedCoupon.value) || 0) / 100;
                } else {
                    couponDiscount = parseFloat(this.appliedCoupon.value) || 0;
                }
            }

            return manualDiscount + couponDiscount + this.cardDiscount;
        },

        get taxAmount() { return Math.max(0, this.cartSubtotal - this.discountAmount) * 0.08; },
        get grandTotal() { return Math.max(0, this.cartSubtotal - this.discountAmount + this.taxAmount + this.cardServiceCharge); },

        recalcTotal() {
            // Force redraw/recalculation of computed properties in Alpine
        },

        async init() {
            this.filteredProducts = Array.isArray(this.allProducts) ? [...this.allProducts] : [];
            this.setActiveCategoryName();
            this.startClock();
            await this.fetchCards();
        },

        async fetchCards() {
            try {
                const res = await fetch('/api/cards');
                this.cards = await res.json();
            } catch (e) {
                console.error('Failed to fetch cards', e);
            }
        },

        async resolveOffersForCard(accountId, cardId, amount) {
            if (!this.cardDetails[accountId]) {
                this.cardDetails[accountId] = { card_id: '', offer_id: '' };
            }
            if (!cardId) {
                this.cardDetails[accountId].card_id = '';
                this.cardDetails[accountId].offer_id = '';
                this.cardOffers[accountId] = [];
                return;
            }
            try {
                const res = await fetch('/api/pos/resolve-offers', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        card_id: cardId,
                        subtotal: parseFloat(amount) || 0,
                        cart: Object.values(this.cart),
                        customer_id: this.customer.id || null,
                        branch_id: this.branchId || null
                    })
                });
                const data = await res.json();
                if (data.success && data.offers) {
                    this.cardOffers[accountId] = data.offers;
                    if (data.offers.length > 0) {
                        this.cardDetails[accountId].offer_id = data.offers[0].offer.id;
                    } else {
                        this.cardDetails[accountId].offer_id = '';
                    }
                } else {
                    this.cardOffers[accountId] = [];
                    this.cardDetails[accountId].offer_id = '';
                }
            } catch (e) {
                console.error(e);
                this.cardOffers[accountId] = [];
                this.cardDetails[accountId].offer_id = '';
            }
        },

        async resolveOffersForSplitCard(index, cardId, amount) {
            let p = this.splitPayments[index];
            if (!p) return;
            if (!cardId) {
                p.card_id = '';
                p.offer_id = '';
                p.offers = [];
                return;
            }
            try {
                const res = await fetch('/api/pos/resolve-offers', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        card_id: cardId,
                        subtotal: parseFloat(amount) || 0,
                        cart: Object.values(this.cart),
                        customer_id: this.customer.id || null,
                        branch_id: this.branchId || null
                    })
                });
                const data = await res.json();
                if (data.success && data.offers) {
                    p.offers = data.offers;
                    if (data.offers.length > 0) {
                        p.offer_id = data.offers[0].offer.id;
                    } else {
                        p.offer_id = '';
                    }
                } else {
                    p.offers = [];
                    p.offer_id = '';
                }
            } catch (e) {
                console.error(e);
                p.offers = [];
                p.offer_id = '';
            }
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
            this.discountValue = 0;
            this.appliedCoupon = null;
            this.couponCode = '';
            this.orderNote = '';
            this.showToast('Cart cleared');
            this.syncToBackend('clear', {});
        },
        syncToBackend(action, data) {
            fetch('/pos/cart/' + action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(data)
            }).catch(() => {});
        },

        async fetchActiveTables() {
            this.showTablesModal = true;
            try {
                const res = await fetch('{{ route("pos.active-tables") }}');
                const data = await res.json();
                if (data.success) {
                    this.activeTablesList = data.tables;
                }
            } catch (e) {
                this.showToast('Failed to fetch tables', 'error');
            }
        },

        async loadTableOrder(table) {
            const orderId = table.active_order.id;
            try {
                const res = await fetch('{{ route("pos.load-order") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ order_id: orderId }),
                });
                const data = await res.json();
                if (data.success) {
                    this.cart = data.cart;
                    this.loadedOrderId = data.order.id;
                    this.loadedTableName = table.name;
                    this.loadedOrderTotal = data.order.total_amount;
                    this.customer.name = data.order.customer ? data.order.customer.name : '';
                    this.customer.phone = data.order.customer ? data.order.customer.phone : '';
                    this.discountValue = data.order.discount_value || 0;
                    this.discountType = data.order.discount_type || 'percent';
                    this.orderNote = data.order.note || '';
                    
                    this.showTablesModal = false;
                    this.showToast('Order loaded for ' + table.name);
                    
                    if (this.customer.phone) {
                        this.fetchCustomer();
                    }
                }
            } catch (e) {
                this.showToast('Failed to load order', 'error');
            }
        },

        // Initialize payments when order loads or modal opens
        initPayments() {
            this.payments = {};
            this.cardDetails = {};
            this.cardOffers = {};
            
            // Find cash account or default to the first account
            let cashAcc = this.paymentAccounts.find(acc => acc.account_name.toLowerCase().includes('cash'));
            let defaultAccId = cashAcc ? cashAcc.id : (this.paymentAccounts[0]?.id || '');
            
            if(this.paymentAccounts.length > 0 && defaultAccId) {
                this.payments[defaultAccId] = this.grandTotal.toFixed(2);
            }
            this.isSplit = true;
            if(defaultAccId) {
                this.splitPayments = [{ method: defaultAccId, amount: (this.grandTotal - this.walletAmount).toFixed(2), card_id: '', offer_id: '', offers: [] }];
            } else {
                this.splitPayments = [];
            }
            // Initialize cardDetails for card accounts
            this.paymentAccounts.forEach(acc => {
                if (this.cards.some(c => String(c.settlement_account_id) === String(acc.id))) {
                    this.cardDetails[acc.id] = { card_id: '', offer_id: '' };
                }
            });
        },
        
        checkout() {
            if (this.cartItems.length === 0) {
                this.showToast('Your cart is empty!', 'error');
                return;
            }
            this.showBillingModal = true;
            this.initPayments();
            this.useWallet = false;
        },

        async confirmOrder() {
            if (this.serviceType === 'delivery') {
                if (!this.customer.name || !this.customer.phone || !this.customer.address) {
                    this.showToast('Customer Name, Phone, and Address are required for Delivery', 'error');
                    return;
                }
            } else {
                if (!this.customer.name || !this.customer.phone) {
                    this.showToast('Name and Phone are required', 'error');
                    return;
                }
            }
            
            this.isCheckingOut = true;

            // Snapshot cart items BEFORE clearing, for the bill
            const snapshotItems = [...this.cartItems];
            const snapshotSubtotal = this.cartSubtotal;
            const snapshotDiscount = this.discountAmount;
            const snapshotDiscountPercent = this.discountValue;
            const snapshotTax = this.taxAmount;
            const snapshotTotal = this.grandTotal;
            const snapshotCustomer = { name: this.customer.name, phone: this.customer.phone };

            // Map card_details for single payment mode
            const cardDetailsPayload = {};
            if (!this.isSplit) {
                for (const [accountId, details] of Object.entries(this.cardDetails)) {
                    if (details.card_id) {
                        cardDetailsPayload[accountId] = {
                            card_id: details.card_id,
                            offer_id: details.offer_id || null
                        };
                    }
                }
            }

            // Map split payments, adding card_details to each split payment
            const splitPaymentsPayload = this.splitPayments.map(p => {
                const mapped = {
                    method: p.method,
                    amount: parseFloat(p.amount) || 0
                };
                if (p.card_id) {
                    mapped.card_details = {
                        card_id: p.card_id,
                        offer_id: p.offer_id || null
                    };
                }
                return mapped;
            });

            try {
                const res = await fetch('{{ route("pos.checkout") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        service_type: this.serviceType,
                        discount_percent: this.discountValue,
                        discount_type: this.discountType,
                        subtotal: this.cartSubtotal,
                        tax_amount: this.taxAmount,
                        coupon_id: this.appliedCoupon ? this.appliedCoupon.id : null,
                        note: this.orderNote,
                        total: this.grandTotal,
                        cart: this.cartItems, // cartItems array expected by InventoryService
                        order_id: this.loadedOrderId,
                        customer_name: this.customer.name,
                        customer_phone: this.customer.phone,
                        billing_address: this.customer.address,
                        payment_details: this.payments, 
                        card_details: cardDetailsPayload,
                        use_wallet: this.useWallet, 
                        wallet_amount: this.walletAmount,
                        is_split: this.isSplit,
                        split_payments: splitPaymentsPayload
                    }),
                });

                const data = await res.json();
                
                if (data.success) {
                    this.lastOrderId = data.order_id;
                    this.lastOrderTotal = data.total || snapshotTotal;
                } else {
                    this.showToast(data.message || 'Checkout failed', 'error');
                    this.isCheckingOut = false;
                    return;
                }
            } catch (e) {
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
            this.payments = { cash: 0, card: 0, upi: 0 };
            this.discountValue = 0;
            this.appliedCoupon = null;
            this.couponCode = '';
            this.orderNote = '';
            this.loadedOrderId = null;
            this.loadedTableName = '';
            this.loadedOrderTotal = 0;
            this.loadedTableName = '';

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

        toggleSplit() {
            this.isSplit = !this.isSplit;
            if (this.isSplit && this.splitPayments.length === 0) {
                this.addSplit();
            }
        },

        addSplit() {
            let cashAcc = this.paymentAccounts.find(acc => acc.account_name.toLowerCase().includes('cash'));
            let defaultAccId = cashAcc ? cashAcc.id : (this.paymentAccounts[0]?.id || '');
            this.splitPayments.push({ method: defaultAccId, amount: '0.00', card_id: '', offer_id: '', offers: [] });
        },

        removeSplit(index) {
            this.splitPayments.splice(index, 1);
        },

        async applyCoupon() {
            if (!this.couponCode.trim()) return;
            try {
                const res = await fetch('{{ route("pos.validate-coupon") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ code: this.couponCode.toUpperCase() }),
                });
                const data = await res.json();
                if (data.success) {
                    this.appliedCoupon = data.coupon;
                    this.showToast('Coupon applied: ' + data.coupon.code);
                } else {
                    this.showToast(data.message || 'Invalid coupon', 'error');
                }
            } catch (e) {
                this.showToast('Failed to validate coupon', 'error');
            }
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
</script>

</html>