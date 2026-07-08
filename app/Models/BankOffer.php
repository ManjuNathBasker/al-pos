<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\MultiTenantTrait;

class BankOffer extends Model
{
    use HasFactory, SoftDeletes, MultiTenantTrait;

    protected $fillable = [
        'company_id',
        'name',
        'start_date',
        'end_date',
        'min_purchase',
        'max_discount',
        'discount_type',
        'discount_value',
        'cashback',
        'is_emi_offer',
        'usage_limit',
        'used_count',
        'merchant_contribution',
        'bank_contribution',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'min_purchase' => 'float',
        'max_discount' => 'float',
        'discount_value' => 'float',
        'cashback' => 'float',
        'is_emi_offer' => 'boolean',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'merchant_contribution' => 'float',
        'bank_contribution' => 'float',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
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

    public function cards()
    {
        return $this->belongsToMany(Card::class, 'bank_offer_card');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'bank_offer_product');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'bank_offer_category');
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'bank_offer_customer');
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'bank_offer_branch');
    }

    public function isValid(): bool
    {
        $today = now()->toDateString();
        if (!$this->is_active) return false;
        if ($this->start_date && $this->start_date->toDateString() > $today) return false;
        if ($this->end_date && $this->end_date->toDateString() < $today) return false;
        if ($this->usage_limit > 0 && $this->used_count >= $this->usage_limit) return false;
        return true;
    }
}
