<?php
$file = 'resources/views/pos/index.blade.php';
$content = file_get_contents($file);

// 1. Phone input debounce
$content = str_replace(
    'x-model="customer.phone"',
    'x-model="customer.phone" @input.debounce.500ms="fetchCustomer"',
    $content
);

// 2. Add Wallet UI & change QR to UPI
$paymentHtml = <<<HTML
                        {{-- Payment Methods --}}
                        <div class="border-t border-slate-100 pt-4">
                            <h3 class="text-sm font-semibold text-slate-700 mb-3">Payment Method</h3>

                            <!-- Wallet UI -->
                            <div class="mb-4 bg-slate-50 border border-slate-200 rounded-xl p-4 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-slate-700">Wallet Balance</p>
                                    <p class="text-xs text-slate-500" x-text="'Available: $' + (customer.wallet_balance || 0).toFixed(2)"></p>
                                </div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="useWallet" @change="recalcCash" class="w-5 h-5 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                    <span class="text-sm font-semibold text-slate-700">Use Wallet</span>
                                </label>
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm text-slate-600 mb-1">Cash</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">$</span>
                                        <input type="number" x-model="payments.cash" step="0.01" 
                                               class="w-full pl-9 pr-4 py-3 bg-white border-2 border-slate-100 rounded-2xl focus:border-brand-500 outline-none font-mono font-bold text-slate-700 shadow-sm" />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm text-slate-600 mb-1">UPI</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">$</span>
                                        <input type="number" x-model="payments.upi" @input="recalcCash" step="0.01" 
                                               class="w-full pl-9 pr-4 py-3 bg-white border-2 border-slate-100 rounded-2xl focus:border-brand-500 outline-none font-mono font-bold text-slate-700 shadow-sm" />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm text-slate-600 mb-1">Card</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">$</span>
                                        <input type="number" x-model="payments.card" @input="recalcCash" step="0.01" 
                                               class="w-full pl-9 pr-4 py-3 bg-white border-2 border-slate-100 rounded-2xl focus:border-brand-500 outline-none font-mono font-bold text-slate-700 shadow-sm" />
                                    </div>
                                </div>
                            </div>
                        </div>
HTML;

$content = preg_replace(
    "/\{\{-- Payment Methods --\}\}.*?<\/div>\s*<\/div>\s*<\/div>\s*<\/div>/s",
    $paymentHtml . "\n                    </div>\n                </div>\n            </div>",
    $content
);

// 3. Update checkout() method to default cash
$content = preg_replace(
    "/checkout\(\) \{[^\}]*return;\s*\}/s",
    "checkout() {
            if (this.cartItems.length === 0) {
                this.showToast('Your cart is empty!', 'error');
                return;
            }",
    $content
);

$content = str_replace(
    "this.showBillingModal = true;",
    "this.showBillingModal = true;\n            this.payments.cash = this.grandTotal.toFixed(2);\n            this.payments.upi = 0;\n            this.payments.card = 0;\n            this.useWallet = false;",
    $content
);

// 4. Update confirmOrder payload
$content = str_replace(
    "payment_details: this.payments",
    "payment_details: this.payments, use_wallet: this.useWallet, wallet_amount: this.walletAmount",
    $content
);

file_put_contents($file, $content);
