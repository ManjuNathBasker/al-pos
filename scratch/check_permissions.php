<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Company;

$user = User::find(1);
$company = Company::find(1);

// Set Spatie permissions team ID
app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);

echo "User: {$user->name} (ID: {$user->id})\n";
echo "Roles for Company {$company->id}:\n";
foreach ($user->roles as $role) {
    echo "- Role: {$role->name} (ID: {$role->id}, Team ID: {$role->pivot->team_id})\n";
    echo "  Permissions in role:\n";
    foreach ($role->permissions as $perm) {
        echo "    * {$perm->name}\n";
    }
}

echo "Direct Permissions for User:\n";
foreach ($user->permissions as $perm) {
    echo "- {$perm->name}\n";
}

echo "Check can('access table map'): " . ($user->can('access table map') ? 'YES' : 'NO') . "\n";
echo "Check can('access kitchen kds'): " . ($user->can('access kitchen kds') ? 'YES' : 'NO') . "\n";
echo "Check can('access waiter panel'): " . ($user->can('access waiter panel') ? 'YES' : 'NO') . "\n";
