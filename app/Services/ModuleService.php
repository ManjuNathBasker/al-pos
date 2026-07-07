<?php

namespace App\Services;

use App\Models\Company;
use App\Models\ModuleSetting;

class ModuleService
{
    /**
     * Define all available modules and their default states for business types.
     */
    public static function getAvailableModules(): array
    {
        return [
            'restaurant_mode' => [
                'name' => 'Restaurant Mode',
                'description' => 'Enables dining, tables, and kitchen workflows.',
                'category' => 'Food & Dining',
                'defaults' => ['restaurant', 'cafe', 'bakery', 'food_court'],
            ],
            'table_management' => [
                'name' => 'Table Management',
                'description' => 'Manage physical tables and sections.',
                'category' => 'Food & Dining',
                'defaults' => ['restaurant', 'cafe', 'food_court'],
            ],
            'qr_ordering' => [
                'name' => 'QR Table Ordering',
                'description' => 'Allow guests to order via QR scan.',
                'category' => 'Food & Dining',
                'defaults' => ['restaurant', 'cafe', 'food_court'],
            ],
            'kitchen_display' => [
                'name' => 'Kitchen Display (KDS)',
                'description' => 'Realtime digital screen for kitchen orders.',
                'category' => 'Food & Dining',
                'defaults' => ['restaurant', 'cafe', 'bakery', 'food_court'],
            ],
            'waiter_panel' => [
                'name' => 'Waiter Panel',
                'description' => 'Mobile interface for waitstaff.',
                'category' => 'Food & Dining',
                'defaults' => ['restaurant', 'cafe'],
            ],
            'inventory_management' => [
                'name' => 'Inventory Alerts',
                'description' => 'Get notified when stock levels are low.',
                'category' => 'Retail & Inventory',
                'defaults' => ['retail', 'supermarket', 'pharmacy', 'boutique', 'bookstall'],
            ],
        ];
    }

    /**
     * Enable default modules based on business type.
     */
    public function initializeModules(Company $company): void
    {
        $type = $company->business_type;
        $modules = self::getAvailableModules();

        foreach ($modules as $key => $details) {
            $isEnabled = in_array($type, $details['defaults']);
            
            ModuleSetting::updateOrCreate(
                ['company_id' => $company->id, 'module_key' => $key],
                ['is_enabled' => $isEnabled]
            );
        }
    }

    /**
     * Check if a module is active for the current company.
     */
    public function isActive(string $moduleKey, ?Company $company = null): bool
    {
        if (!$company) {
            $companyId = session('company_id');
            if (!$companyId) {
                return false;
            }
            $company = Company::find($companyId);
        }
        
        if (!$company) {
            return false;
        }

        // Use the model method for consistency
        return $company->isModuleEnabled($moduleKey);
    }
}
