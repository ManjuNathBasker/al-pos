<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Company;

$company = Company::find(1);
echo "Company ID: {$company->id}\n";
echo "isModuleEnabled('restaurant_mode'): " . ($company->isModuleEnabled('restaurant_mode') ? 'true' : 'false') . "\n";
echo "Active settings:\n";
print_r($company->moduleSettings()->get()->toArray());
