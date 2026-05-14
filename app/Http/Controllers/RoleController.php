<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::where('team_id', session('company_id'))
                     ->orWhereNull('team_id')
                     ->get();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissionsByModule = [
            'Companies' => ['view companies', 'create companies', 'edit companies', 'delete companies'],
            'Users' => ['view users', 'create users', 'edit users', 'delete users'],
            'Roles' => ['view roles', 'create roles', 'edit roles', 'delete roles'],
            'Products' => ['view products', 'create products', 'edit products', 'delete products'],
            'Orders' => ['view orders', 'create orders', 'edit orders', 'delete orders'],
            'Customers' => ['view customers', 'create customers', 'edit customers', 'delete customers'],
            'Coupons' => ['view coupons', 'create coupons', 'edit coupons', 'delete coupons'],
            'POS' => ['access pos', 'create orders', 'process billing'],
            'Reports' => ['view reports'],
            'Settings' => ['manage settings'],
        ];
        
        return view('roles.create', compact('permissionsByModule'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'required|array',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'team_id' => session('company_id'),
            'guard_name' => 'web'
        ]);

        $role->syncPermissions($request->permissions);

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        // Allow access if it's a team role for current company, 
        // OR if it's a global role (team_id null) and user is an Admin.
        $isGlobal = is_null($role->team_id);
        if (!$isGlobal && $role->team_id != session('company_id')) {
            abort(403);
        }
        
        if ($isGlobal && !auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can edit global roles.');
        }

        $permissionsByModule = [
            'Companies' => ['view companies', 'create companies', 'edit companies', 'delete companies'],
            'Users' => ['view users', 'create users', 'edit users', 'delete users'],
            'Roles' => ['view roles', 'create roles', 'edit roles', 'delete roles'],
            'Products' => ['view products', 'create products', 'edit products', 'delete products'],
            'Orders' => ['view orders', 'create orders', 'edit orders', 'delete orders'],
            'Customers' => ['view customers', 'create customers', 'edit customers', 'delete customers'],
            'Coupons' => ['view coupons', 'create coupons', 'edit coupons', 'delete coupons'],
            'POS' => ['access pos', 'create orders', 'process billing'],
            'Reports' => ['view reports'],
            'Settings' => ['manage settings'],
        ];

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact('role', 'permissionsByModule', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $isGlobal = is_null($role->team_id);
        if (!$isGlobal && $role->team_id != session('company_id')) {
            abort(403);
        }
        
        if ($isGlobal && !auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can update global roles.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'required|array',
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        $isGlobal = is_null($role->team_id);
        if (!$isGlobal && $role->team_id != session('company_id')) {
            abort(403);
        }
        
        if ($isGlobal && !auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can delete global roles.');
        }

        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }
}
