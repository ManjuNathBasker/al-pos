<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\MultiTenantTrait;

class Purchase extends Model
{
    use SoftDeletes, MultiTenantTrait;

    protected $fillable = [
        'company_id',
        'branch_id',
        'supplier_id',
        'purchase_number',
        'purchase_date',
        'subtotal',
        'discount',
        'tax',
        'total_amount',
        'payment_status',
        'status',
        'notes',
        'created_by',
        'currency_code',
        'currency_symbol',
        'currency_symbol_position',
        'currency_decimal_places',
    ];

    protected $casts = [
        'purchase_date'            => 'date',
        'subtotal'                 => 'float',
        'discount'                 => 'float',
        'tax'                      => 'float',
        'total_amount'             => 'float',
        'currency_decimal_places'  => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $purchase) {
            if (empty($purchase->currency_code) && empty($purchase->currency_symbol)) {
                $currencyConfig = $purchase->company_id 
                    ? currency_config(Company::find($purchase->company_id))
                    : current_currency_config();

                $purchase->currency_code = $currencyConfig['code'];
                $purchase->currency_symbol = $currencyConfig['symbol'];
                $purchase->currency_symbol_position = $currencyConfig['symbol_position'];
                $purchase->currency_decimal_places = $currencyConfig['decimal_places'];
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getPaidAmountAttribute()
    {
        return $this->payments->sum('paid_amount');
    }

    public function getDueAmountAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function getCurrencyConfig(): array
    {
        return currency_config($this);
    }

    public function getCurrencySymbol(): string
    {
        return currency_symbol($this);
    }

    public function formatCurrency(mixed $amount, ?int $decimals = null): string
    {
        return format_currency($amount, $this, $decimals);
    }
}
