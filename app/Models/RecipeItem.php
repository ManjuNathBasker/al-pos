<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MultiTenantTrait;

class RecipeItem extends Model
{
    use MultiTenantTrait;

    protected $fillable = [
        'company_id',
        'product_id',
        'inventory_item_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'float',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
