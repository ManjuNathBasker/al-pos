<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'register_session_id',
        'type',
        'amount',
        'payment_method',
        'description',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function registerSession()
    {
        return $this->belongsTo(RegisterSession::class, 'register_session_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
