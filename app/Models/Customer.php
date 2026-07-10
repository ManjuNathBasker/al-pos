<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MultiTenantTrait;

class Customer extends Model
{
    use MultiTenantTrait;

    protected $fillable = ['name', 'phone', 'wallet_balance', 'company_id', 'created_by', 'updated_by'];

    protected $casts = [
        'wallet_balance' => 'float'
    ];

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
