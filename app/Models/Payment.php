<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MultiTenantTrait;

class Payment extends Model
{
    use MultiTenantTrait;

    protected $fillable = [
        'company_id',
        'order_id',
        'payment_method',
        'amount',
        'reference_id',
        'note',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
