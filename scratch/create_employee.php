<?php

use App\Models\User;
use App\Models\Company;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

echo "--- Creating Multi-Company Employee ---\n";

$company1 = Company::first();
$company2 = Company::skip(1)->first() ?? Company::create(['name' => 'Secondary Test Store', 'email' => 'secondary@test.com']);

$employee = User::updateOrCreate(
    ['email' => 'employee@test.com'],
    [
        'name' => 'Multi-Store Employee',
        'password' => Hash::make('password'),
    ]
);

// Assign to both companies
$employee->companies()->sync([$company1->id, $company2->id]);

// Assign Staff role in both companies
foreach([$company1->id, $company2->id] as $id) {
    setPermissionsTeamId($id);
    $employee->assignRole('Staff');
}

echo "Employee created and assigned to Company {$company1->id} and {$company2->id}\n";
echo "--- Done ---\n";
