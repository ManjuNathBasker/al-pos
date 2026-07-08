<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\MultiTenantTrait;

class Card extends Model
{
    use HasFactory, SoftDeletes, MultiTenantTrait;

    protected $fillable = [
        'company_id',
        'bank_name',
        'card_network',
        'card_type',
        'settlement_account_id',
        'service_charge',
        'mdr',
        'processing_fee',
        'settlement_days',
        'is_active',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'service_charge' => 'float',
        'mdr' => 'float',
        'processing_fee' => 'float',
        'settlement_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function settlementAccount()
    {
        return $this->belongsTo(Account::class, 'settlement_account_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function cardTransactions()
    {
        return $this->hasMany(CardTransaction::class);
    }

    public function bankOffers()
    {
        return $this->belongsToMany(BankOffer::class, 'bank_offer_card');
    }
}
