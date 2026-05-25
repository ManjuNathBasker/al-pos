<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MultiTenantTrait;

class PurchasePayment extends Model
{
    use MultiTenantTrait;

    protected $fillable = [
        'company_id',
        'purchase_id',
        'paid_amount',
        'payment_method',
        'payment_date',
        'reference_number',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'paid_amount'  => 'float',
        'payment_date' => 'date',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
