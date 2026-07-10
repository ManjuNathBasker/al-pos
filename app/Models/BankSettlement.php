<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\MultiTenantTrait;

class BankSettlement extends Model
{
    use HasFactory, MultiTenantTrait;

    protected $fillable = [
        'company_id',
        'card_transaction_id',
        'bank_statement_reference',
        'expected_settlement_amount',
        'actual_settlement_amount',
        'settlement_difference',
        'bank_charges',
        'processing_charges',
        'adjustment_entry_id',
        'status',
        'settlement_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'expected_settlement_amount' => 'float',
        'actual_settlement_amount' => 'float',
        'settlement_difference' => 'float',
        'bank_charges' => 'float',
        'processing_charges' => 'float',
        'settlement_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function cardTransaction()
    {
        return $this->belongsTo(CardTransaction::class);
    }

    public function adjustmentEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'adjustment_entry_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
