<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\MultiTenantTrait;

class CardTransaction extends Model
{
    use HasFactory, SoftDeletes, MultiTenantTrait;

    protected $fillable = [
        'company_id',
        'branch_id',
        'customer_id',
        'order_id',
        'card_id',
        'bank_name',
        'gross_amount',
        'discount_amount',
        'cashback_amount',
        'service_charge_amount',
        'mdr_amount',
        'processing_fee_amount',
        'net_settlement_amount',
        'merchant_discount_share',
        'bank_discount_share',
        'bank_offer_id',
        'settlement_status',
        'settlement_date',
        'created_by',
    ];

    protected $casts = [
        'gross_amount' => 'float',
        'discount_amount' => 'float',
        'cashback_amount' => 'float',
        'service_charge_amount' => 'float',
        'mdr_amount' => 'float',
        'processing_fee_amount' => 'float',
        'net_settlement_amount' => 'float',
        'merchant_discount_share' => 'float',
        'bank_discount_share' => 'float',
        'bank_offer_id' => 'integer',
        'settlement_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bankSettlements()
    {
        return $this->hasMany(BankSettlement::class);
    }
}
