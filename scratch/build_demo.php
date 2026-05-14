<?php
use App\Models\User;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

echo "--- Building New Demo Data ---\n";

// 1. Create a NEW Admin
$admin = User::create([
    'name' => 'Global Administrator',
    'email' => 'admin@system.com',
    'password' => Hash::make('password'),
]);
$admin->assignRole('Admin');
echo "Admin Created: admin@system.com\n";

// 2. Create a NEW Owner
$owner = User::create([
    'name' => 'Hotel Owner',
    'email' => 'owner@hotel.com',
    'password' => Hash::make('password'),
]);
echo "Owner Created: owner@hotel.com\n";

// 3. Create FIRST Company
$hotel = Company::create([
    'name' => 'Grand Hotel',
    'email' => 'contact@grandhotel.com',
    'owner_id' => $owner->id,
]);
$owner->companies()->attach($hotel->id);
echo "First Company Created: Grand Hotel\n";

// 4. Assign Owner Role (Team Scoped)
setPermissionsTeamId($hotel->id);
$owner->assignRole('Owner');

// 5. Add Category and Products to Grand Hotel
session(['company_id' => $hotel->id]); // Force session for model creation
$category = \App\Models\Category::create([
    'name' => 'Food & Beverages',
    'company_id' => $hotel->id,
]);

Product::create([
    'name' => 'Club Sandwich',
    'price' => 12.50,
    'category_id' => $category->id,
    'company_id' => $hotel->id,
]);
Product::create([
    'name' => 'Margarita Pizza',
    'price' => 18.00,
    'category_id' => $category->id,
    'company_id' => $hotel->id,
]);
echo "Products added to Grand Hotel.\n";

// 6. Create NEXT Company
$resort = Company::create([
    'name' => 'Boutique Resort',
    'email' => 'info@boutiqueresort.com',
    'owner_id' => $owner->id, // Same owner
]);
$owner->companies()->attach($resort->id);
echo "Next Company Created: Boutique Resort\n";

// 7. Assign Owner Role for Next Company
setPermissionsTeamId($resort->id);
$owner->assignRole('Owner');

echo "--- Build Complete ---\n";
