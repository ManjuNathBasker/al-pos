<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->isAdmin()) {
            $users = User::all();
        } else {
            // Users in the current company
            $company = Company::find(session('company_id'));
            $users = $company ? $company->users : collect();
        }

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $user = Auth::user();
        if ($user->isAdmin()) {
            $companies = Company::all();
        } else {
            $companies = $user->companies;
        }

        $roles = Role::where('team_id', session('company_id'))
                     ->orWhereNull('team_id')
                     ->get();

        return view('users.create', compact('roles', 'companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'companies' => 'required|array',
            'roles' => 'array'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Attach to selected companies
        $user->companies()->sync($request->companies);

        // Assign roles for companies (only if they exist for that company)
        if ($request->roles) {
            foreach ($request->companies as $companyId) {
                setPermissionsTeamId($companyId);
                
                $validRoles = Role::whereIn('name', $request->roles)
                    ->where(function($q) use ($companyId) {
                        $q->where('team_id', $companyId)->orWhereNull('team_id');
                    })
                    ->pluck('name')
                    ->toArray();

                if (!empty($validRoles)) {
                    $user->assignRole($validRoles);
                }
            }
            // Reset to session company
            setPermissionsTeamId(session('company_id'));
        }

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $currentUser = Auth::user();
        $currentCompanyId = session('company_id');

        if (!$currentUser->isAdmin() && !$user->companies->contains('id', $currentCompanyId)) {
            abort(403);
        }

        if ($currentUser->isAdmin()) {
            $companies = Company::all();
        } else {
            $companies = $currentUser->companies;
        }

        $roles = Role::where('team_id', $currentCompanyId)
                     ->orWhereNull('team_id')
                     ->get();
        
        if ($currentCompanyId) {
            setPermissionsTeamId($currentCompanyId);
        }

        $userRoles = $user->roles()
            ->where(function($q) use ($currentCompanyId) {
                $q->where('roles.team_id', $currentCompanyId)->orWhereNull('roles.team_id');
            })
            ->pluck('name')
            ->toArray();

        $assignedCompanyIds = $user->companies->pluck('id')->toArray();

        return view('users.edit', compact('user', 'roles', 'userRoles', 'companies', 'assignedCompanyIds'));
    }

    public function update(Request $request, User $user)
    {
        $currentUser = Auth::user();
        $currentCompanyId = session('company_id');

        if (!$currentUser->isAdmin() && !$user->companies->contains('id', $currentCompanyId)) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'companies' => 'required|array',
            'companies.*' => 'exists:companies,id',
            'roles' => 'nullable|array'
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        // Validate & sync companies (only valid existing company IDs)
        $validCompanyIds = Company::whereIn('id', (array)$request->companies)->pluck('id')->toArray();
        if (empty($validCompanyIds) && $currentCompanyId) {
            $validCompanyIds = [$currentCompanyId];
        }

        $user->companies()->sync($validCompanyIds);

        // Sync roles for each assigned company
        $submittedRoles = $request->input('roles', []);
        foreach ($validCompanyIds as $companyId) {
            setPermissionsTeamId($companyId);
            
            $validRoles = Role::whereIn('name', (array)$submittedRoles)
                ->where(function($q) use ($companyId) {
                    $q->where('team_id', $companyId)->orWhereNull('team_id');
                })
                ->pluck('name')
                ->toArray();

            $user->syncRoles($validRoles);
        }

        // Reset to active session company team context
        if ($currentCompanyId) {
            setPermissionsTeamId($currentCompanyId);
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // For now just detach from company
        $user->companies()->detach(session('company_id'));
        return redirect()->route('users.index')->with('success', 'User removed from company.');
    }
}
