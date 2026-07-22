<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Company;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'companies' => ['view companies', 'create companies', 'edit companies', 'delete companies'],
            'users' => ['view users', 'create users', 'edit users', 'delete users'],
            'roles' => ['view roles', 'create roles', 'edit roles', 'delete roles'],
            'products' => ['view products', 'create products', 'edit products', 'delete products'],
            'categories' => ['view categories', 'create categories', 'edit categories', 'delete categories'],
            'orders' => ['view orders', 'create orders', 'edit orders', 'delete orders'],
            'customers' => ['view customers', 'create customers', 'edit customers', 'delete customers'],
            'coupons' => ['view coupons', 'create coupons', 'edit coupons', 'delete coupons'],
            'pos' => ['access pos', 'process billing'],
            'inventory' => ['view inventory', 'create inventory', 'edit inventory', 'delete inventory', 'manage recipes'],
            'reports' => ['view reports'],
            'settings' => ['manage settings'],
        ];

        foreach ($permissions as $module => $modulePermissions) {
            foreach ($modulePermissions as $permission) {
                Permission::findOrCreate($permission, 'web');
            }
        }

        // Create Admin role (Global)
        $adminRole = Role::findOrCreate('Admin', 'web');
        $adminRole->syncPermissions(Permission::all());

        // Create Owner role (Global template)
        $ownerRole = Role::findOrCreate('Owner', 'web');
        $ownerRole->syncPermissions(Permission::all());

        // Create Staff role (Global template)
        $staffRole = Role::findOrCreate('Staff', 'web');
        $staffRole->syncPermissions([
            'access pos',
            'view companies',
            'view products',
            'view orders',
            'create orders',
            'view customers',
        ]);
    }
}
