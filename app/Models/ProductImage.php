<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\MultiTenantTrait;

class ProductImage extends Model
{
    use MultiTenantTrait;

    protected $fillable = ['product_id', 'path', 'alt', 'sort_order', 'is_primary', 'company_id', 'created_by', 'updated_by'];

    protected $casts = [
        'is_primary'  => 'boolean',
        'sort_order'  => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }
}
