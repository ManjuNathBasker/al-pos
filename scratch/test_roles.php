<?php

use App\Models\User;
use App\Models\Company;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

// 1. Setup
echo "--- Starting Role/Permission Audit ---\n";

$registrar = app(PermissionRegistrar::class);

// Clear Cache
$registrar->forgetCachedPermissions();

// 2. Test Admin (formerly Super Admin)
$admin = User::where('email', 'admin@pos.com')->first();
if ($admin) {
    echo "Admin: {$admin->name}\n";
    Auth::login($admin);
    
    // Test access across any company
    $company = Company::first();
    if ($company) {
        $registrar->setPermissionsTeamId($company->id);
        $admin->unsetRelation('roles');
        $admin->unsetRelation('permissions');
        
        $canView = $admin->can('view products');
        echo "Admin can view products in Company {$company->id}: " . ($canView ? 'YES' : 'NO') . "\n";
    }
}

// 3. Test Custom Role
echo "\nTesting Custom Role Setup...\n";
$companyA = Company::first();
$companyB = Company::skip(1)->first() ?? Company::create(['name' => 'Test Company B', 'email' => 'b@test.com']);

$testUser = User::where('email', 'staff@demo.com')->first() ?? User::create([
    'name' => 'Test User',
    'email' => 'staff@demo.com',
    'password' => bcrypt('password')
]);

// Ensure user is in both companies
$testUser->companies()->syncWithoutDetaching([$companyA->id, $companyB->id]);

// Create "Manager" role in Company A
$registrar->setPermissionsTeamId($companyA->id);
$managerRoleA = Role::findOrCreate('Manager', 'web'); // findOrCreate uses current team_id if not specified? 
// Actually Spatie's Role::findOrCreate doesn't automatically use team_id unless you pass it or set it.
// Wait, if config teams is true, it uses the registrar's team id.

$managerRoleA->syncPermissions(['view products', 'access pos']);
$testUser->assignRole($managerRoleA);

// Create "Staff" role in Company B
$registrar->setPermissionsTeamId($companyB->id);
$staffRoleB = Role::findOrCreate('Staff', 'web');
$staffRoleB->syncPermissions(['access pos']);
$testUser->assignRole($staffRoleB);

// 4. Verify Context Switching
echo "\nVerifying Context Switching for {$testUser->name}:\n";

// Switch to Company A
echo "Switching to Company A (Manager)...\n";
$registrar->setPermissionsTeamId($companyA->id);
$testUser->unsetRelation('roles');
$testUser->unsetRelation('permissions');
echo "Can view products? " . ($testUser->can('view products') ? 'YES' : 'NO') . "\n";
echo "Can access POS? " . ($testUser->can('access pos') ? 'YES' : 'NO') . "\n";

// Switch to Company B
echo "Switching to Company B (Staff)...\n";
$registrar->setPermissionsTeamId($companyB->id);
$testUser->unsetRelation('roles');
$testUser->unsetRelation('permissions');
echo "Can view products? " . ($testUser->can('view products') ? 'YES' : 'NO') . "\n";
echo "Can access POS? " . ($testUser->can('access pos') ? 'YES' : 'NO') . "\n";

echo "\n--- Audit Complete ---\n";
