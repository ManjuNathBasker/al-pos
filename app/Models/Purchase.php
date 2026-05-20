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
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'subtotal'      => 'float',
        'discount'      => 'float',
        'tax'           => 'float',
        'total_amount'  => 'float',
    ];

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
}
