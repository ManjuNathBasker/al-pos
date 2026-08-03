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

        // Fetch dynamic payment accounts
        $paymentAccounts = \App\Models\Account::where('show_in_pos', true)
                            ->where('company_id', session('company_id'))
                            ->get();

        return view('pos.index', compact('categories', 'products', 'cart', 'paymentAccounts'));
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
        file_put_contents(public_path('checkout_debug.json'), json_encode($request->all(), JSON_PRETTY_PRINT));
        \Log::info('Checkout Payload:', $request->all());
        $request->validate([
            'order_id'         => 'nullable|exists:orders,id',
            'discount_percent' => 'nullable|numeric|min:0',
            'discount_type'    => 'nullable|in:fixed,percent',
            'coupon_id'        => 'nullable|exists:coupons,id',
            'note'             => 'nullable|string|max:500',
            'total'            => 'required|numeric|min:0',
            'customer_phone'   => 'nullable|string',
            'customer_name'    => 'nullable|string',
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

        // Fetch open register session for cash shift mode (if any)
        $openSession = \App\Models\RegisterSession::openForUser(auth()->id())->first();

        DB::beginTransaction();
        try {
            $phone = $request->customer_phone ?: '0000000000';
            $name = $request->customer_name ?: 'Walk-in Customer';
            $customer = Customer::firstOrCreate(
                ['phone' => $phone],
                ['name' => $name, 'wallet_balance' => 0]
            );

            // Track card discounts and service charges
            $totalCardDiscount = 0;
            $totalCardServiceCharge = 0;
            $cardTransactionsData = [];
            $payments = $request->payment_details ?? [];

            // 1. Process Single Card Payments
            if (!$request->is_split) {
                foreach ($payments as $accountId => $amount) {
                    $details = $request->input("card_details.{$accountId}");
                    if ($details && !empty($details['card_id'])) {
                        $amountVal = (float) $amount;
                        if ($amountVal > 0) {
                            $cardTx = $this->resolveAndPrepareCardTransaction($details, $amountVal, $customer, $request);
                            if ($cardTx) {
                                $totalCardDiscount += $cardTx['discount_amount'];
                                $totalCardServiceCharge += $cardTx['service_charge_amount'];
                                $cardTransactionsData[] = $cardTx;
                            }
                        }
                    }
                }
            } 
            // 2. Process Split Card Payments
            else if ($request->is_split && is_array($request->split_payments)) {
                foreach ($request->split_payments as $p) {
                    $details = $p['card_details'] ?? null;
                    if ($details && !empty($details['card_id'])) {
                        $amountVal = (float) $p['amount'];
                        if ($amountVal > 0) {
                            $cardTx = $this->resolveAndPrepareCardTransaction($details, $amountVal, $customer, $request);
                            if ($cardTx) {
                                $totalCardDiscount += $cardTx['discount_amount'];
                                $totalCardServiceCharge += $cardTx['service_charge_amount'];
                                $cardTransactionsData[] = $cardTx;
                            }
                        }
                    }
                }
            }

            // Calculate manual discount amount
            $manualDiscount = 0;
            if ($request->discount_type === 'percent') {
                $manualDiscount = $request->subtotal * (($request->discount_percent ?? 0) / 100);
            } else {
                $manualDiscount = (float) ($request->discount_percent ?? 0);
            }

            // Coupon discount (if coupon used)
            $couponDiscount = 0;
            if ($request->coupon_id) {
                $coupon = Coupon::find($request->coupon_id);
                if ($coupon) {
                    if ($coupon->type === 'percent') {
                        $couponDiscount = $request->subtotal * ($coupon->value / 100);
                    } else {
                        $couponDiscount = $coupon->value;
                    }
                }
            }

            // Total discount on order = Manual + Coupon + Card Offer Discounts
            $totalDiscountAmount = $manualDiscount + $couponDiscount + $totalCardDiscount;
            
            // Adjust tax amount
            $taxableAmount = max(0, $request->subtotal - $totalDiscountAmount);
            $taxAmount = $taxableAmount * 0.08;

            // Final Order Total = Subtotal - Total Discount + Tax + Card Service Charges
            $finalTotalAmount = max(0, $request->subtotal - $totalDiscountAmount + $taxAmount + $totalCardServiceCharge);

            $useWallet = filter_var($request->use_wallet, FILTER_VALIDATE_BOOLEAN);
            $walletUsed = 0;
            
            if ($useWallet) {
                $walletUsed = min($customer->wallet_balance, $finalTotalAmount);
            }

            $totalPaid = $walletUsed;
            $dynamicPayments = [];
            foreach ($payments as $accountId => $amount) {
                $amt = (float) $amount;
                if ($amt > 0) {
                    $dynamicPayments[$accountId] = $amt;
                    $totalPaid += $amt;
                }
            }

            if ($request->is_split && is_array($request->split_payments)) {
                $totalPaid = $walletUsed;
                foreach ($request->split_payments as $p) {
                    $totalPaid += (float) $p['amount'];
                }
            }

            $changeReturned = max(0, $totalPaid - $finalTotalAmount);
            $balanceDue = max(0, $finalTotalAmount - $totalPaid);

            // Create or Update Order
            $orderData = [
                'user_id'          => auth()->id(),
                'customer_id'      => $customer->id,
                'service_type'     => $request->service_type ?? 'retail',
                'delivery_status'  => ($request->service_type === 'delivery') ? 'pending' : null,
                'discount_amount'  => $totalDiscountAmount,
                'discount_type'    => 'fixed',
                'discount_value'   => $totalDiscountAmount,
                'coupon_id'        => $request->coupon_id,
                'note'             => $request->note,
                'subtotal'         => $request->subtotal,
                'tax_amount'       => $taxAmount,
                'total_amount'     => $finalTotalAmount,
                'wallet_used'      => $walletUsed,
                'change_returned'  => $changeReturned,
                'total_paid'       => $totalPaid,
                'status'           => 'paid',
                'register_session_id' => $openSession ? $openSession->id : null,
            ];

            if ($request->order_id) {
                $order = Order::findOrFail($request->order_id);
                $order->update($orderData);
                
                // Removed: Do not automatically free the table or mark KOT as served here.
                // The waiter panel's "Complete Order" action will handle that.

                // Clear existing items and re-add from cart to ensure consistency (except for dine-in to preserve KDS tracking)
                if ($order->service_type !== 'dine_in') {
                    $order->items()->delete();
                }
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
                        'payment_method' => (string) $p['method'], // account_id as string
                        'amount' => $p['amount'],
                    ]);
                }
                // Also record wallet payment for split orders
                if ($walletUsed > 0) {
                    $order->payments()->create([
                        'company_id' => $order->company_id,
                        'payment_method' => 'wallet',
                        'amount' => $walletUsed,
                    ]);
                }
            } else {
                // Dynamic regular payments
                foreach ($dynamicPayments as $accountId => $amount) {
                    $order->payments()->create([
                        'company_id' => $order->company_id,
                        'payment_method' => (string) $accountId,
                        'amount' => $amount,
                    ]);
                }
                if ($walletUsed > 0) {
                    $order->payments()->create([
                        'company_id' => $order->company_id, 
                        'payment_method' => 'wallet', 
                        'amount' => $walletUsed
                    ]);
                }
            }

            // Save card transactions and link to order
            foreach ($cardTransactionsData as $txData) {
                $txData['order_id'] = $order->id;
                $txData['company_id'] = $order->company_id;
                \App\Models\CardTransaction::create($txData);
            }

            // If coupon used, increment count
            if ($request->coupon_id) {
                Coupon::find($request->coupon_id)->increment('used_count');
            }

            // Save items
            if (!$request->order_id || $order->service_type !== 'dine_in') {
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

            // Generate Accounting Entries
            app(\App\Services\AccountingService::class)->recordSale($order);

            // ── Cash Transactions Logic (For Cash Shifts) ──
            if ($openSession) {
                $totalCashCollected = 0;
                
                // For dynamic payments
                if (!$request->is_split) {
                    foreach ($dynamicPayments as $accountId => $amount) {
                        $acc = \App\Models\Account::find($accountId);
                        if ($acc && stripos($acc->account_name, 'cash') !== false) {
                            $totalCashCollected += (float) $amount;
                        }
                    }
                } else if (is_array($request->split_payments)) {
                    // For split payments
                    foreach ($request->split_payments as $p) {
                        if (is_numeric($p['method'])) {
                            $acc = \App\Models\Account::find($p['method']);
                            if ($acc && stripos($acc->account_name, 'cash') !== false) {
                                $totalCashCollected += (float) $p['amount'];
                            }
                        } else if (strtolower($p['method']) === 'cash') {
                            $totalCashCollected += (float) $p['amount'];
                        }
                    }
                }
                
                // Adjust for change returned (deducts physical cash given back)
                if ($changeReturned > 0) {
                    // If the change returned was essentially given from cash drawer
                    $totalCashCollected -= $changeReturned;
                }
                
                if ($totalCashCollected > 0) {
                    \App\Models\CashTransaction::create([
                        'register_session_id' => $openSession->id,
                        'type' => 'CASH_SALE',
                        'amount' => $totalCashCollected,
                        'payment_method' => 'Cash',
                        'description' => 'Payment for Order ' . $order->order_number,
                        'created_by' => auth()->id(),
                    ]);
                } else if ($totalCashCollected < 0) {
                    // In a rare scenario where change given is more than cash received (due to wallet split issue etc.)
                    \App\Models\CashTransaction::create([
                        'register_session_id' => $openSession->id,
                        'type' => 'CASH_REFUND',
                        'amount' => abs($totalCashCollected),
                        'payment_method' => 'Cash',
                        'description' => 'Change returned for Order ' . $order->order_number,
                        'created_by' => auth()->id(),
                    ]);
                }
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

    private function resolveAndPrepareCardTransaction($details, $amount, $customer, $request)
    {
        $cardId = $details['card_id'];
        $card = \App\Models\Card::find($cardId);
        if (!$card) return null;

        $discount = 0;
        $cashback = 0;
        $merchantShare = 0;
        $bankShare = 0;
        $offerId = null;

        // If offer is specified, resolve it
        if (!empty($details['offer_id'])) {
            $offerService = app(\App\Services\BankOfferService::class);
            $cart = $request->input('cart') ?? [];
            $branchId = $request->input('branch_id');
            
            $eligibleOffers = $offerService->getEligibleOffers(
                $cardId,
                $amount,
                $cart,
                $customer->id,
                $branchId
            );

            // Find the selected offer in the eligible ones
            $selected = collect($eligibleOffers)->first(fn($o) => $o['offer']->id == $details['offer_id']);
            if ($selected) {
                $discount = (float) $selected['discount'];
                $cashback = (float) $selected['cashback'];
                $merchantShare = (float) $selected['merchant_share'];
                $bankShare = (float) $selected['bank_share'];
                $offerId = $selected['offer']->id;

                // Increment used count
                $selected['offer']->increment('used_count');
            }
        }

        // Calculate service charge on taxable base (amount after discount)
        $taxableBase = max(0, $amount - $discount);
        $serviceCharge = $taxableBase * ($card->service_charge / 100);
        $processingFee = (float) $card->processing_fee;

        // MDR is calculated on the swiped amount (taxableBase + serviceCharge)
        $mdrAmount = ($taxableBase + $serviceCharge) * ($card->mdr / 100);

        // Net Settlement = Swipe Amount + Bank Discount Share - MDR - Processing Fee
        $netSettlement = ($taxableBase + $serviceCharge) + $bankShare - $mdrAmount - $processingFee;

        return [
            'card_id'                 => $card->id,
            'customer_id'             => $customer->id,
            'branch_id'               => $request->input('branch_id'),
            'bank_name'               => $card->bank_name,
            'card_network'            => $card->card_network,
            'card_type'               => $card->card_type,
            'gross_amount'            => $amount,
            'discount_amount'         => $discount,
            'cashback_amount'         => $cashback,
            'service_charge_amount'   => $serviceCharge,
            'processing_fee_amount'   => $processingFee,
            'merchant_discount_share' => $merchantShare,
            'bank_discount_share'     => $bankShare,
            'net_settlement_amount'   => $netSettlement,
            'settlement_days'         => $card->settlement_days,
            'settlement_status'       => 'pending',
            'bank_offer_id'           => $offerId,
        ];
    }
}
