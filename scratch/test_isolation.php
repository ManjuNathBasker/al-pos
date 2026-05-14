<?php

use App\Models\User;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

echo "--- Verifying Tenant Isolation ---\n";

$companyA = Company::first();
$companyB = Company::skip(1)->first();

if (!$companyB) {
    $companyB = Company::create(['name' => 'Isolation Test B', 'email' => 'iso@test.com']);
}

// Create product in Company B
$prodB = Product::create([
    'name' => 'Secret Product B',
    'price' => 100,
    'company_id' => $companyB->id,
    'category_id' => 1 // Assuming category 1 exists
]);

$staffA = User::where('email', 'staff@demo.com')->first();
Auth::login($staffA);

// Set context to Company A
session(['company_id' => $companyA->id]);
app(PermissionRegistrar::class)->setPermissionsTeamId($companyA->id);

// Query products
$products = Product::all(); // Should be scoped via Global Scope if implemented
echo "Products visible in Company A: " . $products->count() . "\n";
foreach($products as $p) {
    if ($p->company_id != $companyA->id) {
        echo "FAILURE: Found product from company {$p->company_id} while in company {$companyA->id}!\n";
    }
}

$foundSecret = Product::where('name', 'Secret Product B')->exists();
echo "Can see Secret Product B via where()? " . ($foundSecret ? 'YES (FAILURE)' : 'NO (SUCCESS)') . "\n";

echo "--- Isolation Audit Complete ---\n";
