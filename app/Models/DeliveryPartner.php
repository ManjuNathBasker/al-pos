<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryPartner extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'commission_percentage',
        'receivable_account_id',
        'status',
        'created_by',
        'updated_by',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function receivableAccount()
    {
        return $this->belongsTo(Account::class, 'receivable_account_id');
    }
}
