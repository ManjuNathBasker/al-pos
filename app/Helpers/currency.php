<?php

use App\Models\Company;
use App\Models\Order;
use App\Models\Purchase;

if (!function_exists('default_currency_config')) {
    /**
     * Get system default currency configuration.
     */
    function default_currency_config(): array
    {
        return [
            'name'            => 'Indian Rupee',
            'code'            => 'INR',
            'symbol'          => '₹',
            'decimal_places'  => 2,
            'symbol_position' => 'before', // 'before' | 'after'
        ];
    }
}

if (!function_exists('currency_config')) {
    /**
     * Resolve currency configuration based on given context (Order, Purchase, Company, or current tenant session).
     */
    function currency_config(mixed $context = null): array
    {
        $default = default_currency_config();

        // 0. Context is an explicit configuration array
        if (is_array($context)) {
            return [
                'name'            => $context['name'] ?? $default['name'],
                'code'            => $context['code'] ?? $default['code'],
                'symbol'          => $context['symbol'] ?? $default['symbol'],
                'decimal_places'  => (int) ($context['decimal_places'] ?? $default['decimal_places']),
                'symbol_position' => $context['symbol_position'] ?? $default['symbol_position'],
            ];
        }

        // 1. Context is an Order with saved snapshot
        if ($context instanceof Order) {
            if (!empty($context->currency_symbol) || !empty($context->currency_code)) {
                return [
                    'name'            => $context->currency_code ?? $default['name'],
                    'code'            => $context->currency_code ?? $default['code'],
                    'symbol'          => $context->currency_symbol ?? $default['symbol'],
                    'decimal_places'  => (int) ($context->currency_decimal_places ?? $default['decimal_places']),
                    'symbol_position' => $context->currency_symbol_position ?? $default['symbol_position'],
                ];
            }
            if ($context->company_id) {
                $comp = Company::find($context->company_id);
                if ($comp) {
                    return $comp->getCurrencyConfig();
                }
            }
        }

        // 2. Context is a Purchase with saved snapshot
        if ($context instanceof Purchase) {
            if (!empty($context->currency_symbol) || !empty($context->currency_code)) {
                return [
                    'name'            => $context->currency_code ?? $default['name'],
                    'code'            => $context->currency_code ?? $default['code'],
                    'symbol'          => $context->currency_symbol ?? $default['symbol'],
                    'decimal_places'  => (int) ($context->currency_decimal_places ?? $default['decimal_places']),
                    'symbol_position' => $context->currency_symbol_position ?? $default['symbol_position'],
                ];
            }
            if ($context->company_id) {
                $comp = Company::find($context->company_id);
                if ($comp) {
                    return $comp->getCurrencyConfig();
                }
            }
        }

        // 3. Context is a Company model
        if ($context instanceof Company) {
            return $context->getCurrencyConfig();
        }

        // 4. Session Company or Authenticated User's Company
        $companyId = session('company_id');
        if ($companyId) {
            $company = Company::find($companyId);
            if ($company) {
                return $company->getCurrencyConfig();
            }
        }

        if (auth()->check()) {
            $user = auth()->user();
            if (!empty($user->company_id)) {
                $company = Company::find($user->company_id);
                if ($company) {
                    return $company->getCurrencyConfig();
                }
            }
        }

        return $default;
    }
}

if (!function_exists('current_currency_config')) {
    /**
     * Get active tenant's currency configuration.
     */
    function current_currency_config(): array
    {
        return currency_config();
    }
}

if (!function_exists('currency_symbol')) {
    /**
     * Get the currency symbol for the current context.
     */
    function currency_symbol(mixed $context = null): string
    {
        $config = currency_config($context);
        return $config['symbol'] ?? '₹';
    }
}

if (!function_exists('currency_code')) {
    /**
     * Get the currency code (e.g. INR, USD, AED) for the current context.
     */
    function currency_code(mixed $context = null): string
    {
        $config = currency_config($context);
        return $config['code'] ?? 'INR';
    }
}

if (!function_exists('currency_symbol_position')) {
    /**
     * Get currency symbol position ('before' or 'after').
     */
    function currency_symbol_position(mixed $context = null): string
    {
        $config = currency_config($context);
        return $config['symbol_position'] ?? 'before';
    }
}

if (!function_exists('currency_decimal_places')) {
    /**
     * Get configured decimal places.
     */
    function currency_decimal_places(mixed $context = null): int
    {
        $config = currency_config($context);
        return (int) ($config['decimal_places'] ?? 2);
    }
}

if (!function_exists('format_currency')) {
    /**
     * Centralized currency formatter.
     * Formats monetary values with correct symbol, position, decimal places, and thousands separators.
     * Handles negative values properly (e.g. -₹150.00 or -150.00 €).
     */
    function format_currency(mixed $amount, mixed $context = null, ?int $decimals = null): string
    {
        if ($amount === null || $amount === '') {
            $amount = 0.0;
        }

        $config = currency_config($context);
        $decimalPlaces = $decimals !== null ? $decimals : (int) ($config['decimal_places'] ?? 2);
        $symbol = $config['symbol'] ?? '₹';
        $position = $config['symbol_position'] ?? 'before';

        $numericVal = (float) $amount;
        $isNegative = $numericVal < 0;
        $absAmount = abs($numericVal);

        $formattedNumber = number_format($absAmount, $decimalPlaces, '.', ',');

        if ($position === 'after') {
            $result = $formattedNumber . ' ' . $symbol;
        } else {
            $result = $symbol . $formattedNumber;
        }

        return $isNegative ? '-' . $result : $result;
    }
}
