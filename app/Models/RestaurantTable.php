<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MultiTenantTrait;

class RestaurantTable extends Model
{
    use MultiTenantTrait;

    protected $fillable = [
        'company_id',
        'section_id',
        'name',
        'capacity',
        'status',
        'qr_token',
        'customer_name',
        'customer_phone',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function section()
    {
        return $this->belongsTo(TableSection::class, 'section_id');
    }

    public function activeOrder()
    {
        return $this->hasOne(Order::class, 'table_id')
            ->whereIn('status', ['pending', 'processing']);
    }
}
