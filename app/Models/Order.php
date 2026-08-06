<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\MultiTenantTrait;

class Order extends Model
{
    use HasFactory, SoftDeletes, MultiTenantTrait;

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_id',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'amount_paid',
        'change_due',
        'payment_method',
        'payment_reference',
        'status',
        'note',
        'cash_amount',
        'upi_amount',
        'card_amount',
        'wallet_used',
        'change_returned',
        'total_paid',
        'coupon_id',
        'discount_type',
        'discount_value',
        'company_id',
        'created_by',
        'updated_by',
        'table_id',
        'service_type',
        'kitchen_status',
        'waiter_id',
        'is_stock_deducted',
        // Card commission fields
        'card_type_id',
        'card_commission_amount',
        'card_commission_tax_amount',
        'card_commission_total_deduction',
        'card_net_received',
        // Delivery partner fields
        'delivery_partner_id',
        'delivery_commission_amount',
        'settlement_status',
    ];

    protected $casts = [
        'is_stock_deducted' => 'boolean',
        'subtotal'        => 'float',
        'discount_amount' => 'float',
        'tax_amount'      => 'float',
        'total_amount'    => 'float',
        'amount_paid'     => 'float',
        'change_due'      => 'float',
        'cash_amount'     => 'float',
        'upi_amount'      => 'float',
        'card_amount'     => 'float',
        'wallet_used'     => 'float',
        'change_returned' => 'float',
        'total_paid'      => 'float',
        // Card commission casts
        'card_commission_amount'           => 'float',
        'card_commission_tax_amount'       => 'float',
        'card_commission_total_deduction'  => 'float',
        'card_net_received'                => 'float',
    ];

    // ── Boot: auto-generate order number ───────────────────────────
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . str_pad(
                    (static::withTrashed()->max('id') ?? 0) + 1,
                    5, '0', STR_PAD_LEFT
                );
            }
        });
    }

    // ── Relationships ────────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function table()
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    public function waiter()
    {
        return $this->belongsTo(User::class, 'waiter_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function kitchenTickets()
    {
        return $this->hasMany(KitchenTicket::class);
    }

    public function cardTransactions()
    {
        return $this->hasMany(CardTransaction::class);
    }

    public function cardType()
    {
        return $this->belongsTo(CardType::class);
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}
