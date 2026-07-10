<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\MultiTenantTrait;

class Branch extends Model
{
    use HasFactory, SoftDeletes, MultiTenantTrait;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'address',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
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
        return $this->belongsToMany(BankOffer::class, 'bank_offer_branch');
    }
}
