<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\MultiTenantTrait;

class Product extends Model
{
    use HasFactory, SoftDeletes, MultiTenantTrait;

    protected $fillable = [
        'category_id',
        'unit_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'description',
        'image',
        'price',
        'cost_price',
        'compare_price',
        'tax_rate',
        'stock_qty',
        'low_stock_threshold',
        'track_stock',
        'allow_backorder',
        'is_active',
        'is_featured',
        'sort_order',
        'company_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'price'               => 'float',
        'cost_price'          => 'float',
        'compare_price'       => 'float',
        'tax_rate'            => 'integer',
        'stock_qty'           => 'integer',
        'low_stock_threshold' => 'integer',
        'track_stock'         => 'boolean',
        'allow_backorder'     => 'boolean',
        'is_active'           => 'boolean',
        'is_featured'         => 'boolean',
        'sort_order'          => 'integer',
    ];

    // ── Boot: auto-generate slug ────────────────────────────────────
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    // ── Relationships ────────────────────────────────────────────────
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->where('track_stock', false)
              ->orWhere('stock_qty', '>', 0)
              ->orWhere('allow_backorder', true);
        });
    }

    public function scopeLowStock($query)
    {
        return $query->where('track_stock', true)
                     ->whereColumn('stock_qty', '<=', 'low_stock_threshold')
                     ->where('stock_qty', '>', 0);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('track_stock', true)
                     ->where('stock_qty', '<=', 0)
                     ->where('allow_backorder', false);
    }

    public function scopeForPOS($query)
    {
        return $query->active()->inStock()->with(['category', 'unit'])->orderBy('sort_order')->orderBy('name');
    }

    // ── Accessors ────────────────────────────────────────────────────
    public function getIsLowStockAttribute(): bool
    {
        return $this->track_stock && $this->stock_qty <= $this->low_stock_threshold && $this->stock_qty > 0;
    }

    public function getIsOutOfStockAttribute(): bool
    {
        return $this->track_stock && $this->stock_qty <= 0 && !$this->allow_backorder;
    }

    public function getHasDiscountAttribute(): bool
    {
        return $this->compare_price && $this->compare_price > $this->price;
    }

    public function getDiscountPercentAttribute(): int
    {
        if (!$this->has_discount) return 0;
        return (int) round((($this->compare_price - $this->price) / $this->compare_price) * 100);
    }

    public function getPriceWithTaxAttribute(): float
    {
        return round($this->price * (1 + $this->tax_rate / 100), 2);
    }
}
