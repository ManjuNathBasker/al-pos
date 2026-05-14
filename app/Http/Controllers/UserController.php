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
        if (!$currentUser->isAdmin() && !$user->companies->contains('id', session('company_id'))) {
            abort(403);
        }

        if ($currentUser->isAdmin()) {
            $companies = Company::all();
        } else {
            $companies = $currentUser->companies;
        }

        $roles = Role::where('team_id', session('company_id'))
                     ->orWhereNull('team_id')
                     ->get();
        
        setPermissionsTeamId(session('company_id'));
        $userRoles = $user->roles()->where('roles.team_id', session('company_id'))->pluck('name')->toArray();
        $userCompanies = $user->companies->pluck('id')->toArray();

        return view('users.edit', compact('user', 'roles', 'userRoles', 'companies', 'userCompanies'));
    }

    public function update(Request $request, User $user)
    {
        $currentUser = Auth::user();
        if (!$currentUser->isAdmin() && !$user->companies->contains('id', session('company_id'))) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'companies' => 'required|array',
            'roles' => 'array'
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        // Sync companies
        $user->companies()->sync($request->companies);

        // Sync roles for each selected company
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
                    // Using syncRoles with team context only affects the current team
                    $user->syncRoles($validRoles);
                }
            }
            // Reset to session company
            setPermissionsTeamId(session('company_id'));
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
