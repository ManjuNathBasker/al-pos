<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MultiTenantTrait;

class OrderItem extends Model
{
    use MultiTenantTrait;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_sku',
        'quantity',
        'unit_price',
        'cost_price',
        'discount_amount',
        'tax_amount',
        'subtotal',
        'kitchen_status',
        'item_note',
        'company_id',
    ];

    protected $casts = [
        'quantity'        => 'integer',
        'unit_price'      => 'float',
        'cost_price'      => 'float',
        'discount_amount' => 'float',
        'tax_amount'      => 'float',
        'subtotal'        => 'float',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
