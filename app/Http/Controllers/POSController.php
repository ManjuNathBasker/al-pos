<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CardType;
use App\Models\Category;
use App\Models\Company;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\RestaurantTable;
use App\Models\WalletTransaction;
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

        // Pass card account IDs and card types to POS
        $cardAccountIds = $paymentAccounts->where('is_card_account', true)->pluck('id');
        $cardTypes = CardType::where('status', true)->orderBy('name')->get();

        // Load company and settings
        $company = Company::find(session('company_id'));
        $cardCommissionTax = $company ? $company->getCardCommissionTax() : 0;
        $companyTaxPercentage = $company ? $company->getTaxPercentage() : 8.0;
        $currencyConfig = $company ? $company->getCurrencyConfig() : default_currency_config();

        // Load delivery partners
        $deliveryPartners = \App\Models\DeliveryPartner::where('company_id', session('company_id'))
            ->where('status', true)
            ->get();

        return view('pos.index', compact(
            'categories', 'products', 'cart', 'paymentAccounts',
            'cardAccountIds', 'cardTypes', 'cardCommissionTax', 'companyTaxPercentage', 'currencyConfig', 'deliveryPartners'
        ));
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
    //  GET /pos/active-tables — list all tables with active orders
    // ─────────────────────────────────────────────────────────────
    public function activeTables()
    {
        $companyId = session('company_id');
        $query = \App\Models\RestaurantTable::query();

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $tables = $query->with(['activeOrder' => function($q) {
                $q->with('items', 'customer');
            }, 'section'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'tables'  => $tables
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  POST /pos/save-table-order — Save active table order & send KOT
    // ─────────────────────────────────────────────────────────────
    public function saveTableOrder(Request $request)
    {
        $request->validate([
            'table_id' => 'required|exists:restaurant_tables,id',
            'cart'     => 'required|array|min:1',
        ]);

        $companyId = session('company_id');
        $table = RestaurantTable::findOrFail($request->table_id);

        // Resolve customer if provided
        $customerId = null;
        if (!empty($request->customer_name) || !empty($request->customer_phone)) {
            $phone = !empty($request->customer_phone) ? $request->customer_phone : '0000000000';
            $customer = Customer::firstOrCreate(
                ['phone' => $phone, 'company_id' => $companyId],
                ['name' => $request->customer_name ?: 'Dine-in Customer', 'wallet_balance' => 0]
            );
            $customerId = $customer->id;
        }

        DB::beginTransaction();
        try {
            $order = null;
            if ($request->order_id) {
                $order = Order::find($request->order_id);
            }
            if (!$order) {
                $order = Order::where('table_id', $table->id)
                    ->whereNotIn('status', ['completed', 'closed', 'cancelled'])
                    ->first();
            }

            $cart = $request->cart;
            $subtotal = 0;
            foreach ($cart as $item) {
                $subtotal += ($item['price'] * $item['qty']);
            }
            $taxAmount = $subtotal * 0.18;
            $totalAmount = $subtotal + $taxAmount;

            if ($order) {
                $order->update([
                    'table_id'       => $table->id,
                    'service_type'   => 'dine_in',
                    'customer_id'    => $customerId ?: $order->customer_id,
                    'user_id'        => auth()->id(),
                    'waiter_id'      => auth()->id(),
                    'subtotal'       => $subtotal,
                    'tax_amount'     => $taxAmount,
                    'total_amount'   => $totalAmount,
                    'kitchen_status' => 'pending',
                    'status'         => 'pending',
                ]);
                $order->items()->delete();
            } else {
                $maxId = (Order::withTrashed()->where('company_id', $companyId)->max('id') ?? 0) + 1;
                $orderNumber = 'ORD-' . str_pad($maxId, 5, '0', STR_PAD_LEFT);

                $order = Order::create([
                    'company_id'     => $companyId,
                    'order_number'   => $orderNumber,
                    'service_type'   => 'dine_in',
                    'table_id'       => $table->id,
                    'customer_id'    => $customerId,
                    'user_id'        => auth()->id(),
                    'waiter_id'      => auth()->id(),
                    'subtotal'       => $subtotal,
                    'tax_amount'     => $taxAmount,
                    'total_amount'   => $totalAmount,
                    'status'         => 'pending',
                    'kitchen_status' => 'pending',
                ]);
            }

            foreach ($cart as $item) {
                $product = Product::find($item['id']);
                OrderItem::create([
                    'company_id'     => $companyId,
                    'order_id'       => $order->id,
                    'product_id'     => $item['id'],
                    'product_name'   => $item['name'],
                    'unit_price'     => $item['price'],
                    'quantity'       => $item['qty'],
                    'subtotal'       => $item['price'] * $item['qty'],
                    'kitchen_status' => 'pending',
                ]);
            }

            // Update table status to occupied
            $table->update([
                'status'         => 'occupied',
                'customer_name'  => $request->customer_name ?: null,
                'customer_phone' => !empty($request->customer_phone) ? $request->customer_phone : null,
            ]);

            // Generate KOT tickets for Kitchen
            $tickets = app(\App\Services\KOTService::class)->generateTickets($order);

            DB::commit();

            return response()->json([
                'success'      => true,
                'message'      => 'Order saved and sent to kitchen KOT!',
                'order_db_id'  => $order->id,
                'order_id'     => $order->order_number ?? ('#' . str_pad($order->id, 5, '0', STR_PAD_LEFT)),
                'table_id'     => $table->id,
                'table_name'   => $table->name,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    //  POST /pos/complete-table-order/{table} — Complete order & free table
    // ─────────────────────────────────────────────────────────────
    public function completeTableOrder(Request $request, RestaurantTable $table)
    {
        $order = Order::where('table_id', $table->id)
            ->whereNotIn('status', ['closed', 'cancelled'])
            ->first();

        if (!$order) {
            $table->update([
                'status' => 'available',
                'customer_name' => null,
                'customer_phone' => null
            ]);
            return response()->json(['success' => true, 'message' => 'Table freed successfully.']);
        }

        DB::beginTransaction();
        try {
            \App\Models\KitchenTicket::where('order_id', $order->id)
                ->where('status', '!=', 'cancelled')
                ->update(['status' => 'served']);

            \App\Models\OrderItem::where('order_id', $order->id)
                ->update(['kitchen_status' => 'served']);

            $order->update([
                'status' => 'closed',
                'kitchen_status' => 'served'
            ]);

            $table->update([
                'status' => 'available',
                'customer_name' => null,
                'customer_phone' => null
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Order completed successfully and table freed.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    //  GET /pos/active-orders — list live active orders
    // ─────────────────────────────────────────────────────────────
    public function activeOrders()
    {
        $companyId = session('company_id');
        $query = Order::query();

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        // Active orders: orders that are NOT completed, closed, or cancelled
        $dbOrders = $query->whereNotIn('status', ['completed', 'closed', 'cancelled'])
            ->with(['customer', 'table'])
            ->orderBy('created_at', 'asc') // Oldest first (highest duration)
            ->take(20)
            ->get();

        $formattedOrders = $dbOrders->map(function ($order) {
            $diffMinutes = (int) now()->diffInMinutes($order->created_at);
            if ($diffMinutes >= 1440) {
                $durationStr = floor($diffMinutes / 1440) . 'd ' . floor(($diffMinutes % 1440) / 60) . 'h';
            } elseif ($diffMinutes >= 60) {
                $durationStr = floor($diffMinutes / 60) . 'h ' . ($diffMinutes % 60) . 'm';
            } else {
                $durationStr = max(1, $diffMinutes) . 'm';
            }

            $serviceType = ($order->table_id || $order->table) ? 'dine_in' : (($order->service_type === 'retail' || empty($order->service_type)) ? 'counter' : $order->service_type);
            $serviceLabel = match ($serviceType) {
                'dine_in' => 'Dine-In',
                'takeaway', 'pickup' => 'Takeaway',
                'delivery' => 'Delivery',
                default => 'Counter',
            };

            $rawStatus = strtolower($order->kitchen_status ?? $order->status ?? 'pending');
            $status = match ($rawStatus) {
                'preparing' => 'preparing',
                'ready' => 'ready',
                'pending', 'placed', 'open', 'paid', 'none' => 'pending',
                default => 'pending',
            };

            $paymentStatus = strtolower($order->payment_status ?? ($order->status === 'paid' ? 'paid' : 'unpaid'));
            $paymentStatusLabel = match ($paymentStatus) {
                'paid' => 'Paid',
                'partial' => 'Partial',
                default => 'Unpaid',
            };

            return [
                'id' => $order->id,
                'order_number' => $order->order_number ?? ('#ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT)),
                'service_type' => $serviceType,
                'service_type_label' => $serviceLabel,
                'time' => $order->created_at->format('h:i A'),
                'duration' => $durationStr,
                'status' => $status,
                'status_label' => ucfirst($status),
                'payment_status' => $paymentStatus,
                'payment_status_label' => $paymentStatusLabel,
                'total_amount' => (float) $order->total_amount,
            ];
        });

        return response()->json([
            'success' => true,
            'orders'  => $formattedOrders,
            'count'   => $formattedOrders->count(),
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
                'status' => $order->status,
                'payment_status' => $order->payment_status ?? ($order->status === 'paid' ? 'paid' : 'unpaid'),
                'service_type' => $order->service_type,
                'table' => $order->table,
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
        $term = $request->query('query') ?? $request->query('phone') ?? $request->query('search');
        if (!$term) {
            return response()->json(['success' => false, 'customers' => []]);
        }

        $customers = Customer::where(function ($q) use ($term) {
            $q->where('phone', 'like', "%{$term}%")
              ->orWhere('name', 'like', "%{$term}%");
        })->limit(10)->get();

        if ($customers->isNotEmpty()) {
            return response()->json([
                'success'   => true,
                'customers' => $customers,
                'customer'  => $customers->first()
            ]);
        }

        return response()->json(['success' => false, 'customers' => []]);
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
        $company = Company::find(session('company_id'));
        $currencyConfig = $company ? $company->getCurrencyConfig() : default_currency_config();

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
            
            // Adjust tax amount using company configured tax percentage
            $taxableAmount = max(0, $request->subtotal - $totalDiscountAmount);
            $companyTaxPct = $company ? ($company->getTaxPercentage() / 100) : 0.08;
            $taxAmount = $taxableAmount * $companyTaxPct;

            // Final Order Total = Subtotal - Total Discount + Tax + Card Service Charges
            $finalTotalAmount = max(0, $request->subtotal - $totalDiscountAmount + $taxAmount + $totalCardServiceCharge);

            // ── Card Commission Calculation ────────────────────────────────
            // Calculated from the card_type_id sent in the checkout payload.
            $cardTypeId         = $request->input('card_type_id');
            if (!$cardTypeId && $request->is_split && is_array($request->split_payments)) {
                foreach ($request->split_payments as $sp) {
                    if (!empty($sp['card_type_id'])) {
                        $cardTypeId = $sp['card_type_id'];
                        break;
                    }
                }
            }
            if (!$cardTypeId && is_array($request->card_details)) {
                foreach ($request->card_details as $cd) {
                    if (!empty($cd['card_type_id'])) {
                        $cardTypeId = $cd['card_type_id'];
                        break;
                    }
                }
            }

            $commissionAmount   = 0;
            $commissionTax      = 0;
            $commissionTotal    = 0;
            $netReceived        = 0;

            if ($cardTypeId) {
                $cardTypeModel = CardType::find($cardTypeId);
                if ($cardTypeModel) {
                    $company = Company::find(session('company_id'));
                    $billAmount       = $finalTotalAmount; // commission on the full bill
                    $commissionAmount = $cardTypeModel->calculateCommission($billAmount);
                    $commissionTaxPct = $company ? $company->getCardCommissionTax() : 0;
                    $commissionTax    = round($commissionAmount * ($commissionTaxPct / 100), 4);
                    $commissionTotal  = round($commissionAmount + $commissionTax, 4);
                    $netReceived      = round($billAmount - $commissionTotal, 4);
                }
            }

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

            // ── Delivery Partner Calculation ────────────────────────────────
            $deliveryPartnerId = $request->input('delivery_partner_id');
            $deliveryCommissionAmount = 0;
            $settlementStatus = null;
            if ($request->service_type === 'delivery' && $deliveryPartnerId) {
                $deliveryPartner = \App\Models\DeliveryPartner::find($deliveryPartnerId);
                if ($deliveryPartner) {
                    $deliveryCommissionAmount = round($finalTotalAmount * ($deliveryPartner->commission_percentage / 100), 4);
                    $settlementStatus = 'pending';
                }
            }

            // Resolve service type accurately without defaulting to 'retail'
            $resolvedServiceType = $request->service_type;
            $existingOrder = null;
            if ($request->order_id) {
                $existingOrder = Order::find($request->order_id);
                if ($existingOrder) {
                    if ($existingOrder->table_id || $existingOrder->service_type === 'dine_in') {
                        $resolvedServiceType = 'dine_in';
                    } elseif (!empty($existingOrder->service_type) && $existingOrder->service_type !== 'retail') {
                        $resolvedServiceType = $existingOrder->service_type;
                    }
                }
            }
            if (!$resolvedServiceType || $resolvedServiceType === 'counter') {
                $resolvedServiceType = ($request->table_id ? 'dine_in' : ($request->service_type && in_array($request->service_type, ['retail', 'dine_in', 'takeaway', 'delivery']) ? $request->service_type : 'retail'));
            }

            // Create or Update Order
            $orderData = [
                'user_id'          => auth()->id(),
                'waiter_id'        => auth()->id(),
                'table_id'         => $request->table_id ?: ($existingOrder ? $existingOrder->table_id : null),
                'customer_id'      => $customer->id,
                'service_type'     => $resolvedServiceType,
                'delivery_status'  => ($resolvedServiceType === 'delivery') ? 'pending' : null,
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
                // Card commission
                'card_type_id'                    => $cardTypeId ?: null,
                'card_commission_amount'          => $commissionAmount,
                'card_commission_tax_amount'      => $commissionTax,
                'card_commission_total_deduction' => $commissionTotal,
                'card_net_received'               => $commissionTotal > 0 ? $netReceived : 0,
                // Delivery Partner
                'delivery_partner_id'             => $deliveryPartnerId ?: null,
                'delivery_commission_amount'      => $deliveryCommissionAmount,
                'settlement_status'               => $settlementStatus,
                // Currency Snapshot
                'currency_code'                   => $currencyConfig['code'] ?? 'INR',
                'currency_symbol'                 => $currencyConfig['symbol'] ?? '₹',
                'currency_symbol_position'        => $currencyConfig['symbol_position'] ?? 'before',
                'currency_decimal_places'         => (int) ($currencyConfig['decimal_places'] ?? 2),
            ];

            if ($request->order_id) {
                $order = Order::findOrFail($request->order_id);
                $order->update($orderData);
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
                if ($walletUsed !== 0) {
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
                if ($walletUsed !== 0) {
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

            // Save or update items cleanly without breaking KOT ticket links
            if ($request->order_id) {
                $existingItemMap = $order->items->keyBy('product_id');
                $cartProductIds = collect($cart)->pluck('id')->toArray();

                // Safely remove items no longer in cart (only if not linked to active KOT items)
                foreach ($order->items as $existingItem) {
                    if (!in_array($existingItem->product_id, $cartProductIds)) {
                        $isUsedInKOT = \App\Models\KitchenTicketItem::where('order_item_id', $existingItem->id)->exists();
                        if (!$isUsedInKOT) {
                            $existingItem->delete();
                        }
                    }
                }

                foreach ($cart as $item) {
                    if ($existingItemMap->has($item['id'])) {
                        $existingItem = $existingItemMap->get($item['id']);
                        $existingItem->update([
                            'product_name' => $item['name'],
                            'unit_price'   => $item['price'],
                            'quantity'     => $item['qty'],
                            'subtotal'     => $item['price'] * $item['qty'],
                        ]);
                    } else {
                        OrderItem::create([
                            'company_id'   => $order->company_id,
                            'order_id'     => $order->id,
                            'product_id'   => $item['id'],
                            'product_name' => $item['name'],
                            'unit_price'   => $item['price'],
                            'quantity'     => $item['qty'],
                            'subtotal'     => $item['price'] * $item['qty'],
                        ]);
                    }
                }
            } else {
                foreach ($cart as $item) {
                    OrderItem::create([
                        'company_id'   => $order->company_id,
                        'order_id'     => $order->id,
                        'product_id'   => $item['id'],
                        'product_name' => $item['name'],
                        'unit_price'   => $item['price'],
                        'quantity'     => $item['qty'],
                        'subtotal'     => $item['price'] * $item['qty'],
                    ]);
                }
            }

            // KOT System Integration: Check if KOT/Kitchen system is enabled for company
            $company = Company::find(session('company_id'));
            $isKOTEnabled = $company && (
                $company->isModuleEnabled('kitchen_display') || 
                $company->isModuleEnabled('kot_system') || 
                $company->isModuleEnabled('restaurant_mode')
            );

            if ($isKOTEnabled && $order->items()->count() > 0) {
                app(\App\Services\KOTService::class)->generateTickets($order);
            }

            // Update table status and order payment status based on Dine-In lifecycle
            $tableIdToRelease = $order->table_id ?: $request->table_id;
            if ($tableIdToRelease && $resolvedServiceType === 'dine_in') {
                $tableModel = RestaurantTable::find($tableIdToRelease);
                if ($tableModel) {
                    $tableModel->update([
                        'status'         => 'occupied',
                        'customer_name'  => $request->customer_name ?: ($customer->name ?? null),
                        'customer_phone' => $request->customer_phone ?: ($customer->phone ?? null),
                    ]);
                }
            }

            if ($request->is_settlement || $request->settle_payment || !empty($request->payment_details) || !empty($request->split_payments)) {
                $order->update([
                    'status' => 'paid',
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
            } elseif ($walletUsed < 0) {
                $debtPaid = abs($walletUsed);
                $customer->increment('wallet_balance', $debtPaid);
                WalletTransaction::create([
                    'customer_id' => $customer->id,
                    'order_id'    => $order->id,
                    'amount'      => $debtPaid,
                    'type'        => 'credit',
                    'description' => 'Paid off previous debt with Order #' . $order->id,
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
                'success'      => true,
                'order_db_id'  => $order->id,
                'order_number' => $order->order_number,
                'order_id'     => '#' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                'total'        => $request->total,
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
