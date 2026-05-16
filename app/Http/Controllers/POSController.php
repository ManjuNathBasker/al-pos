<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\Customer;
use App\Models\WalletTransaction;
use App\Models\OrderItem;
use App\Models\Coupon;
use App\Models\Payment;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\InventoryService;

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
    //  GET /pos/active-tables — list tables with active orders
    // ─────────────────────────────────────────────────────────────
    public function activeTables()
    {
        $tables = \App\Models\RestaurantTable::where('status', 'occupied')
            ->with(['activeOrder' => function($q) {
                $q->with('items');
            }, 'section'])
            ->get();

        return response()->json([
            'success' => true,
            'tables' => $tables
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  POST /pos/load-order — load existing order into cart
    // ─────────────────────────────────────────────────────────────
    public function loadOrder(Request $request)
    {
        $request->validate(['order_id' => 'required|exists:orders,id']);
        
        $order = Order::with('items.product', 'customer')->findOrFail($request->order_id);
        
        // Convert order items to session cart format
        $cart = [];
        foreach ($order->items as $item) {
            $productId = (string) $item->product_id;
            if (isset($cart[$productId])) {
                $cart[$productId]['qty'] += $item->quantity;
            } else {
                $cart[$productId] = [
                    'id'    => $item->product_id,
                    'name'  => $item->product_name,
                    'price' => (float) $item->unit_price,
                    'image' => $item->product->image ?? null,
                    'sku'   => $item->product->sku   ?? null,
                    'qty'   => $item->quantity,
                ];
            }
        }

        $this->saveCart($cart);

        return response()->json([
            'success' => true,
            'cart' => $cart,
            'order' => [
                'id' => $order->id,
                'customer' => $order->customer,
                'discount_value' => $order->discount_value,
                'discount_type' => $order->discount_type,
                'note' => $order->note,
                'total_amount' => $order->total_amount,
            ]
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  POST /pos/checkout
    //  Body: { discount_percent: float, note: string, total: float, order_id: int|null }
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
            'order_id'         => 'nullable|exists:orders,id',
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

        // Validate Inventory Stock
        $inventoryService = new InventoryService();
        $stockValidation = $inventoryService->validateStockAvailability($cart);
        if (!$stockValidation['success']) {
            return response()->json(['success' => false, 'message' => $stockValidation['message']], 422);
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

            // Create or Update Order
            $orderData = [
                'user_id'          => auth()->id(),
                'customer_id'      => $customer->id,
                'service_type'     => $request->service_type ?? 'retail',
                'delivery_status'  => ($request->service_type === 'delivery') ? 'pending' : null,
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
            ];

            if ($request->order_id) {
                $order = Order::findOrFail($request->order_id);
                $order->update($orderData);
                
                // If it was a dine-in order, free the table
                if ($order->table_id) {
                    \App\Models\RestaurantTable::where('id', $order->table_id)->update(['status' => 'available']);
                }

                // Mark all kitchen tickets as served if they weren't
                \App\Models\KitchenTicket::where('order_id', $order->id)
                    ->where('status', '!=', 'served')
                    ->each(function($ticket) {
                        $ticket->update(['status' => 'served']);
                        // Only update items if the status is valid for them
                        foreach($ticket->items as $item) {
                            if ($item->orderItem) {
                                $item->orderItem->update(['kitchen_status' => 'served']);
                            }
                            // KitchenTicketItem status enum only allows pending/preparing/ready
                            // So we don't update its status to 'served' to avoid DB errors
                        }
                    });

                // Clear existing items and re-add from cart to ensure consistency
                $order->items()->delete();
            } else {
                if (empty($orderData['order_number'])) {
                    $orderData['order_number'] = 'ORD-' . str_pad((Order::withTrashed()->max('id') ?? 0) + 1, 5, '0', STR_PAD_LEFT);
                }
                $order = Order::create($orderData);
            }

            // Split Billing / Granular Payments
            if ($request->is_split && is_array($request->split_payments)) {
                foreach ($request->split_payments as $p) {
                    $order->payments()->create([
                        'company_id' => $order->company_id,
                        'payment_method' => $p['method'],
                        'amount' => $p['amount'],
                    ]);
                }
            } else {
                // Regular payments
                if ($cash > 0) $order->payments()->create(['company_id' => $order->company_id, 'payment_method' => 'cash', 'amount' => $cash]);
                if ($card > 0) $order->payments()->create(['company_id' => $order->company_id, 'payment_method' => 'card', 'amount' => $card]);
                if ($upi > 0) $order->payments()->create(['company_id' => $order->company_id, 'payment_method' => 'upi', 'amount' => $upi]);
                if ($walletUsed > 0) $order->payments()->create(['company_id' => $order->company_id, 'payment_method' => 'wallet', 'amount' => $walletUsed]);
            }

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

            // If it's a new Takeaway or Delivery order in Restaurant Mode, send to Kitchen
            $company = Company::find(session('company_id'));
            if (!$request->order_id && $company->isModuleEnabled('restaurant_mode') && in_array($order->service_type, ['takeaway', 'delivery'])) {
                $ticket = \App\Models\KitchenTicket::create([
                    'order_id'      => $order->id,
                    'company_id'    => $company->id,
                    'ticket_number' => 'KOT-' . rand(100, 999),
                    'status'        => 'pending',
                ]);

                foreach ($order->items as $orderItem) {
                    \App\Models\KitchenTicketItem::create([
                        'kitchen_ticket_id' => $ticket->id,
                        'order_item_id'     => $orderItem->id,
                        'product_name'      => $orderItem->product_name,
                        'quantity'          => $orderItem->quantity,
                        'status'            => 'pending',
                    ]);
                }
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

            // Deduct Inventory Stock
            $inventoryService->deductStockFromOrder($order);

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
