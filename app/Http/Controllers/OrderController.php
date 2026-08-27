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
            'deliveryPartner', 'cardType'
        ]);

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
}
