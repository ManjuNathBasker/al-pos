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
        $categories = Category::all();
        $products = Product::where('is_active', true)->get();
        
        $activeOrder = Order::where('table_id', $table->id)
            ->whereIn('status', ['pending', 'processing'])
            ->with('items')
            ->first();

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
                ->whereIn('status', ['pending', 'processing'])
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

                $createdOrderItems[] = [
                    'id' => $orderItem->id,
                    'name' => $item['name'],
                    'qty' => $item['qty'],
                ];
            }

            $order->update([
                'subtotal' => $orderSubtotal,
                'total_amount' => $orderSubtotal * 1.08,
                'tax_amount' => $orderSubtotal * 0.08,
            ]);

            // Create Kitchen Ticket
            $ticket = \App\Models\KitchenTicket::create([
                'company_id' => $table->company_id,
                'order_id' => $order->id,
                'ticket_number' => 'KOT-' . rand(100, 999),
                'status' => 'pending',
            ]);

            foreach ($createdOrderItems as $item) {
                \App\Models\KitchenTicketItem::create([
                    'kitchen_ticket_id' => $ticket->id,
                    'order_item_id' => $item['id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['qty'],
                    'status' => 'pending',
                ]);
            }

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
        $order = Order::where('table_id', $table->id)
            ->whereIn('status', ['pending', 'processing'])
            ->with('items')
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'No active order']);
        }

        return response()->json([
            'success' => true,
            'status' => $order->status,
            'kitchen_status' => $order->kitchen_status,
            'items' => $order->items
        ]);
    }

    public function removeItem(RestaurantTable $table, \App\Models\OrderItem $item)
    {
        if ($item->order->table_id !== $table->id) abort(403);
        
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $order = $item->order;
            $itemSubtotal = $item->subtotal;
            $item->delete();

            $newSubtotal = $order->subtotal - $itemSubtotal;
            $order->update([
                'subtotal' => $newSubtotal,
                'total_amount' => $newSubtotal * 1.08,
                'tax_amount' => $newSubtotal * 0.08,
            ]);

            if ($order->items()->count() === 0) {
                $order->delete();
                $table->update(['status' => 'available']);
            }

            \Illuminate\Support\Facades\DB::commit();
            return response()->json(['success' => true]);
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

}
