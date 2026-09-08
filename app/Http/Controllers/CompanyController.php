<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\ModuleSetting;
use App\Services\ModuleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class CompanyController extends Controller
{
    protected $moduleService;

    public function __construct(ModuleService $moduleService)
    {
        $this->moduleService = $moduleService;
    }
    /**
     * Switch the active company.
     */
    public function switch(Request $request, Company $company)
    {
        $this->authorize('switch', $company);

        $user = Auth::user();

        if (!$company->is_active && !$user->isAdmin()) {
            return back()->with('error', 'Cannot switch to an inactive company.');
        }

        // 1. Store active company in session
        session(['company_id' => $company->id]);
        
        // 2. Clear POS cart when switching companies
        session()->forget('pos_cart'); 

        // 3. Clear Spatie permission cache and set new team ID context
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);

        // 4. Clear cached role/permission relations for this user
        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        return redirect()->route('dashboard')->with('success', "Switched to {$company->name}");
    }

    /**
     * Display a listing of companies.
     */
    public function index()
    {
        $user = Auth::user();
        if ($user->isAdmin()) {
            $companies = Company::all();
        } else {
            // Include both assigned and owned companies
            $companies = Company::where('owner_id', $user->id)
                ->orWhereHas('users', function($q) use ($user) {
                    $q->where('users.id', $user->id);
                })->get();
        }

        return view('companies.index', compact('companies'));
    }

    public function create()
    {
        $currencyConfig = default_currency_config();
        return view('companies.create', compact('currencyConfig'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                     => 'required|string|max:255',
            'email'                    => 'nullable|email|max:255',
            'phone'                    => 'nullable|string|max:20',
            'address'                  => 'nullable|string',
            'business_type'            => 'required|string|in:retail,restaurant,cafe,bakery,pharmacy,food_court,supermarket,bookstall,boutique',
            'is_active'                => 'nullable|boolean',
            'currency_name'            => 'nullable|string|max:100',
            'currency_code'            => 'nullable|string|max:10',
            'currency_symbol'          => 'nullable|string|max:10',
            'currency_decimal_places'  => 'nullable|integer|min:0|max:4',
            'currency_symbol_position' => 'nullable|in:before,after',
        ]);

        $user = Auth::user();

        $settings = [
            'currency' => [
                'name'            => $request->currency_name ?: 'Indian Rupee',
                'code'            => strtoupper(trim($request->currency_code ?: 'INR')),
                'symbol'          => $request->currency_symbol ?: '₹',
                'decimal_places'  => (int) ($request->currency_decimal_places ?? 2),
                'symbol_position' => $request->currency_symbol_position ?: 'before',
            ]
        ];

        $isActive = $request->has('is_active') ? (bool) $request->is_active : true;

        // Create company and set current user as owner
        $company = Company::create(array_merge(
            $request->only('name', 'email', 'phone', 'address', 'business_type'),
            [
                'is_active' => $isActive,
                'owner_id'  => $user->id,
                'settings'  => $settings,
            ]
        ));

        // Also assign user to company_user pivot for consistency
        $user->companies()->attach($company->id);

        // Auto-assign "Owner" role for this new company if it exists
        $ownerRole = \Spatie\Permission\Models\Role::where('name', 'Owner')->first();
        if ($ownerRole) {
            setPermissionsTeamId($company->id);
            $user->assignRole($ownerRole);
            
            // Restore session company context for the rest of this request
            setPermissionsTeamId(session('company_id'));
        }

        // Initialize default modules based on business type
        $this->moduleService->initializeModules($company);

        return redirect()->route('companies.index')->with('success', 'Company created successfully.');
    }

    public function edit(Company $company)
    {
        $this->authorize('view', $company);

        $cardCommissionTax = $company->getCardCommissionTax();
        $taxPercentage     = $company->getTaxPercentage();
        $currencyConfig    = $company->getCurrencyConfig();

        return view('companies.edit', compact('company', 'cardCommissionTax', 'taxPercentage', 'currencyConfig'));
    }

    public function update(Request $request, Company $company)
    {
        $this->authorize('update', $company);

        $request->validate([
            'name'                     => 'required|string|max:255',
            'email'                    => 'nullable|email|max:255',
            'phone'                    => 'nullable|string|max:20',
            'address'                  => 'nullable|string',
            'business_type'            => 'required|string|in:retail,restaurant,cafe,bakery,pharmacy,food_court,supermarket,bookstall,boutique',
            'is_active'                => 'nullable|boolean',
            'card_commission_tax'      => 'nullable|numeric|min:0|max:100',
            'tax_percentage'           => 'nullable|numeric|min:0|max:100',
            'currency_name'            => 'nullable|string|max:100',
            'currency_code'            => 'nullable|string|max:10',
            'currency_symbol'          => 'nullable|string|max:10',
            'currency_decimal_places'  => 'nullable|integer|min:0|max:4',
            'currency_symbol_position' => 'nullable|in:before,after',
        ]);

        // Merge card_commission_tax, tax_percentage and currency settings into the settings JSON
        $settings = $company->settings ?? [];
        $settings['card_commission_tax'] = (float) ($request->card_commission_tax ?? 0);
        $settings['tax_percentage']      = (float) ($request->tax_percentage ?? $settings['card_commission_tax'] ?? 0);

        $settings['currency'] = [
            'name'            => $request->currency_name ?: ($settings['currency']['name'] ?? 'Indian Rupee'),
            'code'            => strtoupper(trim($request->currency_code ?: ($settings['currency']['code'] ?? 'INR'))),
            'symbol'          => $request->currency_symbol ?: ($settings['currency']['symbol'] ?? '₹'),
            'decimal_places'  => isset($request->currency_decimal_places) ? (int) $request->currency_decimal_places : (int) ($settings['currency']['decimal_places'] ?? 2),
            'symbol_position' => $request->currency_symbol_position ?: ($settings['currency']['symbol_position'] ?? 'before'),
        ];

        $isActive = $request->has('is_active') ? (bool) $request->is_active : true;

        $company->update(array_merge(
            $request->only('name', 'email', 'phone', 'address', 'business_type'),
            [
                'is_active' => $isActive,
                'settings'  => $settings,
            ]
        ));

        return redirect()->route('companies.index')->with('success', 'Company updated successfully.');
    }

    public function destroy(Company $company)
    {
        $this->authorize('delete', $company);

        $company->delete();

        return redirect()->route('companies.index')->with('success', 'Company deleted successfully.');
    }

    /**
     * Show module settings for a company.
     */
    public function modules(Company $company)
    {
        $this->authorize('update', $company);
        
        $availableModules = ModuleService::getAvailableModules();
        $enabledModules = $company->moduleSettings()->pluck('is_enabled', 'module_key')->toArray();

        return view('companies.modules', compact('company', 'availableModules', 'enabledModules'));
    }

    /**
     * Update module settings.
     */
    public function updateModules(Request $request, Company $company)
    {
        $this->authorize('update', $company);

        $modules = ModuleService::getAvailableModules();
        
        foreach ($modules as $key => $details) {
            $isEnabled = $request->has("modules.$key");
            
            ModuleSetting::updateOrCreate(
                ['company_id' => $company->id, 'module_key' => $key],
                ['is_enabled' => $isEnabled]
            );
        }

        return redirect()->back()->with('success', 'Modules updated successfully.');
    }
}
