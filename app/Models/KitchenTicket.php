<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MultiTenantTrait;

class KitchenTicket extends Model
{
    use MultiTenantTrait;

    protected $fillable = [
        'company_id',
        'order_id',
        'ticket_number',
        'station',
        'status',
        'ready_at',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function items()
    {
        return $this->hasMany(KitchenTicketItem::class);
    }
}
