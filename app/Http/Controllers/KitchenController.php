<?php

namespace App\Http\Controllers;

use App\Models\KitchenTicket;
use App\Models\KitchenTicketItem;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    public function index()
    {
        $tickets = KitchenTicket::with('items', 'order.table')
            ->whereIn('status', ['pending', 'preparing', 'ready'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('restaurant.kitchen.index', compact('tickets'));
    }

    public function updateStatus(Request $request, KitchenTicket $ticket)
    {
        $request->validate(['status' => 'required|in:pending,preparing,ready,served,cancelled']);
        
        $ticket->update(['status' => $request->status]);
        
        if ($request->status === 'ready') {
            $ticket->update(['ready_at' => now()]);
        }

        // Sync with individual items in this ticket
        foreach ($ticket->items as $item) {
            if ($item->orderItem) {
                $item->orderItem->update(['kitchen_status' => $request->status]);
            }
            // Only update KitchenTicketItem status if it's within the enum [pending, preparing, ready]
            if (in_array($request->status, ['pending', 'preparing', 'ready'])) {
                $item->update(['status' => $request->status]);
            }
        }

        // Sync with parent order aggregate status
        $order = $ticket->order;
        if ($order) {
            $pendingOrPreparing = $order->kitchenTickets()->whereIn('status', ['pending', 'preparing'])->count();
            $unservedTickets = $order->kitchenTickets()->where('status', '!=', 'served')->count();

            if ($request->status === 'preparing' || $pendingOrPreparing > 0) {
                $order->update(['kitchen_status' => 'preparing']);
            } elseif ($unservedTickets === 0) {
                $order->update(['kitchen_status' => 'served']);
            } else {
                $order->update(['kitchen_status' => 'ready']);
            }
        }
        
        return redirect()->back()->with('success', "Ticket status updated to {$request->status}");
    }

    public function updateItemStatus(Request $request, KitchenTicketItem $item)
    {
        $request->validate(['status' => 'required|in:pending,preparing,ready']);
        $item->update(['status' => $request->status]);
        
        if ($item->orderItem) {
            $item->orderItem->update(['kitchen_status' => $request->status]);
        }

        // If all items in a ticket are ready, mark ticket as ready
        $ticket = $item->ticket;
        $pendingItems = $ticket->items()->where('status', '!=', 'ready')->count();
        if ($pendingItems === 0) {
            $ticket->update(['status' => 'ready', 'ready_at' => now()]);
            
            // Check if all tickets for the order are ready
            $order = $ticket->order;
            if ($order) {
                $incompleteTickets = $order->kitchenTickets()->where('status', '!=', 'ready')->count();
                if ($incompleteTickets === 0) {
                    $order->update(['kitchen_status' => 'ready']);
                }
            }
        }

        return response()->json(['success' => true]);
    }
}
