<?php
$file = 'resources/views/pos/index.blade.php';
$content = file_get_contents($file);

// Replace Alpine state
$content = preg_replace(
    "/(customer:\s*\{[^}]*\},)\s*payments:\s*\{\s*cash:\s*0,\s*card:\s*0,\s*qr:\s*0\s*\}/s",
    "$1\n        payments: { cash: 0, card: 0, upi: 0 },\n        useWallet: false,",
    $content
);

// Replace get totalPaid
$content = preg_replace(
    "/get totalPaid\(\) \{[^\}]*\}/s",
    "get totalPaid() {
            let cash = parseFloat(this.payments.cash) || 0;
            let card = parseFloat(this.payments.card) || 0;
            let upi = parseFloat(this.payments.upi) || 0;
            return cash + card + upi + this.walletAmount;
        }",
    $content
);

// Add missing getters & methods
$content = preg_replace(
    "/(get paymentDifference\(\) \{)/",
    "get walletAmount() {
            if (!this.useWallet) return 0;
            return Math.min(this.customer.wallet_balance || 0, this.grandTotal);
        }

        recalcCash() {
            let others = (parseFloat(this.payments.upi) || 0) + (parseFloat(this.payments.card) || 0) + this.walletAmount;
            if (others >= this.grandTotal) {
                this.payments.cash = 0;
            } else {
                this.payments.cash = (this.grandTotal - others).toFixed(2);
            }
        }

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
        }

        $1",
    $content
);

file_put_contents($file, $content);
