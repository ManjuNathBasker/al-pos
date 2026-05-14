<?php
$file = 'app/Http/Controllers/POSController.php';
$content = file_get_contents($file);

// Add use statements
$content = str_replace(
    "use App\Models\Order;",
    "use App\Models\Order;\nuse App\Models\Customer;\nuse App\Models\WalletTransaction;",
    $content
);

// Add customer fetch route
$customerMethod = <<<PHP
    public function customer(Request \$request)
    {
        \$phone = \$request->query('phone');
        if (!\$phone) return response()->json(['success' => false]);
        \$customer = Customer::where('phone', \$phone)->first();
        if (\$customer) {
            return response()->json(['success' => true, 'customer' => \$customer]);
        }
        return response()->json(['success' => false]);
    }
PHP;
$content = preg_replace('/public function checkout/s', $customerMethod . "\n\n    public function checkout", $content);

// Update checkout method
$checkoutBody = <<<PHP
    public function checkout(Request \$request)
    {
        \$request->validate([
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'note'             => 'nullable|string|max:500',
            'total'            => 'required|numeric|min:0',
            'customer_phone'   => 'required|string',
            'customer_name'    => 'required|string',
        ]);

        \$cart = \$request->input('cart');
        if (empty(\$cart) || !is_array(\$cart)) {
            \$cart = \$this->getCart();
        }

        if (empty(\$cart)) {
            return response()->json(['success' => false, 'message' => 'Cart is empty'], 422);
        }

        DB::beginTransaction();
        try {
            // Find or create customer
            \$customer = Customer::firstOrCreate(
                ['phone' => \$request->customer_phone],
                ['name' => \$request->customer_name, 'wallet_balance' => 0]
            );

            // Payment logic
            \$payments = \$request->payment_details ?? ['cash' => 0, 'card' => 0, 'upi' => 0];
            \$cash = (float) (\$payments['cash'] ?? 0);
            \$card = (float) (\$payments['card'] ?? 0);
            \$upi = (float) (\$payments['upi'] ?? 0);
            
            \$useWallet = filter_var(\$request->use_wallet, FILTER_VALIDATE_BOOLEAN);
            \$walletUsed = 0;
            
            if (\$useWallet) {
                \$walletUsed = min(\$customer->wallet_balance, \$request->total);
            }

            \$totalPaid = \$cash + \$card + \$upi + \$walletUsed;
            \$changeReturned = max(0, \$totalPaid - \$request->total);
            \$balanceDue = max(0, \$request->total - \$totalPaid);

            // Create Order
            \$order = Order::create([
                'user_id'          => auth()->id(),
                'customer_id'      => \$customer->id,
                'discount_amount'  => \$request->discount_percent ?? 0,
                'note'             => \$request->note,
                'total_amount'     => \$request->total,
                'cash_amount'      => \$cash,
                'upi_amount'       => \$upi,
                'card_amount'      => \$card,
                'wallet_used'      => \$walletUsed,
                'change_returned'  => \$changeReturned,
                'total_paid'       => \$totalPaid,
                'status'           => 'paid', // Assuming paid for simplicity, could be 'pending' if balanceDue > 0
            ]);

            // Save items
            foreach (\$cart as \$item) {
                OrderItem::create([
                    'order_id'   => \$order->id,
                    'product_id' => \$item['id'],
                    'product_name'    => \$item['name'],
                    'unit_price'      => \$item['price'],
                    'quantity'        => \$item['qty'],
                    'subtotal'   => \$item['price'] * \$item['qty'],
                ]);
            }

            // Wallet Transactions
            if (\$walletUsed > 0) {
                \$customer->decrement('wallet_balance', \$walletUsed);
                WalletTransaction::create([
                    'customer_id' => \$customer->id,
                    'order_id'    => \$order->id,
                    'amount'      => \$walletUsed,
                    'type'        => 'debit',
                    'description' => 'Applied to Order #' . \$order->id,
                ]);
            }

            if (\$changeReturned > 0) {
                // Add extra to wallet
                \$customer->increment('wallet_balance', \$changeReturned);
                WalletTransaction::create([
                    'customer_id' => \$customer->id,
                    'order_id'    => \$order->id,
                    'amount'      => \$changeReturned,
                    'type'        => 'credit',
                    'description' => 'Change from Order #' . \$order->id,
                ]);
            } elseif (\$balanceDue > 0) {
                // Negative wallet (store credit debt)
                \$customer->decrement('wallet_balance', \$balanceDue);
                WalletTransaction::create([
                    'customer_id' => \$customer->id,
                    'order_id'    => \$order->id,
                    'amount'      => \$balanceDue,
                    'type'        => 'debit',
                    'description' => 'Balance due for Order #' . \$order->id,
                ]);
            }

            \$this->saveCart([]);
            DB::commit();

            return response()->json([
                'success'  => true,
                'order_id' => '#' . str_pad(\$order->id, 5, '0', STR_PAD_LEFT),
                'total'    => \$request->total,
                'customer' => [
                    'name' => \$customer->name,
                    'wallet_balance' => \$customer->wallet_balance
                ]
            ]);

        } catch (\Exception \$e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Order failed: ' . \$e->getMessage()], 500);
        }
    }
PHP;

$content = preg_replace('/public function checkout\(Request \$request\).*?\n    \}/s', $checkoutBody, $content);

file_put_contents($file, $content);
