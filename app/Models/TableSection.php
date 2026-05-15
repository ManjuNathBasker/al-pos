<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MultiTenantTrait;

class TableSection extends Model
{
    use MultiTenantTrait;

    protected $fillable = ['company_id', 'name'];

    public function tables()
    {
        return $this->hasMany(RestaurantTable::class, 'section_id');
    }
}
