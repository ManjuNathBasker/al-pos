<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Permission\PermissionRegistrar;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // 1. Get company ID from session or find default
            $companyId = session('company_id');

            if (!$companyId) {
                $company = $user->ownedCompanies()->first() ?? $user->companies()->first();
                
                // Super Admin fallback: pick first company in system
                if (!$company && $user->isAdmin()) {
                    $company = \App\Models\Company::first();
                }

                if ($company) {
                    $companyId = $company->id;
                    session(['company_id' => $companyId]);
                }
            }

            // 2. Verify access to session company if ID exists
            if ($companyId) {
                $currentCompany = \App\Models\Company::find($companyId);
                
                // Verify access: Super Admin, Owner, or in Pivot
                $hasAccess = $user->isAdmin() || 
                             ($currentCompany && $currentCompany->owner_id === $user->id) || 
                             DB::table('company_user')
                                ->where('company_id', $companyId)
                                ->where('user_id', $user->id)
                                ->exists();

                if (!$hasAccess || !$currentCompany) {
                    // Try to find any valid company they DO have access to
                    $validCompany = $user->ownedCompanies()->first() ?? $user->companies()->first();
                    
                    if ($validCompany) {
                        $companyId = $validCompany->id;
                        session(['company_id' => $companyId]);
                        $currentCompany = $validCompany;
                    } else {
                        // No access to any company
                        $companyId = null;
                        session()->forget('company_id');
                    }
                }
            }

            // 3. Set Context and Share with Views
            if ($companyId && isset($currentCompany)) {
                // Set Spatie team context
                app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($companyId);

                // ALWAYS clear user relations to force fresh load with current team context
                $user->unsetRelation('roles');
                $user->unsetRelation('permissions');

                view()->share('currentCompany', $currentCompany);

                // Share user's companies for the switcher (using simple query)
                $userCompanies = $user->isAdmin() 
                    ? \App\Models\Company::all() 
                    : \App\Models\Company::where('owner_id', $user->id)
                        ->orWhereHas('users', function($q) use ($user) {
                            $q->where('users.id', $user->id);
                        })->get();
                
                view()->share('userCompanies', $userCompanies);
            } else {
                // No company access - logout and redirect
                if (!$request->is('logout') && !$request->is('login')) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect()->route('login')->with('error', 'No company access assigned.');
                }
            }
        }

        return $next($request);
    }
}
