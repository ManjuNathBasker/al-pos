<?php

namespace App\Services;

use App\Models\Order;
use App\Models\KitchenTicket;
use App\Models\KitchenTicketItem;
use Illuminate\Support\Facades\DB;

class KOTService
{
    /**
     * Generate Kitchen Tickets (KOTs) from an order.
     * Only items that have not already been ticketed will generate new KOTs.
     * Items are grouped by station.
     */
    public function generateTickets(Order $order): array
    {
        return DB::transaction(function () use ($order) {
            $tickets = [];

            // Find items that do NOT have a KitchenTicketItem created yet
            $existingItemIds = KitchenTicketItem::whereIn('order_item_id', $order->items()->pluck('id'))
                ->pluck('order_item_id')
                ->toArray();

            $itemsToTicket = $order->items()
                ->whereNotIn('id', $existingItemIds)
                ->with('product')
                ->get();

            if ($itemsToTicket->isEmpty()) {
                return $tickets;
            }

            // Group items by station
            $groupedItems = $itemsToTicket->groupBy(function ($item) {
                return $item->product->station ?? 'Main Kitchen';
            });

            foreach ($groupedItems as $station => $itemsInStation) {
                $ticket = KitchenTicket::create([
                    'company_id' => $order->company_id,
                    'order_id' => $order->id,
                    'ticket_number' => 'KOT-' . str_pad($order->id, 4, '0', STR_PAD_LEFT) . '-' . rand(10, 99),
                    'station' => $station,
                    'status' => 'pending',
                ]);

                foreach ($itemsInStation as $item) {
                    KitchenTicketItem::create([
                        'kitchen_ticket_id' => $ticket->id,
                        'order_item_id' => $item->id,
                        'product_name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'note' => $item->item_note ?? null,
                        'status' => 'pending',
                    ]);
                }

                event(new \App\Events\NewKOTGenerated($ticket));
                $tickets[] = $ticket;
            }

            // Update order kitchen status if pending
            if ($order->kitchen_status === 'none') {
                $order->update(['kitchen_status' => 'pending']);
            }

            return $tickets;
        });
    }
}
