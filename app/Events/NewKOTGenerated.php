<?php

namespace App\Events;

use App\Models\KitchenTicket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewKOTGenerated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ticket;

    public function __construct(KitchenTicket $ticket)
    {
        $this->ticket = $ticket->load('items', 'order.table');
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('company.' . $this->ticket->company_id . '.kitchen'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'kot.new';
    }
}
