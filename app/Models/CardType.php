<?php

namespace App\Models;

use App\Traits\MultiTenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CardType extends Model
{
    use HasFactory, SoftDeletes, MultiTenantTrait;

    protected $fillable = [
        'company_id',
        'name',
        'commission_type',   // percentage | fixed
        'commission_value',
        'commission_handling', // ignore | auto_write_off | settlement_tracking
        'expense_account_id',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'commission_value' => 'float',
        'status'           => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function expenseAccount()
    {
        return $this->belongsTo(Account::class, 'expense_account_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Helpers ────────────────────────────────────────────────────

    /**
     * Calculate commission amount given a bill amount.
     */
    public function calculateCommission(float $billAmount): float
    {
        if ($this->commission_type === 'percentage') {
            return round($billAmount * ($this->commission_value / 100), 4);
        }
        // fixed
        return round($this->commission_value, 4);
    }

    /**
     * Commission handling check helpers.
     */
    public function isAutoWriteOff(): bool
    {
        return $this->commission_handling === 'auto_write_off';
    }

    public function isSettlementTracking(): bool
    {
        return $this->commission_handling === 'settlement_tracking';
    }
}
