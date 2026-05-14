<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\Customer;
use App\Models\WalletTransaction;
use App\Models\OrderItem;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    //  Helper: build the cart array from the session.
    //  Structure stored in session:
    //    cart = [ product_id => [ id, name, price, image, sku, qty ] ]
    // ─────────────────────────────────────────────────────────────
    private function getCart(): array
    {
        return session('pos_cart', []);
    }

    private function saveCart(array $cart): void
    {
        session(['pos_cart' => $cart]);
    }

    // ─────────────────────────────────────────────────────────────
    //  GET /pos  — main POS page
    // ─────────────────────────────────────────────────────────────
    public function index()
    {
        $categories = Category::orderBy('name')->get();

        // Get all active products, regardless of category
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        $cart = $this->getCart();

        return view('pos.index', compact('categories', 'products', 'cart'));
    }

    // ─────────────────────────────────────────────────────────────
    //  POST /pos/cart/add
    //  Body: { product_id: int }
    //  Returns: { success: true, cart: {...}, message: string }
    // ─────────────────────────────────────────────────────────────
    public function cartAdd(Request $request)
    {
        $request->validate(['product_id' => 'required|integer|exists:products,id']);

        $productId = (string) $request->product_id;
        $product   = Product::findOrFail($request->product_id);
        $cart      = $this->getCart();

        if (isset($cart[$productId])) {
            // Already in cart — just bump quantity
            $cart[$productId]['qty']++;
        } else {
            $cart[$productId] = [
                'id'    => $product->id,
                'name'  => $product->name,
                'price' => (float) $product->price,
                'image' => $product->image ?? null,
                'sku'   => $product->sku   ?? null,
                'qty'   => 1,
            ];
        }

        $this->saveCart($cart);

        return response()->json([
            'success' => true,
            'cart'    => $cart,
            'message' => $product->name . ' added to cart',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  POST /pos/cart/update
    //  Body: { product_id: int, action: 'increment'|'decrement' }
    //  Returns: { success: true, cart: {...} }
    // ─────────────────────────────────────────────────────────────
    public function cartUpdate(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'action'     => 'required|in:increment,decrement',
        ]);

        $productId = (string) $request->product_id;
        $cart      = $this->getCart();

        if (!isset($cart[$productId])) {
            return response()->json(['success' => false, 'message' => 'Item not in cart'], 404);
        }

        if ($request->action === 'increment') {
            $cart[$productId]['qty']++;
        } else {
            $cart[$productId]['qty']--;
            if ($cart[$productId]['qty'] <= 0) {
                unset($cart[$productId]);
            }
        }

        $this->saveCart($cart);

        return response()->json(['success' => true, 'cart' => $cart]);
    }

    // ─────────────────────────────────────────────────────────────
    //  POST /pos/cart/remove
    //  Body: { product_id: int }
    //  Returns: { success: true, cart: {...}, message: string }
    // ─────────────────────────────────────────────────────────────
    public function cartRemove(Request $request)
    {
        $request->validate(['product_id' => 'required|integer']);

        $productId = (string) $request->product_id;
        $cart      = $this->getCart();

        $name = $cart[$productId]['name'] ?? 'Item';
        unset($cart[$productId]);

        $this->saveCart($cart);

        return response()->json([
            'success' => true,
            'cart'    => $cart,
            'message' => $name . ' removed',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  POST /pos/cart/clear
    //  Returns: { success: true, cart: {} }
    // ─────────────────────────────────────────────────────────────
    public function cartClear()
    {
        $this->saveCart([]);

        return response()->json(['success' => true, 'cart' => []]);
    }

    // ─────────────────────────────────────────────────────────────
    //  POST /pos/checkout
    //  Body: { discount_percent: float, note: string, total: float }
    //  Returns: { success: true, order_id: string, total: float }
    // ─────────────────────────────────────────────────────────────
        public function customer(Request $request)
    {
        $phone = $request->query('phone');
        if (!$phone) return response()->json(['success' => false]);
        $customer = Customer::where('phone', $phone)->first();
        if ($customer) {
            return response()->json(['success' => true, 'customer' => $customer]);
        }
        return response()->json(['success' => false]);
    }

        public function validateCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $coupon = Coupon::where('code', $request->code)->first();

        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid coupon code.'], 404);
        }

        if (!$coupon->isValid()) {
            return response()->json(['success' => false, 'message' => 'Coupon is expired or limit reached.'], 422);
        }

        return response()->json([
            'success' => true,
            'coupon'  => [
                'id'    => $coupon->id,
                'code'  => $coupon->code,
                'type'  => $coupon->type,
                'value' => $coupon->value,
            ]
        ]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'discount_percent' => 'nullable|numeric|min:0',
            'discount_type'    => 'nullable|in:fixed,percent',
            'coupon_id'        => 'nullable|exists:coupons,id',
            'note'             => 'nullable|string|max:500',
            'total'            => 'required|numeric|min:0',
            'customer_phone'   => 'required|string',
            'customer_name'    => 'required|string',
        ]);

        $cart = $request->input('cart');
        if (empty($cart) || !is_array($cart)) {
            $cart = $this->getCart();
        }

        if (empty($cart)) {
            return response()->json(['success' => false, 'message' => 'Cart is empty'], 422);
        }

        DB::beginTransaction();
        try {
            // Find or create customer
            $customer = Customer::firstOrCreate(
                ['phone' => $request->customer_phone],
                ['name' => $request->customer_name, 'wallet_balance' => 0]
            );

            // Payment logic
            $payments = $request->payment_details ?? ['cash' => 0, 'card' => 0, 'upi' => 0];
            $cash = (float) ($payments['cash'] ?? 0);
            $card = (float) ($payments['card'] ?? 0);
            $upi = (float) ($payments['upi'] ?? 0);
            
            $useWallet = filter_var($request->use_wallet, FILTER_VALIDATE_BOOLEAN);
            $walletUsed = 0;
            
            if ($useWallet) {
                $walletUsed = min($customer->wallet_balance, $request->total);
            }

            $totalPaid = $cash + $card + $upi + $walletUsed;
            $changeReturned = max(0, $totalPaid - $request->total);
            $balanceDue = max(0, $request->total - $totalPaid);

            // Create Order
            $order = Order::create([
                'user_id'          => auth()->id(),
                'customer_id'      => $customer->id,
                'discount_amount'  => $request->discount_percent ?? 0,
                'discount_type'    => $request->discount_type ?? 'percent',
                'discount_value'   => $request->discount_percent ?? 0,
                'coupon_id'        => $request->coupon_id,
                'note'             => $request->note,
                'subtotal'         => $request->subtotal,
                'tax_amount'       => $request->tax_amount,
                'total_amount'     => $request->total,
                'cash_amount'      => $cash,
                'upi_amount'       => $upi,
                'card_amount'      => $card,
                'wallet_used'      => $walletUsed,
                'change_returned'  => $changeReturned,
                'total_paid'       => $totalPaid,
                'status'           => 'paid',
            ]);

            // If coupon used, increment count
            if ($request->coupon_id) {
                Coupon::find($request->coupon_id)->increment('used_count');
            }

            // Save items
            foreach ($cart as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item['id'],
                    'product_name'    => $item['name'],
                    'unit_price'      => $item['price'],
                    'quantity'        => $item['qty'],
                    'subtotal'   => $item['price'] * $item['qty'],
                ]);
            }

            // Wallet Transactions
            if ($walletUsed > 0) {
                $customer->decrement('wallet_balance', $walletUsed);
                WalletTransaction::create([
                    'customer_id' => $customer->id,
                    'order_id'    => $order->id,
                    'amount'      => $walletUsed,
                    'type'        => 'debit',
                    'description' => 'Applied to Order #' . $order->id,
                ]);
            }

            if ($changeReturned > 0) {
                // Add extra to wallet
                $customer->increment('wallet_balance', $changeReturned);
                WalletTransaction::create([
                    'customer_id' => $customer->id,
                    'order_id'    => $order->id,
                    'amount'      => $changeReturned,
                    'type'        => 'credit',
                    'description' => 'Change from Order #' . $order->id,
                ]);
            } elseif ($balanceDue > 0) {
                // Negative wallet (store credit debt)
                $customer->decrement('wallet_balance', $balanceDue);
                WalletTransaction::create([
                    'customer_id' => $customer->id,
                    'order_id'    => $order->id,
                    'amount'      => $balanceDue,
                    'type'        => 'debit',
                    'description' => 'Balance due for Order #' . $order->id,
                ]);
            }

            $this->saveCart([]);
            DB::commit();

            return response()->json([
                'success'  => true,
                'order_id' => '#' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                'total'    => $request->total,
                'customer' => [
                    'name' => $customer->name,
                    'wallet_balance' => $customer->wallet_balance
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Order failed: ' . $e->getMessage()], 500);
        }
    }
}
