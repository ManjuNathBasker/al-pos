<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user')->orderBy('created_at', 'desc');

        // Search by order number or note
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load([
            'items.product', 'user', 'customer', 'payments',
            'cardTransactions.card', 'table.section', 'waiter',
            'deliveryPartner', 'cardType', 'kitchenTickets.items'
        ]);

        // Self-correct totals calculation if subtotal is out of sync with line items
        $calculatedSubtotal = (float) $order->items->sum('subtotal');
        if (abs($calculatedSubtotal - (float)$order->subtotal) > 0.01 && $order->items->count() > 0) {
            $order->recalculateTotals();
            $order->refresh();
        }

        // Build a lookup map of account IDs to account names for resolving payment methods
        $accountIds = $order->payments->filter(fn($p) => is_numeric($p->payment_method))->pluck('payment_method')->unique();
        $accountNames = \App\Models\Account::whereIn('id', $accountIds)->pluck('account_name', 'id');

        return view('orders.show', compact('order', 'accountNames'));
    }

    public function cancel(Order $order)
    {
        if ($order->status === 'cancelled') {
            return redirect()->back()->with('error', 'Order is already cancelled.');
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $order->update(['status' => 'cancelled']);
            
            // Restore stock if it was deducted
            $inventoryService = new \App\Services\InventoryService();
            $inventoryService->restoreStockFromOrder($order);

            \Illuminate\Support\Facades\DB::commit();
            return redirect()->back()->with('success', 'Order cancelled and stock restored successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }
    }

    public function cancelItem(Order $order, \App\Models\OrderItem $item)
    {
        // 1. Multi-tenant company verification
        if (session('company_id') && (int) $order->company_id !== (int) session('company_id')) {
            abort(403, 'Unauthorized company access.');
        }

        // 2. Order item ownership verification
        if ((int) $item->order_id !== (int) $order->id) {
            abort(403, 'Order item does not belong to this order.');
        }

        // 3. Unpaid restriction check (authoritative)
        if (!$order->isUnpaid()) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Item cancellation is only allowed on unpaid orders.'], 403);
            }
            return redirect()->back()->with('error', 'Item cancellation is only allowed on unpaid orders.');
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Restore stock for this item if stock was previously deducted
            $inventoryService = new \App\Services\InventoryService();
            $inventoryService->restoreStockFromOrderItem($item);

            // Clean up linked KOT ticket item
            \App\Models\KitchenTicketItem::where('order_item_id', $item->id)->delete();

            // Check parent KitchenTickets - cancel any ticket with no remaining items
            foreach ($order->kitchenTickets as $ticket) {
                if ($ticket->items()->count() === 0) {
                    $ticket->update(['status' => 'cancelled']);
                }
            }

            // Delete order item
            $item->delete();

            // Recalculate order totals (subtotal, discounts, company tax rate, total)
            $order->recalculateTotals();

            // If 0 items remain in the order, follow order cancellation and table freeing flow
            if ($order->items()->count() === 0) {
                $order->update([
                    'status'         => 'cancelled',
                    'kitchen_status' => 'none',
                ]);

                \App\Models\KitchenTicket::where('order_id', $order->id)->update(['status' => 'cancelled']);

                if ($order->table_id) {
                    $table = \App\Models\RestaurantTable::find($order->table_id);
                    if ($table) {
                        $table->update(['status' => 'available']);
                    }
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Item cancelled successfully.']);
            }
            return redirect()->back()->with('success', 'Order item cancelled successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to cancel item: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Failed to cancel item: ' . $e->getMessage());
        }
    }
}
