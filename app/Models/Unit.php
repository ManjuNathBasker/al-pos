<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\MultiTenantTrait;

class Unit extends Model
{
    use MultiTenantTrait;

    protected $fillable = ['name', 'abbreviation', 'is_active', 'company_id', 'created_by', 'updated_by'];

    protected $casts = ['is_active' => 'boolean'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
