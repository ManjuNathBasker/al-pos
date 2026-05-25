<?php

namespace App\Models;

use App\Traits\MultiTenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    use HasFactory, SoftDeletes, MultiTenantTrait;

    protected $fillable = [
        'company_id',
        'journal_number',
        'transaction_date',
        'reference_type', // 'Order', 'Purchase', 'Expense', etc.
        'reference_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $entry) {
            if (empty($entry->journal_number)) {
                $entry->journal_number = 'JNL-' . str_pad(
                    (static::withTrashed()->max('id') ?? 0) + 1,
                    6, '0', STR_PAD_LEFT
                );
            }
        });
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function items()
    {
        return $this->hasMany(JournalEntryItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
