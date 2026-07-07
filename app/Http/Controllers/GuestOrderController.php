<?php

namespace App\Http\Controllers;

use App\Models\RestaurantTable;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\KitchenTicket;
use App\Models\KitchenTicketItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuestOrderController extends Controller
{
    public function show(string $token)
    {
        $table = RestaurantTable::where('qr_token', $token)->firstOrFail();
        $this->verifyQrOrdering($table);
        $categories = Category::all();
        $products = Product::where('is_active', true)->get();
        
        // Fetch active order for this table if any
        $activeOrder = Order::where('table_id', $table->id)
            ->whereIn('status', ['pending', 'processing'])
            ->with('items')
            ->first();

        return view('restaurant.guest.menu', compact('table', 'categories', 'products', 'activeOrder'));
    }

    public function placeOrder(Request $request, string $token)
    {
        $table = RestaurantTable::where('qr_token', $token)->firstOrFail();
        $this->verifyQrOrdering($table);
        $cart = $request->input('cart');

        if (empty($cart)) {
            return response()->json(['success' => false, 'message' => 'Your cart is empty'], 422);
        }

        DB::beginTransaction();
        try {
            // Find existing active order or create new
            $order = Order::where('table_id', $table->id)
                ->whereIn('status', ['pending', 'processing'])
                ->first();

            if (!$order) {
                $order = Order::create([
                    'company_id' => $table->company_id,
                    'table_id' => $table->id,
                    'status' => 'pending',
                    'kitchen_status' => 'pending',
                    'service_type' => 'dine_in',
                    'subtotal' => 0,
                    'total_amount' => 0,
                ]);
            } else {
                // If order exists but is already "Accepted" or "Preparing", 
                // we should handle it (usually append new items as a new KOT)
                // For this implementation, we'll allow appending as long as not 'paid'
            }

            $orderSubtotal = $order->subtotal;
            $createdOrderItems = [];

            foreach ($cart as $item) {
                $orderSubtotal += $item['price'] * $item['qty'];
                
                $orderItem = OrderItem::create([
                    'company_id' => $table->company_id,
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'unit_price' => $item['price'],
                    'quantity' => $item['qty'],
                    'subtotal' => $item['price'] * $item['qty'],
                ]);
                
                $createdOrderItems[] = [
                    'id' => $orderItem->id,
                    'name' => $item['name'],
                    'qty' => $item['qty'],
                ];
            }

            $order->update([
                'subtotal' => $orderSubtotal,
                'total_amount' => $orderSubtotal * 1.08, // Simple tax for demo
                'tax_amount' => $orderSubtotal * 0.08,
            ]);

            // CREATE KITCHEN TICKET
            $ticket = KitchenTicket::create([
                'company_id' => $table->company_id,
                'order_id' => $order->id,
                'ticket_number' => 'KOT-' . rand(100, 999),
                'status' => 'pending',
            ]);

            foreach ($createdOrderItems as $item) {
                KitchenTicketItem::create([
                    'kitchen_ticket_id' => $ticket->id,
                    'order_item_id' => $item['id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['qty'],
                    'status' => 'pending',
                ]);
            }

            // Update table status
            $table->update(['status' => 'occupied']);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully!',
                'order_id' => $order->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getStatus(string $token)
    {
        $table = RestaurantTable::where('qr_token', $token)->firstOrFail();
        $this->verifyQrOrdering($table);
        $order = Order::where('table_id', $table->id)
            ->whereIn('status', ['pending', 'processing'])
            ->with('items')
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'No active order found']);
        }

        return response()->json([
            'success' => true,
            'status' => $order->status,
            'kitchen_status' => $order->kitchen_status,
            'items' => $order->items
        ]);
    }

    public function removeItem(Request $request, string $token, OrderItem $item)
    {
        $table = RestaurantTable::where('qr_token', $token)->firstOrFail();
        $this->verifyQrOrdering($table);
        $order = $item->order;

        if ($order->table_id !== $table->id) abort(403);
        
        // Only allow removal if not yet accepted in kitchen
        if ($order->kitchen_status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Order already in preparation and cannot be modified.'], 422);
        }

        DB::beginTransaction();
        try {
            $itemSubtotal = $item->subtotal;
            $item->delete();

            $newSubtotal = $order->subtotal - $itemSubtotal;
            $order->update([
                'subtotal' => $newSubtotal,
                'total_amount' => $newSubtotal * 1.08,
                'tax_amount' => $newSubtotal * 0.08,
            ]);

            // If no items left, we could optionally delete order or keep it
            if ($order->items()->count() === 0) {
                $order->delete();
                $table->update(['status' => 'available']);
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function cancelOrder(Request $request, string $token)
    {
        $table = RestaurantTable::where('qr_token', $token)->firstOrFail();
        $this->verifyQrOrdering($table);
        $order = Order::where('table_id', $table->id)
            ->whereIn('status', ['pending', 'processing'])
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'No active order found.'], 404);
        }

        if ($order->kitchen_status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Order is already in preparation and cannot be cancelled.'], 422);
        }

        DB::beginTransaction();
        try {
            // Cancel all tickets
            KitchenTicket::where('order_id', $order->id)->update(['status' => 'cancelled']);
            
            $order->delete();
            $table->update(['status' => 'available']);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Order cancelled successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    protected function verifyQrOrdering(RestaurantTable $table)
    {
        if (!$table->company->isModuleEnabled('qr_ordering')) {
            abort(403, 'QR Ordering is not enabled for this store.');
        }
    }
}
