<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class CompanyController extends Controller
{
    /**
     * Switch the active company.
     */
    public function switch(Request $request, Company $company)
    {
        $this->authorize('switch', $company);

        $user = Auth::user();

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
        return view('companies.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $user = Auth::user();

        // Create company and set current user as owner
        $company = Company::create(array_merge(
            $request->all(),
            ['owner_id' => $user->id]
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

        return redirect()->route('companies.index')->with('success', 'Company created successfully.');
    }

    public function edit(Company $company)
    {
        $this->authorize('view', $company);

        return view('companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $this->authorize('update', $company);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $company->update($request->all());

        return redirect()->route('companies.index')->with('success', 'Company updated successfully.');
    }

    public function destroy(Company $company)
    {
        $this->authorize('delete', $company);

        $company->delete();

        return redirect()->route('companies.index')->with('success', 'Company deleted successfully.');
    }
}
