<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MultiTenantTrait;

class WalletTransaction extends Model
{
    use MultiTenantTrait;

    protected $fillable = [
        'customer_id', 
        'order_id', 
        'amount', 
        'type', 
        'description',
        'company_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'float'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
