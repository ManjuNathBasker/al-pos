<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\MultiTenantTrait;

class InventoryItem extends Model
{
    use SoftDeletes, MultiTenantTrait;

    protected $fillable = [
        'company_id',
        'branch_id',
        'name',
        'code',
        'unit_type',
        'current_stock',
        'minimum_stock',
        'cost_price',
        'supplier_id',
        'status',
    ];

    protected $casts = [
        'current_stock' => 'float',
        'minimum_stock' => 'float',
        'cost_price'    => 'float',
        'status'        => 'boolean',
    ];

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function recipeItems()
    {
        return $this->hasMany(RecipeItem::class);
    }

    public function getIsLowStockAttribute()
    {
        return $this->current_stock <= $this->minimum_stock;
    }
}
