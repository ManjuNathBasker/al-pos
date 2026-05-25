<?php

namespace App\Models;

use App\Traits\MultiTenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes, MultiTenantTrait;

    protected $fillable = [
        'company_id',
        'account_name',
        'account_code',
        'account_type', // Asset, Liability, Equity, Income, Expense
        'parent_account_id',
        'opening_balance',
        'current_balance',
        'status',
        'is_system',
    ];

    protected $casts = [
        'opening_balance' => 'float',
        'current_balance' => 'float',
        'status' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_account_id');
    }

    public function children()
    {
        return $this->hasMany(Account::class, 'parent_account_id');
    }

    public function journalItems()
    {
        return $this->hasMany(JournalEntryItem::class, 'account_id');
    }

    public function calculateBalance()
    {
        $debits = $this->journalItems()->sum('debit_amount');
        $credits = $this->journalItems()->sum('credit_amount');
        
        if (in_array($this->account_type, ['Asset', 'Expense'])) {
            return $this->opening_balance + $debits - $credits;
        } else {
            return $this->opening_balance + $credits - $debits;
        }
    }
}
