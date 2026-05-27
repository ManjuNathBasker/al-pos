<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\MultiTenantTrait;

class RegisterSession extends Model
{
    use HasFactory, SoftDeletes, MultiTenantTrait;

    protected $fillable = [
        'company_id',
        'user_id',
        'opened_at',
        'closed_at',
        'opening_amount',
        'closing_amount_expected',
        'closing_amount_actual',
        'difference',
        'status',
        'notes',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_amount' => 'float',
        'closing_amount_expected' => 'float',
        'closing_amount_actual' => 'float',
        'difference' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Scope to get the currently open session for a given user.
     */
    public function scopeOpenForUser($query, $userId)
    {
        return $query->where('user_id', $userId)
                     ->where('status', 'open');
    }

    /**
     * Calculate the expected closing amount dynamically based on cash orders.
     */
    public function calculateExpectedAmount()
    {
        // Only count orders placed during this session that were paid in cash.
        // Assuming Order has 'payment_method' and 'total_amount'.
        $cashSales = $this->orders()
                          ->where('payment_method', 'cash')
                          ->sum('total_amount');
                          
        return $this->opening_amount + $cashSales;
    }
}
