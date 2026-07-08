<?php

use App\Models\User;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\Account;
use App\Models\Card;
use App\Models\BankOffer;
use App\Models\RegisterSession;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$company = Company::firstOrCreate(
    ['id' => 1],
    ['name' => 'Yummy']
);

$user = User::where('email', 'admin@yummy.com')->first();
if (!$user) {
    $user = User::create([
        'name' => 'Yummy Owner',
        'email' => 'admin@yummy.com',
        'password' => bcrypt('admin@yummy.com'),
    ]);
    echo "Created admin@yummy.com user.\n";
}

// Ensure attached to company
if (!$user->companies()->where('companies.id', $company->id)->exists()) {
    $user->companies()->attach($company->id);
}

// Assign Admin role inside team context
app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
if (!$user->hasRole('Admin')) {
    $user->assignRole('Admin');
    echo "Assigned Admin role for team " . $company->id . ".\n";
}

// 1. Create Branch
$branch = Branch::firstOrCreate(
    ['company_id' => $company->id, 'code' => 'DT01'],
    [
        'name' => 'Downtown Branch',
        'address' => '123 Main St',
        'is_active' => true,
    ]
);

// 2. Create Category and Product
$category = Category::where('name', 'Yummy Food')->first() ?: Category::create([
    'company_id' => $company->id,
    'name' => 'Yummy Food',
    'slug' => 'yummy-food-' . $company->id
]);

$product1 = Product::where('sku', 'YUMMY-PIZZA-01')->first() ?: Product::create([
    'company_id' => $company->id,
    'name' => 'Yummy Pizza Margherita',
    'category_id' => $category->id,
    'price' => 15.00,
    'sku' => 'YUMMY-PIZZA-01',
    'is_active' => true,
    'track_stock' => false,
]);

$product2 = Product::where('sku', 'YUMMY-BURGER-02')->first() ?: Product::create([
    'company_id' => $company->id,
    'name' => 'Yummy Burger Combo',
    'category_id' => $category->id,
    'price' => 12.00,
    'sku' => 'YUMMY-BURGER-02',
    'is_active' => true,
    'track_stock' => false,
]);

// 3. Create Card Clearing Account (Asset)
$settlementAccount = Account::firstOrCreate(
    ['company_id' => $company->id, 'account_code' => '1025'],
    [
        'account_name' => 'Card Clearing Account',
        'account_type' => 'Asset',
        'status' => true,
        'opening_balance' => 0.00,
        'current_balance' => 0.00,
        'is_system' => false,
        'show_in_pos' => true,
    ]
);

// 4. Create Card Master
$card = Card::firstOrCreate(
    ['company_id' => $company->id, 'card_network' => 'Visa', 'bank_name' => 'HDFC'],
    [
        'card_type' => 'Credit',
        'settlement_account_id' => $settlementAccount->id,
        'service_charge' => 2.00,
        'mdr' => 1.50,
        'processing_fee' => 0.50,
        'settlement_days' => 2,
        'is_active' => true,
    ]
);

// 5. Create Bank Offer
$offer = BankOffer::firstOrCreate(
    ['company_id' => $company->id, 'name' => 'HDFC Visa 10% Promo'],
    [
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addYear()->toDateString(),
        'min_purchase' => 10.00,
        'max_discount' => 20.00,
        'discount_type' => 'percent',
        'discount_value' => 10.00,
        'cashback' => 5.00,
        'merchant_contribution' => 60.00,
        'bank_contribution' => 40.00,
        'is_active' => true,
    ]
);

// Sync Card and Branch to Offer
$offer->cards()->sync([$card->id]);
$offer->branches()->sync([$branch->id]);

// 6. Ensure open register session
$openSession = RegisterSession::where('user_id', $user->id)
    ->where('status', 'open')
    ->first();

if (!$openSession) {
    RegisterSession::create([
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'user_id' => $user->id,
        'opening_balance' => 100.00,
        'opening_time' => now(),
        'status' => 'open',
    ]);
    echo "Opened a new register session.\n";
} else {
    echo "Register session already open.\n";
}

echo "Successfully seeded test branch, product, card, and bank offer!\n";
