<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Account;

class Company extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'address', 'owner_id', 'business_type', 'is_active', 'settings'];

    protected $casts = [
        'is_active' => 'boolean',
        'settings'  => 'array',
    ];

    /**
     * Boot the model and register lifecycle hooks.
     */
    protected static function booted(): void
    {
        static::created(function (Company $company) {
            // Auto-create a default Cash account for POS usage.
            // This is the system account used for register opening balance,
            // cash sales tracking, and the default POS payment method.
            Account::firstOrCreate(
                [
                    'company_id' => $company->id,
                    'account_name' => 'Cash',
                ],
                [
                    'account_code' => '1000',
                    'account_type' => 'Asset',
                    'opening_balance' => 0.00,
                    'current_balance' => 0.00,
                    'status' => true,
                    'is_system' => true,
                    'show_in_pos' => true,
                ]
            );
        });
    }

    /**
     * Get the owner of the company.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get users assigned to this company.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Get module settings for the company.
     */
    public function moduleSettings()
    {
        return $this->hasMany(ModuleSetting::class);
    }

    /**
     * Check if a specific module is enabled.
     */
    public function isModuleEnabled(string $moduleKey): bool
    {
        return $this->moduleSettings()->where('module_key', $moduleKey)->where('is_enabled', true)->exists();
    }

    /**
     * Get the card commission tax percentage from company settings.
     * Returns 0.0 if not configured.
     */
    public function getCardCommissionTax(): float
    {
        $settings = $this->settings ?? [];
        return (float) ($settings['card_commission_tax'] ?? 0);
    }

    /**
     * Get the company's currency configuration.
     */
    public function getCurrencyConfig(): array
    {
        $settings = $this->settings ?? [];
        $currency = $settings['currency'] ?? [];

        return [
            'name'            => $currency['name'] ?? 'Indian Rupee',
            'code'            => $currency['code'] ?? 'INR',
            'symbol'          => $currency['symbol'] ?? '₹',
            'decimal_places'  => (int) ($currency['decimal_places'] ?? 2),
            'symbol_position' => $currency['symbol_position'] ?? 'before',
        ];
    }

    /**
     * Get currency symbol.
     */
    public function getCurrencySymbol(): string
    {
        return $this->getCurrencyConfig()['symbol'];
    }

    /**
     * Get currency code.
     */
    public function getCurrencyCode(): string
    {
        return $this->getCurrencyConfig()['code'];
    }

    /**
     * Get currency decimal places.
     */
    public function getCurrencyDecimalPlaces(): int
    {
        return (int) $this->getCurrencyConfig()['decimal_places'];
    }

    /**
     * Get currency symbol position ('before' | 'after').
     */
    public function getCurrencySymbolPosition(): string
    {
        return $this->getCurrencyConfig()['symbol_position'];
    }

    /**
     * Format a monetary amount using this company's currency rules.
     */
    public function formatCurrency(mixed $amount, ?int $decimals = null): string
    {
        return format_currency($amount, $this, $decimals);
    }
}
