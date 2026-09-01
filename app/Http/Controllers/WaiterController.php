<?php

namespace App\Http\Controllers;

use App\Models\RestaurantTable;
use App\Models\TableSection;
use App\Models\Order;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class WaiterController extends Controller
{
    public function index()
    {
        $sections = TableSection::with('tables')->get();
        return view('restaurant.waiter.index', compact('sections'));
    }

    public function createOrder(RestaurantTable $table)
    {
        $categories = Category::orderBy('name')->get();
        $products   = Product::where('is_active', true)->orderBy('name')->get();
        
        $activeOrder = Order::where('table_id', $table->id)
            ->whereNotIn('status', ['closed', 'cancelled'])
            ->with('items')
            ->latest()
            ->first();

        if (!$activeOrder && $table->status === 'occupied') {
            $activeOrder = Order::where('table_id', $table->id)
                ->with('items')
                ->latest()
                ->first();
        }

        return view('restaurant.waiter.order', compact('table', 'categories', 'products', 'activeOrder'));
    }

    public function storeOrder(Request $request, RestaurantTable $table)
    {
        $cart = $request->input('cart');

        if (empty($cart)) {
            return response()->json(['success' => false, 'message' => 'Cart is empty'], 422);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $order = Order::where('table_id', $table->id)
                ->whereNotIn('status', ['closed', 'cancelled'])
                ->latest()
                ->first();

            if (!$order) {
                $order = Order::create([
                    'company_id' => $table->company_id,
                    'table_id' => $table->id,
                    'waiter_id' => auth()->id(),
                    'status' => 'pending',
                    'kitchen_status' => 'pending',
                    'service_type' => 'dine_in',
                    'subtotal' => 0,
                    'total_amount' => 0,
                ]);
            }

            $orderSubtotal = $order->subtotal;
            $createdOrderItems = [];

            foreach ($cart as $item) {
                $orderSubtotal += $item['price'] * $item['qty'];
                
                $orderItem = \App\Models\OrderItem::create([
                    'company_id' => $table->company_id,
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'unit_price' => $item['price'],
                    'quantity' => $item['qty'],
                    'subtotal' => $item['price'] * $item['qty'],
                ]);

            }

            $order->recalculateTotals();

            // Generate KOT tickets for unticketed items
            app(\App\Services\KOTService::class)->generateTickets($order);

            $table->update(['status' => 'occupied']);

            \Illuminate\Support\Facades\DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getStatus(RestaurantTable $table)
    {
        $table->refresh();
        $order = Order::where('table_id', $table->id)
            ->whereNotIn('status', ['closed', 'cancelled'])
            ->with('items')
            ->latest()
            ->first();

        if (!$order && $table->status === 'occupied') {
            $order = Order::where('table_id', $table->id)
                ->with('items')
                ->latest()
                ->first();
        }

        return response()->json([
            'success' => $order ? true : false,
            'table_status' => $table->status,
            'status' => $order?->status,
            'kitchen_status' => $order?->kitchen_status,
            'items' => $order?->items ?: []
        ]);
    }

    public function removeItem(RestaurantTable $table, \App\Models\OrderItem $item)
    {
        $order = $item->order;
        if (!$order || $order->table_id !== $table->id) abort(403, 'Item does not belong to this table order.');

        if (!$order->isUnpaid()) {
            return response()->json(['success' => false, 'message' => 'Item cancellation is only allowed on unpaid orders.'], 403);
        }
        
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Restore stock if it was deducted
            $inventoryService = new \App\Services\InventoryService();
            $inventoryService->restoreStockFromOrderItem($item);

            // Clean up KOT ticket item
            \App\Models\KitchenTicketItem::where('order_item_id', $item->id)->delete();
            foreach ($order->kitchenTickets as $ticket) {
                if ($ticket->items()->count() === 0) {
                    $ticket->update(['status' => 'cancelled']);
                }
            }

            $item->delete();

            // Recalculate order totals centrally
            $order->recalculateTotals();

            if ($order->items()->count() === 0) {
                $order->update([
                    'status' => 'cancelled',
                    'kitchen_status' => 'none',
                ]);
                $table->update(['status' => 'available']);
            }

            \Illuminate\Support\Facades\DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateItemQuantity(RestaurantTable $table, \App\Models\OrderItem $item, Request $request)
    {
        $order = $item->order;
        if (!$order || (int)$order->table_id !== (int)$table->id) {
            return response()->json(['success' => false, 'message' => 'Item does not belong to this table order.'], 403);
        }

        if (!$order->isUnpaid()) {
            return response()->json(['success' => false, 'message' => 'Quantity adjustments are only allowed on unpaid orders.'], 403);
        }

        $change = (int) $request->input('change', 0);
        $newQuantity = (int) $request->input('quantity', $item->quantity + $change);

        if ($newQuantity <= 0) {
            return $this->removeItem($table, $item);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $item->quantity = $newQuantity;
            $item->subtotal = round($newQuantity * $item->unit_price, 2);
            $item->save();

            // Update linked KOT ticket item quantity if pending
            \App\Models\KitchenTicketItem::where('order_item_id', $item->id)
                ->where('status', 'pending')
                ->update(['quantity' => $newQuantity]);

            // Recalculate order totals centrally
            $order->recalculateTotals();

            \Illuminate\Support\Facades\DB::commit();
            return response()->json([
                'success' => true,
                'new_quantity' => $item->quantity,
                'item_subtotal' => $item->subtotal,
                'order_subtotal' => $order->subtotal,
                'order_total' => $order->total_amount,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function cancelOrder(Request $request, RestaurantTable $table)
    {
        $order = Order::where('table_id', $table->id)
            ->whereIn('status', ['pending', 'processing'])
            ->first();

        if ($order->kitchen_status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Order already in preparation and cannot be cancelled.'], 422);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            \App\Models\KitchenTicket::where('order_id', $order->id)->update(['status' => 'cancelled']);
            
            $order->delete();
            $table->update(['status' => 'available']);

            \Illuminate\Support\Facades\DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function completeOrder(Request $request, RestaurantTable $table)
    {
        $order = Order::where('table_id', $table->id)
            ->whereNotIn('status', ['closed', 'cancelled'])
            ->first();

        if (!$order) {
            // If table has no active unclosed order, free the table directly
            $table->update([
                'status' => 'available',
                'customer_name' => null,
                'customer_phone' => null
            ]);
            return response()->json(['success' => true, 'message' => 'Table freed successfully.']);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Auto mark all kitchen tickets as served
            \App\Models\KitchenTicket::where('order_id', $order->id)
                ->where('status', '!=', 'cancelled')
                ->update(['status' => 'served']);

            // Auto mark order items as served
            \App\Models\OrderItem::where('order_id', $order->id)
                ->update(['kitchen_status' => 'served']);

            // Close the order
            $order->update([
                'status' => 'closed',
                'kitchen_status' => 'served'
            ]);
            
            // Free the table and clear customer details
            $table->update([
                'status' => 'available',
                'customer_name' => null,
                'customer_phone' => null
            ]);

            \Illuminate\Support\Facades\DB::commit();
            return response()->json(['success' => true, 'message' => 'Order completed successfully and table freed.']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error completing order: ' . $e->getMessage()], 500);
        }
    }

}
