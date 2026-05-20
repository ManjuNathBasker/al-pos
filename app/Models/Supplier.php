<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\MultiTenantTrait;

class Supplier extends Model
{
    use SoftDeletes, MultiTenantTrait;

    protected $fillable = [
        'company_id',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'tax_number',
        'opening_balance',
        'status',
    ];

    protected $casts = [
        'opening_balance' => 'float',
        'status'          => 'boolean',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
